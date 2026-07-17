<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Services\ScheduledTaskLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class OperationsController
{
    protected array $commands = [
        'inventory:expire-reservations' => ['frequency' => 'Every minute', 'description' => 'Release expired stock reservations'],
        'inventory:expiry-alerts' => ['frequency' => 'Daily at 06:00', 'description' => 'Find batches expiring within threshold'],
        'inventory:classify-stock' => ['frequency' => 'Daily at 02:00', 'description' => 'Classify inventory by ABC value and velocity'],
        'inventory:cycle-count' => ['frequency' => 'Daily at 03:00', 'description' => 'Generate cycle counts for random subset'],
        'inventory:generate-forecasts' => ['frequency' => 'Daily at 04:00', 'description' => 'Generate demand forecasts'],
        'inventory:generate-suggestions' => ['frequency' => 'Daily at 05:00', 'description' => 'Generate purchase suggestions'],
        'inventory:evaluate-rules' => ['frequency' => 'Every 15 minutes', 'description' => 'Evaluate all active automation rules'],
        'inventory:reconcile' => ['frequency' => 'Daily at 01:00', 'description' => 'Compare ledger totals against balance snapshots'],
        'inventory:dead-stock-report' => ['frequency' => 'Daily at 23:00', 'description' => 'Find products with no stock movement'],
    ];

    public function __construct(
        protected ScheduledTaskLogService $logService,
    ) {}

    public function index(): Response
    {
        $latestLogs = $this->logService->getLatestPerCommand();

        $schedules = [];
        foreach ($this->commands as $command => $meta) {
            $log = $latestLogs[$command] ?? null;
            $schedules[] = [
                'command' => $command,
                'frequency' => $meta['frequency'],
                'description' => $meta['description'],
                'last_run' => $log?->started_at?->diffForHumans(),
                'last_run_at' => $log?->started_at,
                'duration_ms' => $log?->duration_ms,
                'status' => $log?->status ?? 'never',
            ];
        }

        return Inertia::render('Inventory/Operations', [
            'schedules' => $schedules,
        ]);
    }

    public function run(Request $request, string $command): JsonResponse
    {
        if (! isset($this->commands[$command])) {
            return response()->json(['error' => 'Unknown command.'], 404);
        }

        $log = $this->logService->recordStart($command);

        try {
            $start = microtime(true);
            $exitCode = Artisan::call($command);
            $output = Artisan::output();
            $duration = (int) ((microtime(true) - $start) * 1000);

            if ($exitCode === 0) {
                $log->update([
                    'status' => 'success',
                    'duration_ms' => $duration,
                    'output' => $output,
                    'finished_at' => now(),
                ]);
            } else {
                $log->update([
                    'status' => 'failed',
                    'duration_ms' => $duration,
                    'output' => $output ?: "Exit code: {$exitCode}",
                    'finished_at' => now(),
                ]);
            }

            return response()->json([
                'success' => $exitCode === 0,
                'duration_ms' => $duration,
                'output' => $output,
            ]);
        } catch (\Throwable $e) {
            $duration = (int) ((microtime(true) - ($log->started_at?->timestamp ?? now()->timestamp)) * 1000);

            $log->update([
                'status' => 'failed',
                'duration_ms' => $duration,
                'output' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'duration_ms' => $duration,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
