<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionAnnouncementController extends Controller
{
    public function index(Request $request, Competition $competition): Response
    {
        $user = $request->user();
        $organizer = $user->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $announcements = CompetitionAnnouncement::where('competition_id', $competition->id)
            ->with('author:id,name')
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'author_name' => $a->author->name,
                'created_at' => $a->created_at->toIso8601String(),
                'can_delete' => $a->user_id === $user->id,
            ]);

        return Inertia::render('Organizer/Competitions/Announcements', [
            'competition' => $competition->only('id', 'name', 'organizer_id'),
            'announcements' => $announcements,
        ]);
    }

    public function store(Request $request, Competition $competition): RedirectResponse
    {
        $user = $request->user();
        $organizer = $user->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        CompetitionAnnouncement::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Annonce publiée.');
    }

    public function destroy(Request $request, Competition $competition, CompetitionAnnouncement $announcement): RedirectResponse
    {
        $user = $request->user();
        $organizer = $user->resolveOrganizer();
        abort_unless($organizer && $competition->organizer_id === $organizer->id, 403);
        abort_unless($announcement->competition_id === $competition->id, 404);
        abort_unless($announcement->user_id === $user->id, 403);

        $announcement->delete();

        return back()->with('success', 'Annonce supprimée.');
    }
}
