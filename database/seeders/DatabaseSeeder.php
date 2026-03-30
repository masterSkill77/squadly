<?php

namespace Database\Seeders;

use App\Enums\Role;
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
        $admin->assignRole(Role::Admin->value);

        $coach = User::factory()->create([
            'name' => 'Test Coach',
            'email' => 'coach@squadly.test',
        ]);
        $coach->assignRole(Role::Coach->value);

        $membre = User::factory()->create([
            'name' => 'Test Membre',
            'email' => 'membre@squadly.test',
        ]);
        $membre->assignRole(Role::Membre->value);
    }
}
