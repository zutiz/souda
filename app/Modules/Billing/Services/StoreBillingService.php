<?php

declare(strict_types=1);

namespace App\Modules\Billing\Services;

use App\Models\Tenant;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\StoreAllocation;
use App\Modules\Store\Models\Store;

class StoreBillingService
{
    public function calculateStoreAmount(Tenant $tenant, Plan $plan): array
    {
        $activeStores = Store::query()->where('status', 'active')->count();
        $extraStores = max(0, $activeStores - $plan->default_stores);
        $storeAmount = $extraStores * $plan->store_price;

        return [
            'active_stores' => $activeStores,
            'default_stores' => $plan->default_stores,
            'extra_stores' => $extraStores,
            'store_amount' => $storeAmount,
        ];
    }

    public function allocateStore(Store $store, string $subscriptionId, Tenant $tenant): ?StoreAllocation
    {
        $plan = Plan::query()
            ->whereIn('id', fn ($q) => $q->select('plan_id')
                ->from('billing_subscriptions')
                ->where('id', $subscriptionId)
            )->first();

        if (! $plan) {
            return null;
        }

        $activeStores = Store::query()->where('status', 'active')->count();

        if ($activeStores <= $plan->default_stores) {
            return null;
        }

        return StoreAllocation::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscriptionId,
            'store_id' => $store->id,
            'store_code' => $store->code,
            'status' => 'active',
            'allocated_at' => now(),
            'billing_start_at' => now(),
        ]);
    }

    public function releaseStore(Store $store): void
    {
        $allocation = StoreAllocation::query()
            ->forTenant($store->tenant_id)
            ->where('store_id', $store->id)
            ->active()
            ->first();

        $allocation?->release();
    }

    public function getExtraStoreCount(string $tenantId): int
    {
        return StoreAllocation::query()
            ->forTenant($tenantId)
            ->active()
            ->count();
    }

    public function getActiveStoreCount(string $tenantId): int
    {
        return StoreAllocation::query()
            ->forTenant($tenantId)
            ->count();
    }

    public function canCreateStore(Tenant $tenant): bool
    {
        return $this->remainingStores($tenant) > 0;
    }

    public function remainingStores(Tenant $tenant): int
    {
        $subscription = $tenant->activeSubscription();

        if (! $subscription || ! $subscription->plan) {
            return 0;
        }

        $plan = $subscription->plan;
        $activeStores = Store::query()->where('status', 'active')->count();

        return max(0, $plan->default_stores - $activeStores);
    }
}
