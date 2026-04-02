<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionClub;
use App\Models\Game;
use App\Models\MemberProfile;
use App\Models\Organizer;
use App\Models\Phase;
use App\Models\Section;
use App\Models\Team;
use App\Models\User;
use App\Services\SportTemplateService;
use App\Services\StandingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        // Create organizer user
        $orgUser = User::firstOrCreate(
            ['email' => 'organisateur@squadly.test'],
            ['name' => 'Test Organisateur', 'password' => Hash::make('password'), 'has_completed_onboarding' => true]
        );
        if (!$orgUser->hasRole('organizer_admin')) {
            $orgUser->assignRole('organizer_admin');
        }

        // Create organizer entity
        $organizer = Organizer::firstOrCreate(
            ['contact_email' => 'contact@ligue-football.test'],
            ['name' => 'Ligue Régionale Football', 'sport_type' => 'football', 'city' => 'Paris', 'created_by' => $orgUser->id]
        );

        if (!$organizer->users()->where('user_id', $orgUser->id)->exists()) {
            $organizer->users()->attach($orgUser, ['role' => 'admin']);
        }

        // Create 4 demo clubs with owners
        $clubs = [];
        $clubNames = [
            ['name' => 'FC Olympique', 'city' => 'Lyon'],
            ['name' => 'AS Étoile', 'city' => 'Marseille'],
            ['name' => 'Racing Club', 'city' => 'Bordeaux'],
            ['name' => 'SC Dynamique', 'city' => 'Toulouse'],
        ];

        foreach ($clubNames as $i => $data) {
            $email = "admin-club" . ($i + 1) . "@squadly.test";
            $owner = User::firstOrCreate(
                ['email' => $email],
                ['name' => "Admin {$data['name']}", 'password' => Hash::make('password'), 'has_completed_onboarding' => true]
            );
            if (!$owner->hasRole('admin_club')) {
                $owner->assignRole('admin_club');
            }

            $club = Club::firstOrCreate(
                ['owner_id' => $owner->id],
                ['name' => $data['name'], 'city' => $data['city']]
            );

            MemberProfile::firstOrCreate(
                ['user_id' => $owner->id, 'club_id' => $club->id],
                ['first_name' => 'Admin', 'last_name' => $data['name']]
            );

            // Section football + équipe Seniors
            $section = Section::firstOrCreate(
                ['club_id' => $club->id, 'sport_type' => 'football'],
                ['sport_template' => SportTemplateService::get('football')]
            );

            Team::firstOrCreate(
                ['section_id' => $section->id, 'name' => 'Seniors'],
                ['age_category' => 'Seniors', 'season' => '2025-2026']
            );

            $clubs[] = $club;
        }

        // Create competition
        $competition = Competition::firstOrCreate(
            ['organizer_id' => $organizer->id, 'name' => 'Championnat Régional 2025-2026'],
            [
                'sport_type' => 'football', 'season' => '2025-2026', 'format' => 'league',
                'status' => 'ongoing', 'description' => 'Championnat régional de football, phase aller-retour.',
                'rules' => ['points_win' => 3, 'points_draw' => 1, 'points_loss' => 0],
                'starts_at' => '2025-09-01', 'ends_at' => '2026-06-30',
            ]
        );

        // Create a single group phase
        $phase = Phase::firstOrCreate(
            ['competition_id' => $competition->id, 'name' => 'Phase de championnat'],
            ['type' => 'round_robin', 'order' => 1, 'status' => 'ongoing']
        );

        // Register all 4 clubs (approved)
        foreach ($clubs as $club) {
            CompetitionClub::firstOrCreate(
                ['competition_id' => $competition->id, 'club_id' => $club->id],
                ['phase_id' => $phase->id, 'status' => 'approved', 'registered_at' => now(), 'approved_at' => now()]
            );
        }

        // Create 6 matches (round-robin: 4 clubs = 6 matchups), some with scores
        if ($phase->games()->count() === 0) {
            $matchups = [
                [$clubs[0], $clubs[1], 2, 1],
                [$clubs[2], $clubs[3], 0, 0],
                [$clubs[0], $clubs[2], 3, 1],
                [$clubs[1], $clubs[3], 2, 2],
                [$clubs[0], $clubs[3], 1, 0],
                [$clubs[1], $clubs[2], null, null],
            ];

            foreach ($matchups as $i => [$home, $away, $homeScore, $awayScore]) {
                Game::withoutEvents(function () use ($phase, $home, $away, $homeScore, $awayScore, $i) {
                    Game::create([
                        'phase_id' => $phase->id,
                        'home_club_id' => $home->id,
                        'away_club_id' => $away->id,
                        'scheduled_at' => now()->addWeeks($i),
                        'location' => "Stade de {$home->city}",
                        'status' => $homeScore !== null ? 'finished' : 'scheduled',
                        'home_score' => $homeScore,
                        'away_score' => $awayScore,
                    ]);
                });
            }

            StandingService::recalculate($phase->id);
        }
    }
}
