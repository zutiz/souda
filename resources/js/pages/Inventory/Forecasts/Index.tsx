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

type Forecast = {
    id: number;
    product_id: string;
    warehouse_id: number;
    forecast_date: string;
    forecast_quantity: number;
    confidence_lower: number | null;
    confidence_upper: number | null;
    model_used: string;
    period_start: string;
    period_end: string;
    actual_quantity: number | null;
    accuracy_score: number | null;
    product: { id: string; name: string; sku: string } | null;
    created_at: string;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type Strategy = {
    value: string;
    label: string;
};

type ForecastsIndexPageProps = {
    forecasts: {
        data: Forecast[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
    warehouses: Warehouse[];
    strategies: Strategy[];
    filters: {
        warehouse_id: string | null;
        days: string;
    };
};

const modelLabels: Record<string, string> = {
    moving_average: 'Moving Average',
    seasonal: 'Seasonal (YoY)',
    linear_trend: 'Linear Trend',
};

export default function ForecastsIndex() {
    const { forecasts, warehouses, strategies, filters } = usePage<ForecastsIndexPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Forecasts', href: '/inventory/forecasts' },
    ];

    function applyFilter(key: string, value: string) {
        router.get('/inventory/forecasts', { ...filters, [key]: value || null }, { preserveState: true });
    }

    function generateForecasts(strategy: string) {
        router.post('/inventory/forecasts/generate', { strategy, warehouse_id: filters.warehouse_id, horizon: 30 }, { preserveState: true });
    }

    function resolveExpired() {
        router.post('/inventory/forecasts/resolve', { days_old: 1 }, { preserveState: true });
    }

    const handleGenerate: FormEventHandler = (e) => {
        e.preventDefault();
        const form = e.target as HTMLFormElement;
        const strategy = (form.elements.namedItem('strategy') as HTMLSelectElement).value;
        generateForecasts(strategy);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Demand Forecasts" />

            <PageHeader title="Demand Forecasts" description="AI-driven demand predictions and reorder insights">
                <div className="flex items-center gap-2">
                    <Button variant="outline" onClick={resolveExpired}>Resolve Expired</Button>
                </div>
            </PageHeader>

            <div className="rounded-lg border border-sidebar-border mb-6">
                <div className="border-b border-sidebar-border px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                    <span className="font-semibold text-sm">Generate Forecast</span>
                    <form onSubmit={handleGenerate} className="flex items-center gap-2">
                        <select
                            name="strategy"
                            className="rounded-lg border border-sidebar-border px-3 py-1.5 text-sm"
                        >
                            <option value="">Default (Moving Average)</option>
                            <option value="all">All Strategies</option>
                            {strategies.map((s) => (
                                <option key={s.value} value={s.value}>{s.label}</option>
                            ))}
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
                        <select
                            value={filters.days}
                            onChange={(e) => applyFilter('days', e.target.value)}
                            className="rounded-lg border border-sidebar-border px-3 py-1.5 text-sm"
                        >
                            <option value="7">Next 7 days</option>
                            <option value="30">Next 30 days</option>
                            <option value="60">Next 60 days</option>
                            <option value="90">Next 90 days</option>
                        </select>
                        <Button type="submit">Generate</Button>
                    </form>
                </div>
            </div>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Model</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Forecast</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Range</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Period</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actual</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Accuracy</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {forecasts.data.map((f) => (
                            <tr key={f.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm font-medium">
                                    <Link href={`/inventory/forecasts/${f.id}`} className="hover:underline">
                                        {f.product?.name ?? 'Unknown'}
                                    </Link>
                                    <div className="text-xs text-muted-foreground">{f.product?.sku}</div>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{modelLabels[f.model_used] ?? f.model_used}</td>
                                <td className="py-3 px-3 text-sm text-right font-semibold">{f.forecast_quantity}</td>
                                <td className="py-3 px-3 text-sm text-right text-muted-foreground">
                                    {f.confidence_lower !== null && f.confidence_upper !== null
                                        ? `${f.confidence_lower}–${f.confidence_upper}`
                                        : '—'}
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">
                                    {f.period_start} &rarr; {f.period_end}
                                </td>
                                <td className="py-3 px-3 text-sm text-right">
                                    {f.actual_quantity !== null ? (
                                        <span className={f.actual_quantity >= f.confidence_lower && f.actual_quantity <= f.confidence_upper ? 'text-green-600' : 'text-amber-600'}>
                                            {f.actual_quantity}
                                        </span>
                                    ) : (
                                        <span className="text-muted-foreground">Pending</span>
                                    )}
                                </td>
                                <td className="py-3 px-3 text-sm text-right">
                                    {f.accuracy_score !== null ? (
                                        <span className={`font-medium ${f.accuracy_score >= 80 ? 'text-green-600' : f.accuracy_score >= 50 ? 'text-amber-600' : 'text-red-600'}`}>
                                            {f.accuracy_score}%
                                        </span>
                                    ) : (
                                        <span className="text-muted-foreground">—</span>
                                    )}
                                </td>
                                <td className="py-3 px-3 text-right text-sm">
                                    <Link href={`/inventory/forecasts/${f.id}`} className="text-sm text-blue-600 hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {forecasts.data.length === 0 && (
                            <tr>
                                <td colSpan={8} className="py-12 text-center text-sm text-muted-foreground">
                                    No forecasts found. Use the form above to generate them.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={forecasts.links}
                currentPage={forecasts.current_page}
                lastPage={forecasts.last_page}
                perPage={forecasts.per_page}
                total={forecasts.total}
            />
        </AppLayout>
    );
}
