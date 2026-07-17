<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Enums\RuleActionTypeEnum;
use App\Modules\Inventory\Enums\RuleConditionTypeEnum;
use App\Modules\Inventory\Models\InventoryRule;
use App\Modules\Inventory\Services\RuleEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RuleController
{
    public function __construct(
        protected RuleEngine $ruleEngine,
    ) {}

    public function index(): Response
    {
        $rules = InventoryRule::withCount('alerts')
            ->latest()
            ->paginate(25);

        return Inertia::render('Inventory/Rules/Index', [
            'rules' => $rules,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventory/Rules/Create', [
            'conditionTypes' => collect(RuleConditionTypeEnum::cases())->map(fn ($c) => [
                'value' => $c->value,
                'label' => $c->label(),
                'defaultConfig' => $c->defaultConfig(),
            ]),
            'actionTypes' => collect(RuleActionTypeEnum::cases())->map(fn ($a) => [
                'value' => $a->value,
                'label' => $a->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'condition_type' => ['required', 'string', 'in:'.implode(',', array_column(RuleConditionTypeEnum::cases(), 'value'))],
            'condition_config' => ['required', 'array'],
            'action_type' => ['required', 'string', 'in:'.implode(',', array_column(RuleActionTypeEnum::cases(), 'value'))],
            'action_config' => ['required', 'array'],
            'schedule' => ['required', 'string', 'in:every_fifteen_minutes,hourly,daily'],
        ]);

        $rule = InventoryRule::create($validated);

        return redirect()->route('inventory.rules.show', $rule)
            ->with('success', 'Rule created successfully.');
    }

    public function show(InventoryRule $rule): Response
    {
        $rule->loadCount('alerts');
        $rule->load(['alerts' => fn ($q) => $q->latest()->limit(50)]);

        return Inertia::render('Inventory/Rules/Show', [
            'rule' => $rule,
        ]);
    }

    public function update(Request $request, InventoryRule $rule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'condition_config' => ['sometimes', 'required', 'array'],
            'action_config' => ['sometimes', 'required', 'array'],
            'schedule' => ['sometimes', 'required', 'string', 'in:every_fifteen_minutes,hourly,daily'],
        ]);

        $rule->update($validated);

        return redirect()->route('inventory.rules.show', $rule)
            ->with('success', 'Rule updated successfully.');
    }

    public function toggle(InventoryRule $rule): RedirectResponse
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        return redirect()->back()
            ->with('success', $rule->is_active ? 'Rule enabled.' : 'Rule disabled.');
    }

    public function destroy(InventoryRule $rule): RedirectResponse
    {
        $rule->delete();

        return redirect()->route('inventory.rules.index')
            ->with('success', 'Rule deleted.');
    }

    public function evaluate(InventoryRule $rule): RedirectResponse
    {
        $result = $this->ruleEngine->evaluateRule($rule);
        $rule->update(['last_run_at' => now()]);

        return redirect()->route('inventory.rules.show', $rule)
            ->with('success', "Rule evaluated: {$result['triggered']} alert(s) created.");
    }
}
