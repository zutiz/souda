<?php

declare(strict_types=1);

namespace App\Modules\Product\Policies;

use App\Models\User;
use App\Modules\Product\Models\Brand;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('brands.view');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('brands.view');
    }

    public function create(User $user): bool
    {
        return $user->can('brands.create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('brands.update');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('brands.delete');
    }
}
