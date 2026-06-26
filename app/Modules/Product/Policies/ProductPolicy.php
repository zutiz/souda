<?php

declare(strict_types=1);

namespace App\Modules\Product\Policies;

use App\Models\User;
use App\Modules\Product\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.delete');
    }

    public function archive(User $user, Product $product): bool
    {
        return $user->can('products.archive');
    }

    public function publish(User $user, Product $product): bool
    {
        return $user->can('products.publish');
    }

    public function duplicate(User $user, Product $product): bool
    {
        return $user->can('products.duplicate');
    }

    public function import(User $user): bool
    {
        return $user->can('products.import');
    }

    public function export(User $user): bool
    {
        return $user->can('products.export');
    }
}
