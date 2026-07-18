<?php

declare(strict_types=1);

namespace App\Modules\Order\Policies;

use App\Models\User;
use App\Modules\Order\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id;
    }
}
