<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Services\SportTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()->hasClub()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Index', [
            'sportTemplates' => SportTemplateService::keys(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validSports = implode(',', SportTemplateService::keys());

        $request->validate([
            'club_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:5120',
            'sports' => 'nullable|array',
            'sports.*' => "string|in:{$validSports}",
            'teams' => 'nullable|array',
            'teams.*.name' => 'required|string|max:255',
            'teams.*.sport' => "required|string|in:{$validSports}",
            'teams.*.age_category' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        $club = Club::create([
            'owner_id' => $user->id,
            'name' => $request->club_name,
            'city' => $request->city,
        ]);

        if ($request->hasFile('logo')) {
            $club->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        $sectionMap = [];
        foreach ($request->sports ?? [] as $sport) {
            $section = $club->sections()->create([
                'sport_type' => $sport,
                'sport_template' => SportTemplateService::get($sport),
            ]);
            $sectionMap[$sport] = $section->id;
        }

        foreach ($request->teams ?? [] as $team) {
            if ($sectionId = $sectionMap[$team['sport']] ?? null) {
                \App\Models\Team::create([
                    'section_id' => $sectionId,
                    'name' => $team['name'],
                    'age_category' => $team['age_category'] ?? null,
                    'season' => now()->year . '-' . (now()->year + 1),
                ]);
            }
        }

        $user->update(['has_completed_onboarding' => true]);

        return redirect()->route('dashboard');
    }

    public function complete(Request $request): RedirectResponse
    {
        $request->user()->update(['has_completed_onboarding' => true]);

        return back();
    }
}
