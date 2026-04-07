<?php

namespace App\Http\Controllers;

use App\Enums\PhaseStatus;
use App\Enums\PhaseType;
use App\Jobs\GenerateScheduleJob;
use App\Models\Competition;
use App\Models\Phase;
use App\Services\QualificationService;
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
            'qualify_count' => 'sometimes|integer|min:1|max:32',
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
            'qualify_count' => 'sometimes|integer|min:1|max:32',
        ]);

        $phase->update($validated);

        // When a group/round_robin phase is finished, try to qualify teams
        if ($phase->wasChanged('status') && $phase->status === PhaseStatus::Finished) {
            if (in_array($phase->type, [PhaseType::Group, PhaseType::RoundRobin])) {
                $knockoutPhase = QualificationService::qualifyFromGroups($competition);
                if ($knockoutPhase) {
                    return back()->with('success', 'Phase terminée. Phase finale générée automatiquement !');
                }
            }
        }

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

        $config = $request->validate([
            'parallel_matches' => 'sometimes|integer|min:1|max:10',
            'interval_minutes' => 'sometimes|integer|min:30|max:1440',
            'start_time' => 'sometimes|string',
            'days_between' => 'sometimes|integer|min:1|max:30',
        ]);

        GenerateScheduleJob::dispatch($phase->id, $config);

        return back()->with('success', 'Génération du calendrier en cours…');
    }
}
