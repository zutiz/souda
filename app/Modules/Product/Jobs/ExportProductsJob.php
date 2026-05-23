<?php

declare(strict_types=1);

namespace App\Modules\Product\Jobs;

use App\Modules\Product\Services\ProductImportService;
use App\Modules\Product\ValueObjects\ProductSearchCriteria;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 300;

    public function __construct(
        public ProductSearchCriteria $criteria,
        public string $format,
        public int $userId,
    ) {
        $this->onQueue('low');
    }

    public function handle(ProductImportService $importService): void
    {
        $csv = $importService->exportProducts($this->criteria, $this->format);

        $filename = "product_export_{$this->userId}_".now()->format('Ymd_His').".{$this->format}";

        Storage::disk('local')->put("exports/{$filename}", $csv);
    }
}
