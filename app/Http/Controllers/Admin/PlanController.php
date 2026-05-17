<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TransformsPlansForFrontend;
use App\Modules\Billing\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    use TransformsPlansForFrontend;

    public function __construct(
        private readonly PlanService $planService,
    ) {}

    public function index(): Response
    {
        $plans = $this->planService->getAllPlans();

        return Inertia::render('admin/pricing/index', [
            'products' => $this->transformPlans($plans),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/pricing/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:billing_plans,slug'],
            'description' => ['nullable', 'string'],
            'monthly_price' => ['required', 'integer', 'min:0'],
            'yearly_price' => ['nullable', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'features' => ['nullable', 'array'],
            'limits' => ['nullable', 'array'],
            'popular' => ['boolean'],
            'trial_enabled' => ['boolean'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'trial_without_card' => ['boolean'],
        ]);

        $validated['is_active'] = true;
        $validated['display_order'] = $this->planService->getAllPlans()->count() + 1;

        $this->planService->create($validated);

        return redirect()->route('pricing.index')
            ->with('success', 'Plan created successfully.');
    }

    public function show(int $id): Response
    {
        $plan = $this->planService->findOrFail($id);
        $transformed = $this->transformPlanForFrontend($plan);
        $prices = $transformed['prices'];

        unset($transformed['prices']);

        return Inertia::render('admin/pricing/show', [
            'product' => $transformed,
            'prices' => $prices,
        ]);
    }

    public function edit(int $id): Response
    {
        $plan = $this->planService->findOrFail($id);

        return Inertia::render('admin/pricing/edit', [
            'product' => [
                'id' => (string) $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'active' => $plan->is_active,
                'popular' => $plan->popular,
                'cta' => $plan->cta ?? '',
                'trial_enabled' => $plan->trial_enabled,
                'trial_days' => $plan->trial_days,
                'trial_without_card' => $plan->trial_without_card,
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $plan = $this->planService->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:billing_plans,slug,'.$id],
            'description' => ['nullable', 'string'],
            'monthly_price' => ['required', 'integer', 'min:0'],
            'yearly_price' => ['nullable', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'features' => ['nullable', 'array'],
            'limits' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'popular' => ['boolean'],
            'cta' => ['nullable', 'string', 'max:255'],
            'trial_enabled' => ['boolean'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'trial_without_card' => ['boolean'],
        ]);

        $this->planService->update($plan, $validated);

        return redirect()->route('pricing.show', $id)
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $plan = $this->planService->findOrFail($id);
        $this->planService->delete($plan);

        return redirect()->route('pricing.index')
            ->with('success', 'Plan deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:billing_plans,id'],
        ]);

        $this->planService->reorder($request->input('order'));

        return response()->json(['message' => 'Order updated.']);
    }

    public function updateFeatures(Request $request, int $id): JsonResponse
    {
        $plan = $this->planService->findOrFail($id);

        $validated = $request->validate([
            'features' => ['required', 'array'],
            'features.*' => ['string'],
        ]);

        $this->planService->update($plan, [
            'features' => $validated['features'],
        ]);

        return response()->json(['message' => 'Features updated.']);
    }

    public function storePrice(Request $request, int $id): RedirectResponse
    {
        return redirect()->route('pricing.index')
            ->with('info', 'Price configuration is managed via monthly/yearly price fields on the plan.');
    }

    public function updatePrice(Request $request, int $id): RedirectResponse
    {
        return redirect()->route('pricing.index')
            ->with('info', 'Price management is handled directly on the plan.');
    }

    public function destroyPrice(int $id): RedirectResponse
    {
        return redirect()->route('pricing.index')
            ->with('info', 'Price management is handled directly on the plan.');
    }
}
