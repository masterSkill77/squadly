<?php

namespace App\Http\Controllers;

use App\Enums\CompetitionClubStatus;
use App\Enums\PhaseType;
use App\Models\Competition;
use App\Services\QualificationService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class PublicCompetitionController extends Controller
{
    public function index(): Response
    {
        $competitions = Competition::with('organizer')
            ->whereIn('status', ['open', 'ongoing', 'finished'])
            ->withCount([
                'competitionClubs as clubs_count' => fn ($q) => $q->where('status', CompetitionClubStatus::Approved),
                'games',
            ])
            ->latest()
            ->get();

        return Inertia::render('Public/Competition/Index', [
            'competitions' => $competitions,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function show(Competition $competition): Response
    {
        $competition->load([
            'organizer',
            'phases' => fn ($q) => $q->orderBy('order'),
            'phases.standings' => fn ($q) => $q->orderByDesc('points')->orderByDesc('goals_for'),
            'phases.standings.club',
            'phases.games' => fn ($q) => $q->with(['homeClub', 'awayClub'])->orderBy('scheduled_at'),
        ]);

        $competition->loadCount([
            'competitionClubs as clubs_count' => fn ($q) => $q->where('status', CompetitionClubStatus::Approved),
        ]);

        // Bracket data for knockout phase (prefer the one with a generated bracket)
        $knockoutPhase = $competition->phases
            ->where('type', PhaseType::Knockout)
            ->sortByDesc(fn ($p) => $p->games()->count())
            ->first();
        $bracket = $knockoutPhase
            ? QualificationService::getBracketData($knockoutPhase)
            : ['rounds' => [], 'totalRounds' => 0];

        return Inertia::render('Public/Competition/Show', [
            'competition' => $competition,
            'bracket' => $bracket,
            'knockoutPhase' => $knockoutPhase,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function bracket(Competition $competition): Response
    {
        $knockoutPhase = $competition->phases()
            ->where('type', PhaseType::Knockout)
            ->first();

        $bracket = $knockoutPhase
            ? QualificationService::getBracketData($knockoutPhase)
            : ['rounds' => [], 'totalRounds' => 0];

        return Inertia::render('Public/Competition/Bracket', [
            'competition' => $competition->load('organizer'),
            'bracket' => $bracket,
            'phase' => $knockoutPhase,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function standings(Competition $competition): Response
    {
        $competition->load([
            'organizer',
            'phases' => fn ($q) => $q->orderBy('order'),
            'phases.standings' => fn ($q) => $q->orderByDesc('points')->orderByDesc('goals_for'),
            'phases.standings.club',
        ]);

        return Inertia::render('Public/Competition/Standings', [
            'competition' => $competition,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
