<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\PricingRuleDTO;
use App\Modules\Product\Http\Requests\StorePricingRuleRequest;
use App\Modules\Product\Models\PricingRule;
use App\Modules\Product\Services\PricingRuleService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PricingRuleController
{
    public function __construct(
        protected PricingRuleService $pricingRuleService,
    ) {}

    public function index(): Response
    {
        $rules = PricingRule::query()->orderByDesc('priority')->paginate(25);

        return Inertia::render('Product/PricingRule/Index', [
            'rules' => $rules,
        ]);
    }

    public function store(StorePricingRuleRequest $request): RedirectResponse
    {
        $dto = PricingRuleDTO::fromRequest($request->validated());
        $this->pricingRuleService->createRule($dto);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule created successfully.');
    }

    public function update(StorePricingRuleRequest $request, PricingRule $rule): RedirectResponse
    {
        $dto = PricingRuleDTO::fromRequest($request->validated());
        $this->pricingRuleService->updateRule($rule, $dto);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule updated successfully.');
    }

    public function destroy(PricingRule $rule): RedirectResponse
    {
        $this->pricingRuleService->deleteRule($rule);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule deleted successfully.');
    }

    public function toggle(PricingRule $rule): RedirectResponse
    {
        $this->pricingRuleService->toggleActive($rule);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule toggled successfully.');
    }
}
