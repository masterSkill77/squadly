<?php

namespace App\Http\Controllers;

use App\Enums\EventType;
use App\Models\Event;
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
            ]);

        $teams = $user->teams()->select('teams.id', 'teams.name')->get();

        return Inertia::render('Membre/Events', [
            'events' => $events,
            'teams' => $teams,
        ]);
    }
}
