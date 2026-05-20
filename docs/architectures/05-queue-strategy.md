# Queue Strategy

## Overview

Redis-backed queue system with tenant-aware job processing, priority-based queues, and comprehensive failure handling.

## Queue Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                        Application                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐     │
│  │  Events  │  │ Actions  │  │ Services │  │ Webhooks │     │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘     │
│       │              │             │              │           │
│       ▼              ▼             ▼              ▼           │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Dispatch Layer                           │   │
│  │  dispatch(new Job())  │  Job::dispatch()  │  Bus     │   │
│  └──────────────────────┬───────────────────────────────┘   │
└─────────────────────────┼───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                    Redis Queues                              │
│                                                              │
│  queues:critical  queues:high  queues:default  queues:low   │
│                                                              │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                   Queue Workers                              │
│                                                              │
│  Worker 1: --queue=critical  (1+ processes)                 │
│  Worker 2: --queue=high      (2+ processes)                 │
│  Worker 3: --queue=default   (3+ processes)                 │
│  Worker 4: --queue=low       (1+ processes)                 │
│                                                              │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                   Job Processing                             │
│                                                              │
│  1. QueueTenancyBootstrapper initializes tenant context     │
│  2. Job executes within tenant scope                        │
│  3. On failure → retry or failed_jobs table                 │
│  4. On success → dispatches next job / event                │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

## Queue Configuration

### Default Configuration (`config/queue.php`)

```php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],
],

'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
    'database' => env('DB_CONNECTION', 'central'),
    'table' => 'failed_jobs',
],
```

> **Note:** Current default is `database`. Migrate to `redis` for production with `QUEUE_CONNECTION=redis` and set `after_commit => true` for queue reliability.

## Priority Queue Strategy

### Queue Priority Levels

| Priority | Queue Name | Use Case | Worker Count | Retry |
|----------|------------|----------|--------------|-------|
| **Critical** | `critical` | Payment processing, subscription changes, order creation | 2+ | 5 |
| **High** | `high` | Inventory updates, stock deductions, webhook processing | 3+ | 3 |
| **Default** | `default` | Email notifications, CRM updates, report generation | 4+ | 3 |
| **Low** | `low` | Analytics, cleanup, data exports, scheduled tasks | 1+ | 1 |

### Job Assignment

```php
// Critical queue
class ProcessPayment implements ShouldQueue
{
    public $queue = 'critical';
    public $tries = 5;
    public $timeout = 120;
}

// High queue
class DeductInventory implements ShouldQueue
{
    public $queue = 'high';
    public $tries = 3;
    public $timeout = 60;
}

// Default queue
class SendOrderConfirmation implements ShouldQueue
{
    public $queue = 'default';
    public $tries = 3;
    public $timeout = 30;
}

// Low queue
class GenerateAnalyticsReport implements ShouldQueue
{
    public $queue = 'low';
    public $tries = 1;
    public $timeout = 300;
}
```

## Tenant-Aware Job Processing

### QueueTenancyBootstrapper

The `QueueTenancyBootstrapper` automatically handles tenant context for queued jobs:

```php
class ProcessOrder implements ShouldQueue
{
    use Queueable;

    public $tenantId;
    public $orderId;

    public function __construct(int $orderId)
    {
        $this->tenantId = tenancy()->tenant->id;
        $this->orderId = $orderId;
        $this->onConnection('redis');
    }

    public function handle(): void
    {
        // Tenancy is automatically initialized by QueueTenancyBootstrapper
        // using the stored tenantId
        $order = Order::find($this->orderId);
        // Process within tenant context
    }
}
```

### Manual Tenant Initialization (When Needed)

```php
class CrossTenantReport implements ShouldQueue
{
    public function handle(): void
    {
        $tenants = Tenant::cursor();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            try {
                // Process tenant data
                $this->generateReport($tenant);
            } finally {
                tenancy()->end();
            }
        }
    }
}
```

## Job Batching

### Use Cases

| Use Case | Description |
|----------|-------------|
| **Bulk tenant operations** | Migrate all tenants, seed data |
| **Data imports** | Import products, contacts, orders |
| **Report generation** | Multi-tenant analytics |
| **Email campaigns** | Bulk notification sending |

### Batch Implementation

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

class ImportProducts implements ShouldQueue
{
    public function handle(): void
    {
        $batch = Bus::batch(
            collect($this->products)->map(fn($p) => new ImportSingleProduct($p))
        )
        ->name('product-import-' . $this->tenantId)
        ->then(function (Batch $batch) {
            // All jobs completed successfully
            Notification::send('Import completed: ' . $batch->totalJobs . ' products');
        })
        ->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure
            Log::error('Import failed', ['error' => $e->getMessage()]);
        })
        ->finally(function (Batch $batch) {
            // Batch finished (success or failure)
            Cache::forget('import-progress-' . $this->tenantId);
        })
        ->dispatch();

        Cache::put('import-batch-id', $batch->id, 3600);
    }
}
```

## Failure Handling

### Retry Strategy

| Job Type | Tries | Backoff | Timeout |
|----------|-------|---------|---------|
| Payment processing | 5 | Exponential | 120s |
| Inventory updates | 3 | Linear (10s) | 60s |
| Email notifications | 3 | Exponential | 30s |
| Report generation | 1 | None | 300s |

### Exponential Backoff

```php
class ProcessPayment implements ShouldQueue
{
    public $tries = 5;

