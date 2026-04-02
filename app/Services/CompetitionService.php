<?php

namespace App\Services;

use App\Enums\CompetitionClubStatus;
use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionClub;
use App\Models\Game;
use App\Models\Phase;

class CompetitionService
{
    /**
     * Perform a random draw: shuffle approved clubs and assign them to phases.
     * Returns the draw result for animation purposes.
     */
    public static function draw(Competition $competition): array
    {
        $clubs = $competition->competitionClubs()
            ->where('status', CompetitionClubStatus::Approved)
            ->with('club')
            ->get();

        $shuffled = $clubs->shuffle()->values();
        $phases = $competition->phases()->orderBy('order')->get();

        if ($phases->isEmpty() || $shuffled->isEmpty()) {
            return ['phases' => [], 'draws' => []];
        }

        $draws = [];
        $perPhase = (int) ceil($shuffled->count() / $phases->count());

        foreach ($phases as $i => $phase) {
            $chunk = $shuffled->slice($i * $perPhase, $perPhase);
            $phaseDraws = [];

            foreach ($chunk as $cc) {
                $cc->update(['phase_id' => $phase->id]);
                $phaseDraws[] = [
                    'club_id' => $cc->club_id,
                    'club_name' => $cc->club->name,
                    'club_city' => $cc->club->city,
                ];
            }

            $draws[] = [
                'phase_id' => $phase->id,
                'phase_name' => $phase->name,
                'clubs' => $phaseDraws,
            ];
        }

        return ['phases' => $draws, 'total' => $shuffled->count()];
    }

    /**
     * Generate a knockout bracket for a phase.
     * Clubs are paired randomly: 1v2, 3v4, etc.
     */
    public static function generateKnockout(Phase $phase): void
    {
        $clubIds = CompetitionClub::where('phase_id', $phase->id)
            ->where('status', CompetitionClubStatus::Approved)
            ->pluck('club_id')
            ->shuffle()
            ->toArray();

        if (count($clubIds) < 2) {
            return;
        }

        Game::where('phase_id', $phase->id)->delete();

        $competition = $phase->competition;
        $startDate = $competition->starts_at;

        // Pair clubs: 0v1, 2v3, 4v5...
        for ($i = 0; $i + 1 < count($clubIds); $i += 2) {
            Game::create([
                'phase_id' => $phase->id,
                'home_club_id' => $clubIds[$i],
                'away_club_id' => $clubIds[$i + 1],
                'scheduled_at' => $startDate->copy()->addWeeks((int) ($i / 2)),
                'status' => 'scheduled',
            ]);
        }
    }

    /**
     * Generate a round-robin schedule for a given phase.
     */
    public static function generateSchedule(Phase $phase): void
    {
        $clubIds = CompetitionClub::where('phase_id', $phase->id)
            ->where('status', CompetitionClubStatus::Approved)
            ->pluck('club_id')
            ->toArray();

        if (count($clubIds) < 2) {
            return;
        }

        Game::where('phase_id', $phase->id)->delete();

        $competition = $phase->competition;
        $startDate = $competition->starts_at;
        $matchDay = 0;

        for ($i = 0; $i < count($clubIds); $i++) {
            for ($j = $i + 1; $j < count($clubIds); $j++) {
                Game::create([
                    'phase_id' => $phase->id,
                    'home_club_id' => $clubIds[$i],
                    'away_club_id' => $clubIds[$j],
                    'scheduled_at' => $startDate->copy()->addWeeks($matchDay),
                    'status' => 'scheduled',
                ]);

                $matchDay++;
            }
        }
    }

    /**
     * Auto-generate matches based on phase type.
     */
    public static function autoGenerate(Phase $phase): void
    {
        match ($phase->type->value) {
            'knockout', 'single' => self::generateKnockout($phase),
            default => self::generateSchedule($phase),
        };
    }
}
