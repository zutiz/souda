<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class PharmacyPack implements IndustryPack
{
    public function slug(): string
    {
        return 'pharmacy';
    }

    public function name(): string
    {
        return 'Pharmacy';
    }

    public function description(): string
    {
        return 'Pharmacy and drug store with prescription management, batch tracking, and expiry monitoring';
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
            'supplier' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Pill', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                    ['label' => 'Drug Schedule', 'route' => 'pharmacy.schedule'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Stock Overview', 'route' => 'inventory.index'],
                    ['label' => 'Batch Tracking', 'route' => 'pharmacy.batches'],
                    ['label' => 'Expiry Tracking', 'route' => 'pharmacy.expiry'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                    ['label' => 'Low Stock Alerts', 'route' => 'inventory.low-stock'],
                ]],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart', 'children' => [
                    ['label' => 'POS', 'route' => 'pos.index'],
                    ['label' => 'Orders', 'route' => 'orders.index'],
                    ['label' => 'Returns', 'route' => 'orders.returns'],
                ]],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'Truck'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Medicine Sales', 'route' => 'reports.medicine-sales'],
                    ['label' => 'Expiry Report', 'route' => 'reports.expiry'],
                    ['label' => 'Prescription vs OTC', 'route' => 'reports.prescription-otc'],
                    ['label' => 'Sales by Manufacturer', 'route' => 'reports.by-manufacturer'],
                    ['label' => 'Stock Valuation', 'route' => 'reports.stock-valuation'],
                ]],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => [
                'products.*',
                'inventory.*',
                'pos.*',
                'orders.*',
                'customers.*',
                'suppliers.*',
                'reports.*',
                'settings.*',
                'pharmacy.*',
                'pharmacy.schedule.manage',
                'pharmacy.batches.*',
                'pharmacy.expiry.*',
            ],
            'manager' => [
                'products.*',
                'inventory.view',
                'inventory.stock.adjust',
                'pos.*',
                'orders.*',
                'customers.view',
                'customers.create',
                'reports.view',
                'pharmacy.schedule.view',
                'pharmacy.batches.view',
                'pharmacy.expiry.view',
            ],
            'pharmacist' => [
                'products.create',
                'products.update',
                'products.view',
                'inventory.view',
                'inventory.stock.adjust',
                'pos.create',
                'orders.create',
                'orders.view',
                'customers.create',
                'customers.view',
                'pharmacy.schedule.manage',
                'pharmacy.batches.create',
                'pharmacy.expiry.check',
            ],
            'cashier' => [
                'pos.*',
                'orders.create',
                'orders.view',
                'customers.create',
                'inventory.view',
                'inventory.stock.check',
                'pharmacy.expiry.check',
            ],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'barcode', 'generic_name', 'strength'],
            'quick_actions' => [
                ['label' => 'Prescription Required', 'action' => 'mark_prescription'],
                ['label' => 'Add Batch', 'action' => 'select_batch'],
            ],
            'checkout_fields' => [
                ['slug' => 'prescription_number', 'label' => 'Prescription #', 'required' => false],
                ['slug' => 'doctor_name', 'label' => 'Doctor Name', 'required' => false],
                ['slug' => 'patient_name', 'label' => 'Patient Name', 'required' => false],
                ['slug' => 'dispensing_fee', 'label' => 'Dispensing Fee', 'required' => false, 'type' => 'money'],
            ],
            'batch_picking' => true,
            'show_expiry_warning' => true,
            'require_prescription_check' => true,
            'receipt_fields' => ['drug_name', 'batch', 'expiry', 'manufacturer'],
            'tender_types' => ['cash', 'card', 'mobile_banking', 'insurance'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'expiring_products' => [
                'component' => 'ExpiringProductsWidget',
                'title' => 'Expiring Soon (30 days)',
                'width' => 'half',
                'permission' => 'inventory.view',
            ],
            'prescription_vs_otc' => [
                'component' => 'PrescriptionOTCPieChart',
                'title' => 'Prescription vs OTC Sales',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'top_medicines' => [
                'component' => 'TopSellingTable',
                'title' => 'Top Selling Medicines',
                'width' => 'full',
                'permission' => 'reports.view',
            ],
            'low_stock_medicines' => [
                'component' => 'LowStockAlerts',
                'title' => 'Low Stock Alerts',
                'width' => 'half',
                'permission' => 'inventory.view',
            ],
            'today_sales_summary' => [
                'component' => 'TodaySalesSummary',
                'title' => 'Today\'s Sales',
                'width' => 'half',
                'permission' => 'pos.view',
            ],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'medicine-sales' => [
                'name' => 'Medicine Sales Report',
                'description' => 'Sales breakdown by medicine, category, and time period',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category', 'brand', 'manufacturer'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'expiry' => [
                'name' => 'Expiry Report',
                'description' => 'Products expiring within a selected date range',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category'],
                'export_formats' => ['pdf', 'csv'],
            ],
            'prescription-otc' => [
                'name' => 'Prescription vs OTC Analysis',
                'description' => 'Sales comparison between prescription and over-the-counter medicines',
                'permission' => 'reports.view',
                'filters' => ['date_range'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'by-manufacturer' => [
                'name' => 'Sales by Manufacturer',
                'description' => 'Sales grouped by drug manufacturer',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'manufacturer'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'stock-valuation' => [
                'name' => 'Stock Valuation',
                'description' => 'Current inventory valuation at cost and MRP',
                'permission' => 'reports.view',
                'filters' => ['category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'currency' => 'BDT',
            'weight_unit' => 'mg',
            'default_markup_percentage' => 15,
            'require_prescription_for_scheduled_drugs' => true,
            'expiry_warning_days' => 30,
            'auto_generate_batch_codes' => true,
        ];
    }

    public function featureFlags(): array
    {
        return [
            'batch_tracking',
            'expiry_tracking',
            'prescription_management',
            'drug_schedule_management',
            'insurance_billing',
            'fefo_picking',
            'quarantine_management',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}
