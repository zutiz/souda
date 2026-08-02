import { Head, Link } from '@inertiajs/react';
import { Eye } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { DataTable } from '@/modules/shared/components/data-table';
import { StatusBadge } from '@/modules/shared/components/status-badge';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/react-table';

type Refund = {
    id: string;
    order_number: string;
    status: string;
    refund_total: number;
    refund_at: string;
    customer_name: string;
};

type RefundsPageProps = {
    refunds: Refund[];
};

export default function RefundsIndex({ refunds }: RefundsPageProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Orders', href: '/orders' },
        { label: 'Refunds', href: '/orders/refunds' },
    ];

    const columns: ColumnDef<Refund>[] = [
        {
            accessorKey: 'order_number',
            header: 'Order',
            cell: ({ row }) => (
                <Link
                    href={route('orders.show', { order: row.original.id })}
                    className="font-medium text-primary hover:underline"
                >
                    {row.original.order_number}
                </Link>
            ),
        },
        {
            accessorKey: 'customer_name',
            header: 'Customer',
            cell: ({ row }) => (
                <span className="text-sm">{row.original.customer_name || '-'}</span>
            ),
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => (
                <StatusBadge status="cancelled" label="Refunded" />
            ),
        },
        {
            accessorKey: 'refund_total',
            header: 'Refund Amount',
            cell: ({ row }) => (
                <span className="text-sm font-medium">
                    {Number(row.original.refund_total / 100).toFixed(2)}
                </span>
            ),
        },
        {
            accessorKey: 'refund_at',
            header: 'Refunded At',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.refund_at ? new Date(row.original.refund_at).toLocaleDateString() : '-'}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <Button variant="ghost" size="sm" asChild>
                    <Link href={route('orders.show', { order: row.original.id })}>
                        <Eye className="size-4" />
                    </Link>
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Refunds" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader
                    title="Refunds"
                    description="View all refunded orders"
                />

                <DataTable
                    columns={columns}
                    data={refunds}
                    emptyTitle="No refunds found"
                    emptyDescription="Refunded orders will appear here."
                    enableStickyHeader
                    getRowId={(row) => row.id}
                />
            </div>
        </AppLayout>
    );
}