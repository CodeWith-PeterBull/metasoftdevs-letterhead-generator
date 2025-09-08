<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create permissions if they don't exist
        $permissions = [
            'manage users',
            'manage companies',
            'create companies',
            'edit companies',
            'delete companies',
            'view companies',
            'generate letterheads',
            'manage system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to admin role (all permissions)
        $adminRole->syncPermissions(Permission::all());

        // Assign permissions to user role (limited permissions)
        $userRole->syncPermissions([
            'manage companies',
            'create companies',
            'edit companies',
            'delete companies',
            'view companies',
            'generate letterheads',
        ]);
    }
}