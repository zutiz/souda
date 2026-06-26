<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\PriceResult;
use App\Modules\Product\DTOs\PricingRuleDTO;
use App\Modules\Product\Models\PricingRule;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use Illuminate\Database\Eloquent\Collection;

class PricingRuleService
{
    public function createRule(PricingRuleDTO $dto): PricingRule
    {
        return PricingRule::query()->create([
            'name' => $dto->name,
            'type' => $dto->type->value,
            'scope' => $dto->scope->value,
            'scope_id' => $dto->scopeId,
            'condition_type' => $dto->conditionType?->value,
            'condition_value' => $dto->conditionValue,
            'discount_value' => $dto->discountValue,
            'start_at' => $dto->startAt,
            'end_at' => $dto->endAt,
            'is_active' => $dto->isActive,
            'priority' => $dto->priority,
            'max_uses' => $dto->maxUses,
        ]);
    }

    public function updateRule(PricingRule $rule, PricingRuleDTO $dto): PricingRule
    {
        $rule->update([
            'name' => $dto->name,
            'type' => $dto->type->value,
            'scope' => $dto->scope->value,
            'scope_id' => $dto->scopeId,
            'condition_type' => $dto->conditionType?->value,
            'condition_value' => $dto->conditionValue,
            'discount_value' => $dto->discountValue,
            'start_at' => $dto->startAt,
            'end_at' => $dto->endAt,
            'is_active' => $dto->isActive,
            'priority' => $dto->priority,
            'max_uses' => $dto->maxUses,
        ]);

        return $rule;
    }

    public function deleteRule(PricingRule $rule): bool
    {
        $rule->delete();

        return true;
    }

    public function toggleActive(PricingRule $rule): PricingRule
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        return $rule;
    }

    public function getActiveRulesForProduct(Product $product, ?Variant $variant = null): Collection
    {
        return PricingRule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($product) {
                $query->where('scope', 'all')
                    ->orWhere(function ($q) use ($product) {
                        $q->where('scope', 'product')
                            ->where('scope_id', $product->id);
                    })
                    ->orWhere(function ($q) use ($product) {
                        $q->where('scope', 'category')
                            ->whereIn('scope_id', $product->categories()->pluck('categories.id'));
                    })
                    ->orWhere(function ($q) use ($product) {
                        $q->where('scope', 'brand')
                            ->where('scope_id', $product->brand_id);
                    });
            })
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->orderByDesc('priority')
            ->get();
    }

    public function applyRules(int $basePrice, Collection $rules): PriceResult
    {
        $finalPrice = $basePrice;
        $totalDiscount = 0;
        $appliedRule = null;

        foreach ($rules as $rule) {
            $discount = match ($rule->type) {
                'fixed' => min($rule->discount_value, $finalPrice),
                'percentage' => (int) round($finalPrice * ($rule->discount_value / 10000)),
                'tiered' => $this->calculateTieredDiscount($finalPrice, $rule),
                default => 0,
            };

            if ($discount > 0) {
                $finalPrice -= $discount;
                $totalDiscount += $discount;
                $appliedRule = $rule;
            }
        }

        return new PriceResult(
            basePrice: $basePrice,
            finalPrice: max($finalPrice, 0),
            discountAmount: $totalDiscount,
            appliedRule: $appliedRule,
        );
    }

    public function incrementUsage(PricingRule $rule): void
    {
        $rule->increment('used_count');
    }

    protected function calculateTieredDiscount(int $price, PricingRule $rule): int
    {
        $conditions = $rule->condition_value ?? [];

        $tiers = $conditions['tiers'] ?? [];

        usort($tiers, fn (array $a, array $b) => $b['min'] <=> $a['min']);

        foreach ($tiers as $tier) {
            if ($price >= ($tier['min'] ?? 0)) {
                if (isset($tier['percentage'])) {
                    return (int) round($price * ($tier['percentage'] / 100));
                }

                return (int) ($tier['fixed'] ?? 0);
            }
        }

        return 0;
    }
}
