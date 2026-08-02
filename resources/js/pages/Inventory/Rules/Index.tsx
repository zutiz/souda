import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type Rule = {
    id: number;
    name: string;
    description: string | null;
    condition_type: string;
    action_type: string;
    is_active: boolean;
    schedule: string;
    last_run_at: string | null;
    alerts_count: number;
    created_at: string;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type RulesIndexPageProps = {
    rules: {
        data: Rule[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
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

export default function RulesIndex() {
    const { rules } = usePage<RulesIndexPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Rules', href: '/inventory/rules' },
    ];

    function toggleRule(rule: Rule) {
        router.post(`/inventory/rules/${rule.id}/toggle`);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Automation Rules" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title="Automation Rules" description="IF-THEN rules for inventory automation">
                <Link href="/inventory/rules/create">
                    <Button>New Rule</Button>
                </Link>
            </PageHeader>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Name</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Condition</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Action</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Alerts</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Last Run</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rules.data.map((rule) => (
                            <tr key={rule.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm font-medium">
                                    <Link href={`/inventory/rules/${rule.id}`} className="hover:underline">
                                        {rule.name}
                                    </Link>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{conditionLabels[rule.condition_type] ?? rule.condition_type}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{actionLabels[rule.action_type] ?? rule.action_type}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{rule.alerts_count}</td>
                                <td className="py-3 px-3 text-sm">
                                    <button
                                        type="button"
                                        onClick={() => toggleRule(rule)}
                                        className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium cursor-pointer ${
                                            rule.is_active
                                                ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                        }`}
                                    >
                                        {rule.is_active ? 'Active' : 'Inactive'}
                                    </button>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{rule.last_run_at ?? 'Never'}</td>
                                <td className="py-3 px-3 text-right text-sm">
                                    <Link href={`/inventory/rules/${rule.id}`} className="text-sm text-blue-600 hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {rules.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="py-12 text-center text-sm text-muted-foreground">
                                    No rules found.{' '}
                                    <Link href="/inventory/rules/create" className="text-blue-600 hover:underline">Create one</Link>.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={rules.links}
                currentPage={rules.current_page}
                lastPage={rules.last_page}
                perPage={rules.per_page}
                total={rules.total}
            />
            </div>
        </AppLayout>
    );
}
