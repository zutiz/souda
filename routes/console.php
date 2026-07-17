<?php

use App\Console\Commands\ExpireStockReservations;
use App\Console\Commands\ExpireSubscriptions;
use App\Modules\Inventory\Console\Commands\ClassifyStockCommand;
use App\Modules\Inventory\Console\Commands\CycleCountCommand;
use App\Modules\Inventory\Console\Commands\DeadStockReportCommand;
use App\Modules\Inventory\Console\Commands\EvaluateRulesCommand;
use App\Modules\Inventory\Console\Commands\ExpiryAlertsCommand;
use App\Modules\Inventory\Console\Commands\GenerateForecastsCommand;
use App\Modules\Inventory\Console\Commands\GenerateSuggestionsCommand;
use App\Modules\Inventory\Console\Commands\ReconcileCommand;
use App\Modules\Inventory\Models\ScheduledTaskLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ExpireSubscriptions::class)
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/subscription-expiry.log'))
    ->onSuccess(fn () => logTaskSuccess('subscription:expire-expired'))
    ->onFailure(fn () => logTaskFailure('subscription:expire-expired'));

Schedule::command(ExpireStockReservations::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/stock-reservation-expiry.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:expire-reservations'))
    ->onFailure(fn () => logTaskFailure('inventory:expire-reservations'));

Schedule::command(ExpiryAlertsCommand::class)
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-expiry-alerts.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:expiry-alerts'))
    ->onFailure(fn () => logTaskFailure('inventory:expiry-alerts'));

Schedule::command(GenerateSuggestionsCommand::class)
    ->dailyAt('05:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-suggestions.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:generate-suggestions'))
    ->onFailure(fn () => logTaskFailure('inventory:generate-suggestions'));

Schedule::command(CycleCountCommand::class)
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-cycle-count.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:cycle-count'))
    ->onFailure(fn () => logTaskFailure('inventory:cycle-count'));

Schedule::command(ClassifyStockCommand::class)
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-classification.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:classify-stock'))
    ->onFailure(fn () => logTaskFailure('inventory:classify-stock'));

Schedule::command(EvaluateRulesCommand::class)
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-rules.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:evaluate-rules'))
    ->onFailure(fn () => logTaskFailure('inventory:evaluate-rules'));

Schedule::command(GenerateForecastsCommand::class.' --all-strategies --resolve-expired')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-forecasts.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:generate-forecasts'))
    ->onFailure(fn () => logTaskFailure('inventory:generate-forecasts'));

Schedule::command(ReconcileCommand::class)
    ->dailyAt(config('inventory.schedules.reconcile', '01:00'))
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-reconcile.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:reconcile'))
    ->onFailure(fn () => logTaskFailure('inventory:reconcile'));

Schedule::command(DeadStockReportCommand::class)
    ->dailyAt(config('inventory.schedules.dead_stock', '23:00'))
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/inventory-dead-stock.log'))
    ->onSuccess(fn () => logTaskSuccess('inventory:dead-stock-report'))
    ->onFailure(fn () => logTaskFailure('inventory:dead-stock-report'));

if (! function_exists('logTaskSuccess')) {
    function logTaskSuccess(string $command): void
    {
        try {
            ScheduledTaskLog::create([
                'command' => $command,
                'status' => 'success',
                'started_at' => now(),
                'finished_at' => now(),
                'duration_ms' => 0,
            ]);
        } catch (Throwable) {
            // Silently ignore logging failures
        }
    }
}

if (! function_exists('logTaskFailure')) {
    function logTaskFailure(string $command): void
    {
        try {
            ScheduledTaskLog::create([
                'command' => $command,
                'status' => 'failed',
                'started_at' => now(),
                'finished_at' => now(),
                'duration_ms' => 0,
            ]);
        } catch (Throwable) {
            // Silently ignore logging failures
        }
    }
}
