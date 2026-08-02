import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import type { BreadcrumbItem } from '@/types';

type ScheduleEntry = {
    command: string;
    frequency: string;
    description: string;
    last_run: string | null;
    last_run_at: string | null;
    duration_ms: number | null;
    status: 'success' | 'failed' | 'running' | 'never';
};

type OperationsPageProps = {
    schedules: ScheduleEntry[];
};

export default function OperationsIndex() {
    const { schedules } = usePage<OperationsPageProps>().props;
    const [runningCmd, setRunningCmd] = useState<string | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Operations', href: '/inventory/operations' },
    ];

    function runCommand(command: string) {
        setRunningCmd(command);
        router.post(
            `/inventory/operations/${encodeURIComponent(command)}/run`,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setRunningCmd(null),
            },
        );
    }

    function statusBadge(status: string) {
        switch (status) {
            case 'success':
                return <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Success</span>;
            case 'failed':
                return <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Failed</span>;
            case 'running':
                return <span className="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">Running</span>;
            default:
                return <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Never</span>;
        }
    }

    function formatDuration(ms: number | null) {
        if (ms === null) return '--';
        if (ms < 1000) return `${ms}ms`;
        return `${(ms / 1000).toFixed(1)}s`;
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inventory Operations" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader
                    title="Operations"
                    description="Monitor and manually trigger scheduled inventory tasks"
                />

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Command</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Description</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Frequency</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Last Run</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Duration</th>
                            <th className="py-3 px-3 text-center text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sidebar-border">
                        {schedules.map((entry) => (
                            <tr key={entry.command} className="hover:bg-muted/30">
                                <td className="py-3 px-3 text-sm font-mono">{entry.command}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{entry.description}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{entry.frequency}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{entry.last_run ?? '--'}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground text-right">{formatDuration(entry.duration_ms)}</td>
                                <td className="py-3 px-3 text-center">{statusBadge(entry.status)}</td>
                                <td className="py-3 px-3 text-right">
                                    <button
                                        onClick={() => runCommand(entry.command)}
                                        disabled={runningCmd === entry.command}
                                        className="rounded px-2 py-1 text-xs font-medium text-primary hover:bg-primary/10 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {runningCmd === entry.command ? 'Running...' : 'Run Now'}
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            </div>
        </AppLayout>
    );
}
