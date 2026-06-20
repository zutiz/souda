---
name: pest-testing
description: "Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works."
license: MIT
metadata:
  author: laravel
---

# Pest Testing 4

## When to Apply

Activate this skill when:

- Creating new tests (unit, feature, or browser)
- Modifying existing tests
- Debugging test failures
- Working with browser testing or smoke testing
- Writing architecture tests or visual regression tests

## Documentation

Use `search-docs` for detailed Pest 4 patterns and documentation.

## Basic Usage

### Creating Tests

All tests must be written using Pest. Use `php artisan make:test --pest {name}`.

### Test Organization

- Unit/Feature tests: `tests/Feature` and `tests/Unit` directories.
- Browser tests: `tests/Browser/` directory.
- Do NOT remove tests without approval - these are core application code.

### Basic Test Structure

<!-- Basic Pest Test Example -->
```php
it('is true', function () {
    expect(true)->toBeTrue();
});
```

### Running Tests

- Run minimal tests with filter before finalizing: `php artisan test --compact --filter=testName`.
- Run all tests: `php artisan test --compact`.
- Run file: `php artisan test --compact tests/Feature/ExampleTest.php`.

## Assertions

Use specific assertions (`assertSuccessful()`, `assertNotFound()`) instead of `assertStatus()`:

<!-- Pest Response Assertion -->
```php
it('returns all', function () {
    $this->postJson('/api/docs', [])->assertSuccessful();
});
```

| Use | Instead of |
|-----|------------|
| `assertSuccessful()` | `assertStatus(200)` |
| `assertNotFound()` | `assertStatus(404)` |
| `assertForbidden()` | `assertStatus(403)` |

## Mocking

Import mock function before use: `use function Pest\Laravel\mock;`

## Datasets

Use datasets for repetitive tests (validation rules, etc.):

<!-- Pest Dataset Example -->
```php
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
```

## Pest 4 Features

| Feature | Purpose |
|---------|---------|
| Browser Testing | Full integration tests in real browsers |
| Smoke Testing | Validate multiple pages quickly |
| Visual Regression | Compare screenshots for visual changes |
| Test Sharding | Parallel CI runs |
| Architecture Testing | Enforce code conventions |

### Browser Test Example

Browser tests run in real browsers for full integration testing:

- Browser tests live in `tests/Browser/`.
- Use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories.
- Use `RefreshDatabase` for clean state per test.
- Interact with page: click, type, scroll, select, submit, drag-and-drop, touch gestures.
- Test on multiple browsers (Chrome, Firefox, Safari) if requested.
- Test on different devices/viewports (iPhone 14 Pro, tablets) if requested.
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging.

<!-- Pest Browser Test Example -->
```php
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in');

    $page->assertSee('Sign In')
        ->assertNoJavaScriptErrors()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!');

    Notification::assertSent(ResetPassword::class);
});
```

### Smoke Testing

Quickly validate multiple pages have no JavaScript errors:

<!-- Pest Smoke Testing Example -->
```php
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavaScriptErrors()->assertNoConsoleLogs();
```

### Visual Regression Testing

Capture and compare screenshots to detect visual changes.

### Test Sharding

Split tests across parallel processes for faster CI runs.

### Architecture Testing

Pest 4 includes architecture testing (from Pest 3):

<!-- Architecture Test Example -->
```php
arch('controllers')
    ->expect('App\Http\Controllers')
    ->toExtendNothing()
    ->toHaveSuffix('Controller');
```

## Project-Specific Patterns (Souda)

### Tenancy Testing

This project uses `RefreshMultiDatabase` trait (in `tests/Support/`). Apply to feature test files:

```php
uses(RefreshMultiDatabase::class);
```

**User factory with tenant + subscription:**
```php
$user = User::factory()->withSubscription()->create();
$this->actingAs($user);
```

**Cross-tenant isolation pattern:**
```php
$user = User::factory()->withSubscription()->create();
$otherUser = User::factory()->withSubscription()->create();

tenancy()->initialize($otherUser->tenant);
$task = Task::factory()->create();
tenancy()->end();

$this->actingAs($user)
    ->get(route('tasks.show', $task))
    ->assertForbidden();
```

**Billing tests** — billing models use `CentralConnection`, query outside tenant context:
```php
$plan = Plan::factory()->create(['monthly_price' => 0]);
$subscription = SubscriptionService::createSubscription($user->tenant, $plan, 'free', 'monthly');
expect($subscription->status)->toBe(SubscriptionStatus::Active);
```

### HasTenantScope Safety

The `HasTenantScope` trait wraps `app()` calls in try-catch for test safety. `TestCase::setUp()` resets `Model::$booting` via reflection. Do NOT remove these guards.

### Useful Factories

- `User::factory()->withSubscription()->create()` — user + tenant + active subscription
- `Tenant::factory()->create(['tenancy_mode' => 'dedicated'])` — dedicated-mode tenant
- `Plan::factory()` — billing plan
- `Task::factory()` — tenant-scoped task

### Command Tests

```php
$this->artisan('subscription:expire-expired --dry-run')
    ->expectsOutputToContain('DRY RUN')
    ->assertExitCode(0);
```

## Common Pitfalls

- Not importing `use function Pest\Laravel\mock;` before using mock
- Using `assertStatus(200)` instead of `assertSuccessful()`
- Forgetting datasets for repetitive validation tests
- Deleting tests without approval
- Forgetting `assertNoJavaScriptErrors()` in browser tests
- Not using `RefreshMultiDatabase` for tenant tests
- Forgetting `afterEach` tenant cleanup: `$tenant->delete(); $tenant->forceDelete();`
- Not clearing `Model::$booting` state across test classes