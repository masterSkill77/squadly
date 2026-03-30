<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $club = $request->user()->resolveClub();
        $teamIds = $club->teams()->pluck('teams.id');

        $events = Event::whereIn('team_id', $teamIds)
            ->with('team:id,name,section_id', 'team.section:id,sport_type')
            ->orderBy('start_at')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'type' => $e->type->value,
                'type_label' => $e->type_label,
                'custom_type' => $e->custom_type,
                'location' => $e->location,
                'description' => $e->description,
                'start_at' => $e->start_at->toIso8601String(),
                'end_at' => $e->end_at?->toIso8601String(),
                'team_id' => $e->team_id,
                'team_name' => $e->team->name,
                'sport_type' => $e->team->section->sport_type,
            ]);

        $teams = $club->teams()->select('teams.id', 'teams.name')->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'teams' => $teams,
            'eventTypes' => collect(EventType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $club = $request->user()->resolveClub();

        $request->validate([
            'team_ids' => 'required|array|min:1',
            'team_ids.*' => 'exists:teams,id',
            'type' => 'required|string',
            'custom_type' => 'nullable|required_if:type,other|string|max:255',
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'description' => 'nullable|string|max:2000',
        ]);

        $clubTeamIds = $club->teams()->pluck('teams.id');
        $fields = $request->only('type', 'custom_type', 'title', 'location', 'start_at', 'end_at', 'description');

        foreach ($request->team_ids as $teamId) {
            abort_if(!$clubTeamIds->contains($teamId), 403);
            Event::create(['team_id' => $teamId, ...$fields]);
        }

        $count = count($request->team_ids);
        $msg = $count > 1 ? "$count événements créés avec succès." : 'Événement créé avec succès.';

        return back()->with('success', $msg);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $club = $request->user()->resolveClub();
        abort_if(!$club->teams()->where('teams.id', $event->team_id)->exists(), 403);

        $request->validate([
            'type' => 'required|string',
            'custom_type' => 'nullable|required_if:type,other|string|max:255',
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'description' => 'nullable|string|max:2000',
        ]);

        $event->update($request->only(
            'type', 'custom_type', 'title',
            'location', 'start_at', 'end_at', 'description',
        ));

        return back()->with('success', 'Événement mis à jour.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $club = $request->user()->resolveClub();
        abort_if(!$club->teams()->where('teams.id', $event->team_id)->exists(), 403);

        $event->delete();

        return back()->with('success', 'Événement supprimé.');
    }
}
