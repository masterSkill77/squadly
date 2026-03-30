<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\DocumentType;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\Document;
use App\Models\Event;
use App\Models\MemberProfile;
use App\Models\MemberSectionProfile;
use App\Models\TeamMember;
use App\Notifications\MemberInvited;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $club = $request->user()->resolveClub();

        $clubTeamIds = $club->teams()->pluck('teams.id');
        $teamMemberships = TeamMember::whereIn('team_id', $clubTeamIds)
            ->get()
            ->groupBy('user_id');

        $members = $club->memberProfiles()
            ->with('user:id,email')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'first_name' => $m->first_name,
                'last_name' => $m->last_name,
                'full_name' => $m->full_name,
                'email' => $m->user->email,
                'phone' => $m->phone,
                'birth_date' => $m->birth_date?->format('Y-m-d'),
                'photo_url' => $m->getFirstMediaUrl('photo'),
                'role' => $m->user->getRoleNames()->first() ?? Role::Membre->value,
                'team_ids' => $teamMemberships->get($m->user_id)?->pluck('team_id')->values() ?? [],
            ]);

        $sections = $club->sections()->with('teams')->get()->map(fn ($s) => [
            'id' => $s->id,
            'sport_type' => $s->sport_type,
            'sport_template' => $s->sport_template,
            'teams' => $s->teams->map(fn ($t) => $t->only('id', 'name', 'age_category')),
        ]);

        return Inertia::render('Members/Index', [
            'members' => $members,
            'club' => $club->only('id', 'name'),
            'sections' => $sections,
        ]);
    }

    public function show(Request $request, MemberProfile $member): Response
    {
        abort_if($member->club_id !== $request->user()->resolveClub()?->id, 403);

        $member->load('user:id,email');
        $club = $request->user()->resolveClub()->load('sections.teams');

        $sectionProfiles = MemberSectionProfile::where('user_id', $member->user_id)
            ->whereIn('section_id', $club->sections->pluck('id'))
            ->get()
            ->keyBy('section_id');

        $teamIds = TeamMember::where('user_id', $member->user_id)
            ->whereIn('team_id', $club->teams->pluck('id'))
            ->pluck('team_id');

        return Inertia::render('Members/Show', [
            'member' => [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->user->email,
                'phone' => $member->phone,
                'birth_date' => $member->birth_date?->format('Y-m-d'),
                'photo_url' => $member->getFirstMediaUrl('photo'),
                'role' => $member->user->getRoleNames()->first() ?? Role::Membre->value,
            ],
            'sections' => $club->sections->map(fn ($s) => [
                'id' => $s->id,
                'sport_type' => $s->sport_type,
                'sport_template' => $s->sport_template,
                'teams' => $s->teams->map(fn ($t) => $t->only('id', 'name', 'age_category')),
                'sport_profile' => $sectionProfiles->get($s->id)?->sport_profile,
            ]),
            'teamIds' => $teamIds,
            'attendance' => $this->getAttendanceStats($member->user_id, $club->teams->pluck('id')),
            'documents' => $this->getMemberDocuments($member->user_id, $club->id),
            'currentSeason' => Document::currentSeason(),
        ]);
    }

    private function getMemberDocuments(int $userId, int $clubId): array
    {
        $season = Document::currentSeason();
        $existing = Document::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->where('season', $season)
            ->get()
            ->keyBy(fn ($d) => $d->type->value);

        $docs = [];
        foreach (DocumentType::requiredTypes() as $type) {
            $doc = $existing->get($type->value);
            $docs[] = [
                'id' => $doc?->id,
                'type' => $type->value,
                'type_label' => $type->label(),
                'status' => $doc ? $doc->status : 'missing',
                'status_label' => $doc ? $doc->status_label : 'Manquant',
                'expires_at' => $doc?->expires_at?->format('Y-m-d'),
                'file_url' => $doc?->getFirstMediaUrl('document_file'),
            ];
        }

        foreach ($existing->where('type', DocumentType::Other) as $doc) {
            $docs[] = [
                'id' => $doc->id,
                'type' => 'other',
                'type_label' => $doc->custom_label ?? 'Autre',
                'status' => $doc->status,
                'status_label' => $doc->status_label,
                'expires_at' => $doc->expires_at?->format('Y-m-d'),
                'file_url' => $doc->getFirstMediaUrl('document_file'),
            ];
        }

        return $docs;
    }

    private function getAttendanceStats(int $userId, $clubTeamIds): array
    {
        $eventIds = Event::whereIn('team_id', $clubTeamIds)->pluck('id');
        $records = Attendance::where('user_id', $userId)->whereIn('event_id', $eventIds)->get();
        $total = $records->count();
        $present = $records->where('status', AttendanceStatus::Present)->count();
        $absent = $records->where('status', AttendanceStatus::Absent)->count();
        $justified = $records->where('status', AttendanceStatus::Justified)->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'justified' => $justified,
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : null,
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $club = $request->user()->resolveClub();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'role' => 'required|string|in:' . Role::Membre->value . ',' . Role::Coach->value,
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'sport_profiles' => 'nullable|array',
            'sport_profiles.*.section_id' => 'required|exists:sections,id',
            'sport_profiles.*.sport_profile' => 'required|array',
        ]);

        $temporaryPassword = str()->random(10);
        $isNewUser = false;

        $user = \App\Models\User::firstOrCreate(
            ['email' => $request->email],
            ['name' => "{$request->first_name} {$request->last_name}", 'password' => bcrypt($temporaryPassword)],
        );

        if ($user->wasRecentlyCreated) {
            $isNewUser = true;
        }

        if (!$user->hasRole(Role::Admin->value)) {
            $user->syncRoles([$request->role]);
        }

        $member = MemberProfile::firstOrCreate(
            ['user_id' => $user->id, 'club_id' => $club->id],
            $request->only('first_name', 'last_name', 'phone', 'birth_date'),
        );

        if ($request->team_ids) {
            foreach ($request->team_ids as $teamId) {
                if ($club->teams()->where('teams.id', $teamId)->exists()) {
                    TeamMember::firstOrCreate(['user_id' => $user->id, 'team_id' => $teamId]);
                }
            }
        }

        if ($request->sport_profiles) {
            $clubSectionIds = $club->sections->pluck('id');
            foreach ($request->sport_profiles as $sp) {
                if ($clubSectionIds->contains($sp['section_id'])) {
                    MemberSectionProfile::updateOrCreate(
                        ['user_id' => $user->id, 'section_id' => $sp['section_id']],
                        ['sport_profile' => $sp['sport_profile']],
                    );
                }
            }
        }

        if ($isNewUser) {
            $user->notify(new MemberInvited($club, $temporaryPassword));
        }

        return redirect()->route('members.show', $member)->with('success', 'Membre ajouté avec succès.');
    }

    public function update(Request $request, MemberProfile $member): RedirectResponse
    {
        abort_if($member->club_id !== $request->user()->resolveClub()?->id, 403);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
        ]);

        $member->update($request->only('first_name', 'last_name', 'phone', 'birth_date'));

        return back()->with('success', 'Profil mis à jour.');
    }

    public function destroy(Request $request, MemberProfile $member): RedirectResponse
    {
        abort_if($member->club_id !== $request->user()->resolveClub()?->id, 403);

        TeamMember::where('user_id', $member->user_id)
            ->whereIn('team_id', $request->user()->resolveClub()->teams->pluck('id'))
            ->delete();

        MemberSectionProfile::where('user_id', $member->user_id)
            ->whereIn('section_id', $request->user()->resolveClub()->sections->pluck('id'))
            ->delete();

        $member->delete();

        return redirect()->route('members.index')->with('success', 'Membre retiré du club.');
    }

    public function updateSportProfile(Request $request, MemberProfile $member): RedirectResponse
    {
        abort_if($member->club_id !== $request->user()->resolveClub()?->id, 403);

        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'sport_profile' => 'required|array',
        ]);

        MemberSectionProfile::updateOrCreate(
            ['user_id' => $member->user_id, 'section_id' => $request->section_id],
            ['sport_profile' => $request->sport_profile],
        );

        return back()->with('success', 'Profil sportif enregistré.');
    }

    public function updateTeams(Request $request, MemberProfile $member): RedirectResponse
    {
        abort_if($member->club_id !== $request->user()->resolveClub()?->id, 403);

        $request->validate([
            'team_ids' => 'array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        $clubTeamIds = $request->user()->resolveClub()->teams->pluck('id');

        TeamMember::where('user_id', $member->user_id)
            ->whereIn('team_id', $clubTeamIds)
            ->delete();

        foreach ($request->team_ids ?? [] as $teamId) {
            if ($clubTeamIds->contains($teamId)) {
                TeamMember::create(['user_id' => $member->user_id, 'team_id' => $teamId]);
            }
        }

        return back()->with('success', 'Équipes mises à jour.');
    }
}
