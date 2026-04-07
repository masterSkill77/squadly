<?php

namespace App\Http\Controllers;

use App\Enums\CompetitionClubStatus;
use App\Enums\CompetitionStatus;
use App\Enums\GameStatus;
use App\Enums\PhaseType;
use App\Enums\Role;
use App\Models\Competition;
use App\Services\QualificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClubCompetitionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $club = $user->resolveClub();
        abort_unless($club, 403);

        $role = $user->getRoleNames()->first();
        $isCoach = $role === Role::Coach->value;

        // Coach: only teams they coach. Admin/Membre: all club teams.
        $myTeamIds = $isCoach
            ? $user->teams()->pluck('teams.id')
            : $club->teams()->pluck('teams.id');

        // My competitions — filtered by team_id for coaches
        $myCompetitionsQuery = $club->competitions()
            ->with('organizer')
            ->withPivot('status', 'phase_id', 'team_id');

        if ($isCoach) {
            $myCompetitionsQuery->wherePivotIn('team_id', $myTeamIds);
        }

        $myCompetitions = $myCompetitionsQuery->get();

        // Upcoming games
        $upcomingGames = collect();
        foreach ($myCompetitions as $competition) {
            $games = $competition->games()
                ->where('games.status', GameStatus::Scheduled)
                ->where(fn ($q) => $q->where('home_club_id', $club->id)->orWhere('away_club_id', $club->id))
                ->with(['homeClub', 'awayClub', 'phase'])
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get();
            $upcomingGames = $upcomingGames->merge($games);
        }

        // All competitions with search + pagination
        $search = $request->input('search', '');
        $allCompetitionsQuery = Competition::with('organizer')
            ->whereIn('status', [CompetitionStatus::Open->value, CompetitionStatus::Ongoing->value])
            ->withCount(['competitionClubs as clubs_count' => fn ($q) => $q->where('status', CompetitionClubStatus::Approved)]);

        if (mb_strlen($search) >= 4) {
            $allCompetitionsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sport_type', 'like', "%{$search}%")
                    ->orWhereHas('organizer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $allCompetitions = $allCompetitionsQuery->latest()->paginate(12)->withQueryString();

        // Mark which ones the club is already registered for
        $registeredIds = $myCompetitions->pluck('id')->toArray();
        $pendingIds = $club->competitions()
            ->wherePivot('status', CompetitionClubStatus::Pending->value)
            ->pluck('competitions.id')
            ->toArray();

        $clubTeams = $isCoach
            ? $user->teams()->with('section:id,sport_type')->get(['teams.id', 'teams.name', 'teams.section_id', 'teams.age_category'])
            : $club->teams()->with('section:id,sport_type')->get(['teams.id', 'teams.name', 'teams.section_id', 'teams.age_category']);

        return Inertia::render('Club/Competitions', [
            'club' => $club,
            'myCompetitions' => $myCompetitions,
            'upcomingGames' => $upcomingGames->sortBy('scheduled_at')->values()->take(10),
            'allCompetitions' => $allCompetitions,
            'registeredIds' => $registeredIds,
            'pendingIds' => $pendingIds,
            'search' => $search,
            'clubTeams' => $clubTeams,
            'canRegister' => $role === Role::Admin->value,
        ]);
    }

    public function show(Request $request, Competition $competition): Response
    {
        $club = $request->user()->resolveClub();
        abort_unless($club, 403);

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

        // Club's upcoming games in this competition
        $myGames = $competition->games()
            ->where(fn ($q) => $q->where('home_club_id', $club->id)->orWhere('away_club_id', $club->id))
            ->with(['homeClub', 'awayClub', 'phase'])
            ->orderBy('scheduled_at')
            ->get();

        // Bracket data for knockout phase (prefer the one with a generated bracket)
        $knockoutPhase = $competition->phases
            ->where('type', PhaseType::Knockout)
            ->sortByDesc(fn ($p) => $p->games()->count())
            ->first();
        $bracket = $knockoutPhase
            ? QualificationService::getBracketData($knockoutPhase)
            : ['rounds' => [], 'totalRounds' => 0];

        return Inertia::render('Club/CompetitionShow', [
            'competition' => $competition,
            'club' => $club,
            'myGames' => $myGames,
            'bracket' => $bracket,
            'knockoutPhase' => $knockoutPhase,
        ]);
    }
}
