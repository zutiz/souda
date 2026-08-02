import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import type { BreadcrumbItem } from '@/types';

type StockItem = {
    id: number;
    product_id: string;
    warehouse_id: number;
    quantity: number;
    reserved_quantity: number;
    last_movement_at: string | null;
    product: { id: string; name: string; sku: string; low_stock_threshold: number } | null;
    warehouse: { id: number; name: string };
};

type PersistentAlert = {
    id: number;
    type: string;
    title: string;
    message: string | null;
    severity: string;
    product_id: string | null;
    warehouse_id: number | null;
    dismissed_at: string | null;
    resolved_at: string | null;
    created_at: string;
    rule: { id: number; name: string } | null;
};

type AlertsPageProps = {
    lowStock: StockItem[];
    deadStock: StockItem[];
    overstock: StockItem[];
    persistentAlerts: PersistentAlert[];
};

const severityColors: Record<string, string> = {
    info: 'bg-blue-100 text-blue-800',
    warning: 'bg-amber-100 text-amber-800',
    critical: 'bg-red-100 text-red-800',
};

export default function InventoryAlerts() {
    const { lowStock, deadStock, overstock, persistentAlerts } = usePage<AlertsPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Alerts', href: '/inventory/alerts' },
    ];

    function dismissAlert(id: number) {
        router.post(`/inventory/alerts/${id}/dismiss`);
    }

    function resolveAlert(id: number) {
        router.post(`/inventory/alerts/${id}/resolve`);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inventory Alerts" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title="Alerts" description="Low stock, dead stock, overstock, and rule-triggered alerts" />

            <div className="space-y-6">
                {persistentAlerts.length > 0 && (
                    <AlertSection title="Rule-Based Alerts" count={persistentAlerts.length} variant="default" empty="">
                        {persistentAlerts.map((alert) => (
                            <div key={alert.id} className="px-4 py-3 flex items-center justify-between text-sm">
                                <div>
                                    <div className="font-medium">{alert.title}</div>
                                    {alert.message && <div className="text-xs text-muted-foreground mt-0.5">{alert.message}</div>}
                                    <div className="text-xs text-muted-foreground mt-0.5">
                                        {alert.created_at}
                                        {alert.rule && <span> &middot; Rule: {alert.rule.name}</span>}
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${severityColors[alert.severity] ?? 'bg-muted'}`}>
                                        {alert.severity}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => dismissAlert(alert.id)}
                                        className="text-xs text-muted-foreground hover:text-foreground cursor-pointer"
                                    >
                                        Dismiss
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => resolveAlert(alert.id)}
                                        className="text-xs text-green-600 hover:text-green-800 cursor-pointer"
                                    >
                                        Resolve
                                    </button>
                                </div>
                            </div>
                        ))}
                    </AlertSection>
                )}

                <AlertSection title="Low Stock" count={lowStock.length} variant="warning" empty="All stock levels are healthy.">
                    {lowStock.map((item) => (
                        <AlertRow key={item.id} item={item}>
                            <span className="text-amber-600 font-medium">{item.quantity} / {item.product?.low_stock_threshold ?? '?'}</span>
                        </AlertRow>
                    ))}
                </AlertSection>

                <AlertSection title="Dead Stock (90d no movement)" count={deadStock.length} variant="danger" empty="No dead stock found.">
                    {deadStock.map((item) => (
                        <AlertRow key={item.id} item={item}>
                            <span className="text-red-600 text-xs">
                                Last movement: {item.last_movement_at ?? 'Never'}
                            </span>
                        </AlertRow>
                    ))}
                </AlertSection>

                <AlertSection title="Overstock (>1000 units)" count={overstock.length} variant="default" empty="No overstock items.">
                    {overstock.map((item) => (
                        <AlertRow key={item.id} item={item}>
                            <span className="font-medium">{item.quantity} units</span>
                        </AlertRow>
                    ))}
                </AlertSection>
            </div>
            </div>
        </AppLayout>
    );
}

function AlertSection({
    title, count, variant, empty, children,
}: {
    title: string;
    count: number;
    variant: 'default' | 'warning' | 'danger';
    empty: string;
    children: React.ReactNode;
}) {
    const headerColors = {
        default: 'border-sidebar-border',
        warning: 'border-amber-200 bg-amber-50',
        danger: 'border-red-200 bg-red-50',
    };

    return (
        <div className={`rounded-lg border ${headerColors[variant]}`}>
            <div className={`border-b px-4 py-3 font-semibold text-sm flex items-center justify-between ${variant === 'default' ? 'border-sidebar-border' : ''}`}>
                <span>{title}</span>
                <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${variant === 'default' ? 'bg-muted' : variant === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'}`}>
                    {count}
                </span>
            </div>
            <div className="divide-y divide-sidebar-border">
                {count === 0 && (
                    <div className="px-4 py-8 text-center text-sm text-muted-foreground">{empty}</div>
                )}
                {children}
            </div>
        </div>
    );
}

function AlertRow({ item, children }: { item: StockItem; children: React.ReactNode }) {
    return (
        <div className="px-4 py-3 flex items-center justify-between text-sm">
            <div>
                <div className="font-medium">{item.product?.name ?? 'Unknown'} ({item.product?.sku ?? '—'})</div>
                <div className="text-xs text-muted-foreground">{item.warehouse.name}</div>
            </div>
            <div>{children}</div>
        </div>
    );
}
