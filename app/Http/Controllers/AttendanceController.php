<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Convocation;
use App\Models\Event;
use App\Models\MemberProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $club = $request->user()->resolveClub();
        $teams = $club->teams()->select('teams.id', 'teams.name')->get();
        $teamIds = $teams->pluck('id');

        $eventIds = Event::whereIn('team_id', $teamIds)->pluck('id');

        $stats = Attendance::selectRaw('user_id, COUNT(*) as total, SUM(status = ?) as present_count, SUM(status = ?) as absent_count, SUM(status = ?) as justified_count', [
            AttendanceStatus::Present->value,
            AttendanceStatus::Absent->value,
            AttendanceStatus::Justified->value,
        ])
            ->whereIn('event_id', $eventIds)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $playerIds = $stats->keys();
        $profiles = MemberProfile::where('club_id', $club->id)
            ->whereIn('user_id', $playerIds)
            ->get()
            ->keyBy('user_id');

        $players = $stats->map(fn ($s, $userId) => [
            'user_id' => $userId,
            'link_id' => $profiles->get($userId)?->id,
            'first_name' => $profiles->get($userId)?->first_name ?? 'Inconnu',
            'last_name' => $profiles->get($userId)?->last_name ?? '',
            'total' => (int) $s->total,
            'present' => (int) $s->present_count,
            'absent' => (int) $s->absent_count,
            'justified' => (int) $s->justified_count,
            'rate' => $s->total > 0 ? round(($s->present_count / $s->total) * 100, 1) : 0,
        ])->values();

        return Inertia::render('Attendance/Index', [
            'players' => $players,
            'teams' => $teams,
        ]);
    }

    public function show(Request $request, Event $event): Response
    {
        $club = $request->user()->resolveClub();
        abort_if(!$club->teams()->where('teams.id', $event->team_id)->exists(), 403);

        $event->load('team:id,name,section_id', 'team.section:id,club_id');

        // Only show convoked players, fallback to all team players if no convocations
        $convokedUserIds = Convocation::where('event_id', $event->id)->pluck('user_id');
        $teamPlayers = $convokedUserIds->isNotEmpty()
            ? $event->team->players()->whereIn('users.id', $convokedUserIds)->get()
            : $event->team->players()->get();
        $playerIds = $teamPlayers->pluck('id');

        $profiles = MemberProfile::where('club_id', $club->id)
            ->whereIn('user_id', $playerIds)
            ->get()
            ->keyBy('user_id');

        $existingAttendances = Attendance::where('event_id', $event->id)
            ->whereIn('user_id', $playerIds)
            ->get()
            ->keyBy('user_id');

        $players = $teamPlayers->map(fn ($u) => [
            'user_id' => $u->id,
            'first_name' => $profiles->get($u->id)?->first_name ?? $u->name,
            'last_name' => $profiles->get($u->id)?->last_name ?? '',
            'status' => $existingAttendances->get($u->id)?->status?->value,
        ]);

        return Inertia::render('Attendance/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'type_label' => $event->type_label,
                'start_at' => $event->start_at->toIso8601String(),
                'team_name' => $event->team->name,
            ],
            'players' => $players,
            'statuses' => collect(AttendanceStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $club = $request->user()->resolveClub();
        abort_if(!$club->teams()->where('teams.id', $event->team_id)->exists(), 403);

        $request->validate([
            'attendances' => 'required|array',
            'attendances.*.user_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|string|in:' . implode(',', array_column(AttendanceStatus::cases(), 'value')),
        ]);

        foreach ($request->attendances as $entry) {
            Attendance::updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $entry['user_id']],
                ['status' => $entry['status']],
            );
        }

        return back()->with('success', 'Présences enregistrées.');
    }
}
