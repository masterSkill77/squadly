<?php

namespace App\Http\Controllers;

use App\Enums\CompetitionClubStatus;
use App\Models\Competition;
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

        return Inertia::render('Public/Competition/Show', [
            'competition' => $competition,
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
