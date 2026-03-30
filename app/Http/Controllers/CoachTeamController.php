<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\MemberProfile;
use App\Models\MemberSectionProfile;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoachTeamController extends Controller
{
    public function index(Request $request): Response
    {
        $teams = $request->user()
            ->teams()
            ->with('section:id,sport_type')
            ->withCount('players')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'age_category' => $t->age_category,
                'season' => $t->season,
                'sport_type' => $t->section->sport_type,
                'players_count' => $t->players_count,
            ]);

        return Inertia::render('Coach/Effectifs', [
            'teams' => $teams,
        ]);
    }

    public function show(Request $request, Team $team): Response
    {
        $user = $request->user();

        abort_if(!$user->teams()->where('teams.id', $team->id)->exists(), 403);

        $team->load('section:id,sport_type,sport_template,club_id');
        $clubId = $team->section->club_id;

        $memberUsers = $team->players()->get();
        $memberUserIds = $memberUsers->pluck('id');

        $profiles = MemberProfile::where('club_id', $clubId)
            ->whereIn('user_id', $memberUserIds)
            ->get()
            ->keyBy('user_id');

        $sportProfiles = MemberSectionProfile::where('section_id', $team->section_id)
            ->whereIn('user_id', $memberUserIds)
            ->get()
            ->keyBy('user_id');

        $players = $memberUsers->map(fn ($u) => [
            'user_id' => $u->id,
            'first_name' => $profiles->get($u->id)?->first_name ?? $u->name,
            'last_name' => $profiles->get($u->id)?->last_name ?? '',
            'email' => $u->email,
            'phone' => $profiles->get($u->id)?->phone,
            'sport_profile' => $sportProfiles->get($u->id)?->sport_profile,
            'role' => $u->getRoleNames()->first() ?? Role::Membre->value,
        ]);

        return Inertia::render('Coach/Team', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'age_category' => $team->age_category,
                'season' => $team->season,
                'sport_type' => $team->section->sport_type,
                'sport_template' => $team->section->sport_template,
            ],
            'players' => $players,
        ]);
    }

    public function addPlayer(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        abort_if(!$user->teams()->where('teams.id', $team->id)->exists(), 403);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'sport_profile' => 'nullable|array',
        ]);

        $team->load('section:id,club_id');
        $clubId = $team->section->club_id;

        $player = User::firstOrCreate(
            ['email' => $request->email],
            ['name' => "{$request->first_name} {$request->last_name}", 'password' => bcrypt(str()->random(16))],
        );

        if (!$player->hasAnyRole([Role::Admin->value, Role::Coach->value])) {
            $player->syncRoles([Role::Membre->value]);
        }

        MemberProfile::firstOrCreate(
            ['user_id' => $player->id, 'club_id' => $clubId],
            $request->only('first_name', 'last_name', 'phone', 'birth_date'),
        );

        TeamMember::firstOrCreate(['user_id' => $player->id, 'team_id' => $team->id]);

        if ($request->sport_profile) {
            MemberSectionProfile::updateOrCreate(
                ['user_id' => $player->id, 'section_id' => $team->section_id],
                ['sport_profile' => $request->sport_profile],
            );
        }

        return back()->with('success', 'Joueur ajouté avec succès.');
    }

    public function showPlayer(Request $request, Team $team, User $user): Response
    {
        abort_if(!$request->user()->teams()->where('teams.id', $team->id)->exists(), 403);
        abort_if(!$team->players()->where('users.id', $user->id)->exists(), 404);

        $team->load('section:id,sport_type,sport_template,club_id');
        $clubId = $team->section->club_id;

        $profile = MemberProfile::where('user_id', $user->id)->where('club_id', $clubId)->first();
        $sportProfile = MemberSectionProfile::where('user_id', $user->id)
            ->where('section_id', $team->section_id)
            ->first();

        $eventIds = Event::where('team_id', $team->id)->pluck('id');
        $records = Attendance::where('user_id', $user->id)->whereIn('event_id', $eventIds)->get();
        $total = $records->count();
        $present = $records->where('status', AttendanceStatus::Present)->count();
        $absent = $records->where('status', AttendanceStatus::Absent)->count();
        $justified = $records->where('status', AttendanceStatus::Justified)->count();

        return Inertia::render('Coach/Player', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'sport_type' => $team->section->sport_type,
                'sport_template' => $team->section->sport_template,
            ],
            'player' => [
                'user_id' => $user->id,
                'first_name' => $profile?->first_name ?? $user->name,
                'last_name' => $profile?->last_name ?? '',
                'email' => $user->email,
                'phone' => $profile?->phone,
                'birth_date' => $profile?->birth_date?->format('Y-m-d'),
                'photo_url' => $profile?->getFirstMediaUrl('photo'),
                'sport_profile' => $sportProfile?->sport_profile,
            ],
            'attendance' => [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'justified' => $justified,
                'rate' => $total > 0 ? round(($present / $total) * 100, 1) : null,
            ],
        ]);
    }

    public function updatePlayer(Request $request, Team $team, User $user): RedirectResponse
    {
        abort_if(!$request->user()->teams()->where('teams.id', $team->id)->exists(), 403);
        abort_if(!$team->players()->where('users.id', $user->id)->exists(), 404);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
        ]);

        $clubId = $team->load('section:id,club_id')->section->club_id;

        $profile = MemberProfile::where('user_id', $user->id)->where('club_id', $clubId)->first();
        $profile?->update($request->only('first_name', 'last_name', 'phone', 'birth_date'));

        return back()->with('success', 'Informations mises à jour.');
    }

    public function updatePlayerSportProfile(Request $request, Team $team, User $user): RedirectResponse
    {
        abort_if(!$request->user()->teams()->where('teams.id', $team->id)->exists(), 403);
        abort_if(!$team->players()->where('users.id', $user->id)->exists(), 404);

        $request->validate([
            'sport_profile' => 'required|array',
        ]);

        MemberSectionProfile::updateOrCreate(
            ['user_id' => $user->id, 'section_id' => $team->section_id],
            ['sport_profile' => $request->sport_profile],
        );

        return back()->with('success', 'Profil sportif enregistré.');
    }
}
