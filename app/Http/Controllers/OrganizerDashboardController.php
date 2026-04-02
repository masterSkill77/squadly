<?php

namespace App\Http\Controllers;

use App\Enums\CompetitionClubStatus;
use App\Enums\CompetitionStatus;
use App\Enums\GameStatus;
use App\Models\Organizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizerDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $organizer = $request->user()->resolveOrganizer();

        if (!$organizer) {
            return Inertia::render('Organizer/Setup', [
                'user' => $request->user()->only('id', 'name', 'email'),
            ]);
        }

        $competitions = $organizer->competitions()
            ->withCount(['competitionClubs as clubs_count' => fn ($q) => $q->where('status', CompetitionClubStatus::Approved)])
            ->latest()
            ->get();

        $activeCompetitions = $competitions->whereIn('status', [CompetitionStatus::Open, CompetitionStatus::Ongoing]);

        $pendingRegistrations = $organizer->competitions()
            ->with(['competitionClubs' => fn ($q) => $q->where('status', CompetitionClubStatus::Pending)->with(['club', 'competition'])])
            ->get()
            ->pluck('competitionClubs')
            ->flatten();

        $upcomingGames = $organizer->competitions()
            ->with(['games' => fn ($q) => $q->where('games.status', GameStatus::Scheduled)
                ->with(['homeClub', 'awayClub', 'phase.competition'])
                ->orderBy('scheduled_at')
                ->limit(10)])
            ->get()
            ->pluck('games')
            ->flatten()
            ->sortBy('scheduled_at')
            ->take(10)
            ->values();

        return Inertia::render('Organizer/Dashboard', [
            'organizer' => $organizer,
            'competitions' => $competitions,
            'activeCount' => $activeCompetitions->count(),
            'pendingRegistrations' => $pendingRegistrations,
            'upcomingGames' => $upcomingGames,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sport_type' => 'required|string|max:100',
            'contact_email' => 'required|email|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        $organizer = Organizer::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        $organizer->users()->attach($request->user(), ['role' => 'admin']);

        return redirect()->route('organizer.dashboard')->with('success', 'Organisation créée !');
    }
}
