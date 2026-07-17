<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Services\RuleEngine;
use Illuminate\Console\Command;

class EvaluateRulesCommand extends Command
{
    protected $signature = 'inventory:evaluate-rules
        {--rule= : Specific rule ID to evaluate}
        {--warehouse= : Warehouse ID to scope evaluation}
        {--classify-first : Run stock classification before evaluating rules}';

    protected $description = 'Evaluate all active automation rules';

    public function handle(RuleEngine $ruleEngine): int
    {
        if ($this->option('classify-first')) {
            $this->call(ClassifyStockCommand::class, array_filter([
                '--warehouse' => $this->option('warehouse'),
            ]));
        }

        $ruleId = $this->option('rule') ? (int) $this->option('rule') : null;
        $warehouseId = $this->option('warehouse') ? (int) $this->option('warehouse') : null;

        $results = $ruleEngine->evaluateAll($ruleId, $warehouseId);

        if (empty($results)) {
            $this->warn('No active rules found to evaluate.');

            return 0;
        }

        $totalTriggered = 0;

        foreach ($results as $id => $result) {
            $totalTriggered += $result['triggered'];

            if (! empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->error("Rule #{$id}: {$error}");
                }
            }
        }

        $this->info("Rule evaluation complete: {$totalTriggered} alert(s) created across ".count($results).' rule(s).');

        return 0;
    }
}
