<?php

namespace App\Services;

use App\Enums\CompetitionClubStatus;
use App\Enums\GameStatus;
use App\Enums\PhaseStatus;
use App\Enums\PhaseType;
use App\Models\Competition;
use App\Models\CompetitionClub;
use App\Models\Game;
use App\Models\Phase;
use App\Models\Standing;

class QualificationService
{
    /**
     * Get the French label for a knockout round.
     */
    public static function getRoundLabel(int $round): string
    {
        return match ($round) {
            1 => 'Finale',
            2 => 'Demi-finales',
            3 => 'Quarts de finale',
            4 => 'Huitièmes de finale',
            5 => 'Seizièmes de finale',
            default => "Tour $round",
        };
    }

    /**
     * Qualify teams from finished group phases and generate the knockout bracket.
     * Only proceeds when ALL group phases in the competition are finished.
     */
    public static function qualifyFromGroups(Competition $competition): ?Phase
    {
        $groupPhases = $competition->phases()
            ->whereIn('type', [PhaseType::Group, PhaseType::RoundRobin])
            ->get();

        if ($groupPhases->isEmpty()) {
            return null;
        }

        // Wait until all group phases are finished
        $allFinished = $groupPhases->every(fn (Phase $p) => $p->status === PhaseStatus::Finished);
        if (! $allFinished) {
            return null;
        }

        // Check if bracket was already generated for an existing knockout phase
        $existingKnockout = $competition->phases()
            ->where('type', PhaseType::Knockout)
            ->first();

        if ($existingKnockout && $existingKnockout->source_phase_id) {
            return null;
        }

        // Collect qualified teams from each group, ranked by position
        // $groupResults[position][groupIndex] = club_id
        // e.g. $groupResults[0] = [1stOfA, 1stOfB, 1stOfC, 1stOfD]
        //      $groupResults[1] = [2ndOfA, 2ndOfB, 2ndOfC, 2ndOfD]
        $groupResults = [];

        foreach ($groupPhases as $groupIndex => $groupPhase) {
            $standings = Standing::where('phase_id', $groupPhase->id)
                ->orderByDesc('points')
                ->orderByDesc('goals_for')
                ->orderBy('goals_against')
                ->limit($groupPhase->qualify_count)
                ->pluck('club_id')
                ->toArray();

            foreach ($standings as $position => $clubId) {
                $groupResults[$position][$groupIndex] = $clubId;
            }
        }

        if (empty($groupResults)) {
            return null;
        }

        // Cross-seed: 1st of group A vs 2nd of group B, 1st of B vs 2nd of A, etc.
        $seededClubIds = self::crossSeed($groupResults, $groupPhases->count());

        if (count($seededClubIds) < 2) {
            return null;
        }

        // Use existing knockout phase or create a new one
        if ($existingKnockout) {
            $knockoutPhase = $existingKnockout;
            $knockoutPhase->update([
                'status' => PhaseStatus::Pending,
                'source_phase_id' => $groupPhases->first()->id,
            ]);
        } else {
            $maxOrder = $competition->phases()->max('order') ?? 0;
            $knockoutPhase = $competition->phases()->create([
                'name' => 'Phase finale',
                'type' => PhaseType::Knockout,
                'order' => $maxOrder + 1,
                'status' => PhaseStatus::Pending,
                'source_phase_id' => $groupPhases->first()->id,
            ]);
        }

        // Register qualified clubs to this phase
        foreach ($seededClubIds as $clubId) {
            CompetitionClub::updateOrCreate(
                ['competition_id' => $competition->id, 'club_id' => $clubId],
                ['phase_id' => $knockoutPhase->id, 'status' => CompetitionClubStatus::Approved],
            );
        }

        // Generate the full bracket tree
        self::generateBracket($knockoutPhase, $seededClubIds);

        return $knockoutPhase;
    }

