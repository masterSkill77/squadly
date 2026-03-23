<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@squadly.test',
        ]);
        $admin->assignRole('admin_club');

        $coach = User::factory()->create([
            'name' => 'Test Coach',
            'email' => 'coach@squadly.test',
        ]);
        $coach->assignRole('coach');

        $membre = User::factory()->create([
            'name' => 'Test Membre',
            'email' => 'membre@squadly.test',
        ]);
        $membre->assignRole('membre');
    }
}
