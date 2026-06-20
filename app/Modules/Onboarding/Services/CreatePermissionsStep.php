<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Modules\BusinessType\Services\IndustryPackRegistry;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;
use Illuminate\Support\Facades\DB;

class CreatePermissionsStep implements ProvisioningStep
{
    private array $createdPermissionIds = [];

    private array $createdRoleIds = [];

    public function handle(ProvisioningContext $context): void
    {
        $registry = app(IndustryPackRegistry::class);
        $pack = $registry->get($context->businessTypeSlug);

        if ($pack === null) {
            return;
        }

        tenancy()->initialize($context->tenant);

        DB::transaction(function () use ($pack, $context) {
            foreach ($pack->permissions() as $roleName => $perms) {
                $role = Role::query()->firstOrCreate(
                    ['name' => $roleName, 'guard_name' => 'web'],
                );
                $this->createdRoleIds[] = $role->id;

                foreach ($perms as $permName) {
                    $permission = Permission::query()->firstOrCreate(
                        ['name' => $permName, 'guard_name' => 'web'],
                    );
                    $this->createdPermissionIds[] = $permission->id;
                    $role->givePermissionTo($permission);
                }
            }

            $owner = User::query()
                ->where('tenant_id', $context->tenant->id)
                ->first();

            if ($owner !== null) {
                $owner->assignRole('admin');
            }
        });

        tenancy()->end();
    }

    public function rollback(ProvisioningContext $context): void
    {
        tenancy()->initialize($context->tenant);

        Permission::query()->whereIn('id', $this->createdPermissionIds)->delete();
        Role::query()->whereIn('id', $this->createdRoleIds)->delete();

        tenancy()->end();
    }

    public function label(): string
    {
        return 'Creating permissions and roles';
    }
}