    /**
     * Cross-seed qualified teams so that 1st of group A faces 2nd of group B, etc.
     *
     * With 2 groups (A, B) and 2 qualifiers:
     *   1A vs 2B, 1B vs 2A
     *
     * With 4 groups (A, B, C, D) and 2 qualifiers:
     *   1A vs 2B, 1C vs 2D, 1B vs 2A, 1D vs 2C
     *
     * Returns a flat array of club IDs in bracket order (paired: 0v1, 2v3...).
     */
    private static function crossSeed(array $groupResults, int $groupCount): array
    {
        $firsts = $groupResults[0] ?? [];
        $seconds = $groupResults[1] ?? [];

        // If only 1 qualifier per group, no crossing possible
        if (empty($seconds)) {
            return array_values($firsts);
        }

        $seeded = [];

        // Pair groups: A↔B, C↔D, E↔F...
        for ($i = 0; $i < $groupCount; $i += 2) {
            $oppositeIndex = $i + 1;

            // 1st of group i vs 2nd of opposite group
            if (isset($firsts[$i])) {
                $seeded[] = $firsts[$i];
                $seeded[] = $seconds[$oppositeIndex] ?? $seconds[$i] ?? null;
            }

            // 1st of opposite group vs 2nd of group i
            if (isset($firsts[$oppositeIndex])) {
                $seeded[] = $firsts[$oppositeIndex];
                $seeded[] = $seconds[$i] ?? null;
            }
        }

        // Handle odd number of groups (last group has no pair)
        if ($groupCount % 2 !== 0) {
            $lastIndex = $groupCount - 1;
            if (isset($firsts[$lastIndex]) && ! in_array($firsts[$lastIndex], $seeded)) {
                $seeded[] = $firsts[$lastIndex];
                $seeded[] = $seconds[$lastIndex] ?? null;
            }
        }

        // Add any remaining qualifiers (3rd place etc.) at the end
        foreach ($groupResults as $position => $clubs) {
            if ($position <= 1) continue;
            foreach ($clubs as $clubId) {
                $seeded[] = $clubId;
            }
        }

        return array_filter($seeded, fn ($id) => $id !== null);
    }

    /**
     * Generate a complete knockout bracket tree.
     * Creates all rounds upfront, with feeder game links.
     */
    public static function generateBracket(Phase $phase, array $clubIds): void
    {
        // Clear any existing games
        Game::where('phase_id', $phase->id)->delete();

        $count = count($clubIds);
        if ($count < 2) {
            return;
        }

        // Pad to next power of 2 for byes
        $bracketSize = 1;
        while ($bracketSize < $count) {
            $bracketSize *= 2;
        }

        // Pad with nulls (byes)
        while (count($clubIds) < $bracketSize) {
            $clubIds[] = null;
        }

        $totalRounds = (int) (log($bracketSize) / log(2));
        $competition = $phase->competition;
        $startDate = $competition->starts_at ?? now();

        // Create first round games (the outermost round)
        $firstRoundGames = [];
        $gamesPerRound = $bracketSize / 2;

        for ($i = 0; $i < $gamesPerRound; $i++) {
            $homeClubId = $clubIds[$i * 2];
            $awayClubId = $clubIds[$i * 2 + 1];

            $isBye = $homeClubId === null || $awayClubId === null;

            $game = Game::create([
                'phase_id' => $phase->id,
                'round' => $totalRounds,
                'bracket_position' => $i + 1,
                'home_club_id' => $homeClubId,
                'away_club_id' => $awayClubId,
                'scheduled_at' => $startDate->copy()->addWeeks($totalRounds - 1),
                'status' => $isBye ? GameStatus::Finished : GameStatus::Scheduled,
                'home_score' => $isBye ? ($homeClubId ? 0 : null) : null,
                'away_score' => $isBye ? ($awayClubId ? 0 : null) : null,
            ]);

            $firstRoundGames[] = $game;
        }

        // Create subsequent rounds (working inward to the final)
        $previousRoundGames = $firstRoundGames;

        for ($round = $totalRounds - 1; $round >= 1; $round--) {
            $currentRoundGames = [];
            $gamesInRound = count($previousRoundGames) / 2;

            for ($i = 0; $i < $gamesInRound; $i++) {
                $homeFeeder = $previousRoundGames[$i * 2];
                $awayFeeder = $previousRoundGames[$i * 2 + 1];

                $game = Game::create([
                    'phase_id' => $phase->id,
                    'round' => $round,
                    'bracket_position' => $i + 1,
                    'home_feeder_game_id' => $homeFeeder->id,
                    'away_feeder_game_id' => $awayFeeder->id,
                    'home_club_id' => null,
                    'away_club_id' => null,
                    'scheduled_at' => $startDate->copy()->addWeeks($totalRounds - $round + 1),
                    'status' => GameStatus::Scheduled,
                ]);

                $currentRoundGames[] = $game;
            }

            $previousRoundGames = $currentRoundGames;
        }

        // Propagate bye winners immediately
        self::propagateByes($phase);
    }

