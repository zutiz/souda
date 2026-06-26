<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\BrandDTO;
use App\Modules\Product\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandService
{
    public function createBrand(BrandDTO $dto): Brand
    {
        return Brand::query()->create([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
            'logo_path' => $dto->logoPath,
            'website_url' => $dto->websiteUrl,
            'is_active' => $dto->isActive,
        ]);
    }

    public function updateBrand(Brand $brand, BrandDTO $dto): Brand
    {
        $brand->update([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
            'logo_path' => $dto->logoPath,
            'website_url' => $dto->websiteUrl,
            'is_active' => $dto->isActive,
        ]);

        return $brand;
    }

    public function deleteBrand(Brand $brand): bool
    {
        $brand->delete();

        return true;
    }

    public function listActiveBrands(): Collection
    {
        return Brand::query()->active()->orderBy('name')->get();
    }
}
