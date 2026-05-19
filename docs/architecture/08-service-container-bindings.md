# Service Container Bindings

## Overview

The Laravel service container manages dependency injection and resolves class dependencies. Proper bindings ensure loose coupling, testability, and flexibility.

## Binding Strategies

### 1. Interface to Implementation (Primary)

Used for gateway drivers, service contracts, and swappable implementations.

```php
// app/Providers/BillingServiceProvider.php
public function register(): void
{
    $this->app->bind(
        SubscriptionChecker::class,
        BillingSubscriptionChecker::class
    );

    $this->app->bind(
        PaymentGateway::class,
        StripeDriver::class
    );
}
```

### 2. Singleton Binding

Used for services that maintain state or are expensive to instantiate.

```php
public function register(): void
{
    $this->app->singleton(BillingManager::class, function ($app) {
        return new BillingManager(
            config('billing.drivers'),
            config('billing.default_gateway')
        );
    });
}
```

### 3. Contextual Binding

Used when different implementations are needed based on the consuming class.

```php
public function register(): void
{
    $this->app->when(OrderService::class)
        ->needs(PaymentGateway::class)
        ->give(StripeDriver::class);

    $this->app->when(ManualOrderService::class)
        ->needs(PaymentGateway::class)
        ->give(ManualDriver::class);
}
```

### 4. Tagged Binding

Used for resolving multiple implementations of the same interface.

```php
public function register(): void
{
    $this->app->bind(StripeDriver::class);
    $this->app->bind(SSLCommerzDriver::class);
    $this->app->bind(BKashDriver::class);

    $this->app->tag([
        StripeDriver::class,
        SSLCommerzDriver::class,
        BKashDriver::class,
    ], 'payment.gateways');
}

// Resolution
$gateways = $this->app->tagged('payment.gateways');
```

### 5. Automatic Resolution

Laravel auto-resolves concrete classes with no explicit binding needed.

```php
// No binding needed - Laravel resolves automatically
class ProductController
{
    public function __construct(
        protected ProductService $service // Auto-resolved
    ) {}
}
```

## Module Service Provider Bindings

### Billing Module

```php
// app/Providers/BillingServiceProvider.php
public function register(): void
{
    // Singleton services
    $this->app->singleton(BillingManager::class);
    $this->app->singleton(SubscriptionService::class);
    $this->app->singleton(PaymentService::class);
    $this->app->singleton(PlanService::class);
    $this->app->singleton(InvoiceService::class);

    // Gateway drivers (resolved via BillingManager factory)
    $this->app->bind(StripeDriver::class);
    $this->app->bind(SSLCommerzDriver::class);
    $this->app->bind(BKashDriver::class);
    $this->app->bind(NagadDriver::class);
    $this->app->bind(PortWalletDriver::class);
    $this->app->bind(ManualDriver::class);

    // Middleware aliases
    $router = $this->app['router'];
    $router->aliasMiddleware('subscription', EnsureSubscribed::class);
    $router->aliasMiddleware('feature', EnsureTenantHasFeature::class);
}

public function boot(): void
{
    // Event listeners - method-based resolution
    $events = $this->app->make('events');
    $listener = $this->app->make(SendSubscriptionNotification::class);

    $events->listen(
        SubscriptionActivated::class,
        [$listener, 'handleSubscriptionActivated']
    );

    $events->listen(
        SubscriptionCancelled::class,
        [$listener, 'handleSubscriptionCancelled']
    );

    $events->listen(
        SubscriptionExpired::class,
        [$listener, 'handleSubscriptionExpired']
    );

    $events->listen(
        PaymentReceived::class,
        [$listener, 'handlePaymentReceived']
    );

    $events->listen(
        PaymentFailed::class,
        [$listener, 'handlePaymentFailed']
    );
}
```

### Products Module (Proposed)

```php
// app/Providers/ProductServiceProvider.php
public function register(): void
{
    $this->app->singleton(ProductService::class);
    $this->app->singleton(CategoryService::class);
    $this->app->singleton(ProductImportService::class);

    // Contracts
    $this->app->bind(
        ProductResolver::class,
        EloquentProductResolver::class,
    );

    $this->app->bind(
        StockChecker::class,
        InventoryStockChecker::class,
    );
}

public function boot(): void
{
    Event::listen(
        ProductCreated::class,
        IndexProductForSearch::class,
    );

    Event::listen(
        ProductCreated::class,
        GenerateProductSKU::class,
    );
}
```

### Orders Module (Proposed)

```php
// app/Providers/OrderServiceProvider.php
public function register(): void
{
    $this->app->singleton(OrderService::class);
    $this->app->singleton(OrderExportService::class);

    $this->app->bind(
        OrderNumberGenerator::class,
        SequentialOrderNumberGenerator::class,
    );
}

public function boot(): void
{
    Event::listen(
        OrderCreated::class,
        DeductInventory::class,
    );

    Event::listen(
        OrderCreated::class,
        UpdateCustomerActivity::class,
    );

    Event::listen(
        OrderPaid::class,
        SendOrderConfirmation::class,
    );
}
```

