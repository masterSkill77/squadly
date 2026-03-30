<?php

namespace App\Http\Controllers;

use App\Enums\ConvocationStatus;
use App\Enums\Role;
use App\Models\Announcement;
use App\Models\Convocation;
use App\Models\Event;
use App\Models\MemberProfile;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first() ?? Role::Membre->value;

        if ($role === Role::Admin->value && !$user->hasClub()) {
            return redirect()->route('onboarding');
        }

        $data = [
            'role' => $role,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'hasCompletedOnboarding' => $user->has_completed_onboarding ?? false,
        ];

        $data['latestAnnouncements'] = Announcement::visibleTo($user)
            ->with('author:id,name')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => Str::limit($a->content, 100),
                'author_name' => $a->author->name,
                'created_at' => $a->created_at->toIso8601String(),
            ]);

        if ($role === Role::Admin->value) {
            $club = $user->club?->loadCount(['sections', 'teams', 'memberProfiles']);
            $eventsCount = $club ? Event::whereIn('team_id', $club->teams()->pluck('teams.id'))->count() : 0;
            $nextEvent = $club ? Event::whereIn('team_id', $club->teams()->pluck('teams.id'))
                ->where('start_at', '>=', now())
                ->orderBy('start_at')
                ->with('team:id,name')
                ->first() : null;

            $data['club'] = $club ? [
                'name' => $club->name,
                'city' => $club->city,
                'sections_count' => $club->sections_count,
                'teams_count' => $club->teams_count,
                'members_count' => $club->member_profiles_count,
                'events_count' => $eventsCount,
            ] : null;
            $data['nextEvent'] = $nextEvent ? [
                'title' => $nextEvent->title,
                'type_label' => $nextEvent->type_label,
                'start_at' => $nextEvent->start_at->toIso8601String(),
                'team_name' => $nextEvent->team->name,
            ] : null;
        }

        if ($role === Role::Coach->value) {
            $memberProfile = MemberProfile::where('user_id', $user->id)->first();
            $teams = $user->teams()->with('section:id,sport_type')->withCount('players')->get();
            $teamIds = $teams->pluck('id');

            $nextEvent = Event::whereIn('team_id', $teamIds)
                ->where('start_at', '>=', now())
                ->orderBy('start_at')
                ->with('team:id,name')
                ->first();

            $data['club'] = $memberProfile ? [
                'name' => $memberProfile->club->name,
                'city' => $memberProfile->club->city,
            ] : null;
            $data['coachTeams'] = $teams->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'age_category' => $t->age_category,
                'sport_type' => $t->section->sport_type,
                'players_count' => $t->players_count,
            ]);
            $data['nextEvent'] = $nextEvent ? [
                'title' => $nextEvent->title,
                'type_label' => $nextEvent->type_label,
                'start_at' => $nextEvent->start_at->toIso8601String(),
                'team_name' => $nextEvent->team->name,
            ] : null;
        }

        if ($role === Role::Membre->value) {
            $memberProfile = MemberProfile::where('user_id', $user->id)->first();
            $teams = $user->teams()->with('section:id,sport_type')->get();
            $teamIds = $teams->pluck('id');

            $nextEvent = Event::whereIn('team_id', $teamIds)
                ->where('start_at', '>=', now())
                ->orderBy('start_at')
                ->with('team:id,name')
                ->first();

            $data['club'] = $memberProfile ? [
                'name' => $memberProfile->club->name,
            ] : null;
            $data['myTeams'] = $teams->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'sport_type' => $t->section->sport_type,
            ]);
            $data['nextEvent'] = $nextEvent ? [
                'title' => $nextEvent->title,
                'type_label' => $nextEvent->type_label,
                'start_at' => $nextEvent->start_at->toIso8601String(),
                'team_name' => $nextEvent->team->name,
            ] : null;

            $data['pendingConvocations'] = Convocation::where('user_id', $user->id)
                ->where('status', ConvocationStatus::Pending->value)
                ->whereHas('event', fn ($q) => $q->where('start_at', '>=', now()))
                ->with('event.team:id,name')
                ->get()
                ->sortBy(fn ($c) => $c->event->start_at)
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'event_title' => $c->event->title,
                    'event_start_at' => $c->event->start_at->toIso8601String(),
                    'team_name' => $c->event->team->name,
                ])->values();
        }

        return Inertia::render('Dashboard', $data);
    }
}
