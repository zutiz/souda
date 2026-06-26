<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    /**
     * Get all active plans with their details.
     */
    public function getActivePlans(): Collection
    {
        return Plan::active()->ordered()->get();
    }

    /**
     * Get all plans (including inactive).
     */
    public function getAllPlans(): Collection
    {
        return Plan::ordered()->get();
    }

    /**
     * Find a plan by ID.
     */
    public function findById(int $id): ?Plan
    {
        return Plan::find($id);
    }

    /**
     * Find a plan by slug.
     */
    public function findBySlug(string $slug): ?Plan
    {
        return Plan::bySlug($slug)->first();
    }

    /**
     * Find a plan or throw an exception.
     */
    public function findOrFail(int $id): Plan
    {
        return Plan::findOrFail($id);
    }

    /**
     * Create a new plan.
     */
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    /**
     * Update an existing plan.
     */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);

        return $plan->fresh();
    }

    /**
     * Toggle plan active status.
     */
    public function toggleActive(Plan $plan): Plan
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return $plan->fresh();
    }

    /**
     * Delete a plan.
     */
    public function delete(Plan $plan): bool
    {
        return $plan->delete();
    }

    /**
     * Get a plan's pricing summary.
     */
    public function getPricingSummary(Plan $plan): array
    {
        return [
            'monthly' => $plan->monthly_price,
            'yearly' => $plan->yearly_price,
            'yearly_savings' => $plan->yearly_price
                ? (($plan->monthly_price * 12) - $plan->yearly_price)
                : 0,
            'currency' => $plan->currency,
            'features' => $plan->features ?? [],
            'limits' => $plan->limits ?? [],
        ];
    }

    /**
     * Reorder plans.
     */
    public function reorder(array $order): void
    {
        foreach ($order as $position => $planId) {
            Plan::where('id', $planId)->update(['display_order' => $position]);
        }
    }
}
