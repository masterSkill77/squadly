<?php

namespace App\Http\Controllers;

use App\Enums\CompetitionClubStatus;
use App\Models\Competition;
use App\Services\CompetitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    public function index(Request $request): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer, 403);

        $competitions = $organizer->competitions()
            ->withCount(['competitionClubs as clubs_count' => fn ($q) => $q->where('status', CompetitionClubStatus::Approved)])
            ->latest()
            ->get();

        return Inertia::render('Organizer/Competitions/Index', [
            'competitions' => $competitions,
            'organizer' => $organizer,
        ]);
    }

    public function create(Request $request): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer, 403);

        return Inertia::render('Organizer/Competitions/Create', [
            'organizer' => $organizer,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sport_type' => 'required|string|max:100',
            'season' => 'required|string|max:20',
            'format' => 'required|in:league,cup,group_knockout,custom',
            'description' => 'nullable|string',
            'rules' => 'nullable|array',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'phases' => 'nullable|array',
            'phases.*.name' => 'required|string|max:255',
            'phases.*.type' => 'required|in:group,knockout,round_robin,single',
        ]);

        $competition = $organizer->competitions()->create([
            ...$validated,
            'status' => 'draft',
        ]);

        if (!empty($validated['phases'])) {
            foreach ($validated['phases'] as $i => $phase) {
                $competition->phases()->create([
                    'name' => $phase['name'],
                    'type' => $phase['type'],
                    'order' => $i + 1,
                ]);
            }
        }

        return redirect()->route('organizer.competitions.show', $competition)
            ->with('success', 'Compétition créée.');
    }

    public function show(Request $request, Competition $competition): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competition->load([
            'phases.standings.club',
            'phases.games' => fn ($q) => $q->with(['homeClub', 'awayClub'])->orderBy('scheduled_at'),
            'competitionClubs.club',
        ]);

        $competition->loadCount([
            'competitionClubs as clubs_count' => fn ($q) => $q->where('status', CompetitionClubStatus::Approved),
            'games',
        ]);

        return Inertia::render('Organizer/Competitions/Show', [
            'competition' => $competition,
        ]);
    }

    public function edit(Request $request, Competition $competition): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        return Inertia::render('Organizer/Competitions/Edit', [
            'competition' => $competition,
        ]);
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sport_type' => 'required|string|max:100',
            'season' => 'required|string|max:20',
            'format' => 'required|in:league,cup,group_knockout,custom',
            'status' => 'required|in:draft,open,ongoing,finished',
            'description' => 'nullable|string',
            'rules' => 'nullable|array',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $competition->update($validated);

        return back()->with('success', 'Compétition mise à jour.');
    }

    public function destroy(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competition->delete();

        return redirect()->route('organizer.competitions.index')
            ->with('success', 'Compétition supprimée.');
    }

    public function draw(Request $request, Competition $competition): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competition->load([
            'phases' => fn ($q) => $q->orderBy('order'),
            'phases.competitionClubs.club',
            'competitionClubs' => fn ($q) => $q->where('status', CompetitionClubStatus::Approved)->with('club'),
        ]);

        return Inertia::render('Organizer/Competitions/Draw', [
            'competition' => $competition,
        ]);
    }

    public function performDraw(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $result = CompetitionService::draw($competition);

        return back()->with('success', 'Tirage au sort effectué !')->with('drawResult', $result);
    }

    public function autoGenerate(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        foreach ($competition->phases as $phase) {
            CompetitionService::autoGenerate($phase);
        }

        return redirect()->route('organizer.competitions.show', $competition)
            ->with('success', 'Matchs générés automatiquement !');
    }
}
