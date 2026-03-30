<?php

namespace App\Http\Controllers;

use App\Enums\AnnouncementTarget;
use App\Enums\Role;
use App\Models\Announcement;
use App\Models\MemberProfile;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $club = $user->resolveClub();
        $role = $user->getRoleNames()->first() ?? Role::Membre->value;
        $isAdmin = $role === Role::Admin->value;
        $isCoach = $role === Role::Coach->value;

        $announcements = Announcement::visibleTo($user)
            ->with('author:id,name', 'teams:id,name', 'section:id,sport_type', 'media')
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'target_type' => $a->target_type->value,
                'target_label' => $a->target_label,
                'author_name' => $a->author->name,
                'created_at' => $a->created_at->toIso8601String(),
                'can_delete' => $a->user_id === $user->id || $isAdmin,
                'attachments' => $a->getMedia('attachments')->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->file_name,
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'size' => $m->size,
                    'is_image' => str_starts_with($m->mime_type, 'image/'),
                ]),
            ]);

        $teams = [];
        $sections = [];
        if ($isAdmin && $club) {
            $teams = $club->teams()->select('teams.id', 'teams.name')->get();
            $sections = $club->sections()->select('id', 'sport_type')->get();
        } elseif ($isCoach) {
            $teams = $user->teams()->select('teams.id', 'teams.name')->get();
        }

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
            'teams' => $teams,
            'sections' => $sections,
            'canCreate' => $isAdmin || $isCoach,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $club = $user->resolveClub();
        $role = $user->getRoleNames()->first() ?? Role::Membre->value;
        $isAdmin = $role === Role::Admin->value;
        $isCoach = $role === Role::Coach->value;

        abort_if(!$isAdmin && !$isCoach, 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'target_type' => 'required|string|in:club,section,teams',
            'section_id' => 'nullable|required_if:target_type,section|exists:sections,id',
            'team_ids' => 'nullable|required_if:target_type,teams|array|min:1',
            'team_ids.*' => 'exists:teams,id',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        // Coach can only target their own teams
        if ($isCoach) {
            abort_if($request->target_type !== 'teams', 403);
            $coachTeamIds = $user->teams()->pluck('teams.id');
            foreach ($request->team_ids ?? [] as $teamId) {
                abort_if(!$coachTeamIds->contains($teamId), 403);
            }
        }

        $announcement = Announcement::create([
            'user_id' => $user->id,
            'club_id' => $club->id,
            'section_id' => $request->target_type === 'section' ? $request->section_id : null,
            'title' => $request->title,
            'content' => $request->content,
            'target_type' => $request->target_type,
        ]);

        if ($request->target_type === 'teams' && $request->team_ids) {
            $announcement->teams()->attach($request->team_ids);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $announcement->addMedia($file)->toMediaCollection('attachments');
            }
        }

        // Notify targeted users
        $recipients = $this->resolveRecipients($announcement, $club, $user);
        if ($recipients->isNotEmpty()) {
            $announcement->load('author');
            Notification::send($recipients, new AnnouncementPublished($announcement));
        }

        return back()->with('success', 'Annonce publiée avec succès.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first() ?? Role::Membre->value;
        $isAdmin = $role === Role::Admin->value;

        abort_if($announcement->user_id !== $user->id && !$isAdmin, 403);
        abort_if($announcement->club_id !== $user->resolveClub()?->id, 403);

        $announcement->delete();

        return back()->with('success', 'Annonce supprimée.');
    }

    private function resolveRecipients(Announcement $announcement, $club, User $excludeUser)
    {
        $userIds = collect();

        if ($announcement->target_type === AnnouncementTarget::Club) {
            $userIds = $club->memberProfiles()->pluck('user_id');
        } elseif ($announcement->target_type === AnnouncementTarget::Section) {
            $teamIds = $club->teams()->where('section_id', $announcement->section_id)->pluck('teams.id');
            $userIds = \DB::table('team_members')->whereIn('team_id', $teamIds)->pluck('user_id')->unique();
        } elseif ($announcement->target_type === AnnouncementTarget::Teams) {
            $teamIds = $announcement->teams()->pluck('teams.id');
            $userIds = \DB::table('team_members')->whereIn('team_id', $teamIds)->pluck('user_id')->unique();
        }

        return User::whereIn('id', $userIds)->where('id', '!=', $excludeUser->id)->get();
    }
}
