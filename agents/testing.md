# Testing Guide

This application uses [Pest v4](https://pestphp.com/) on top of PHPUnit v12.

## Running Tests

```bash
# Run all tests
php artisan test

# Run lint + tests
composer test

# Run a specific test file or filter
php artisan test --compact tests/Feature/TaskTest.php
php artisan test --compact --filter=TaskTest

# Run billing tests only
php artisan test --compact --filter='Tests\\Feature\\Billing'
```

## Tenancy Testing Model

- Hybrid mode: `Tenant::factory()->create()` creates a tenant in the central DB. Dedicated-mode tenants also create a real MySQL database.
- `RefreshMultiDatabase` trait drops all `souda_tenant_%` DBs between tests and runs `migrate:fresh --database=central` once per class.
- The trait also handles `HasTenantScope` boot state reset via reflection in `TestCase::setUp()` to prevent stale boot state across tests.
- Always clean up tenants in `afterEach`: `$tenant->delete(); $tenant->forceDelete();`

## Test Structure

```text
tests/
├── Pest.php
├── TestCase.php
├── Suggestions/
├── Unit/
├── Support/
│   └── RefreshMultiDatabase.php
└── Feature/
    ├── Auth/
    ├── Billing/        # Seat pricing, team invitations, payment tests
    ├── Tenant/         # Lifecycle, expiry, feature gating, middleware tests
    ├── Admin/
    ├── Settings/
    ├── Product/        # Product CRUD, stock, variants, pricing
    └── ...
```

## Key Testing Patterns

### Tenant Setup

Use `User::factory()->withSubscription()->create()` to create a user with a tenant and active subscription:

```php
test('authenticated user can access dashboard', function () {
    $user = User::factory()->withSubscription()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});
```

For dedicated-mode tenants, pass `tenancy_mode` to the factory:
```php
$tenant = Tenant::factory()->create(['tenancy_mode' => 'dedicated']);
```

### Cross-Tenant Isolation

Always test that one tenant cannot access another tenant's data:

```php
test('users cannot access another tenant task', function () {
    $user = User::factory()->withSubscription()->create();
    $otherUser = User::factory()->withSubscription()->create();

    tenancy()->initialize($otherUser->tenant);
    $task = Task::factory()->create();
    tenancy()->end();

    $this->actingAs($user)
        ->get(route('tasks.show', $task))
        ->assertForbidden();
});
```

### Billing Tests

Billing models use `CentralConnection`, so they can be queried outside tenant context:

```php
test('subscription activates on payment', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['monthly_price' => 0]);

    $subscription = SubscriptionService::createSubscription(
        $user->tenant,
        $plan,
        'free',
        'monthly'
    );

    expect($subscription->status)->toBe(SubscriptionStatus::Active);
});
```

### Shared-Mode Model Testing

Models using `HasTenantScope` need tenant context initialized:

```php
test('task is scoped to tenant', function () {
    $user = User::factory()->withSubscription()->create();
    $this->actingAs($user);

    tenancy()->initialize($user->tenant);
    $task = Task::factory()->create();
    tenancy()->end();

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertSee($task->title);
});
```

### Feature Gating Tests

Test that features are gated by plan:

```php
test('tenant without reports feature cannot access reports', function () {
    $user = User::factory()->withSubscription()->create();
    // Plan features are set via Plan factory

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertRedirect(route('billing'));
});
```

### Command Tests

Test scheduled commands with `--dry-run`:

```php
test('expire subscriptions dry run', function () {
    $this->artisan('subscription:expire-expired --dry-run')
        ->expectsOutputToContain('DRY RUN')
        ->assertExitCode(0);
});
```

## Assertion Conventions

- Use `assertSuccessful()` not `assertStatus(200)`
- Use `assertNotFound()` not `assertStatus(404)`
- Use `assertForbidden()` not `assertStatus(403)`
- Use `assertRedirect()` for redirect assertions
- Use `assertSee()` for content assertions in Inertia responses

## HasTenantScope Test Safety

The `HasTenantScope` trait wraps `app()` calls in try-catch blocks to prevent unit test failures. `TestCase::setUp()` resets `Model::$booting` via reflection:

```php
protected function setUp(): void
{
    parent::setUp();
    Model::clearBootedModels();
    $reflection = new ReflectionClass(Model::class);
    $bootingProperty = $reflection->getProperty('booting');
    $bootingProperty->setAccessible(true);
    $bootingProperty->setValue(null, []);
}
```

Do NOT remove these guards — they prevent stale boot state across tests.
