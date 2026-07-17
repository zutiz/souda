import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type Alert = {
    id: number;
    type: string;
    title: string;
    message: string | null;
    severity: string;
    created_at: string;
    dismissed_at: string | null;
    resolved_at: string | null;
};

type Rule = {
    id: number;
    name: string;
    description: string | null;
    condition_type: string;
    condition_config: Record<string, unknown>;
    action_type: string;
    action_config: Record<string, unknown>;
    is_active: boolean;
    schedule: string;
    last_run_at: string | null;
    alerts_count: number;
    alerts: Alert[];
    created_at: string;
    updated_at: string;
};

type RuleShowPageProps = {
    rule: Rule;
};

const conditionLabels: Record<string, string> = {
    low_stock: 'Low Stock',
    dead_stock: 'Dead Stock',
    overstock: 'Overstock',
    expiring_batch: 'Expiring Batch',
    slow_moving: 'Slow Moving',
    fast_moving: 'Fast Moving',
};

const actionLabels: Record<string, string> = {
    create_alert: 'Create Alert',
    send_notification: 'Send Notification',
    generate_suggestion: 'Generate Suggestion',
};

const severityColors: Record<string, string> = {
    info: 'bg-blue-100 text-blue-800',
    warning: 'bg-amber-100 text-amber-800',
    critical: 'bg-red-100 text-red-800',
};

export default function RulesShow() {
    const { rule } = usePage<RuleShowPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Rules', href: '/inventory/rules' },
        { title: rule.name, href: `/inventory/rules/${rule.id}` },
    ];

    function toggleRule() {
        router.post(`/inventory/rules/${rule.id}/toggle`);
    }

    function evaluateRule() {
        router.post(`/inventory/rules/${rule.id}/evaluate`);
    }

    function deleteRule() {
        if (confirm('Delete this rule?')) {
            router.delete(`/inventory/rules/${rule.id}`);
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={rule.name} />

            <PageHeader title={rule.name} description={rule.description ?? 'Automation rule'}>
                <div className="flex items-center gap-2">
                    <Button variant="outline" onClick={toggleRule}>
                        {rule.is_active ? 'Deactivate' : 'Activate'}
                    </Button>
                    <Button variant="outline" onClick={evaluateRule}>
                        Run Now
                    </Button>
                    <Button variant="destructive" onClick={deleteRule}>
                        Delete
                    </Button>
                </div>
            </PageHeader>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Condition</div>
                    <div className="text-lg font-semibold">{conditionLabels[rule.condition_type] ?? rule.condition_type}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Action</div>
                    <div className="text-lg font-semibold">{actionLabels[rule.action_type] ?? rule.action_type}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Status</div>
                    <div className="text-lg font-semibold">
                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                            rule.is_active ? 'bg-green-100 text-green-800' : 'bg-muted text-muted-foreground'
                        }`}>
                            {rule.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-2">Condition Config</div>
                    <pre className="text-sm whitespace-pre-wrap font-mono bg-muted/50 p-3 rounded">
                        {JSON.stringify(rule.condition_config, null, 2)}
                    </pre>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-2">Action Config</div>
                    <pre className="text-sm whitespace-pre-wrap font-mono bg-muted/50 p-3 rounded">
                        {JSON.stringify(rule.action_config, null, 2)}
                    </pre>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Schedule</div>
                    <div className="text-sm font-medium">{rule.schedule.replace(/_/g, ' ')}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Last Run</div>
                    <div className="text-sm font-medium">{rule.last_run_at ?? 'Never'}</div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <div className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Total Alerts</div>
                    <div className="text-sm font-medium">{rule.alerts_count}</div>
                </div>
            </div>

            <div className="rounded-lg border border-sidebar-border">
                <div className="border-b border-sidebar-border px-4 py-3 font-semibold text-sm">Recent Alerts</div>
                <div className="divide-y divide-sidebar-border">
                    {rule.alerts.length === 0 && (
                        <div className="px-4 py-8 text-center text-sm text-muted-foreground">No alerts triggered yet.</div>
                    )}
                    {rule.alerts.map((alert) => (
                        <div key={alert.id} className="px-4 py-3 flex items-center justify-between text-sm">
                            <div>
                                <div className="font-medium">{alert.title}</div>
                                {alert.message && <div className="text-xs text-muted-foreground mt-0.5">{alert.message}</div>}
                                <div className="text-xs text-muted-foreground mt-0.5">{alert.created_at}</div>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${severityColors[alert.severity] ?? 'bg-muted'}`}>
                                    {alert.severity}
                                </span>
                                {alert.dismissed_at && <span className="text-xs text-muted-foreground">Dismissed</span>}
                                {alert.resolved_at && <span className="text-xs text-green-600">Resolved</span>}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
