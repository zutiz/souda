import { Head, Link, usePage } from '@inertiajs/react';
import { Download } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/modules/shared/components/page-header';
import { ComparisonBarChart } from '@/modules/inventory/components/charts/ComparisonBarChart';
import { DistributionPieChart } from '@/modules/inventory/components/charts/DistributionPieChart';
import { HealthGauge } from '@/modules/inventory/components/charts/HealthGauge';
import { TrendLineChart } from '@/modules/inventory/components/charts/TrendLineChart';
import { DashboardFilters } from '@/modules/inventory/components/DashboardFilters';
import type { BreadcrumbItem } from '@/types';

type Stat = {
    total_stock_value: number;
    today_movements_in: number;
    today_movements_out: number;
    low_stock_count: number;
    expiring_count: number;
};

type ClassificationStats = {
    abc: Record<string, number>;
    velocity: Record<string, number>;
};

type RecentMovement = {
    id: number;
    product_name: string;
    sku: string | null;
    warehouse_name: string | null;
    quantity: number;
    type: string;
    created_at: string;
};

type LowStockItem = {
    id: number;
    product: { id: number; name: string } | null;
    warehouse: { id: number; name: string };
    quantity: number;
    reserved_quantity: number;
};

type ChartData = {
    movement_trend: { date: string; quantity_in: number; quantity_out: number; net_movement: number }[];
    stock_value_trend: { date: string; value: number }[];
    top_moving_products: { product_id: string; product_name: string | null; sku: string | null; total_out: number; movement_days: number }[];
    classification_distribution: ClassificationStats;
    dead_stock_trend: { date: string; dead_stock_count: number; dead_stock_value: number }[];
    health_score: { score: number; grade: string; low_stock_ratio: number; dead_stock_ratio: number; avg_velocity: number };
    forecast_accuracy: { model_used: string; avg_accuracy: number; count: number }[];
};

type DashboardPageProps = {
    stats: Stat;
    classificationStats: ClassificationStats;
    recentMovements: RecentMovement[];
    lowStockItems: LowStockItem[];
    chartData: ChartData;
};

function formatCents(cents: number) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(cents / 100);
}

function formatNumber(n: number) {
    return n.toLocaleString();
}

export default function InventoryDashboard() {
    const { stats, classificationStats, recentMovements, lowStockItems, chartData } = usePage<DashboardPageProps>().props;
    const totalClassified = Object.values(classificationStats.abc).reduce((a, b) => a + b, 0);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
    ];

    const abcPieData = Object.entries(classificationStats.abc).map(([key, val]) => ({
        name: key === 'a' ? 'A (High Value)' : key === 'b' ? 'B (Medium)' : 'C (Low)',
        value: val,
    }));

    const velocityPieData = Object.entries(classificationStats.velocity).map(([key, val]) => ({
        name: key === 'fast' ? 'Fast' : key === 'slow' ? 'Slow' : key === 'dead' ? 'Dead' : 'New',
        value: val,
    }));

    const accuracyBarData = chartData.forecast_accuracy.map((f) => ({
        name: f.model_used.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
        accuracy: f.avg_accuracy,
        count: f.count,
    }));

    const topProducts = chartData.top_moving_products.map((p, i) => ({
        rank: i + 1,
        product_id: p.product_id,
        total_out: p.total_out,
        movement_days: p.movement_days,
    }));

    const exportUrl = `/inventory/dashboard/export/csv`;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inventory Dashboard" />

            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <PageHeader title="Inventory" description="Overview of your inventory performance" />
                <div className="flex items-center gap-3">
                    <DashboardFilters days={30} warehouseId={null} />
                    <Button variant="outline" size="sm" asChild>
                        <Link href={exportUrl}>
                            <Download className="mr-1 size-4" />
                            Export CSV
                        </Link>
                    </Button>
                </div>
            </div>

            {/* Stat Cards */}
            <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <StatCard label="Total Stock Value" value={formatCents(stats.total_stock_value)} />
                <StatCard label="Movements In (Today)" value={formatNumber(stats.today_movements_in)} />
                <StatCard label="Movements Out (Today)" value={formatNumber(stats.today_movements_out)} />
                <StatCard label="Low Stock Items" value={formatNumber(stats.low_stock_count)} variant={stats.low_stock_count > 0 ? 'warning' : 'default'} />
                <StatCard label="Expiring Soon" value={formatNumber(stats.expiring_count)} variant={stats.expiring_count > 0 ? 'danger' : 'default'} />
            </div>

            {/* Health Score + Classification Cards */}
            <div className="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-medium">Inventory Health</CardTitle>
                    </CardHeader>
                    <CardContent className="flex justify-center pt-2">
                        <HealthGauge score={chartData.health_score.score} grade={chartData.health_score.grade} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-medium">ABC A (High Value)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-green-700">{classificationStats.abc.a}</div>
                        <p className="text-xs text-muted-foreground mt-1">
                            {totalClassified > 0 ? `${((classificationStats.abc.a / totalClassified) * 100).toFixed(1)}% of classified` : '—'}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-medium">Fast Moving</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-green-700">{classificationStats.velocity.fast}</div>
                        <p className="text-xs text-muted-foreground mt-1">
                            <Link href="/inventory/classification?velocity_class=dead" className="hover:underline text-red-600">
                                {classificationStats.velocity.dead} dead stock
                            </Link>
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-medium">Health Details</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-1 text-sm">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Low Stock Ratio</span>
                            <span>{(chartData.health_score.low_stock_ratio * 100).toFixed(1)}%</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Dead Stock Ratio</span>
                            <span>{(chartData.health_score.dead_stock_ratio * 100).toFixed(1)}%</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Avg Velocity</span>
                            <span>{chartData.health_score.avg_velocity}/day</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Movement Trend + Stock Value Trend */}
            <div className="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">Movement Trend</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TrendLineChart
                            data={chartData.movement_trend.map((m) => ({
                                ...m,
                                date: m.date.slice(5),
                            }))}
                            xKey="date"
                            series={[
                                { key: 'quantity_in', color: 'var(--chart-1)', name: 'In' },
                                { key: 'quantity_out', color: 'var(--chart-2)', name: 'Out' },
                                { key: 'net_movement', color: 'var(--chart-3)', name: 'Net' },
                            ]}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">Stock Value Trend</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TrendLineChart
                            data={chartData.stock_value_trend.map((v) => ({
                                ...v,
                                date: v.date.slice(5),
                            }))}
                            xKey="date"
                            series={[
                                { key: 'value', color: 'var(--chart-4)', name: 'Stock Value' },
                            ]}
                            yFormatter={(v) => formatCents(v)}
                        />
                    </CardContent>
                </Card>
            </div>

            {/* ABC Distribution + Velocity Distribution */}
            <div className="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">ABC Classification</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DistributionPieChart data={abcPieData} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">Velocity Classification</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DistributionPieChart
                            data={velocityPieData}
                            colors={['var(--chart-1)', 'var(--chart-3)', 'var(--chart-5)', 'var(--chart-2)']}
                        />
                    </CardContent>
                </Card>
            </div>

            {/* Dead Stock Trend + Top Moving Products */}
            <div className="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">Dead Stock Trend</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TrendLineChart
                            data={chartData.dead_stock_trend.map((d) => ({
                                ...d,
                                date: d.date.slice(5),
                            }))}
                            xKey="date"
                            series={[
                                { key: 'dead_stock_count', color: 'var(--chart-5)', name: 'Dead Stock Count' },
                            ]}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">Top Moving Products</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ComparisonBarChart
                            data={topProducts}
                            xKey="rank"
                            bars={[{ key: 'total_out', color: 'var(--chart-1)', name: 'Units Moved' }]}
                            yFormatter={(v) => formatNumber(v)}
                            barSize={16}
                        />
                    </CardContent>
                </Card>
            </div>

            {/* Forecast Accuracy + Recent Movements + Low Stock */}
            <div className="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">Forecast Accuracy</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {accuracyBarData.length > 0 ? (
                            <ComparisonBarChart
                                data={accuracyBarData}
                                xKey="name"
                                bars={[{ key: 'accuracy', color: 'var(--chart-2)', name: 'Accuracy %' }]}
                                barSize={24}
                            />
                        ) : (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                No forecast data with accuracy yet.
                            </p>
                        )}
                    </CardContent>
                </Card>

                <RecentMovementsPanel movements={recentMovements} />
                <LowStockPanel items={lowStockItems} />
            </div>
        </AppLayout>
    );
}

