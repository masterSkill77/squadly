<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Game;
use App\Models\Phase;
use App\Models\PlayerStat;
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
}
