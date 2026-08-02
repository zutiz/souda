<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Modules\BusinessType\Services\BusinessTypeEngine;
use App\Tenancy\TenantManager;

beforeEach(function () {
    $this->tenant = Tenant::factory()->shared()->create();

    $user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    tenancy()->initialize($this->tenant);
    app(TenantManager::class)->initialize($this->tenant);

    $this->actingAs($user);
});

test('branding colors are saved and reflected in the effective config without a business type', function () {
    $tenant = $this->tenant;

    $this->patch(route('branding.update'), [
        'brand_primary_color' => '#FF5733',
        'brand_accent_color' => '#33B5FF',
    ])->assertSessionHas('success');

    $setting = TenantSetting::firstOrFail();
    expect($setting->brand_primary_color)->toBe('#FF5733');
    expect($setting->brand_accent_color)->toBe('#33B5FF');

    $config = app(BusinessTypeEngine::class)->getEffectiveConfig($tenant);

    expect($config->getPrimaryColor())->toBe('#FF5733');
    expect($config->getAccentColor())->toBe('#33B5FF');
});

test('custom brand colors are mirrored to the sidebar palette', function () {
    $tenant = $this->tenant;

    $this->patch(route('branding.update'), [
        'brand_primary_color' => '#FF5733',
        'brand_accent_color' => '#33B5FF',
    ])->assertSessionHas('success');

    $config = app(BusinessTypeEngine::class)->getEffectiveConfig($tenant);

    expect($config->getSidebarAccentColor())->toBe('#33B5FF');
    expect($config->getSidebarPrimaryColor())->toBe('#FF5733');
});

test('sidebar colors fall back to defaults when no custom colors are saved', function () {
    $config = app(BusinessTypeEngine::class)->getEffectiveConfig($this->tenant);

    expect($config->getSidebarAccentColor())->toBe('oklch(0.97 0 0)');
    expect($config->getSidebarPrimaryColor())->toBeNull();
});

test('branding defaults are used when no custom colors are saved', function () {
    $config = app(BusinessTypeEngine::class)->getEffectiveConfig($this->tenant);

    expect($config->getPrimaryColor())->toBe('oklch(0.205 0 0)');
    expect($config->getAccentColor())->toBe('oklch(0.97 0 0)');
});

test('branding colors can be reset to defaults', function () {
    $tenant = $this->tenant;

    TenantSetting::firstOrCreate(
        ['tenant_id' => $tenant->id],
        ['brand_primary_color' => '#FF5733', 'brand_accent_color' => '#33B5FF'],
    );

    $this->patch(route('branding.update'), [
        'reset_colors' => '1',
    ])->assertSessionHas('success');

    $setting = TenantSetting::first();
    expect($setting->brand_primary_color)->toBeNull();
    expect($setting->brand_accent_color)->toBeNull();

    $config = app(BusinessTypeEngine::class)->getEffectiveConfig($tenant);

    expect($config->getPrimaryColor())->toBe('oklch(0.205 0 0)');
});

test('branding rejects an invalid hex color', function () {
    $this->patch(route('branding.update'), [
        'brand_primary_color' => 'not-a-color',
    ])->assertSessionHasErrors('brand_primary_color');
});