## App Service Provider

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    // Application-wide singletons
    $this->app->singleton(SocialAuthService::class);
    $this->app->singleton(BillingEmailService::class);
}

public function boot(): void
{
    // Global configurations
    CarbonImmutable::mixin(new CarbonMacros());

    // Prohibit destructive commands in production
    if ($this->app->isProduction()) {
        Model::preventLazyLoading();
        Model::preventSilentlyDiscardingAttributes();
        Model::preventAccessingMissingAttributes();
    }
}
```

## Tenancy Service Provider

```php
// app/Providers/TenancyServiceProvider.php
public function register(): void
{
    // Tenancy events
    $this->listen = [
        TenantCreated::class => [
            JobPipeline::make([
                CreateDatabase::class,
                MigrateDatabase::class,
            ])->shouldBeQueued(false)->send(),
        ],
        TenantDeleted::class => [
            DeleteDatabase::class,
        ],
        TenancyInitialized::class => [
            BootstrapTenancy::class,
        ],
        TenancyEnded::class => [
            RevertToCentralContext::class,
        ],
    ];
}

public function boot(): void
{
    $this->bootEvents();
    $this->mapRoutes();
    $this->makeTenancyMiddlewareHighestPriority();
}
```

> **Note:** The custom `InitializeTenancyByUser` middleware is registered in `bootstrap/app.php` via `$middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: InitializeTenancyByUser::class)`.

## Binding Patterns by Use Case

### Factory Pattern (BillingManager)

```php
class BillingManager
{
    protected array $drivers = [];

    public function __construct(
        protected Application $app,
        protected array $config,
        protected string $defaultGateway,
    ) {}

    public function driver(?string $gateway = null): BillingGatewayInterface
    {
        $gateway = $gateway ?? $this->defaultGateway;

        if (!isset($this->drivers[$gateway])) {
            $this->drivers[$gateway] = $this->resolveDriver($gateway);
        }

        return $this->drivers[$gateway];
    }

    protected function resolveDriver(string $gateway): BillingGatewayInterface
    {
        $driverClass = $this->config[$gateway]['driver'];

        return $this->app->make($driverClass);
    }
}
```

### Repository Pattern

```php
// Contract
interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findBySku(string $sku): ?Product;
    public function create(array $data): Product;
    public function update(Product $product, array $data): bool;
}

// Implementation
class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $model
    ) {}

    public function findById(int $id): ?Product
    {
        return $this->model->query()->find($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->query()->where('sku', $sku)->first();
    }

    public function create(array $data): Product
    {
        return $this->model->query()->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }
}

// Binding
$this->app->bind(
    ProductRepositoryInterface::class,
    EloquentProductRepository::class
);
```

### Strategy Pattern (Payment Gateways)

```php
// Contract
interface PaymentGateway
{
    public function charge(int $amount, string $currency, array $options): PaymentResult;
    public function refund(string $transactionId, int $amount): PaymentResult;
    public function verify(string $transactionId): PaymentResult;
}

// Implementations
class StripeDriver implements PaymentGateway { }
class SSLCommerzDriver implements PaymentGateway { }
class BKashDriver implements PaymentGateway { }

// Resolution via factory
$gateway = $billingManager->driver('stripe');
$result = $gateway->charge(1000, 'BDT', [...]);
```

## Testing with Container Bindings

### Mocking in Tests

```php
test('subscription is activated after payment', function () {
    $this->mock(SSLCommerzDriver::class, function ($mock) {
        $mock->shouldReceive('verifyPayment')
            ->once()
            ->andReturn(new PaymentDTO(
                transactionId: 'txn_123',
                status: PaymentStatus::Completed,
                amount: 1000,
            ));
    });

    // ... test logic
});
```

### Swapping Implementations

```php
// In test setup
$this->app->bind(
    PaymentGateway::class,
    FakePaymentGateway::class,
);

// Or use partial mock
$mock = Mockery::mock(SSLCommerzDriver::class);
$mock->shouldReceive('createPayment')->andReturn($paymentDTO);
$this->app->instance(SSLCommerzDriver::class, $mock);
```

## Binding Resolution Order

1. **Explicit instance** (`$app->instance()`)
2. **Contextual binding** (`$app->when()->needs()->give()`)
3. **Singleton binding** (`$app->singleton()`)
4. **Regular binding** (`$app->bind()`)
5. **Auto-resolution** (concrete class with no binding)

## Best Practices

1. **Bind interfaces, not concrete classes** - Enables swapping implementations
2. **Use singletons for stateful services** - Avoids re-instantiation overhead
3. **Use contextual binding for conditional implementations** - Cleaner than if/else
4. **Register bindings in module service providers** - Keeps modules self-contained
5. **Use tagged bindings for collections** - Cleaner than manual arrays
6. **Avoid service locator pattern** - Use constructor injection instead
7. **Test with mocks** - Bind test doubles in test setup
8. **Document binding contracts** - Clear interface definitions
