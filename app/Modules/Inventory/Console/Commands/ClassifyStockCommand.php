<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Services\StockClassificationService;
use Illuminate\Console\Command;

class ClassifyStockCommand extends Command
{
    protected $signature = 'inventory:classify-stock
        {--warehouse= : Warehouse ID to classify}
        {--abc-only : Only run ABC classification}
        {--velocity-only : Only run velocity classification}';

    protected $description = 'Classify inventory balances by ABC value and sales velocity';

    public function handle(StockClassificationService $classificationService): int
    {
        $warehouseId = $this->option('warehouse') ? (int) $this->option('warehouse') : null;

        $abcOnly = (bool) $this->option('abc-only');
        $velocityOnly = (bool) $this->option('velocity-only');

        if ($velocityOnly) {
            $velocity = $classificationService->classifyVelocity($warehouseId);
            $this->table(
                ['Class', 'Count'],
                [['Fast', $velocity['fast']], ['Slow', $velocity['slow']], ['Dead', $velocity['dead']], ['New', $velocity['new']]],
            );

            return 0;
        }

        $abc = $classificationService->classifyAbc($warehouseId);
        $this->table(
            ['Class', 'Count'],
            [['A', $abc['a']], ['B', $abc['b']], ['C', $abc['c']]],
        );

        if ($abcOnly) {
            return 0;
        }

        $velocity = $classificationService->classifyVelocity($warehouseId);
        $this->table(
            ['Class', 'Count'],
            [['Fast', $velocity['fast']], ['Slow', $velocity['slow']], ['Dead', $velocity['dead']], ['New', $velocity['new']]],
        );

        $this->info('Stock classification complete.');

        return 0;
    }
}
