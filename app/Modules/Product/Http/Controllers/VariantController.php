<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\VariantDTO;
use App\Modules\Product\Http\Requests\StoreVariantRequest;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use App\Modules\Product\Services\VariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VariantController
{
    public function __construct(
        protected VariantService $variantService,
    ) {}

    public function index(Product $product): Response
    {
        $variants = $product->variants()->with('attributeValues')->get();

        return Inertia::render('Product/Variant/Index', [
            'product' => $product,
            'variants' => $variants,
        ]);
    }

    public function store(StoreVariantRequest $request, Product $product): RedirectResponse
    {
        $dto = VariantDTO::fromRequest(array_merge(
            $request->validated(),
            ['product_id' => $product->id],
        ));

        $this->variantService->createVariant($dto);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variant created successfully.');
    }

    public function update(StoreVariantRequest $request, Product $product, Variant $variant): RedirectResponse
    {
        $dto = VariantDTO::fromRequest(array_merge(
            $request->validated(),
            ['product_id' => $product->id],
        ));

        $this->variantService->updateVariant($variant, $dto);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variant updated successfully.');
    }

    public function destroy(Product $product, Variant $variant): RedirectResponse
    {
        $this->variantService->deleteVariant($variant);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variant deleted successfully.');
    }

    public function generate(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'combinations' => ['required', 'array'],
            'combinations.*.name' => ['required', 'string', 'max:500'],
            'combinations.*.attribute_value_ids' => ['required', 'array'],
        ]);

        $this->variantService->generateVariants($product, $request->input('combinations'));

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Variants generated successfully.');
    }

    public function setDefault(Product $product, Variant $variant): RedirectResponse
    {
        $this->variantService->setDefaultVariant($product, $variant);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Default variant set successfully.');
    }
}
