<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\InventoryBalanceService;
use Illuminate\Console\Command;

class ReconcileCommand extends Command
{
    protected $signature = 'inventory:reconcile
        {--fix : Automatically fix discrepancies by rebuilding balances}';

    protected $description = 'Compare ledger totals against balance snapshots and report discrepancies';

    public function handle(InventoryBalanceService $balanceService): int
    {
        $groups = InventoryLedger::query()
            ->selectRaw('product_id, COALESCE(variant_id, \'\') as variant_key, warehouse_id')
            ->selectRaw('SUM(quantity) as ledger_quantity')
            ->groupBy('product_id', 'variant_key', 'warehouse_id')
            ->get();

        $discrepancies = 0;

        foreach ($groups as $group) {
            $variantId = $group->variant_key !== '' ? $group->variant_key : null;

            $balance = InventoryBalance::where([
                'product_id' => $group->product_id,
                'warehouse_id' => $group->warehouse_id,
            ])->where('variant_id', $variantId)->first();

            $balanceQty = $balance?->quantity ?? 0;
            $ledgerQty = (int) $group->ledger_quantity;

            if ($balanceQty !== $ledgerQty) {
                $discrepancies++;

                $this->warn(
                    "Discrepancy: product={$group->product_id} warehouse={$group->warehouse_id} ".
                    "balance={$balanceQty} ledger={$ledgerQty}"
                );
            }
        }

        if ($discrepancies === 0) {
            $this->info('All balances match the ledger.');

            return 0;
        }

        $this->line("Found {$discrepancies} discrepancy(ies).");

        if ($this->option('fix')) {
            $this->info('Rebuilding balances from ledger...');
            $count = $balanceService->rebuildFromLedger();
            $this->info("Rebuilt {$count} balance record(s).");
        }

        return 0;
    }
}
