<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class BookstorePack implements IndustryPack
{
    public function slug(): string
    {
        return 'bookstore';
    }

    public function name(): string
    {
        return 'Bookstore';
    }

    public function description(): string
    {
        return 'Bookstore with ISBN management, author/publisher tracking, genre classification, and stock management';
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
                ['label' => 'Books', 'route' => 'products.index', 'icon' => 'BookOpen', 'children' => [
                    ['label' => 'All Books', 'route' => 'products.index'],
                    ['label' => 'Genres', 'route' => 'categories.index'],
                    ['label' => 'Authors', 'route' => 'bookstore.authors'],
                    ['label' => 'Publishers', 'route' => 'bookstore.publishers'],
                ]],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package'],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Author Sales', 'route' => 'reports.by-author'],
                    ['label' => 'Genre Analysis', 'route' => 'reports.by-genre'],
                ]],
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
            'product_search_columns' => ['name', 'isbn', 'author', 'publisher'],
            'quick_actions' => [['label' => 'Scan ISBN', 'action' => 'scan_isbn']],
            'checkout_fields' => [
                ['slug' => 'is_gift', 'label' => 'Gift Wrap', 'required' => false, 'type' => 'boolean'],
            ],
            'supports_isbn_scanning' => true,
            'receipt_fields' => ['items', 'isbn', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'daily_sales' => ['component' => 'RevenueSummary', 'title' => 'Daily Sales', 'width' => 'half', 'permission' => 'reports.view'],
            'top_books' => ['component' => 'TopSellingTable', 'title' => 'Top Selling Books', 'width' => 'full', 'permission' => 'reports.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Sales by genre and publisher', 'permission' => 'reports.view', 'filters' => ['date_range', 'genre'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'by-author' => ['name' => 'Author Sales', 'description' => 'Sales grouped by author', 'permission' => 'reports.view', 'filters' => ['date_range', 'author'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'by-genre' => ['name' => 'Genre Analysis', 'description' => 'Genre-wise sales performance', 'permission' => 'reports.view', 'filters' => ['date_range', 'genre'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'enable_isbn_validation' => true];
    }

    public function featureFlags(): array
    {
        return [
            'isbn_management',
            'author_tracking',
            'publisher_portal',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
            'overstock_detection',
        ];
    }

    public function branding(): array
    {
        return [
            'primary' => 'oklch(0.50 0.06 50)',      // Warm brown
            'primary_foreground' => 'oklch(0.985 0 0)',
            'accent' => 'oklch(0.95 0.04 60)',       // Cream
            'accent_foreground' => 'oklch(0.205 0 0)',
            'sidebar' => 'oklch(0.96 0.02 50)',
            'sidebar_foreground' => 'oklch(0.205 0 0)',
            'sidebar_accent' => 'oklch(0.90 0.03 50)',
            'radius' => '0.5rem',                    // Classic
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
