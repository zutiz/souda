<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Tenant;
use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\Onboarding\Data\ProvisioningContext;
use App\Modules\Onboarding\Services\CreateDefaultStoreStep;
use App\Modules\Store\Models\Store;
use App\Tenancy\TenantManager;

beforeEach(function () {
    $this->step = app(CreateDefaultStoreStep::class);
});

test('creates a store via bakery template defaults', function () {
    $businessType = BusinessType::create([
        'slug' => 'bakery',
        'name' => 'Bakery',
        'is_active' => true,
    ]);
    $tenant = Tenant::factory()->shared()->create([
        'business_type_id' => $businessType->id,
    ]);

    Permission::firstOrCreate(['name' => 'stores.create']);

    $context = new ProvisioningContext($tenant, 'bakery');

    $this->step->handle($context);

    app(TenantManager::class)->initialize($tenant);
    $store = Store::where('tenant_id', $tenant->id)->first();
    expect($store)->not->toBeNull();
    expect($store->name)->toBe('Main Store');
    expect($store->is_default)->toBeTrue();
    app(TenantManager::class)->end();
});

test('creates a store via general template', function () {
    $businessType = BusinessType::firstOrCreate(
        ['slug' => 'general'],
        ['name' => 'General', 'is_active' => true],
    );
    $tenant = Tenant::factory()->shared()->create([
        'business_type_id' => $businessType->id,
    ]);

    Permission::firstOrCreate(['name' => 'stores.create']);

    $context = new ProvisioningContext($tenant, 'general');

    $this->step->handle($context);

    app(TenantManager::class)->initialize($tenant);
    $store = Store::where('tenant_id', $tenant->id)->first();
    expect($store)->not->toBeNull();
    expect($store->is_default)->toBeTrue();
    app(TenantManager::class)->end();
});

test('rollback deletes the store', function () {
    $businessType = BusinessType::firstOrCreate(
        ['slug' => 'bakery'],
        ['name' => 'Bakery', 'is_active' => true],
    );
    $tenant = Tenant::factory()->shared()->create([
        'business_type_id' => $businessType->id,
    ]);

    $context = new ProvisioningContext($tenant, 'bakery');

    $this->step->handle($context);

    app(TenantManager::class)->initialize($tenant);
    $store = Store::where('tenant_id', $tenant->id)->first();
    expect($store)->not->toBeNull();
    app(TenantManager::class)->end();

    $this->step->rollback($context);

    app(TenantManager::class)->initialize($tenant);
    $store = Store::withTrashed()->where('tenant_id', $tenant->id)->first();
    expect($store)->toBeNull();
    app(TenantManager::class)->end();
});

test('label returns string', function () {
    expect($this->step->label())->toBeString();
});
