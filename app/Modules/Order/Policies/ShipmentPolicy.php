<?php

declare(strict_types=1);

namespace App\Modules\Order\Policies;

use App\Models\User;
use App\Modules\Order\Models\Shipment;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $user->tenant_id === $shipment->tenant_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $user->tenant_id === $shipment->tenant_id;
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->tenant_id === $shipment->tenant_id;
    }
}
