<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionClub;
use App\Models\MemberProfile;
use App\Models\Organizer;
use App\Models\Phase;
use App\Models\Section;
use App\Models\Team;
use App\Models\User;
use App\Services\QualificationService;
use App\Services\SportTemplateService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BracketDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Organizer
        $orgUser = User::firstOrCreate(
            ['email' => 'organisateur@squadly.test'],
            ['name' => 'Test Organisateur', 'password' => Hash::make('password'), 'has_completed_onboarding' => true]
        );
        if (! $orgUser->hasRole('organizer_admin')) {
            $orgUser->assignRole('organizer_admin');
        }

        $organizer = Organizer::firstOrCreate(
            ['contact_email' => 'contact@ligue-football.test'],
            ['name' => 'Ligue Régionale Football', 'sport_type' => 'football', 'city' => 'Antananarivo', 'created_by' => $orgUser->id]
        );

        if (! $organizer->users()->where('user_id', $orgUser->id)->exists()) {
            $organizer->users()->attach($orgUser, ['role' => 'admin']);
        }

        // 32 clubs
        $teamNames = [
            ['name' => 'FC Tana', 'city' => 'Antananarivo'],
            ['name' => 'AS Adema', 'city' => 'Antananarivo'],
            ['name' => 'Fosa Juniors', 'city' => 'Mahajanga'],
            ['name' => 'Ajesaia', 'city' => 'Antananarivo'],
            ['name' => 'Elgeco Plus', 'city' => 'Antananarivo'],
            ['name' => 'CNaPS Sport', 'city' => 'Itasy'],
            ['name' => 'Dato FC', 'city' => 'Toamasina'],
            ['name' => 'AS Fanalamanga', 'city' => 'Ambatondrazaka'],
            ['name' => 'Zanakala FC', 'city' => 'Fianarantsoa'],
            ['name' => 'Disciples FC', 'city' => 'Antsirabe'],
            ['name' => 'Tia Kitra', 'city' => 'Antsirabe'],
            ['name' => 'AS Cosfa', 'city' => 'Antananarivo'],
            ['name' => 'Jet Kintana', 'city' => 'Antananarivo'],
            ['name' => 'AS Niaravo', 'city' => 'Antsirabe'],
            ['name' => 'THB FC', 'city' => 'Antsirabe'],
            ['name' => 'FCA Ilakaka', 'city' => 'Ilakaka'],
            ['name' => 'US Antsirabe', 'city' => 'Antsirabe'],
            ['name' => 'Avenir FC', 'city' => 'Toliara'],
            ['name' => 'Mama FC', 'city' => 'Mahajanga'],
            ['name' => 'FC Vakinankaratra', 'city' => 'Antsirabe'],
            ['name' => 'Stade Olympique', 'city' => 'Antananarivo'],
            ['name' => 'AS Fortior', 'city' => 'Antananarivo'],
            ['name' => 'Fikambanana FC', 'city' => 'Mananjary'],
            ['name' => 'Tsaramandroso FC', 'city' => 'Tsaramandroso'],
            ['name' => 'Avaradrano FC', 'city' => 'Avaradrano'],
            ['name' => 'Union Sportive', 'city' => 'Morondava'],
            ['name' => 'AS Lundi', 'city' => 'Antananarivo'],
            ['name' => 'Dina FC', 'city' => 'Antananarivo'],
            ['name' => 'Real Fanahy', 'city' => 'Fianarantsoa'],
            ['name' => 'FC Ilakaka Star', 'city' => 'Ilakaka'],
            ['name' => 'Atsimo FC', 'city' => 'Toliara'],
            ['name' => 'Boina FC', 'city' => 'Mahajanga'],
        ];

        $clubs = [];
        foreach ($teamNames as $i => $data) {
            $email = "admin-bracket-{$i}@squadly.test";
            $owner = User::firstOrCreate(
                ['email' => $email],
                ['name' => "Admin {$data['name']}", 'password' => Hash::make('password'), 'has_completed_onboarding' => true]
            );
            if (! $owner->hasRole('admin_club')) {
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

        // Competition: Coupe de Madagascar
        $competition = Competition::firstOrCreate(
            ['organizer_id' => $organizer->id, 'name' => 'Coupe de Madagascar 2025-2026'],
            [
                'sport_type' => 'football',
                'season' => '2025-2026',
                'format' => 'cup',
                'status' => 'ongoing',
                'description' => 'Coupe nationale à élimination directe — 32 équipes.',
                'rules' => ['points_win' => 3, 'points_draw' => 1, 'points_loss' => 0],
                'starts_at' => '2025-09-01',
                'ends_at' => '2026-06-30',
            ]
        );

        // Single knockout phase with 32 teams
        $phase = Phase::firstOrCreate(
            ['competition_id' => $competition->id, 'name' => 'Phase finale'],
            ['type' => 'knockout', 'order' => 1, 'status' => 'ongoing']
        );

        // Register all 32 clubs
        foreach ($clubs as $club) {
            CompetitionClub::firstOrCreate(
                ['competition_id' => $competition->id, 'club_id' => $club->id],
                ['phase_id' => $phase->id, 'status' => 'approved', 'registered_at' => now(), 'approved_at' => now()]
            );
        }

        // Generate the full bracket (32 → 16 → 8 → 4 → 2 → 1)
        if ($phase->games()->count() === 0) {
            $clubIds = array_map(fn ($c) => $c->id, $clubs);
            QualificationService::generateBracket($phase, $clubIds);

            // Simulate some results in the first round (seizièmes)
            $firstRoundGames = $phase->games()
                ->where('round', 5) // 32 teams = 5 rounds
                ->whereNotNull('home_club_id')
                ->whereNotNull('away_club_id')
                ->get();

            foreach ($firstRoundGames->take(8) as $game) {
                $homeWins = rand(0, 1);
                $game->update([
                    'home_score' => $homeWins ? rand(1, 4) : rand(0, 2),
                    'away_score' => $homeWins ? rand(0, $game->home_score - 1 < 0 ? 0 : $game->home_score - 1) : rand($game->home_score + 1, 5),
                    'status' => 'finished',
                ]);
                QualificationService::advanceWinner($game);
            }
        }

        $this->command->info("Coupe de Madagascar créée : 32 équipes, bracket complet généré !");
        $this->command->info("8 matchs du premier tour simulés avec avancement automatique.");
    }
}
