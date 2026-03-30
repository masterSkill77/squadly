<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage_club',
            'manage_members',
            'manage_teams',
            'manage_sections',
            'manage_events',
            'manage_convocations',
            'manage_attendance',
            'manage_documents',
            'manage_finances',
            'view_dashboard',
            'respond_convocation',
            'view_own_profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => RoleEnum::Admin->value])->syncPermissions([
            'manage_club',
            'manage_members',
            'manage_teams',
            'manage_sections',
            'manage_events',
            'manage_convocations',
            'manage_attendance',
            'manage_documents',
            'manage_finances',
            'view_dashboard',
        ]);

        Role::firstOrCreate(['name' => RoleEnum::Coach->value])->syncPermissions([
            'manage_events',
            'manage_convocations',
            'manage_attendance',
            'view_dashboard',
        ]);

        Role::firstOrCreate(['name' => RoleEnum::Membre->value])->syncPermissions([
            'respond_convocation',
            'view_own_profile',
            'view_dashboard',
        ]);
    }
}
