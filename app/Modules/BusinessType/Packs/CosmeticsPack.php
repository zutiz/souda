<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class CosmeticsPack implements IndustryPack
{
    public function slug(): string
    {
        return 'cosmetics';
    }

    public function name(): string
    {
        return 'Cosmetics';
    }

    public function description(): string
    {
        return 'Cosmetics and beauty products store with shade management, ingredient tracking, and skin type filtering';
    }

    public function modules(): array
    {
        return [
            'product' => ['required' => true],
            'inventory' => ['required' => true],
            'order' => ['required' => true],
            'pos' => ['required' => true],
            'crm' => ['required' => true],
            'billing' => ['required' => true],
            'team' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Palette', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                    ['label' => 'Shades', 'route' => 'cosmetics.shades'],
                ]],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3'],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'reports.*', 'settings.*'],
            'manager' => ['products.*', 'inventory.view', 'pos.*', 'orders.*', 'customers.*', 'reports.view'],
            'salesperson' => ['pos.*', 'orders.create', 'customers.create', 'products.view'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'brand', 'shade', 'category'],
            'quick_actions' => [['label' => 'Shade Finder', 'action' => 'shade_finder']],
            'checkout_fields' => [['slug' => 'shade', 'label' => 'Shade/Color', 'required' => false]],
            'has_shade_selection' => true,
            'receipt_fields' => ['items', 'shade', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'daily_sales' => ['component' => 'RevenueSummary', 'title' => 'Daily Sales', 'width' => 'half', 'permission' => 'reports.view'],
            'popular_shades' => ['component' => 'PopularShadesWidget', 'title' => 'Popular Shades', 'width' => 'half', 'permission' => 'reports.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Sales by product and brand', 'permission' => 'reports.view', 'filters' => ['date_range', 'brand'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'enable_shade_management' => true];
    }

    public function featureFlags(): array
    {
        return [
            'shade_management',
            'ingredient_tracking',
            'skin_type_filtering',
            'batch_tracking',
            'expiry_tracking',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
        ];
    }

    public function branding(): array
    {
        return [
            'primary' => 'oklch(0.65 0.14 340)',     // Soft pink
            'primary_foreground' => 'oklch(0.985 0 0)',
            'accent' => 'oklch(0.92 0.05 340)',      // Rose light
            'accent_foreground' => 'oklch(0.205 0 0)',
            'sidebar' => 'oklch(0.96 0.03 340)',
            'sidebar_foreground' => 'oklch(0.205 0 0)',
            'sidebar_accent' => 'oklch(0.88 0.06 340)',
            'radius' => '0.875rem',                  // Soft, feminine
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