    /**
     * Propagate bye winners through the bracket.
     */
    private static function propagateByes(Phase $phase): void
    {
        $byeGames = Game::where('phase_id', $phase->id)
            ->where('status', GameStatus::Finished)
            ->whereNotNull('round')
            ->get();

        foreach ($byeGames as $game) {
            $winnerId = $game->home_club_id ?? $game->away_club_id;
            if ($winnerId) {
                self::advanceWinner($game);
            }
        }
    }

    /**
     * After a knockout game finishes, advance the winner to the next round.
     */
    public static function advanceWinner(Game $game): void
    {
        $winnerId = $game->winner_id;
        if (! $winnerId) {
            return;
        }

        // Find the next game that this game feeds into
        $nextGame = Game::where('phase_id', $game->phase_id)
            ->where(function ($q) use ($game) {
                $q->where('home_feeder_game_id', $game->id)
                    ->orWhere('away_feeder_game_id', $game->id);
            })
            ->first();

        if (! $nextGame) {
            // This was the final — mark phase as finished
            if ($game->round === 1) {
                $game->phase->update(['status' => PhaseStatus::Finished]);
            }
            return;
        }

        if ($nextGame->home_feeder_game_id === $game->id) {
            $nextGame->update(['home_club_id' => $winnerId]);
        } else {
            $nextGame->update(['away_club_id' => $winnerId]);
        }
    }

    /**
     * Build the bracket data structure for frontend rendering.
     */
    public static function getBracketData(Phase $phase): array
    {
        $games = Game::where('phase_id', $phase->id)
            ->whereNotNull('round')
            ->with(['homeClub', 'awayClub'])
            ->orderBy('round', 'desc')
            ->orderBy('bracket_position')
            ->get();

        if ($games->isEmpty()) {
            return ['rounds' => [], 'totalRounds' => 0];
        }

        $totalRounds = $games->max('round');
        $rounds = [];

        for ($r = $totalRounds; $r >= 1; $r--) {
            $roundGames = $games->where('round', $r)->values();

            $rounds[] = [
                'round' => $r,
                'label' => self::getRoundLabel($r),
                'games' => $roundGames->map(fn (Game $g) => [
                    'id' => $g->id,
                    'bracket_position' => $g->bracket_position,
                    'home_club' => $g->homeClub ? [
                        'id' => $g->homeClub->id,
                        'name' => $g->homeClub->name,
                    ] : null,
                    'away_club' => $g->awayClub ? [
                        'id' => $g->awayClub->id,
                        'name' => $g->awayClub->name,
                    ] : null,
                    'home_score' => $g->home_score,
                    'away_score' => $g->away_score,
                    'status' => $g->status->value,
                    'scheduled_at' => $g->scheduled_at?->toISOString(),
                    'winner_id' => $g->winner_id,
                ])->toArray(),
            ];
        }

        return [
            'rounds' => $rounds,
            'totalRounds' => $totalRounds,
        ];
    }
}
