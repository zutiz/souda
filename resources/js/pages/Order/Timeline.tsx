import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Clock, Package } from 'lucide-react';
import type { Order } from '@/modules/order/types';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type TimelineEvent = {
    id: string;
    from_status: string | null;
    to_status: string;
    changed_by: string | null;
    reason: string | null;
    occurred_at: string;
    type: string;
};

type TimelinePageProps = {
    order: Order;
    events: TimelineEvent[];
};

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-indigo-100 text-indigo-800',
    shipped: 'bg-cyan-100 text-cyan-800',
    delivered: 'bg-green-100 text-green-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
    failed: 'bg-red-100 text-red-800',
};

export default function OrderTimeline() {
    const { order, events } = usePage<TimelinePageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: route('store.dashboard', { store: window.location.pathname.split('/')[1] }) },
        { label: 'Orders', href: route('orders.index') },
        { label: `#${order.order_number}`, href: route('orders.show', { order: order.order_id }) },
        { label: 'Timeline' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Order #${order.order_number} — Timeline`} />

            <div className="py-6">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center gap-4">
                        <Link
                            href={route('orders.show', { order: order.order_id })}
                            className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back to Order
                        </Link>
                    </div>

                    <h2 className="text-lg font-medium text-gray-900">Order Timeline</h2>
                    <p className="mt-1 text-sm text-gray-500">
                        History for #{order.order_number}
                    </p>

                    <div className="mt-6 space-y-4">
                        {events.length === 0 && (
                            <div className="rounded-lg border border-gray-200 bg-white p-12 text-center">
                                <Clock className="mx-auto h-12 w-12 text-gray-400" />
                                <p className="mt-2 text-sm text-gray-500">No timeline events yet.</p>
                            </div>
                        )}

                        {events.map((event) => (
                            <div
                                key={event.id}
                                className="relative border-l-4 border-l-gray-200 pl-6 pb-6"
                            >
                                <div className="absolute -left-3 top-0 flex h-6 w-6 items-center justify-center rounded-full bg-white">
                                    {event.type === 'placed' ? (
                                        <Package className="h-4 w-4 text-gray-500" />
                                    ) : (
                                        <Clock className="h-4 w-4 text-gray-500" />
                                    )}
                                </div>

                                <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                    <div className="flex items-center justify-between">
                                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${statusColors[event.to_status] || 'bg-gray-100 text-gray-800'}`}>
                                            {event.to_status.replace(/_/g, ' ')}
                                        </span>
                                        <time className="text-xs text-gray-400">
                                            {new Date(event.occurred_at).toLocaleString()}
                                        </time>
                                    </div>

                                    {event.reason && (
                                        <p className="mt-2 text-sm text-gray-600">{event.reason}</p>
                                    )}

                                    {event.changed_by && (
                                        <p className="mt-1 text-xs text-gray-400">By: {event.changed_by}</p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
