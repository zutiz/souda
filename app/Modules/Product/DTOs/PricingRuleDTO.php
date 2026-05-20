<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\PricingRuleConditionEnum;
use App\Modules\Product\Enums\PricingRuleScopeEnum;
use App\Modules\Product\Enums\PricingRuleTypeEnum;
use App\Modules\Product\Models\PricingRule;
use Carbon\CarbonImmutable;

readonly class PricingRuleDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public PricingRuleTypeEnum $type,
        public PricingRuleScopeEnum $scope,
        public ?int $scopeId,
        public ?PricingRuleConditionEnum $conditionType,
        public ?array $conditionValue,
        public int $discountValue,
        public ?CarbonImmutable $startAt,
        public ?CarbonImmutable $endAt,
        public bool $isActive,
        public int $priority,
        public ?int $maxUses,
        public int $usedCount,
    ) {}

    public static function fromModel(PricingRule $rule): self
    {
        return new self(
            id: $rule->id,
            name: $rule->name,
            type: PricingRuleTypeEnum::from($rule->type),
            scope: PricingRuleScopeEnum::from($rule->scope),
            scopeId: $rule->scope_id,
            conditionType: $rule->condition_type ? PricingRuleConditionEnum::tryFrom($rule->condition_type) : null,
            conditionValue: $rule->condition_value,
            discountValue: $rule->discount_value,
            startAt: $rule->start_at ? CarbonImmutable::instance($rule->start_at) : null,
            endAt: $rule->end_at ? CarbonImmutable::instance($rule->end_at) : null,
            isActive: $rule->is_active,
            priority: $rule->priority,
            maxUses: $rule->max_uses,
            usedCount: $rule->used_count,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            name: $data['name'],
            type: PricingRuleTypeEnum::from($data['type']),
            scope: PricingRuleScopeEnum::from($data['scope']),
            scopeId: $data['scope_id'] ?? null,
            conditionType: isset($data['condition_type']) ? PricingRuleConditionEnum::tryFrom($data['condition_type']) : null,
            conditionValue: $data['condition_value'] ?? null,
            discountValue: (int) $data['discount_value'],
            startAt: isset($data['start_at']) ? CarbonImmutable::parse($data['start_at']) : null,
            endAt: isset($data['end_at']) ? CarbonImmutable::parse($data['end_at']) : null,
            isActive: (bool) ($data['is_active'] ?? true),
            priority: (int) ($data['priority'] ?? 0),
            maxUses: isset($data['max_uses']) ? (int) $data['max_uses'] : null,
            usedCount: 0,
        );
    }
}
