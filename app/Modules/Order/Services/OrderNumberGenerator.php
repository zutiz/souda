<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use Illuminate\Support\Facades\DB;

class OrderNumberGenerator
{
    public function generate(?string $storeCode = null): string
    {
        $prefix = $storeCode ? strtoupper($storeCode) : 'ORD';
        $datePart = now()->format('ymd');
        $sequence = $this->nextSequence($prefix, $datePart);

        return sprintf('%s-%s-%04d', $prefix, $datePart, $sequence);
    }

    public function generateShipmentNumber(): string
    {
        $prefix = 'SHIP';
        $datePart = now()->format('ymd');
        $sequence = $this->nextSequence($prefix, $datePart);

        return sprintf('%s-%s-%04d', $prefix, $datePart, $sequence);
    }

    private function nextSequence(string $prefix, string $datePart): int
    {
        $lockKey = "order_number_sequence:{$prefix}:{$datePart}";

        return DB::transaction(function () use ($prefix, $datePart) {
            $current = (int) DB::table('order_number_sequences')
                ->where('prefix', $prefix)
                ->where('date_part', $datePart)
                ->value('last_sequence');

            $next = $current + 1;

            DB::table('order_number_sequences')
                ->updateOrInsert(
                    ['prefix' => $prefix, 'date_part' => $datePart],
                    ['last_sequence' => $next, 'updated_at' => now()],
                );

            return $next;
        });
    }
}
