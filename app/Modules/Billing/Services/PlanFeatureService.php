<?php

namespace App\Modules\Billing\Services;

use App\Models\Tenant;
use App\Modules\Billing\Exceptions\FeatureNotAccessibleException;
use App\Modules\Billing\Models\Subscription;

class PlanFeatureService
{
    /**
     * Check if a tenant has access to a given feature.
     *
     * Usage:
     *   PlanFeatureService::tenantHasFeature($tenant, 'inventory_management');
     */
    public static function tenantHasFeature(Tenant|string $tenant, string $feature): bool
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        $subscription = Subscription::forTenant($tenantId)
            ->accessible()
            ->latest('id')
            ->first();

        if (! $subscription) {
            return false;
        }

        $plan = $subscription->plan;

        if (! $plan) {
            return false;
        }

        $features = $plan->features ?? [];

        return in_array($feature, $features, true);
    }

    /**
     * Check if a tenant has access to a feature and throw if not.
     *
     * @throws FeatureNotAccessibleException
     */
    public static function requireFeature(Tenant|string $tenant, string $feature): void
    {
        if (! static::tenantHasFeature($tenant, $feature)) {
            throw new FeatureNotAccessibleException($feature);
        }
    }

    /**
     * Get all available feature keys for a tenant based on their plan.
     *
     * @return array<string>
     */
    public static function getTenantFeatures(Tenant|string $tenant): array
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        $subscription = Subscription::forTenant($tenantId)
            ->accessible()
            ->latest('id')
            ->first();

        if (! $subscription || ! $subscription->plan) {
            return [];
        }

        return $subscription->plan->features ?? [];
    }

    /**
     * Get the limit value for a specific feature for a tenant.
     */
    public static function getFeatureLimit(Tenant|string $tenant, string $feature): ?int
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        $subscription = Subscription::forTenant($tenantId)
            ->accessible()
            ->latest('id')
            ->first();

        if (! $subscription || ! $subscription->plan) {
            return null;
        }

        $limits = $subscription->plan->limits ?? [];

        return $limits[$feature] ?? null;
    }

    /**
     * Check if a tenant has reached the usage limit for a feature.
     */
    public static function hasReachedLimit(Tenant|string $tenant, string $feature, int $currentUsage): bool
    {
        $limit = static::getFeatureLimit($tenant, $feature);

        // No limit defined = unlimited.
        if ($limit === null) {
            return false;
        }

        return $currentUsage >= $limit;
    }
}
