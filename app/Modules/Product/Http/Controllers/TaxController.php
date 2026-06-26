<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\TaxCategoryDTO;
use App\Modules\Product\DTOs\TaxRateDTO;
use App\Modules\Product\Models\TaxCategory;
use App\Modules\Product\Models\TaxRate;
use App\Modules\Product\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxController
{
    public function __construct(
        protected TaxService $taxService,
    ) {}

    public function categories(): Response
    {
        $categories = TaxCategory::query()->with('rates')->paginate(25);

        return Inertia::render('Product/Tax/Categories', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $dto = TaxCategoryDTO::fromRequest($request->all());
        $this->taxService->createTaxCategory($dto);

        return redirect()->route('tax.categories.index')
            ->with('success', 'Tax category created successfully.');
    }

    public function updateCategory(Request $request, TaxCategory $category): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $dto = TaxCategoryDTO::fromRequest($request->all());
        $this->taxService->updateTaxCategory($category, $dto);

        return redirect()->route('tax.categories.index')
            ->with('success', 'Tax category updated successfully.');
    }

    public function destroyCategory(TaxCategory $category): RedirectResponse
    {
        $this->taxService->deleteTaxCategory($category);

        return redirect()->route('tax.categories.index')
            ->with('success', 'Tax category deleted successfully.');
    }

    public function storeRate(Request $request): RedirectResponse
    {
        $request->validate([
            'tax_category_id' => ['required', 'exists:tax_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_compound' => ['boolean'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0'],
        ]);

        $dto = TaxRateDTO::fromRequest($request->all());
        $this->taxService->createTaxRate($dto);

        return redirect()->route('tax.categories.index')
            ->with('success', 'Tax rate created successfully.');
    }

    public function updateRate(Request $request, TaxRate $rate): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_compound' => ['boolean'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0'],
        ]);

        $dto = TaxRateDTO::fromRequest($request->all());
        $this->taxService->updateTaxRate($rate, $dto);

        return redirect()->route('tax.categories.index')
            ->with('success', 'Tax rate updated successfully.');
    }

    public function destroyRate(TaxRate $rate): RedirectResponse
    {
        $this->taxService->deleteTaxRate($rate);

        return redirect()->route('tax.categories.index')
            ->with('success', 'Tax rate deleted successfully.');
    }
}
