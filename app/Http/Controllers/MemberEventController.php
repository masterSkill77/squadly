<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberEventController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $teamIds = $user->teams()->pluck('teams.id');

        $events = Event::whereIn('team_id', $teamIds)
            ->with('team:id,name')
            ->orderBy('start_at')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'type' => $e->type->value,
                'type_label' => $e->type_label,
                'location' => $e->location,
                'description' => $e->description,
                'start_at' => $e->start_at->toIso8601String(),
                'end_at' => $e->end_at?->toIso8601String(),
                'team_id' => $e->team_id,
                'team_name' => $e->team->name,
                'source' => 'event',
            ]);

        // Competition games
        $club = $user->resolveClub();
        if ($club) {
            $competitionGames = Game::where(fn ($q) => $q->where('home_club_id', $club->id)->orWhere('away_club_id', $club->id))
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->with(['homeClub', 'awayClub', 'phase.competition'])
                ->orderBy('scheduled_at')
                ->get()
                ->map(fn ($g) => [
                    'id' => 'game-' . $g->id,
                    'title' => ($g->homeClub->name ?? '?') . ' vs ' . ($g->awayClub->name ?? '?'),
                    'type' => 'competition',
                    'type_label' => 'Compétition',
                    'location' => $g->location,
                    'description' => $g->phase?->competition?->name . ' — ' . $g->phase?->name,
                    'start_at' => $g->scheduled_at->toIso8601String(),
                    'end_at' => null,
                    'team_id' => null,
                    'team_name' => $g->phase?->competition?->name,
                    'source' => 'competition',
                ]);
            $events = $events->concat($competitionGames)->sortBy('start_at')->values();
        }

        $teams = $user->teams()->select('teams.id', 'teams.name')->get();

        return Inertia::render('Membre/Events', [
            'events' => $events,
            'teams' => $teams,
        ]);
    }
}
