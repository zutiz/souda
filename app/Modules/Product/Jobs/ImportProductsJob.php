<?php

declare(strict_types=1);

namespace App\Modules\Product\Jobs;

use App\Modules\Product\Services\ProductImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 300;

    public function __construct(
        public string $filePath,
        public int $userId,
        public array $options = [],
    ) {
        $this->onQueue('high');
    }

    public function handle(ProductImportService $importService): void
    {
        $importService->importCSV($this->filePath, $this->options);
    }
}
