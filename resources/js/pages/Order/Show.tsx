import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Truck, Package, Clock, MapPin, CreditCard } from 'lucide-react';
import { useOrder } from '@/modules/order/hooks/use-order';
import type { Order } from '@/modules/order/types';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
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

export default function OrderShow() {
    const { order } = useOrder();

    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: route('store.dashboard', { store: window.location.pathname.split('/')[1] }) },
        { label: 'Orders', href: route('orders.index') },
        { label: `#${order.order_number}`, href: route('orders.show', { order: order.order_id }) },
    ];

    const handleStatusChange = (status: string) => {
        router.put(route('orders.update-status', { order: order.order_id }), { status }, {
            preserveScroll: true,
            onSuccess: () => router.reload(),
        });
    };

    const handleCancel = () => {
        const reason = window.prompt('Cancellation reason:');
        if (reason) {
            router.post(route('orders.cancel', { order: order.order_id }), { reason }, {
                preserveScroll: true,
                onSuccess: () => router.reload(),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Order #${order.order_number}`} />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-4 mb-6">
                        <Link
                            href={route('orders.index')}
                            className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back
                        </Link>
                        <span className={`inline-flex rounded-full px-3 py-1 text-sm font-medium ${statusColors[order.status] || 'bg-gray-100 text-gray-800'}`}>
                            {order.status.replace(/_/g, ' ')}
                        </span>
                    </div>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* Order Summary */}
                        <div className="lg:col-span-2 space-y-6">
                            <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h2 className="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                                    <Package className="h-5 w-5 text-gray-400" />
                                    Order Items
                                </h2>
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr className="text-left text-xs font-medium uppercase text-gray-500">
                                            <th className="pb-2">Item</th>
                                            <th className="pb-2">SKU</th>
                                            <th className="pb-2 text-right">Qty</th>
                                            <th className="pb-2 text-right">Price</th>
                                            <th className="pb-2 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {order.line_items.map((item, i) => (
                                            <tr key={i}>
                                                <td className="py-3 text-sm text-gray-900">{item.name}</td>
                                                <td className="py-3 text-sm text-gray-500">{item.sku || '-'}</td>
                                                <td className="py-3 text-sm text-right text-gray-700">{item.quantity}</td>
                                                <td className="py-3 text-sm text-right text-gray-700">{Number(item.unit_price / 100).toFixed(2)}</td>
                                                <td className="py-3 text-sm text-right text-gray-900 font-medium">{Number(item.total_price / 100).toFixed(2)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot className="border-t border-gray-200">
                                        <tr>
                                            <td colSpan={4} className="pt-3 text-sm text-right text-gray-500">Subtotal</td>
                                            <td className="pt-3 text-sm text-right font-medium">{Number(order.subtotal / 100).toFixed(2)}</td>
                                        </tr>
                                        {order.shipping_total > 0 && (
                                            <tr>
                                                <td colSpan={4} className="pt-1 text-sm text-right text-gray-500">Shipping</td>
                                                <td className="pt-1 text-sm text-right">{Number(order.shipping_total / 100).toFixed(2)}</td>
                                            </tr>
                                        )}
                                        {order.tax_total > 0 && (
                                            <tr>
                                                <td colSpan={4} className="pt-1 text-sm text-right text-gray-500">Tax</td>
                                                <td className="pt-1 text-sm text-right">{Number(order.tax_total / 100).toFixed(2)}</td>
                                            </tr>
                                        )}
                                        {order.discount_total > 0 && (
                                            <tr>
                                                <td colSpan={4} className="pt-1 text-sm text-right text-green-600">Discount</td>
                                                <td className="pt-1 text-sm text-right text-green-600">-{Number(order.discount_total / 100).toFixed(2)}</td>
                                            </tr>
                                        )}
                                        <tr className="text-base font-semibold">
                                            <td colSpan={4} className="pt-2 text-sm text-right text-gray-900">Total</td>
                                            <td className="pt-2 text-sm text-right text-gray-900">{order.currency} {Number(order.grand_total / 100).toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Status Actions */}
                            <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="text-sm font-medium text-gray-900 mb-3 flex items-center gap-2">
                                    <Clock className="h-4 w-4 text-gray-400" />
                                    Update Status
                                </h3>
                                <div className="space-y-2">
                                    {['confirmed', 'processing', 'completed'].map((status) => (
                                        <button
                                            key={status}
                                            onClick={() => handleStatusChange(status)}
                                            className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                        >
                                            Mark as {status.replace(/_/g, ' ')}
                                        </button>
                                    ))}
                                    {order.status !== 'cancelled' && (
                                        <button
                                            onClick={handleCancel}
                                            className="w-full rounded-md border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50"
                                        >
                                            Cancel Order
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Customer */}
                            <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="text-sm font-medium text-gray-900 mb-3">Customer</h3>
                                <div className="space-y-2 text-sm text-gray-600">
                                    {order.customer_name && <p>{order.customer_name}</p>}
                                    {order.customer_phone && <p>{order.customer_phone}</p>}
                                    {order.customer_email && <p className="text-indigo-600">{order.customer_email}</p>}
                                </div>
                            </div>

                            {/* Shipping Address */}
                            {order.shipping_address && (
                                <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                    <h3 className="text-sm font-medium text-gray-900 mb-3 flex items-center gap-2">
                                        <MapPin className="h-4 w-4 text-gray-400" />
                                        Shipping Address
                                    </h3>
                                    <div className="space-y-1 text-sm text-gray-600">
                                        <p>{order.shipping_address.name}</p>
                                        <p>{order.shipping_address.phone}</p>
                                        <p>{order.shipping_address.address_line_1}</p>
                                        {order.shipping_address.address_line_2 && <p>{order.shipping_address.address_line_2}</p>}
                                        <p>
                                            {[order.shipping_address.city, order.shipping_address.state, order.shipping_address.postal_code].filter(Boolean).join(', ')}
                                        </p>
                                    </div>
                                </div>
                            )}

                            {/* Payment */}
                            <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="text-sm font-medium text-gray-900 mb-3 flex items-center gap-2">
                                    <CreditCard className="h-4 w-4 text-gray-400" />
                                    Payment
                                </h3>
                                <div className="space-y-2 text-sm text-gray-600">
                                    <div className="flex justify-between">
                                        <span>Status</span>
                                        <span className="font-medium capitalize">{order.payment_status}</span>
                                    </div>
                                    {order.payment_method && (
                                        <div className="flex justify-between">
                                            <span>Method</span>
                                            <span className="capitalize">{order.payment_method}</span>
                                        </div>
                                    )}
                                    <div className="flex justify-between">
                                        <span>Paid</span>
                                        <span className="font-medium">{Number(order.paid_total / 100).toFixed(2)}</span>
                                    </div>
                                    {order.due_total > 0 && (
                                        <div className="flex justify-between text-red-600">
                                            <span>Due</span>
                                            <span className="font-medium">{Number(order.due_total / 100).toFixed(2)}</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
