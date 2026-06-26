<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);

        $tenant = Tenant::firstOrCreate(
            ['name' => 'Admin Account'],
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'tenant_id' => $tenant->id,
            ],
        );

        if ($admin->tenant_id !== $tenant->id) {
            $admin->update(['tenant_id' => $tenant->id]);
        }

        if (! $admin->hasRole('admin')) {
            $admin->assignRole($role);
        }

        if ($admin->tenant && ! $admin->tenant->owner_id) {
            $admin->tenant->update(['owner_id' => $admin->id]);
        }
    }
}
