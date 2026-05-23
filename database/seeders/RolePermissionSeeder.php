<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.archive',
            'products.publish',
            'products.duplicate',
            'products.import',
            'products.export',
        ];

        $guard = 'web';

        $created = collect($permissions)->map(fn (string $name) => Permission::firstOrCreate(
            ['name' => $name, 'guard_name' => $guard],
        ));

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $admin->syncPermissions($created);

        $tenantRole = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => $guard]);
        $tenantRole->syncPermissions($created);

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
