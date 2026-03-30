<?php

namespace App\Http\Controllers;

use App\Services\SportTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    public function show(Request $request): Response
    {
        $club = $request->user()->club()->with('sections.teams')->firstOrFail();

        $usedSports = $club->sections->pluck('sport_type')->toArray();
        $availableSports = array_values(array_diff(SportTemplateService::keys(), $usedSports));

        return Inertia::render('Club/Show', [
            'club' => [
                ...$club->only('id', 'name', 'city', 'slug'),
                'logo_url' => $club->getFirstMediaUrl('logo'),
            ],
            'sections' => $club->sections->map(fn ($s) => [
                'id' => $s->id,
                'sport_type' => $s->sport_type,
                'teams' => $s->teams->map(fn ($t) => $t->only('id', 'name', 'age_category', 'season')),
            ]),
            'availableSports' => $availableSports,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $club = $request->user()->club()->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:5120',
        ]);

        $club->update($request->only('name', 'city'));

        if ($request->hasFile('logo')) {
            $club->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return back()->with('success', 'Club mis à jour.');
    }
}
