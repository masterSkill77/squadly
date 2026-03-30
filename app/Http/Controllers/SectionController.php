<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\SportTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $club = $request->user()->club;

        $request->validate([
            'sport_type' => 'required|string|in:' . implode(',', SportTemplateService::keys()),
        ]);

        abort_if(
            $club->sections()->where('sport_type', $request->sport_type)->exists(),
            422,
            'Ce sport est déjà dans votre club.'
        );

        $club->sections()->create([
            'sport_type' => $request->sport_type,
            'sport_template' => SportTemplateService::get($request->sport_type),
        ]);

        return back()->with('success', 'Section ajoutée.');
    }

    public function destroy(Request $request, Section $section): RedirectResponse
    {
        abort_if($section->club_id !== $request->user()->club?->id, 403);

        $section->delete();

        return back()->with('success', 'Section supprimée.');
    }
}
