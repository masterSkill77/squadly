<?php

namespace App\Http\Controllers;

use App\Enums\ConvocationStatus;
use App\Models\Convocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberConvocationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $convocations = Convocation::where('user_id', $user->id)
            ->whereHas('event', fn ($q) => $q->where('start_at', '>=', now()))
            ->with('event.team:id,name')
            ->get()
            ->sortBy(fn ($c) => $c->event->start_at)
            ->map(fn ($c) => [
                'id' => $c->id,
                'status' => $c->status->value,
                'status_label' => $c->status->label(),
                'event_title' => $c->event->title,
                'event_type_label' => $c->event->type_label,
                'event_start_at' => $c->event->start_at->toIso8601String(),
                'event_location' => $c->event->location,
                'team_name' => $c->event->team->name,
            ])
            ->values();

        return Inertia::render('Membre/Convocations', [
            'convocations' => $convocations,
        ]);
    }

    public function respond(Request $request, Convocation $convocation): RedirectResponse
    {
        abort_if($convocation->user_id !== $request->user()->id, 403);

        $request->validate([
            'status' => 'required|string|in:' . ConvocationStatus::Confirmed->value . ',' . ConvocationStatus::Declined->value,
        ]);

        $convocation->update(['status' => $request->status]);

        return back()->with('success', 'Réponse enregistrée.');
    }
}
