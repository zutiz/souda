<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\BrandDTO;
use App\Modules\Product\Http\Requests\StoreBrandRequest;
use App\Modules\Product\Models\Brand;
use App\Modules\Product\Services\BrandService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BrandController
{
    public function __construct(
        protected BrandService $brandService,
    ) {}

    public function index(): Response
    {
        $brands = Brand::query()->orderBy('name')->paginate(25);

        return Inertia::render('Product/Brand/Index', [
            'brands' => $brands,
        ]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $dto = BrandDTO::fromRequest($request->validated());
        $this->brandService->createBrand($dto);

        return redirect()->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function update(StoreBrandRequest $request, Brand $brand): RedirectResponse
    {
        $dto = BrandDTO::fromRequest($request->validated());
        $this->brandService->updateBrand($brand, $dto);

        return redirect()->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->brandService->deleteBrand($brand);

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
