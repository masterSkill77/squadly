<?php

namespace App\Http\Controllers;

use App\Enums\PhaseType;
use App\Models\Competition;
use App\Models\Game;
use App\Models\PlayerStat;
use App\Services\QualificationService;
use App\Services\StandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function index(Request $request, Competition $competition): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competition->load([
            'phases.games' => fn ($q) => $q->with(['homeClub', 'awayClub'])->orderBy('scheduled_at'),
            'competitionClubs' => fn ($q) => $q->where('status', 'approved')->with('club'),
        ]);

        return Inertia::render('Organizer/Competitions/Matches', [
            'competition' => $competition,
        ]);
    }

    public function store(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $validated = $request->validate([
            'phase_id' => 'required|exists:phases,id',
            'home_club_id' => 'required|exists:clubs,id',
            'away_club_id' => 'required|exists:clubs,id|different:home_club_id',
            'scheduled_at' => 'required|date',
            'location' => 'nullable|string|max:255',
        ]);

        Game::create([
            ...$validated,
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Match programmé.');
    }

    public function score(Request $request, Game $game): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer, 403);

        $game->load(['homeClub', 'awayClub', 'phase.competition', 'playerStats']);

        return Inertia::render('Organizer/Matches/Score', [
            'game' => $game,
        ]);
    }

    public function updateScore(Request $request, Game $game): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer, 403);

        $validated = $request->validate([
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
            'player_stats' => 'nullable|array',
            'player_stats.*.user_id' => 'required|exists:users,id',
            'player_stats.*.club_id' => 'required|exists:clubs,id',
            'player_stats.*.goals' => 'integer|min:0',
            'player_stats.*.assists' => 'integer|min:0',
            'player_stats.*.yellow_cards' => 'integer|min:0',
            'player_stats.*.red_cards' => 'integer|min:0',
            'player_stats.*.minutes_played' => 'nullable|integer|min:0',
        ]);

        // Block draws in knockout phases
        $phase = $game->phase;
        if ($phase->type === PhaseType::Knockout && $game->round !== null) {
            if ($validated['home_score'] === $validated['away_score']) {
                return back()->withErrors([
                    'home_score' => 'Un match à élimination directe ne peut pas se terminer par un nul.',
                ]);
            }
        }

        $game->update([
            'home_score' => $validated['home_score'],
            'away_score' => $validated['away_score'],
            'status' => 'finished',
        ]);

        if (!empty($validated['player_stats'])) {
            foreach ($validated['player_stats'] as $stat) {
                PlayerStat::updateOrCreate(
                    ['game_id' => $game->id, 'user_id' => $stat['user_id']],
                    $stat
                );
            }
        }

        // Recalculate standings for group/round_robin phases
        if (in_array($phase->type, [PhaseType::Group, PhaseType::RoundRobin])) {
            StandingService::recalculate($phase->id);
        }

        // Advance winner in knockout bracket
        if ($phase->type === PhaseType::Knockout && $game->round !== null) {
            QualificationService::advanceWinner($game);
        }

        return back()->with('success', 'Score enregistré.');
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer, 403);

        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date',
            'location' => 'nullable|string|max:255',
            'status' => 'sometimes|in:scheduled,ongoing,finished,cancelled',
        ]);

        $game->update($validated);

        return back()->with('success', 'Match mis à jour.');
    }

    public function destroy(Request $request, Game $game): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer, 403);

        $game->delete();

        return back()->with('success', 'Match supprimé.');
    }

    /**
     * DEV/TEST: Generate random scores for all scheduled games.
     */
    public function simulateScores(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        \App\Jobs\SimulateScoresJob::dispatch($competition->id);

        return back()->with('success', 'Simulation des scores en cours…');
    }

    public function bracket(Request $request, Competition $competition): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $knockoutPhase = $competition->phases()
            ->where('type', PhaseType::Knockout)
            ->whereNotNull('source_phase_id')
            ->first();

        // Also check for standalone knockout phases
        if (! $knockoutPhase) {
            $knockoutPhase = $competition->phases()
                ->where('type', PhaseType::Knockout)
                ->first();
        }

        $bracket = $knockoutPhase
            ? QualificationService::getBracketData($knockoutPhase)
            : ['rounds' => [], 'totalRounds' => 0];

        return Inertia::render('Organizer/Competitions/Bracket', [
            'competition' => $competition->load('organizer'),
            'bracket' => $bracket,
            'phase' => $knockoutPhase,
        ]);
    }
}
