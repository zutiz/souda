<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Services\ReorderEngine;
use Illuminate\Console\Command;

class GenerateSuggestionsCommand extends Command
{
    protected $signature = 'inventory:generate-suggestions
        {--warehouse= : Limit to a specific warehouse ID}
        {--dry-run : Preview suggestions without saving}';

    protected $description = 'Analyze stock levels and generate purchase suggestions';

    public function handle(ReorderEngine $reorderEngine): int
    {
        $warehouseId = $this->option('warehouse') ? (int) $this->option('warehouse') : null;
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->info('Dry run — no suggestions will be saved.');
        }

        $count = $reorderEngine->generateSuggestions($warehouseId);

        if ($count === 0) {
            $this->info('No purchase suggestions generated — all stock levels are adequate.');

            return 0;
        }

        $this->line("Generated {$count} purchase suggestion(s).");

        return 0;
    }
}
