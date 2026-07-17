<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\SerialStatusEnum;
use App\Modules\Inventory\Events\SerialNumberSold;
use App\Modules\Inventory\Exceptions\SerialNumberAlreadyExistsException;
use App\Modules\Inventory\Models\SerialNumber;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SerialNumberService
{
    public function register(
        string $productId,
        string $serialNumber,
        ?string $variantId = null,
        ?int $warehouseId = null,
        ?int $batchId = null,
        ?CarbonInterface $warrantyExpiresAt = null,
    ): SerialNumber {
        $existing = SerialNumber::where([
            'product_id' => $productId,
            'serial_number' => $serialNumber,
        ])->first();

        if ($existing !== null) {
            throw new SerialNumberAlreadyExistsException($serialNumber, $productId);
        }

        return SerialNumber::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'serial_number' => $serialNumber,
            'status' => SerialStatusEnum::Available,
            'warehouse_id' => $warehouseId,
            'batch_id' => $batchId,
            'warranty_expires_at' => $warrantyExpiresAt,
        ]);
    }

    public function registerBatch(
        string $productId,
        array $serialNumbers,
        ?string $variantId = null,
        ?int $warehouseId = null,
        ?int $batchId = null,
        ?CarbonInterface $warrantyExpiresAt = null,
    ): Collection {
        $results = new Collection;

        DB::transaction(function () use (
            $productId, $serialNumbers, $variantId, $warehouseId, $batchId,
            $warrantyExpiresAt, $results
        ) {
            foreach ($serialNumbers as $serialNumber) {
                $results->push($this->register(
                    productId: $productId,
                    serialNumber: $serialNumber,
                    variantId: $variantId,
                    warehouseId: $warehouseId,
                    batchId: $batchId,
                    warrantyExpiresAt: $warrantyExpiresAt,
                ));
            }
        });

        return $results;
    }

    public function validate(string $serialNumber, string $productId): bool
    {
        $serial = SerialNumber::where([
            'product_id' => $productId,
            'serial_number' => $serialNumber,
        ])->first();

        return $serial !== null && $serial->status === SerialStatusEnum::Available;
    }

    public function markAsSold(string $serialNumber, string $productId, string $orderReference): SerialNumber
    {
        return DB::transaction(function () use ($serialNumber, $productId, $orderReference) {
            $serial = SerialNumber::where([
                'product_id' => $productId,
                'serial_number' => $serialNumber,
            ])->lockForUpdate()->firstOrFail();

            $serial->markAsSold($orderReference);

            event(new SerialNumberSold(
                serialId: $serial->id,
                serialNumber: $serial->serial_number,
                productId: $productId,
                orderReference: $orderReference,
            ));

            return $serial->fresh();
        });
    }

    public function markAsReturned(string $serialNumber, string $productId): SerialNumber
    {
        return DB::transaction(function () use ($serialNumber, $productId) {
            $serial = SerialNumber::where([
                'product_id' => $productId,
                'serial_number' => $serialNumber,
            ])->lockForUpdate()->firstOrFail();

            $serial->markAsReturned();

            return $serial->fresh();
        });
    }

    public function findByStatus(string $status, ?string $productId = null): Collection
    {
        $query = SerialNumber::where('status', $status);

        if ($productId !== null) {
            $query->where('product_id', $productId);
        }

        return $query->get();
    }

    public function warrantyStatus(string $serialNumber): string
    {
        $serial = SerialNumber::where('serial_number', $serialNumber)->first();

        if ($serial === null) {
            return 'not_found';
        }

        if ($serial->warranty_expires_at === null) {
            return 'no_warranty';
        }

        return $serial->isUnderWarranty() ? 'active' : 'expired';
    }
}
