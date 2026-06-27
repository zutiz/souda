<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Http\Requests\StoreProductRequest;
use App\Modules\Product\Http\Requests\UpdateProductRequest;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\BrandService;
use App\Modules\Product\Services\CategoryService;
use App\Modules\Product\Services\ProductService;
use App\Modules\Product\ValueObjects\ProductSearchCriteria;
use App\Modules\Store\Services\StoreContextManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController
{
    use AuthorizesRequests;

    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
        protected BrandService $brandService,
        protected StoreContextManager $storeContext,
    ) {}

    public function index(Request $request): Response
    {
        $criteria = ProductSearchCriteria::fromRequest($request->all());
        $storeId = $this->storeContext->id();

        if ($storeId) {
            $criteria->storeId = $storeId;
        }

        $products = $this->productService->listProducts($criteria);

        return Inertia::render('Product/Index', [
            'products' => $products,
            'filters' => $criteria->toQueryParams(),
            'currentStoreId' => $storeId,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Product/Create', [
            'categories' => $this->categoryService->getCategoryTree(),
            'brands' => $this->brandService->listActiveBrands(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $dto = ProductDTO::fromRequest($request->validated());
        $product = $this->productService->createProduct($dto);

        $storeId = $this->storeContext->id();
        if ($storeId) {
            $product->stores()->attach($storeId, [
                'price' => $dto->basePrice,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): Response
    {
        $product->loadMissing(['category', 'brand', 'variants', 'media', 'warehouseStock.warehouse']);

        return Inertia::render('Product/Show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->loadMissing(['category', 'brand', 'variants', 'media', 'categories']);

        return Inertia::render('Product/Edit', [
            'product' => $product,
            'categories' => $this->categoryService->getCategoryTree(),
            'brands' => $this->brandService->listActiveBrands(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $dto = ProductDTO::fromRequest($request->validated());
        $this->productService->updateProduct($product, $dto);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function archive(Product $product): RedirectResponse
    {
        $this->productService->archiveProduct($product);

        return redirect()->route('products.index')
            ->with('success', 'Product archived successfully.');
    }

    public function restore(Product $product): RedirectResponse
    {
        $this->productService->restoreProduct($product);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product restored successfully.');
    }

    public function publish(Product $product): RedirectResponse
    {
        $this->productService->publishProduct($product);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product published successfully.');
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $clone = $this->productService->duplicateProduct($product);

        return redirect()->route('products.edit', $clone)
            ->with('success', 'Product duplicated successfully.');
    }
}
