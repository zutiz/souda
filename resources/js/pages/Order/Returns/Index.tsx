import { Head, Link } from '@inertiajs/react';
import { Package, Eye } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { DataTable } from '@/modules/shared/components/data-table';
import { DataTableToolbar, ToolbarSection } from '@/modules/shared/components/data-table-toolbar';
import { StatusBadge } from '@/modules/shared/components/status-badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/react-table';

type Return = {
    id: number;
    order_id: string;
    status: string;
    reason: string;
    total_refund_amount: number;
    created_at: string;
    order?: {
        order_number: string;
    };
};

type ReturnsPageProps = {
    returns: Return[];
    filters: {
        status?: string;
    };
};

const statusToBadgeType: Record<string, 'pending' | 'completed' | 'cancelled'> = {
    pending: 'pending',
    approved: 'completed',
    rejected: 'cancelled',
    completed: 'completed',
    cancelled: 'cancelled',
};

export default function ReturnsIndex({ returns, filters }: ReturnsPageProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Orders', href: '/orders' },
        { label: 'Returns', href: '/orders/returns' },
    ];

    const columns: ColumnDef<Return>[] = [
        {
            accessorKey: 'id',
            header: 'Return ID',
            cell: ({ row }) => (
                <span className="font-medium">#{row.original.id}</span>
            ),
        },
        {
            accessorKey: 'order.order_number',
            header: 'Order',
            cell: ({ row }) => (
                <Link
                    href={route('orders.show', { order: row.original.order_id })}
                    className="font-medium text-primary hover:underline"
                >
                    {row.original.order?.order_number ?? row.original.order_id}
                </Link>
            ),
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const statusType = statusToBadgeType[row.original.status] ?? 'pending';
                const label = row.original.status.replace(/_/g, ' ');
                return <StatusBadge status={statusType} label={label} />;
            },
        },
        {
            accessorKey: 'reason',
            header: 'Reason',
            cell: ({ row }) => (
                <span className="text-sm max-w-[200px] truncate" title={row.original.reason}>
                    {row.original.reason}
                </span>
            ),
        },
        {
            accessorKey: 'total_refund_amount',
            header: 'Refund Amount',
            cell: ({ row }) => (
                <span className="text-sm font-medium">
                    {Number(row.original.total_refund_amount / 100).toFixed(2)}
                </span>
            ),
        },
        {
            accessorKey: 'created_at',
            header: 'Created',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {new Date(row.original.created_at).toLocaleDateString()}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <Button variant="ghost" size="sm" asChild>
                    <Link href={route('orders.returns.show', { return: row.original.id })}>
                        <Eye className="size-4" />
                    </Link>
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Returns" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader
                    title="Returns"
                    description="Manage order returns and refunds"
                />

                <DataTable
                    columns={columns}
                    data={returns}
                    emptyTitle="No returns found"
                    emptyDescription="Returns will appear here when customers request them."
                    enableStickyHeader
                    getRowId={(row) => String(row.id)}
                />
            </div>
        </AppLayout>
    );
}