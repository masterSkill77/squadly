<?php

namespace App\Http\Controllers;

use App\Services\SportTemplateService;
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
            'club' => $club->only('id', 'name', 'city', 'slug'),
            'sections' => $club->sections->map(fn ($s) => [
                'id' => $s->id,
                'sport_type' => $s->sport_type,
                'teams' => $s->teams->map(fn ($t) => $t->only('id', 'name', 'age_category', 'season')),
            ]),
            'availableSports' => $availableSports,
        ]);
    }
}
