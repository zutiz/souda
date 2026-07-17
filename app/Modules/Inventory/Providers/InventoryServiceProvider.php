<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Console\Commands\ClassifyStockCommand;
use App\Modules\Inventory\Console\Commands\CycleCountCommand;
use App\Modules\Inventory\Console\Commands\DeadStockReportCommand;
use App\Modules\Inventory\Console\Commands\EvaluateRulesCommand;
use App\Modules\Inventory\Console\Commands\ExpiryAlertsCommand;
use App\Modules\Inventory\Console\Commands\GenerateForecastsCommand;
use App\Modules\Inventory\Console\Commands\GenerateSuggestionsCommand;
use App\Modules\Inventory\Console\Commands\ReconcileCommand;
use App\Modules\Inventory\Contracts\InventoryBalanceServiceInterface;
use App\Modules\Inventory\Contracts\InventoryEngineInterface;
use App\Modules\Inventory\Contracts\StockMovementEngineInterface;
use App\Modules\Inventory\Events\InventoryBalanceUpdated;
use App\Modules\Inventory\Events\StockDepleted;
use App\Modules\Inventory\Events\StockMovementCreated;
use App\Modules\Inventory\Listeners\AutoReorderOnBalanceChange;
use App\Modules\Inventory\Listeners\CreateAlertOnStockDepleted;
use App\Modules\Inventory\Listeners\SyncProductAvailability;
use App\Modules\Inventory\Services\AlertEngine;
use App\Modules\Inventory\Services\Audit\AuditService;
use App\Modules\Inventory\Services\BatchService;
use App\Modules\Inventory\Services\Costing\CostingEngine;
use App\Modules\Inventory\Services\Costing\Strategies\FifoCosting;
use App\Modules\Inventory\Services\Costing\Strategies\LifoCosting;
use App\Modules\Inventory\Services\Costing\Strategies\WeightedAverageCosting;
use App\Modules\Inventory\Services\CountEngine;
use App\Modules\Inventory\Services\DashboardDataService;
use App\Modules\Inventory\Services\Forecasting\DemandForecastService;
use App\Modules\Inventory\Services\InventoryBalanceService;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\ReservationEngine;
use App\Modules\Inventory\Services\RuleEngine;
use App\Modules\Inventory\Services\ScheduledTaskLogService;
use App\Modules\Inventory\Services\SerialNumberService;
use App\Modules\Inventory\Services\StockClassificationService;
use App\Modules\Inventory\Services\StockMovementEngine;
use App\Modules\Inventory\Services\TransferEngine;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StockMovementEngine::class);
        $this->app->singleton(InventoryBalanceService::class);
        $this->app->singleton(InventoryEngine::class);
        $this->app->singleton(CostingEngine::class);
        $this->app->singleton(ReservationEngine::class);
        $this->app->singleton(TransferEngine::class);
        $this->app->singleton(CountEngine::class);
        $this->app->singleton(AlertEngine::class);
        $this->app->singleton(ReorderEngine::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(BatchService::class);
        $this->app->singleton(SerialNumberService::class);
        $this->app->singleton(StockClassificationService::class);
        $this->app->singleton(RuleEngine::class);
        $this->app->singleton(DashboardDataService::class);
        $this->app->singleton(DemandForecastService::class);
        $this->app->singleton(ScheduledTaskLogService::class);

        $this->app->singleton(WeightedAverageCosting::class);
        $this->app->singleton(FifoCosting::class);
        $this->app->singleton(LifoCosting::class);

        $this->app->bind(StockMovementEngineInterface::class, StockMovementEngine::class);
        $this->app->bind(InventoryBalanceServiceInterface::class, InventoryBalanceService::class);
        $this->app->bind(InventoryEngineInterface::class, InventoryEngine::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations/Tenant');

        Event::listen(StockMovementCreated::class, SyncProductAvailability::class);
        Event::listen(InventoryBalanceUpdated::class, AutoReorderOnBalanceChange::class);
        Event::listen(StockDepleted::class, CreateAlertOnStockDepleted::class);

        $this->commands([
            ExpiryAlertsCommand::class,
            DeadStockReportCommand::class,
            ReconcileCommand::class,
            CycleCountCommand::class,
            GenerateSuggestionsCommand::class,
            ClassifyStockCommand::class,
            EvaluateRulesCommand::class,
            GenerateForecastsCommand::class,
        ]);
    }
}
