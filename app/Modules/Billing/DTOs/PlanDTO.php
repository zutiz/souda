<?php

namespace App\Modules\Billing\DTOs;

class PlanDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly int $monthlyPrice,
        public readonly ?int $yearlyPrice,
        public readonly string $currency,
        public readonly array $features,
        public readonly array $limits,
        public readonly bool $isActive,
        public readonly int $displayOrder,
        public readonly bool $popular,
        public readonly ?string $cta,
        public readonly bool $trialEnabled,
        public readonly int $trialDays,
        public readonly bool $trialWithoutCard,
    ) {}

    public static function fromModel(object $plan): self
    {
        return new self(
            id: $plan->id,
            name: $plan->name,
            slug: $plan->slug,
            description: $plan->description,
            monthlyPrice: $plan->monthly_price,
            yearlyPrice: $plan->yearly_price,
            currency: $plan->currency,
            features: $plan->features ?? [],
            limits: $plan->limits ?? [],
            isActive: $plan->is_active ?? $plan->active ?? true,
            displayOrder: $plan->display_order ?? 0,
            popular: $plan->popular ?? false,
            cta: $plan->cta ?? null,
            trialEnabled: $plan->trial_enabled ?? false,
            trialDays: $plan->trial_days ?? 0,
            trialWithoutCard: $plan->trial_without_card ?? false,
        );
    }
}
