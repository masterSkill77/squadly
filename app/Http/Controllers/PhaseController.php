<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Phase;
use App\Services\CompetitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhaseController extends Controller
{
    public function index(Request $request, Competition $competition): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competition->load([
            'phases' => fn ($q) => $q->orderBy('order')->withCount('games'),
            'phases.standings.club',
        ]);

        return Inertia::render('Organizer/Competitions/Phases', [
            'competition' => $competition,
        ]);
    }

    public function store(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:group,knockout,round_robin,single',
        ]);

        $maxOrder = $competition->phases()->max('order') ?? 0;

        $competition->phases()->create([
            ...$validated,
            'order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Phase ajoutée.');
    }

    public function update(Request $request, Competition $competition, Phase $phase): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:group,knockout,round_robin,single',
            'status' => 'sometimes|in:pending,ongoing,finished',
        ]);

        $phase->update($validated);

        return back()->with('success', 'Phase mise à jour.');
    }

    public function destroy(Request $request, Competition $competition, Phase $phase): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $phase->delete();

        return back()->with('success', 'Phase supprimée.');
    }

    public function generateSchedule(Request $request, Competition $competition, Phase $phase): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        CompetitionService::generateSchedule($phase);

        return back()->with('success', 'Calendrier généré.');
    }
}
