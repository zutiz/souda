<?php

namespace App\Http\Controllers;

use App\Modules\Billing\Models\Plan;

trait TransformsPlansForFrontend
{
    private function transformPlans($plans): array
    {
        return $plans->map(fn (Plan $plan) => $this->transformPlanForFrontend($plan))->values()->all();
    }

    private function transformPlanForFrontend(Plan $plan): array
    {
        $prices = [];

        if ($plan->monthly_price !== null) {
            $prices[] = [
                'id' => 'monthly_'.$plan->id,
                'type' => 'recurring',
                'unit_amount' => $plan->monthly_price,
                'currency' => $plan->currency,
                'interval' => 'month',
                'interval_count' => 1,
                'recurring' => [
                    'interval' => 'month',
                    'interval_count' => 1,
                ],
                'active' => true,
                'nickname' => 'Monthly',
                'created' => $plan->created_at->timestamp,
            ];
        }

        if ($plan->yearly_price !== null) {
            $prices[] = [
                'id' => 'yearly_'.$plan->id,
                'type' => 'recurring',
                'unit_amount' => $plan->yearly_price,
                'currency' => $plan->currency,
                'interval' => 'year',
                'interval_count' => 1,
                'recurring' => [
                    'interval' => 'year',
                    'interval_count' => 1,
                ],
                'active' => true,
                'nickname' => 'Yearly',
                'created' => $plan->created_at->timestamp,
            ];
        }

        $metadata = [
            'popular' => $plan->popular ? 'true' : '',
            'cta' => $plan->cta ?? '',
            'trial_enabled' => $plan->trial_enabled ? 'true' : '',
            'trial_days' => $plan->trial_enabled ? (string) $plan->trial_days : '',
            'trial_without_card' => $plan->trial_without_card ? 'true' : '',
        ];

        foreach ($plan->features ?? [] as $index => $feature) {
            $metadata["feature_{$index}"] = $feature;
        }

        return [
            'id' => (string) $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'active' => $plan->is_active,
            'created' => $plan->created_at->timestamp,
            'display_order' => $plan->display_order,
            'metadata' => $metadata,
            'prices' => $prices,
        ];
    }
}