function StatCard({ label, value, variant = 'default' }: { label: string; value: string; variant?: 'default' | 'warning' | 'danger' }) {
    const colors = {
        default: 'bg-card text-card-foreground',
        warning: 'bg-amber-50 border-amber-200 text-amber-800',
        danger: 'bg-red-50 border-red-200 text-red-800',
    };

    return (
        <div className={`rounded-lg border p-4 ${colors[variant]}`}>
            <div className="text-xs font-medium uppercase tracking-wider opacity-70">{label}</div>
            <div className="mt-1 text-2xl font-bold">{value}</div>
        </div>
    );
}

function RecentMovementsPanel({ movements }: { movements: RecentMovement[] }) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Recent Movements</CardTitle>
            </CardHeader>
            <CardContent className="p-0">
                <div className="divide-y divide-border">
                    {movements.length === 0 && (
                        <div className="px-4 py-8 text-center text-sm text-muted-foreground">No movements today.</div>
                    )}
                    {movements.map((m) => (
                        <div key={m.id} className="px-4 py-3 flex items-center justify-between text-sm">
                            <div className="min-w-0 flex-1">
                                <div className="font-medium truncate">{m.product_name}</div>
                                <div className="text-xs text-muted-foreground truncate">
                                    {m.warehouse_name} &middot; {m.created_at}
                                </div>
                            </div>
                            <span className={m.quantity > 0 ? 'text-green-600 font-medium shrink-0 ml-2' : 'text-red-600 font-medium shrink-0 ml-2'}>
                                {m.quantity > 0 ? '+' : ''}{m.quantity}
                            </span>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}

function LowStockPanel({ items }: { items: LowStockItem[] }) {
    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
                <CardTitle className="text-sm font-medium">Low Stock Alerts</CardTitle>
                {items.length > 0 && (
                    <Badge variant="destructive" className="text-xs">
                        {items.length}
                    </Badge>
                )}
            </CardHeader>
            <CardContent className="p-0">
                <div className="divide-y divide-border">
                    {items.length === 0 && (
                        <div className="px-4 py-8 text-center text-sm text-muted-foreground">All stock levels are healthy.</div>
                    )}
                    {items.map((item) => (
                        <div key={item.id} className="px-4 py-3 flex items-center justify-between text-sm">
                            <div className="min-w-0 flex-1">
                                <div className="font-medium truncate">{item.product?.name ?? 'Unknown'}</div>
                                <div className="text-xs text-muted-foreground">{item.warehouse.name}</div>
                            </div>
                            <span className="text-amber-600 font-medium shrink-0 ml-2">{item.quantity} on hand</span>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
