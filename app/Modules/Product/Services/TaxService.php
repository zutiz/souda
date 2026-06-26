<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\TaxCategoryDTO;
use App\Modules\Product\DTOs\TaxRateDTO;
use App\Modules\Product\DTOs\TaxResult;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\TaxCategory;
use App\Modules\Product\Models\TaxRate;
use Illuminate\Database\Eloquent\Collection;

class TaxService
{
    public function createTaxCategory(TaxCategoryDTO $dto): TaxCategory
    {
        return TaxCategory::query()->create([
            'name' => $dto->name,
            'description' => $dto->description,
        ]);
    }

    public function updateTaxCategory(TaxCategory $category, TaxCategoryDTO $dto): TaxCategory
    {
        $category->update([
            'name' => $dto->name,
            'description' => $dto->description,
        ]);

        return $category;
    }

    public function deleteTaxCategory(TaxCategory $category): bool
    {
        $category->delete();

        return true;
    }

    public function createTaxRate(TaxRateDTO $dto): TaxRate
    {
        return TaxRate::query()->create([
            'tax_category_id' => $dto->taxCategoryId,
            'name' => $dto->name,
            'rate' => $dto->rate,
            'country' => $dto->country,
            'state' => $dto->state,
            'postal_code' => $dto->postalCode,
            'is_compound' => $dto->isCompound,
            'is_active' => $dto->isActive,
            'priority' => $dto->priority,
        ]);
    }

    public function updateTaxRate(TaxRate $rate, TaxRateDTO $dto): TaxRate
    {
        $rate->update([
            'tax_category_id' => $dto->taxCategoryId,
            'name' => $dto->name,
            'rate' => $dto->rate,
            'country' => $dto->country,
            'state' => $dto->state,
            'postal_code' => $dto->postalCode,
            'is_compound' => $dto->isCompound,
            'is_active' => $dto->isActive,
            'priority' => $dto->priority,
        ]);

        return $rate;
    }

    public function deleteTaxRate(TaxRate $rate): bool
    {
        $rate->delete();

        return true;
    }

    public function calculateTaxForProduct(int $priceAmount, Product $product, ?array $location = null): TaxResult
    {
        $taxCategory = $product->taxCategory;

        if ($taxCategory === null) {
            return new TaxResult(
                netAmount: $priceAmount,
                taxAmount: 0,
                grossAmount: $priceAmount,
                appliedRates: [],
            );
        }

        $rates = $this->getApplicableRates($taxCategory->id, $location);

        $netAmount = $product->tax_inclusive ? $priceAmount : $priceAmount;
        $taxAmount = 0;
        $appliedRates = [];

        foreach ($rates as $rate) {
            if ($rate->is_compound) {
                $taxOnBase = (int) round($netAmount * ($rate->rate / 100));
            } else {
                $taxOnBase = (int) round($priceAmount * ($rate->rate / 100));
            }

            $taxAmount += $taxOnBase;

            $appliedRates[] = [
                'rate_id' => $rate->id,
                'name' => $rate->name,
                'rate' => $rate->rate,
                'amount' => $taxOnBase,
                'is_compound' => $rate->is_compound,
            ];
        }

        if ($product->tax_inclusive) {
            $netAmount = $priceAmount - $taxAmount;
            $grossAmount = $priceAmount;
        } else {
            $netAmount = $priceAmount;
            $grossAmount = $priceAmount + $taxAmount;
        }

        return new TaxResult(
            netAmount: $netAmount,
            taxAmount: $taxAmount,
            grossAmount: $grossAmount,
            appliedRates: $appliedRates,
        );
    }

    public function getApplicableRates(?int $taxCategoryId = null, ?array $location = null): Collection
    {
        $query = TaxRate::query()->where('is_active', true);

        if ($taxCategoryId !== null) {
            $query->where('tax_category_id', $taxCategoryId);
        }

        if ($location !== null) {
            $query->where(function ($q) use ($location) {
                $q->whereNull('country')
                    ->orWhere('country', $location['country'] ?? '');

                $q->where(function ($sub) use ($location) {
                    $sub->whereNull('state')
                        ->orWhere('state', $location['state'] ?? '');
                });

                $q->where(function ($sub) use ($location) {
                    $sub->whereNull('postal_code')
                        ->orWhere('postal_code', $location['postal_code'] ?? '');
                });
            });
        }

        return $query->orderBy('priority')->get();
    }
}
