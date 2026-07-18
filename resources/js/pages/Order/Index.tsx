import { Head, Link, router } from '@inertiajs/react';
import { Plus, Package, Search, Eye, XCircle } from 'lucide-react';
import { useState, useCallback } from 'react';
import { useOrders } from '@/modules/order/hooks/use-orders';
import type { Order, OrderFilters } from '@/modules/order/types';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { DataTable } from '@/modules/shared/components/data-table';
import type { BreadcrumbItem } from '@/types';

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-indigo-100 text-indigo-800',
    ready_for_pickup: 'bg-purple-100 text-purple-800',
    out_for_delivery: 'bg-orange-100 text-orange-800',
    shipped: 'bg-cyan-100 text-cyan-800',
    delivered: 'bg-green-100 text-green-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
    on_hold: 'bg-slate-100 text-slate-800',
    failed: 'bg-red-100 text-red-800',
};

type OrderIndexPageProps = {
    orders: Order[];
    filters: OrderFilters;
};

export default function OrderIndex() {
    const { orders, filters, navigate, clearFilters } = useOrders();
    const [search, setSearch] = useState(filters.status ?? '');

    const handleSearch = useCallback((value: string) => {
        setSearch(value);
        navigate({ status: value || undefined, page: 1 });
    }, [navigate]);

    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: route('store.dashboard', { store: window.location.pathname.split('/')[1] }) },
        { label: 'Orders', href: route('orders.index') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Orders" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <PageHeader
                        title="Orders"
                        description="Manage customer orders and shipments"
                        actions={
                            <Link
                                href={route('orders.create')}
                                className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                <Plus className="h-4 w-4" />
                                New Order
                            </Link>
                        }
                    />

                    <div className="mt-6 flex items-center gap-4">
                        <div className="relative flex-1 max-w-sm">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <select
                                value={search}
                                onChange={(e) => handleSearch(e.target.value)}
                                className="w-full rounded-md border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All statuses</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="processing">Processing</option>
                                <option value="ready_for_pickup">Ready for Pickup</option>
                                <option value="out_for_delivery">Out for Delivery</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="on_hold">On Hold</option>
                            </select>
                        </div>
                        {filters.status && (
                            <button
                                onClick={clearFilters}
                                className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700"
                            >
                                <XCircle className="h-4 w-4" />
                                Clear
                            </button>
                        )}
                    </div>

                    <div className="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Order</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {orders.length === 0 && (
                                    <tr>
                                        <td colSpan={7} className="px-6 py-12 text-center text-sm text-gray-500">
                                            <Package className="mx-auto h-12 w-12 text-gray-400" />
                                            <p className="mt-2 font-medium text-gray-900">No orders found</p>
                                            <p className="mt-1">Create your first order to get started.</p>
                                        </td>
                                    </tr>
                                )}
                                {orders.map((order) => (
                                    <tr key={order.order_id} className="hover:bg-gray-50">
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-indigo-600">
                                            <Link href={route('orders.show', { order: order.order_id })}>
                                                {order.order_number}
                                            </Link>
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {order.customer_name || order.customer_phone || '-'}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4">
                                            <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${statusColors[order.status] || 'bg-gray-100 text-gray-800'}`}>
                                                {order.status.replace(/_/g, ' ')}
                                            </span>
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {order.currency} {Number(order.grand_total / 100).toFixed(2)}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500 capitalize">
                                            {order.order_type}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {new Date(order.placed_at).toLocaleDateString()}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <Link
                                                href={route('orders.show', { order: order.order_id })}
                                                className="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800"
                                            >
                                                <Eye className="h-4 w-4" />
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
