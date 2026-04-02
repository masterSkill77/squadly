<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class PublicClubController extends Controller
{
    public function index(Request $request): Response
    {
        $sport = $request->input('sport');

        $query = Club::with('sections')
            ->withCount(['memberProfiles', 'teams'])
            ->latest();

        if ($sport) {
            $query->whereHas('sections', fn ($q) => $q->where('sport_type', $sport));
        }

        $clubs = $query->paginate(12)->withQueryString();

        $clubs->getCollection()->transform(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'city' => $c->city,
            'logo_url' => $c->getFirstMediaUrl('logo') ?: null,
            'members_count' => $c->member_profiles_count,
            'teams_count' => $c->teams_count,
            'sports' => $c->sections->pluck('sport_type')->unique()->values(),
        ]);

        $availableSports = Section::select('sport_type')
            ->distinct()
            ->orderBy('sport_type')
            ->pluck('sport_type');

        return Inertia::render('Public/Club/Index', [
            'clubs' => $clubs,
            'availableSports' => $availableSports,
            'currentSport' => $sport,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function show(Club $club): Response
    {
        $club->load([
            'sections.teams' => fn ($q) => $q->withCount('members'),
            'owner:id,name',
        ]);
        $club->loadCount('memberProfiles');

        // Club's competitions
        $competitions = $club->competitions()
            ->with('organizer')
            ->withPivot('status')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'season' => $c->season,
                'sport_type' => $c->sport_type,
                'status' => $c->status->value,
                'organizer_name' => $c->organizer->name,
            ]);

        return Inertia::render('Public/Club/Show', [
            'club' => [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'city' => $club->city,
                'logo_url' => $club->getFirstMediaUrl('logo') ?: null,
                'members_count' => $club->member_profiles_count,
                'sports' => $club->sections->pluck('sport_type')->unique()->values(),
                'sections' => $club->sections->map(fn ($s) => [
                    'sport_type' => $s->sport_type,
                    'teams' => $s->teams->map(fn ($t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                        'age_category' => $t->age_category,
                        'members_count' => $t->members_count,
                    ]),
                ]),
            ],
            'competitions' => $competitions,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
