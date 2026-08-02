import { Head, Link } from '@inertiajs/react';
import { Eye, Plus, Truck } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { DataTable } from '@/modules/shared/components/data-table';
import { StatusBadge } from '@/modules/shared/components/status-badge';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/react-table';

type Shipment = {
    id: string;
    shipment_number: string;
    order_id: string;
    courier: string | null;
    tracking_number: string | null;
    status: string;
    created_at: string;
    order?: {
        order_number: string;
    };
};

type ShipmentsPageProps = {
    shipments: Shipment[];
    order?: Record<string, unknown> | null;
};

const statusStyles: Record<string, 'pending' | 'processing' | 'completed' | 'cancelled' | 'shipped'> = {
    pending: 'pending',
    processing: 'processing',
    shipped: 'shipped',
    delivered: 'completed',
    cancelled: 'cancelled',
};

export default function ShipmentsIndex({ shipments, order }: ShipmentsPageProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Orders', href: '/orders' },
        { label: 'Shipments', href: '/orders/shipments' },
    ];

    const columns: ColumnDef<Shipment>[] = [
        {
            accessorKey: 'shipment_number',
            header: 'Shipment',
            cell: ({ row }) => (
                <span className="font-medium">{row.original.shipment_number}</span>
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
            accessorKey: 'courier',
            header: 'Courier',
        },
        {
            accessorKey: 'tracking_number',
            header: 'Tracking',
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const type = statusStyles[row.original.status] ?? 'pending';
                return <StatusBadge status={type} label={row.original.status} />;
            },
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
                    <Link href={route('orders.order-shipments.index', { order: row.original.order_id })}>
                        <Eye className="size-4" />
                    </Link>
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Shipments" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader
                    title="Shipments"
                    description="Manage order shipments and tracking"
                />

                <DataTable
                    columns={columns}
                    data={shipments}
                    emptyTitle="No shipments found"
                    emptyDescription="Shipments will appear here when orders are fulfilled."
                    enableStickyHeader
                    getRowId={(row) => row.id}
                />
            </div>
        </AppLayout>
    );
}
