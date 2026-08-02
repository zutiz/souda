import { Head, Link, router } from '@inertiajs/react';
import { Plus, Package, Search, Eye, XCircle } from 'lucide-react';
import { useState, useCallback } from 'react';
import { useOrders } from '@/modules/order/hooks/use-orders';
import type { Order, OrderFilters } from '@/modules/order/types';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { DataTable } from '@/modules/shared/components/data-table';
import { DataTableToolbar, ToolbarSection } from '@/modules/shared/components/data-table-toolbar';
import { DataTablePagination } from '@/modules/shared/components/data-table-pagination';
import { StatusBadge, orderStatusPresets } from '@/modules/shared/components/status-badge';
import { EmptyStates } from '@/modules/shared/components/empty-state';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/react-table';

// Map order status to StatusBadge types
const statusToBadgeType: Record<string, 'pending' | 'processing' | 'completed' | 'cancelled' | 'shipped' | 'delivered'> = {
    pending: 'pending',
    confirmed: 'processing',
    processing: 'processing',
    ready_for_pickup: 'pending',
    out_for_delivery: 'shipped',
    shipped: 'shipped',
    delivered: 'delivered',
    completed: 'completed',
    cancelled: 'cancelled',
    refunded: 'cancelled',
    on_hold: 'pending',
    failed: 'cancelled',
};

type OrderIndexPageProps = {
    orders: Order[];
    filters: OrderFilters;
    pagination?: {
        current_page: number;
        per_page: number;
        total: number;
    };
};

export default function OrderIndex() {
    const { orders, filters, navigate, clearFilters } = useOrders();
    const [search, setSearch] = useState(filters.status ?? 'all');

    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Orders', href: '/orders' },
    ];

    const handleSearch = useCallback((value: string) => {
        setSearch(value);
        navigate({ status: value === 'all' ? undefined : value, page: 1 });
    }, [navigate]);

    const handleClearFilters = useCallback(() => {
        setSearch('all');
        clearFilters();
    }, [clearFilters]);

    const columns: ColumnDef<Order>[] = [
        {
            accessorKey: 'order_number',
            header: 'Order',
            cell: ({ row }) => (
                <Link
                    href={route('orders.show', { order: row.original.order_id })}
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
                <span className="text-sm">
                    {row.original.customer_name || row.original.customer_phone || '-'}
                </span>
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
            accessorKey: 'grand_total',
            header: 'Total',
            cell: ({ row }) => (
                <span className="text-sm font-medium">
                    {row.original.currency} {Number(row.original.grand_total / 100).toFixed(2)}
                </span>
            ),
        },
        {
            accessorKey: 'order_type',
            header: 'Type',
            cell: ({ row }) => (
                <Badge variant="secondary" className="capitalize">
                    {row.original.order_type}
                </Badge>
            ),
        },
        {
            accessorKey: 'placed_at',
            header: 'Date',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {new Date(row.original.placed_at).toLocaleDateString()}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <Button variant="ghost" size="sm" asChild>
                    <Link href={route('orders.show', { order: row.original.order_id })}>
                        <Eye className="size-4" />
                    </Link>
                </Button>
            ),
        },
    ];

    const hasActiveFilters = !!filters.status;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Orders" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader
                    title="Orders"
                    description="Manage customer orders and shipments"
                    actions={
                        <Button asChild>
                            <Link href="/orders/create">
                                <Plus className="size-4" />
                                New Order
                            </Link>
                        </Button>
                    }
                />

                <DataTableToolbar
                    searchValue={search}
                    onSearchChange={handleSearch}
                    searchPlaceholder="Filter by status..."
                    showSearch={false}
                >
                    <ToolbarSection>
                        <Select
                            value={search}
                            onValueChange={handleSearch}
                        >
                            <SelectTrigger className="h-9 w-[180px]">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All statuses</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="confirmed">Confirmed</SelectItem>
                                <SelectItem value="processing">Processing</SelectItem>
                                <SelectItem value="ready_for_pickup">Ready for Pickup</SelectItem>
                                <SelectItem value="out_for_delivery">Out for Delivery</SelectItem>
                                <SelectItem value="shipped">Shipped</SelectItem>
                                <SelectItem value="delivered">Delivered</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                                <SelectItem value="on_hold">On Hold</SelectItem>
                            </SelectContent>
                        </Select>

                        {hasActiveFilters && (
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={handleClearFilters}
                                className="gap-1.5 text-muted-foreground"
                            >
                                <XCircle className="size-4" />
                                Clear
                            </Button>
                        )}
                    </ToolbarSection>
                </DataTableToolbar>

                <DataTable
                    columns={columns}
                    data={orders}
                    emptyTitle="No orders found"
                    emptyDescription="Create your first order to get started."
                    emptyAction={{
                        label: 'New Order',
                        onClick: () => router.visit('/orders/create'),
                    }}
                    enableStickyHeader
                    getRowId={(row) => row.order_id}
                />

                {orders.length > 0 && (
                    <DataTablePagination
                        pageIndex={(filters.page ?? 1) - 1}
                        pageSize={filters.per_page ?? 10}
                        total={filters.total ?? orders.length}
                        onPageChange={(page) => navigate({ page: page + 1 })}
                        onPageSizeChange={(size) => navigate({ per_page: size, page: 1 })}
                    />
                )}
            </div>
        </AppLayout>
    );
}
