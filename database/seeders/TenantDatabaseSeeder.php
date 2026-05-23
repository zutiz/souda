<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['name' => 'Demo Account'],
        );

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'tenant_id' => $tenant->id,
            ],
        );

        if ($user->tenant_id !== $tenant->id) {
            $user->update(['tenant_id' => $tenant->id]);
        }

        if ($user->hasRole('admin')) {
            $user->removeRole('admin');
        }

        $user->syncPermissions([]);

        if (! $user->hasRole('tenant')) {
            $user->assignRole(Role::firstOrCreate(['name' => 'tenant']));
        }
    }
}
