<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Event;
use App\Models\Game;
use App\Models\MemberProfile;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $user = $request->user();
        $role = $user->getRoleNames()->first();
        $results = [];
        $like = "%{$query}%";

        if (in_array($role, ['organizer_admin', 'organizer_staff'])) {
            $organizer = $user->resolveOrganizer();
            if ($organizer) {
                // Competitions
                $competitions = Competition::where('organizer_id', $organizer->id)
                    ->where('name', 'like', $like)
                    ->limit(5)->get();
                foreach ($competitions as $c) {
                    $results[] = [
                        'type' => 'competition',
                        'label' => $c->name,
                        'sublabel' => $c->season . ' — ' . $c->sport_type,
                        'url' => route('organizer.competitions.show', $c->id),
                    ];
                }

                // Games (by club name)
                $games = Game::whereHas('phase.competition', fn ($q) => $q->where('organizer_id', $organizer->id))
                    ->where(fn ($q) => $q
                        ->whereHas('homeClub', fn ($q) => $q->where('name', 'like', $like))
                        ->orWhereHas('awayClub', fn ($q) => $q->where('name', 'like', $like)))
                    ->with(['homeClub', 'awayClub'])
                    ->limit(5)->get();
                foreach ($games as $g) {
                    $results[] = [
                        'type' => 'match',
                        'label' => ($g->homeClub->name ?? '?') . ' vs ' . ($g->awayClub->name ?? '?'),
                        'sublabel' => $g->scheduled_at?->format('d/m/Y H:i'),
                        'url' => route('organizer.matches.score', $g->id),
                    ];
                }
            }
        } else {
            $club = $user->resolveClub();
            if (!$club) {
                return response()->json([]);
            }

            // Members
            $members = MemberProfile::where('club_id', $club->id)
                ->where(fn ($q) => $q->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like))
                ->limit(5)->get();
            foreach ($members as $m) {
                $results[] = [
                    'type' => 'member',
                    'label' => $m->first_name . ' ' . $m->last_name,
                    'sublabel' => 'Membre',
                    'url' => route('members.show', $m->user_id),
                ];
            }

            // Teams
            $teams = Team::whereHas('section', fn ($q) => $q->where('club_id', $club->id))
                ->where('name', 'like', $like)
                ->limit(5)->get();
            foreach ($teams as $t) {
                $results[] = [
                    'type' => 'team',
                    'label' => $t->name,
                    'sublabel' => $t->age_category ?? 'Équipe',
                    'url' => $role === 'coach'
                        ? route('coach.team', $t->id)
                        : route('club.show'),
                ];
            }

            // Events
            $teamIds = $club->teams()->pluck('teams.id');
            $events = Event::whereIn('team_id', $teamIds)
                ->where('title', 'like', $like)
                ->with('team:id,name')
                ->limit(5)->get();
            foreach ($events as $e) {
                $results[] = [
                    'type' => 'event',
                    'label' => $e->title,
                    'sublabel' => $e->team->name . ' — ' . $e->start_at->format('d/m/Y'),
                    'url' => route('events.index'),
                ];
            }

            // Competitions
            $competitions = Competition::where('name', 'like', $like)
                ->whereHas('competitionClubs', fn ($q) => $q->where('club_id', $club->id))
                ->limit(5)->get();
            foreach ($competitions as $c) {
                $results[] = [
                    'type' => 'competition',
                    'label' => $c->name,
                    'sublabel' => $c->season,
                    'url' => route('club.competitions.show', $c->id),
                ];
            }
        }

        return response()->json(array_slice($results, 0, 10));
    }
}
