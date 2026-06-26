<?php

use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;

test('default tenant settings contain all required keys', function () {
    $defaults = TenantSetting::getDefaults();

    $expectedKeys = [
        'timezone', 'locale', 'currency', 'date_format', 'time_format',
        'company_name', 'company_address', 'company_email', 'company_phone',
        'default_language', 'notification_preferences', 'feature_toggles', 'extra',
    ];

    foreach ($expectedKeys as $key) {
        expect($defaults)->toHaveKey($key);
    }
});

test('tenant data column stores arbitrary attributes', function () {
    $tenant = Tenant::factory()->create();
    $tenant->custom_field = 'custom_value';
    $tenant->save();

    $row = DB::connection('central')
        ->table('tenants')
        ->where('id', $tenant->id)
        ->value('data');

    expect($row)->not->toBeNull();

    $decoded = is_string($row) ? json_decode($row, true) : $row;

    expect($decoded)->toBeArray();
    expect($decoded)->toHaveKey('custom_field');
    expect($decoded['custom_field'])->toBe('custom_value');
});

test('tenant belonging to user returns correct owner', function () {
    $tenant = Tenant::factory()->create(['owner_id' => null]);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    expect($tenant->fresh()->owner_id)->toBe($user->id);

    $tenant->delete();
    $tenant->forceDelete();
});