    public function backoff(): array
    {
        return [10, 30, 60, 120, 300]; // seconds
    }

    public function retryUntil(): DateTime
    {
        return now()->addHours(1);
    }
}
```

### Failed Job Handling

```php
class ProcessPayment implements ShouldQueue
{
    public function failed(Throwable $exception): void
    {
        // Notify admin
        Notification::route('mail', 'admin@souda.com')
            ->notify(new PaymentProcessingFailed($this->orderId, $exception));

        // Update order status
        tenancy()->initialize($this->tenantId);
        $order = Order::find($this->orderId);
        $order->update(['payment_status' => 'failed']);
        tenancy()->end();
    }
}
```

### Failed Job Recovery

```bash
# List failed jobs
php artisan queue:failed

# Retry specific failed job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all

# Delete failed job
php artisan queue:forget {id}

# Delete all failed jobs
php artisan queue:flush
```

## Worker Configuration

### Development (`composer run dev`)

```json
{
    "dev": [
        "php artisan serve",
        "php artisan queue:listen --tries=1 --timeout=0",
        "php artisan pail",
        "npm run dev"
    ]
}
```

### Production (Supervisor)

```ini
[program:souda-queue-critical]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/souda/artisan queue:work redis --queue=critical --sleep=3 --tries=5 --timeout=120 --max-jobs=500
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/souda/storage/logs/worker-critical.log

[program:souda-queue-high]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/souda/artisan queue:work redis --queue=high --sleep=3 --tries=3 --timeout=60 --max-jobs=1000
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/souda/storage/logs/worker-high.log

[program:souda-queue-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/souda/artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=30 --max-jobs=1000
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/souda/storage/logs/worker-default.log

[program:souda-queue-low]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/souda/artisan queue:work redis --queue=low --sleep=5 --tries=1 --timeout=300 --max-jobs=2000
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/souda/storage/logs/worker-low.log
```

### Worker Scaling Guidelines

| Tenant Count | Critical | High | Default | Low |
|--------------|----------|------|---------|-----|
| 1-50 | 1 | 1 | 2 | 1 |
| 51-200 | 2 | 2 | 3 | 1 |
| 201-500 | 2 | 3 | 4 | 1 |
| 500+ | 3+ | 4+ | 6+ | 2+ |

## Job Types & Patterns

### 1. Simple Job

```php
class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        Mail::to($user)->send(new WelcomeEmail($user));
    }
}
```

### 2. Rate-Limited Job

```php
use Illuminate\Support\Facades\RateLimiter;

class SendBulkNotification implements ShouldQueue
{
    use Queueable;

    public $middleware = [
        new RateLimited('notifications'),
    ];

    public function handle(): void
    {
        // Send notification
    }
}

// In AppServiceProvider
RateLimiter::for('notifications', function ($job) {
    return Limit::perMinute(100);
});
```

### 3. Unique Job (Prevent Duplicates)

```php
use Illuminate\Contracts\Queue\ShouldBeUnique;

class GenerateMonthlyReport implements ShouldQueue, ShouldBeUnique
{
    public $uniqueId = 'monthly-report';
    public $uniqueFor = 3600; // 1 hour

    public function handle(): void
    {
        // Generate report
    }
}
```

### 4. Chained Jobs

```php
ProcessPayment::withChain([
    new DeductInventory($orderId),
    new SendOrderConfirmation($orderId),
    new UpdateCRMActivity($orderId),
])->dispatch();
```

## Monitoring

### Queue Metrics

```bash
# Queue size
php artisan queue:monitor redis --queue=critical,high,default,low

# Worker status
php artisan queue:status

# Clear queue
php artisan queue:clear redis --queue=low
```

### Horizon (Recommended for Production)

```bash
composer require laravel/horizon
php artisan horizon:install
```

Horizon provides:
- Real-time queue monitoring dashboard
- Job throughput metrics
- Failed job tracking
- Worker process management
- Queue wait time alerts

## Best Practices

1. **Always queue non-critical operations** - Emails, notifications, analytics
2. **Use `after_commit => true`** - Prevents jobs dispatching before DB commit
3. **Store tenant ID in job** - QueueTenancyBootstrapper handles initialization
4. **Implement `failed()` method** - Handle job failures gracefully
5. **Set appropriate timeouts** - Prevent long-running jobs from blocking workers
6. **Use job batching for bulk operations** - Track progress, handle partial failures
7. **Monitor queue depth** - Alert when queues grow beyond threshold
8. **Use unique jobs for scheduled tasks** - Prevent duplicate execution
9. **Rate limit external API calls** - Prevent hitting rate limits
10. **Test jobs in isolation** - Write unit tests for job handlers
