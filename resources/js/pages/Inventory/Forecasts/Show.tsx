import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import type { BreadcrumbItem } from '@/types';

type ForecastDetail = {
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
    metadata: Record<string, unknown> | null;
    product: { id: string; name: string; sku: string } | null;
    created_at: string;
};

type ForecastHistoryItem = {
    id: number;
    forecast_date: string;
    forecast_quantity: number;
    model_used: string;
    actual_quantity: number | null;
    accuracy_score: number | null;
};

type DemandHistoryItem = {
    period: string;
    quantity: number;
    movement_days: number;
};

type ForecastShowPageProps = {
    forecast: ForecastDetail;
    history: ForecastHistoryItem[];
    demandHistory: DemandHistoryItem[];
};

const modelLabels: Record<string, string> = {
    moving_average: 'Moving Average',
    seasonal: 'Seasonal (YoY)',
    linear_trend: 'Linear Trend',
};

export default function ForecastsShow() {
    const { forecast, history, demandHistory } = usePage<ForecastShowPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Forecasts', href: '/inventory/forecasts' },
        { title: forecast.product?.name ?? 'Forecast', href: `/inventory/forecasts/${forecast.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={forecast.product?.name ?? 'Forecast Detail'} />

            <PageHeader title={forecast.product?.name ?? 'Forecast Detail'} description={`SKU: ${forecast.product?.sku ?? '—'}`} />

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Forecast</div>
                    <div className="text-2xl font-bold text-blue-700">{forecast.forecast_quantity} units</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Confidence Range</div>
                    <div className="text-2xl font-bold">
                        {forecast.confidence_lower !== null && forecast.confidence_upper !== null
                            ? `${forecast.confidence_lower} – ${forecast.confidence_upper}`
                            : '—'}
                    </div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Actual</div>
                    <div className="text-2xl font-bold" style={{ color: forecast.actual_quantity !== null ? '#16a34a' : '#6b7280' }}>
                        {forecast.actual_quantity !== null ? forecast.actual_quantity : 'Pending'}
                    </div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Accuracy</div>
                    <div className="text-2xl font-bold" style={{
                        color: forecast.accuracy_score !== null
                            ? forecast.accuracy_score >= 80 ? '#16a34a' : forecast.accuracy_score >= 50 ? '#d97706' : '#dc2626'
                            : '#6b7280',
                    }}>
                        {forecast.accuracy_score !== null ? `${forecast.accuracy_score}%` : '—'}
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Model</div>
                    <div className="text-sm font-medium">{modelLabels[forecast.model_used] ?? forecast.model_used}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Period</div>
                    <div className="text-sm font-medium">{forecast.period_start} &rarr; {forecast.period_end}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Forecast Date</div>
                    <div className="text-sm font-medium">{forecast.forecast_date}</div>
                </div>
            </div>

            {forecast.metadata && Object.keys(forecast.metadata).length > 0 && (
                <div className="rounded-lg border border-sidebar-border p-4 mb-6">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-2">Model Parameters</div>
                    <pre className="text-sm whitespace-pre-wrap font-mono bg-muted/50 p-3 rounded">
                        {JSON.stringify(forecast.metadata, null, 2)}
                    </pre>
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="rounded-lg border border-sidebar-border">
                    <div className="border-b border-sidebar-border px-4 py-3 font-semibold text-sm">Forecast History</div>
                    <div className="divide-y divide-sidebar-border">
                        {history.length === 0 && (
                            <div className="px-4 py-8 text-center text-sm text-muted-foreground">No previous forecasts for this product.</div>
                        )}
                        {history.map((h) => (
                            <div key={h.id} className="px-4 py-3 flex items-center justify-between text-sm">
                                <div>
                                    <div className="font-medium">{h.forecast_date}</div>
                                    <div className="text-xs text-muted-foreground">{modelLabels[h.model_used] ?? h.model_used}</div>
                                </div>
                                <div className="text-right">
                                    <div className="font-medium">{h.forecast_quantity} units</div>
                                    {h.actual_quantity !== null && (
                                        <div className="text-xs" style={{ color: (h.accuracy_score ?? 0) >= 80 ? '#16a34a' : '#d97706' }}>
                                            Actual: {h.actual_quantity} &middot; {h.accuracy_score}% acc.
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="rounded-lg border border-sidebar-border">
                    <div className="border-b border-sidebar-border px-4 py-3 font-semibold text-sm">Demand History (Monthly)</div>
                    <div className="divide-y divide-sidebar-border">
                        {demandHistory.length === 0 && (
                            <div className="px-4 py-8 text-center text-sm text-muted-foreground">No demand data available.</div>
                        )}
                        {demandHistory.map((d, i) => (
                            <div key={i} className="px-4 py-3 flex items-center justify-between text-sm">
                                <div>
                                    <div className="font-medium">{d.period}</div>
                                    <div className="text-xs text-muted-foreground">{d.movement_days} movement day(s)</div>
                                </div>
                                <span className="font-medium text-amber-700">{d.quantity} units</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
