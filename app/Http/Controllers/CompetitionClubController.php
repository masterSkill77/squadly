<?php

namespace App\Http\Controllers;

use App\Enums\CompetitionClubStatus;
use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionClub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionClubController extends Controller
{
    public function index(Request $request, Competition $competition): Response
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competition->load([
            'competitionClubs.club',
            'competitionClubs.phase',
            'phases',
        ]);

        return Inertia::render('Organizer/Competitions/Clubs', [
            'competition' => $competition,
        ]);
    }

    public function store(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $validated = $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'phase_id' => 'nullable|exists:phases,id',
        ]);

        CompetitionClub::firstOrCreate(
            ['competition_id' => $competition->id, 'club_id' => $validated['club_id']],
            [
                'phase_id' => $validated['phase_id'] ?? null,
                'status' => 'approved',
                'registered_at' => now(),
                'approved_at' => now(),
            ]
        );

        return back()->with('success', 'Club ajouté à la compétition.');
    }

    public function update(Request $request, Competition $competition, CompetitionClub $competitionClub): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,rejected',
            'phase_id' => 'nullable|exists:phases,id',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'approved' && !$competitionClub->approved_at) {
            $validated['approved_at'] = now();
        }

        $competitionClub->update($validated);

        return back()->with('success', 'Inscription mise à jour.');
    }

    public function destroy(Request $request, Competition $competition, CompetitionClub $competitionClub): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competitionClub->delete();

        return back()->with('success', 'Club retiré de la compétition.');
    }

    public function approveAll(Request $request, Competition $competition): RedirectResponse
    {
        $organizer = $request->user()->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $competition->competitionClubs()
            ->where('status', 'pending')
            ->update(['status' => 'approved', 'approved_at' => now()]);

        return back()->with('success', 'Tous les clubs en attente ont été approuvés.');
    }

    /**
     * Club-side: register own club to a competition.
     */
    public function register(Request $request, Competition $competition): RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin_club'), 403);

        $club = $request->user()->resolveClub();
        abort_unless($club, 403);

        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        // Verify team belongs to this club
        $team = $club->teams()->where('teams.id', $request->team_id)->first();
        abort_unless($team, 403);

        CompetitionClub::firstOrCreate(
            ['competition_id' => $competition->id, 'club_id' => $club->id, 'team_id' => $team->id],
            [
                'status' => 'pending',
                'registered_at' => now(),
            ]
        );

        return back()->with('success', 'Demande d\'inscription envoyée.');
    }
}
