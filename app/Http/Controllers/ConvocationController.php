<?php

namespace App\Http\Controllers;

use App\Models\Convocation;
use App\Models\Event;
use App\Models\MemberProfile;
use App\Models\User;
use App\Notifications\ConvocationReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ConvocationController extends Controller
{
    public function show(Request $request, Event $event): Response
    {
        $club = $request->user()->resolveClub();
        abort_if(!$club->teams()->where('teams.id', $event->team_id)->exists(), 403);

        $event->load('team:id,name,section_id', 'team.section:id,club_id');

        $teamPlayers = $event->team->players()->get();
        $playerIds = $teamPlayers->pluck('id');

        $profiles = MemberProfile::where('club_id', $club->id)
            ->whereIn('user_id', $playerIds)
            ->get()
            ->keyBy('user_id');

        $existing = Convocation::where('event_id', $event->id)
            ->whereIn('user_id', $playerIds)
            ->get()
            ->keyBy('user_id');

        $players = $teamPlayers->map(fn ($u) => [
            'user_id' => $u->id,
            'first_name' => $profiles->get($u->id)?->first_name ?? $u->name,
            'last_name' => $profiles->get($u->id)?->last_name ?? '',
            'is_convoked' => $existing->has($u->id),
            'status' => $existing->get($u->id)?->status?->value,
            'status_label' => $existing->get($u->id)?->status?->label(),
        ]);

        return Inertia::render('Convocations/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'type_label' => $event->type_label,
                'start_at' => $event->start_at->toIso8601String(),
                'team_name' => $event->team->name,
            ],
            'players' => $players,
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $club = $request->user()->resolveClub();
        abort_if(!$club->teams()->where('teams.id', $event->team_id)->exists(), 403);

        $request->validate([
            'user_ids' => 'present|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $selectedIds = collect($request->user_ids);
        $existingConvocations = Convocation::where('event_id', $event->id)->get()->keyBy('user_id');

        // Add new convocations
        $newUserIds = [];
        foreach ($selectedIds as $userId) {
            if (!$existingConvocations->has($userId)) {
                Convocation::create(['event_id' => $event->id, 'user_id' => $userId]);
                $newUserIds[] = $userId;
            }
        }

        // Notify newly convoked players
        if ($newUserIds) {
            $event->load('team');
            $users = User::whereIn('id', $newUserIds)->get();
            Notification::send($users, new ConvocationReceived($event));
        }

        // Remove deselected (don't touch those still selected)
        foreach ($existingConvocations as $userId => $convocation) {
            if (!$selectedIds->contains($userId)) {
                $convocation->delete();
            }
        }

        return back()->with('success', 'Convocations enregistrées.');
    }
}
