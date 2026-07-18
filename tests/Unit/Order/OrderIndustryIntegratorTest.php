<?php

declare(strict_types=1);

use App\Modules\BusinessType\ValueObjects\TenantConfig;
use App\Modules\Order\Services\OrderIndustryIntegrator;

function integratorForBusinessType(string $type, array $features = []): OrderIndustryIntegrator
{
    return new OrderIndustryIntegrator(new TenantConfig(
        businessType: $type,
        enabledModules: [],
        menus: [],
        permissions: [],
        fieldDefinitions: [],
        dashboardWidgets: [],
        posConfig: [],
        reportDefinitions: [],
        workflows: [],
        settings: ['features' => $features],
    ));
}

it('routes orders to kitchen when kitchen_display feature is enabled', function () {
    $integrator = integratorForBusinessType('restaurant', ['kitchen_display']);

    expect($integrator->shouldSendToKitchen())->toBeTrue();
});

it('routes orders to kitchen when kitchen module is enabled', function () {
    $integrator = new OrderIndustryIntegrator(new TenantConfig(
        businessType: 'restaurant',
        enabledModules: ['kitchen'],
        menus: [],
        permissions: [],
        fieldDefinitions: [],
        dashboardWidgets: [],
        posConfig: [],
        reportDefinitions: [],
        workflows: [],
        settings: [],
    ));

    expect($integrator->shouldSendToKitchen())->toBeTrue();
});

it('does not route to kitchen by default', function () {
    $integrator = integratorForBusinessType('retail');

    expect($integrator->shouldSendToKitchen())->toBeFalse();
});

it('requires prescription check when prescription_management feature is enabled', function () {
    $integrator = integratorForBusinessType('pharmacy', ['prescription_management']);

    expect($integrator->requiresPrescriptionCheck())->toBeTrue();
});

it('supports delivery when delivery feature is enabled', function () {
    $integrator = integratorForBusinessType('restaurant', ['delivery']);

    expect($integrator->supportsDelivery())->toBeTrue();
});

it('supports delivery when courier_integration feature is enabled', function () {
    $integrator = integratorForBusinessType('retail', ['courier_integration']);

    expect($integrator->supportsDelivery())->toBeTrue();
});

it('supports pickup for restaurant, cafe, bakery, and pharmacy', function (string $type) {
    $integrator = integratorForBusinessType($type);

    expect($integrator->supportsPickup())->toBeTrue();
})->with(['restaurant', 'cafe', 'bakery', 'pharmacy']);

it('supports pickup when takeaway feature is enabled', function () {
    $integrator = integratorForBusinessType('retail', ['takeaway']);

    expect($integrator->supportsPickup())->toBeTrue();
});

it('requires table assignment when table_management feature is enabled', function () {
    $integrator = integratorForBusinessType('restaurant', ['table_management']);

    expect($integrator->requiresTableAssignment())->toBeTrue();
});

it('requires staff assignment when staff_scheduling feature is enabled', function () {
    $integrator = integratorForBusinessType('restaurant', ['staff_scheduling']);

    expect($integrator->requiresStaffAssignment())->toBeTrue();
});

it('supports installment when installment feature is enabled', function () {
    $integrator = integratorForBusinessType('retail', ['installment']);

    expect($integrator->supportsInstallment())->toBeTrue();
});

it('supports installment for electronics business type', function () {
    $integrator = integratorForBusinessType('electronics');

    expect($integrator->supportsInstallment())->toBeTrue();
});

it('returns default in_store order type for basic retail', function () {
    $integrator = integratorForBusinessType('retail');

    expect($integrator->orderTypes())->toBe(['in_store']);
});

it('includes delivery and pickup order types when supported', function () {
    $integrator = integratorForBusinessType('restaurant', ['delivery', 'takeaway']);

    expect($integrator->orderTypes())->toContain('in_store', 'delivery', 'takeaway');
});

it('includes dine_in when table management is available', function () {
    $integrator = integratorForBusinessType('restaurant', ['table_management']);

    expect($integrator->orderTypes())->toContain('dine_in');
});

it('includes wholesale for wholesale business type', function () {
    $integrator = integratorForBusinessType('wholesale');

    expect($integrator->orderTypes())->toContain('wholesale');
});

it('returns full config array', function () {
    $integrator = integratorForBusinessType('restaurant', ['delivery', 'kitchen_display']);
    $config = $integrator->orderConfig();

    expect($config)->toHaveKey('should_send_to_kitchen')
        ->and($config['should_send_to_kitchen'])->toBeTrue()
        ->and($config['supports_delivery'])->toBeTrue()
        ->and($config['order_types'])->toContain('in_store', 'delivery')
        ->and($config['business_type'])->toBe('restaurant');
});
