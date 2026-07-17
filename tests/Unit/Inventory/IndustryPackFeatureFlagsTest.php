<?php

declare(strict_types=1);

use App\Modules\BusinessType\Packs\AgroShopPack;
use App\Modules\BusinessType\Packs\BakeryPack;
use App\Modules\BusinessType\Packs\BookstorePack;
use App\Modules\BusinessType\Packs\CafePack;
use App\Modules\BusinessType\Packs\CosmeticsPack;
use App\Modules\BusinessType\Packs\DistributionPack;
use App\Modules\BusinessType\Packs\ElectronicsPack;
use App\Modules\BusinessType\Packs\FashionPack;
use App\Modules\BusinessType\Packs\GroceryPack;
use App\Modules\BusinessType\Packs\HardwarePack;
use App\Modules\BusinessType\Packs\PharmacyPack;
use App\Modules\BusinessType\Packs\RestaurantPack;
use App\Modules\BusinessType\Packs\SalonPack;
use App\Modules\BusinessType\Packs\SpaPack;
use App\Modules\BusinessType\Packs\WholesalePack;

test('all packs include low_stock_alerts feature flag', function () {
    $packs = getAllPacks();

    foreach ($packs as $pack) {
        $flags = $pack->featureFlags();

        expect(in_array('low_stock_alerts', $flags, true))
            ->toBeTrue("{$pack->slug()} must include low_stock_alerts");
    }
});

test('all packs include stock_transfers feature flag', function () {
    $packs = getAllPacks();

    foreach ($packs as $pack) {
        $flags = $pack->featureFlags();

        expect(in_array('stock_transfers', $flags, true))
            ->toBeTrue("{$pack->slug()} must include stock_transfers");
    }
});

test('all packs include cycle_counting feature flag', function () {
    $packs = getAllPacks();

    foreach ($packs as $pack) {
        $flags = $pack->featureFlags();

        expect(in_array('cycle_counting', $flags, true))
            ->toBeTrue("{$pack->slug()} must include cycle_counting");
    }
});

test('no pack uses deprecated flag names', function () {
    $deprecated = ['serial_tracking', 'warehouse_transfers'];
    $packs = getAllPacks();

    foreach ($packs as $pack) {
        $flags = $pack->featureFlags();

        foreach ($deprecated as $old) {
            expect(in_array($old, $flags, true))
                ->toBeFalse("{$pack->slug()} must not use deprecated flag '{$old}'");
        }
    }
});

test('pharmacy pack has inventory-specific flags', function () {
    $pack = new PharmacyPack;
    $flags = $pack->featureFlags();

    expect($flags)->toContain('batch_tracking', 'expiry_tracking', 'fefo_picking', 'quarantine_management');
});

test('electronics pack uses serial_number_tracking not serial_tracking', function () {
    $pack = new ElectronicsPack;
    $flags = $pack->featureFlags();

    expect($flags)->toContain('serial_number_tracking')
        ->and($flags)->not->toContain('serial_tracking');
});

test('grocery pack has perishable inventory flags', function () {
    $pack = new GroceryPack;
    $flags = $pack->featureFlags();

    expect($flags)->toContain('batch_tracking', 'expiry_tracking', 'fefo_picking', 'waste_tracking');
});

test('restaurant pack has recipe consumption and waste tracking', function () {
    $pack = new RestaurantPack;
    $flags = $pack->featureFlags();

    expect($flags)->toContain('recipe_consumption', 'waste_tracking', 'batch_tracking');
});

test('distribution pack uses stock_transfers not warehouse_transfers', function () {
    $pack = new DistributionPack;
    $flags = $pack->featureFlags();

    expect($flags)->toContain('stock_transfers')
        ->and($flags)->not->toContain('warehouse_transfers');
});

test('bakery pack has recipe and waste inventory flags', function () {
    $pack = new BakeryPack;
    $flags = $pack->featureFlags();

    expect($flags)->toContain('recipe_management', 'recipe_consumption', 'waste_tracking');
});

function getAllPacks(): array
{
    return [
        new AgroShopPack,
        new BakeryPack,
        new BookstorePack,
        new CafePack,
        new CosmeticsPack,
        new DistributionPack,
        new ElectronicsPack,
        new FashionPack,
        new GroceryPack,
        new HardwarePack,
        new PharmacyPack,
        new RestaurantPack,
        new SalonPack,
        new SpaPack,
        new WholesalePack,
    ];
}
