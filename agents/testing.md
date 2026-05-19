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

- Multi-database mode: each `Tenant::factory()->create()` creates a real MySQL database.
- `RefreshMultiDatabase` trait drops all `souda_tenant_%` DBs between tests and runs `migrate:fresh --database=central` once per class.
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
    └── ...
```

## Isolation Test Pattern

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

Focus on assertions that confirm cross-tenant data access is blocked.
