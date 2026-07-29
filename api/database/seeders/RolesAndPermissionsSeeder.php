<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view',
            'users.manage',

            'catalog.view',
            'catalog.manage',

            'orders.view',
            'orders.manage',

            'site.view',
            'site.manage',

            'bot.view',
            'bot.manage',

            'monitoring.view',
            'monitoring.manage',
            '1c.sync',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'superadmin' => $permissions,
            'manager' => [
                'catalog.view', 'catalog.manage',
                'orders.view', 'orders.manage',
            ],
            'content' => [
                'site.view', 'site.manage',
                'catalog.view',
            ],
            'bot-operator' => [
                'bot.view', 'bot.manage',
                'catalog.view',
            ],
            '1c-operator' => [
                'monitoring.view', 'monitoring.manage',
                '1c.sync',
                'catalog.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}
