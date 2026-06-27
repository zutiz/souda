<?php

declare(strict_types=1);

namespace App\Modules\Store\Policies;

use App\Models\User;
use App\Modules\Store\Models\Store;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('stores.view');
    }

    public function view(User $user, Store $store): bool
    {
        return $user->can('stores.view');
    }

    public function create(User $user): bool
    {
        return $user->can('stores.create');
    }

    public function update(User $user, Store $store): bool
    {
        return $user->can('stores.update');
    }

    public function delete(User $user, Store $store): bool
    {
        return $user->can('stores.delete');
    }

    public function switch(User $user, Store $store): bool
    {
        return $store->isActive();
    }
}
