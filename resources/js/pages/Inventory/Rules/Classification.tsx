import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
import { type FormEventHandler } from 'react';

type Warehouse = {
    id: number;
    name: string;
};

type Balance = {
    id: number;
    product_id: string;
    warehouse_id: number;
    quantity: number;
    total_stock_value: number;
    abc_class: string | null;
    velocity_class: string | null;
    product: { id: string; name: string; sku: string } | null;
    warehouse: { id: number; name: string };
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type ClassificationPageProps = {
    stats: {
        abc: Record<string, number>;
        velocity: Record<string, number>;
    };
    balances: {
        data: Balance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
    warehouses: Warehouse[];
    filters: {
        abc_class: string | null;
        velocity_class: string | null;
        warehouse_id: string | null;
        search: string | null;
    };
};

const abcColors: Record<string, string> = {
    a: 'bg-green-100 text-green-800',
    b: 'bg-amber-100 text-amber-800',
    c: 'bg-red-100 text-red-800',
};

const velocityColors: Record<string, string> = {
    fast: 'bg-green-100 text-green-800',
    slow: 'bg-amber-100 text-amber-800',
    dead: 'bg-red-100 text-red-800',
    new: 'bg-blue-100 text-blue-800',
};

const velocityLabels: Record<string, string> = {
    fast: 'Fast Moving',
    slow: 'Slow Moving',
    dead: 'Dead Stock',
    new: 'New',
};

export default function Classification() {
    const { stats, balances, warehouses, filters } = usePage<ClassificationPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Classification', href: '/inventory/classification' },
    ];

    function applyFilter(key: string, value: string) {
        router.get('/inventory/classification', { ...filters, [key]: value || null }, { preserveState: true });
    }

    function refreshClassification() {
        router.post('/inventory/classification/refresh', {}, { preserveState: true });
    }

    const handleSearch: FormEventHandler = (e) => {
        e.preventDefault();
        const form = e.target as HTMLFormElement;
        const searchInput = form.elements.namedItem('search') as HTMLInputElement;
        applyFilter('search', searchInput.value);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Stock Classification" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title="Stock Classification" description="ABC value analysis and velocity classification">
                <Button onClick={refreshClassification}>Refresh Classification</Button>
            </PageHeader>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">ABC A (High Value)</div>
                    <div className="text-2xl font-bold text-green-700">{stats.abc.a}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">ABC B (Medium Value)</div>
                    <div className="text-2xl font-bold text-amber-700">{stats.abc.b}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">ABC C (Low Value)</div>
                    <div className="text-2xl font-bold text-red-700">{stats.abc.c}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Total Classified</div>
                    <div className="text-2xl font-bold">{Object.values(stats.abc).reduce((a, b) => a + b, 0)}</div>
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Fast Moving</div>
                    <div className="text-2xl font-bold text-green-700">{stats.velocity.fast}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Slow Moving</div>
                    <div className="text-2xl font-bold text-amber-700">{stats.velocity.slow}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Dead Stock</div>
                    <div className="text-2xl font-bold text-red-700">{stats.velocity.dead}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">New / Unclassified</div>
                    <div className="text-2xl font-bold text-blue-700">{stats.velocity.new}</div>
                </div>
            </div>

            <div className="rounded-lg border border-sidebar-border mb-6">
                <div className="border-b border-sidebar-border px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                    <span className="font-semibold text-sm">Filter</span>
                    <div className="flex items-center gap-2 flex-wrap">
                        <select
                            value={filters.abc_class ?? ''}
                            onChange={(e) => applyFilter('abc_class', e.target.value)}
                            className="rounded-lg border border-sidebar-border px-3 py-1.5 text-sm"
                        >
                            <option value="">All ABC Classes</option>
                            <option value="a">A (High Value)</option>
                            <option value="b">B (Medium Value)</option>
                            <option value="c">C (Low Value)</option>
                        </select>
                        <select
                            value={filters.velocity_class ?? ''}
                            onChange={(e) => applyFilter('velocity_class', e.target.value)}
                            className="rounded-lg border border-sidebar-border px-3 py-1.5 text-sm"
                        >
                            <option value="">All Velocity Classes</option>
                            <option value="fast">Fast Moving</option>
                            <option value="slow">Slow Moving</option>
                            <option value="dead">Dead Stock</option>
                            <option value="new">New</option>
                        </select>
                        <select
                            value={filters.warehouse_id ?? ''}
                            onChange={(e) => applyFilter('warehouse_id', e.target.value)}
                            className="rounded-lg border border-sidebar-border px-3 py-1.5 text-sm"
                        >
                            <option value="">All Warehouses</option>
                            {warehouses.map((w) => (
                                <option key={w.id} value={String(w.id)}>{w.name}</option>
                            ))}
                        </select>
                        <form onSubmit={handleSearch} className="flex items-center gap-1">
                            <input
                                name="search"
                                defaultValue={filters.search ?? ''}
                                placeholder="Search products..."
                                className="rounded-lg border border-sidebar-border px-3 py-1.5 text-sm w-48"
                            />
                            <Button type="submit" variant="outline" className="text-xs">Search</Button>
                        </form>
                    </div>
                </div>
            </div>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Qty</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Value</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">ABC Class</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Velocity</th>
                        </tr>
                    </thead>
                    <tbody>
                        {balances.data.map((b) => (
                            <tr key={b.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm">
                                    <div className="font-medium">{b.product?.name ?? 'Unknown'}</div>
                                    <div className="text-xs text-muted-foreground">{b.product?.sku}</div>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{b.warehouse.name}</td>
                                <td className="py-3 px-3 text-sm text-right">{b.quantity}</td>
                                <td className="py-3 px-3 text-sm text-right">{(b.total_stock_value / 100).toFixed(2)}</td>
                                <td className="py-3 px-3 text-sm">
                                    {b.abc_class && (
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${abcColors[b.abc_class] ?? 'bg-muted'}`}>
                                            {b.abc_class.toUpperCase()}
                                        </span>
                                    )}
                                </td>
                                <td className="py-3 px-3 text-sm">
                                    {b.velocity_class && (
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${velocityColors[b.velocity_class] ?? 'bg-muted'}`}>
                                            {velocityLabels[b.velocity_class] ?? b.velocity_class}
                                        </span>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {balances.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="py-12 text-center text-sm text-muted-foreground">
                                    No classified balances found.{' '}
                                    <button type="button" onClick={refreshClassification} className="text-blue-600 hover:underline cursor-pointer">
                                        Run classification
                                    </button>.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={balances.links}
                currentPage={balances.current_page}
                lastPage={balances.last_page}
                perPage={balances.per_page}
                total={balances.total}
            />
            </div>
        </AppLayout>
    );
}
