<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $club = $request->user()->club;

        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255',
            'age_category' => 'nullable|string|max:100',
        ]);

        abort_if(
            !$club->sections()->where('id', $request->section_id)->exists(),
            403
        );

        Team::create([
            'section_id' => $request->section_id,
            'name' => $request->name,
            'age_category' => $request->age_category,
            'season' => now()->year . '-' . (now()->year + 1),
        ]);

        return back()->with('success', 'Équipe créée.');
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        abort_if($team->section->club_id !== $request->user()->club?->id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'age_category' => 'nullable|string|max:100',
        ]);

        $team->update($request->only('name', 'age_category'));

        return back()->with('success', 'Équipe modifiée.');
    }

    public function destroy(Request $request, Team $team): RedirectResponse
    {
        abort_if($team->section->club_id !== $request->user()->club?->id, 403);

        $team->delete();

        return back()->with('success', 'Équipe supprimée.');
    }
}
