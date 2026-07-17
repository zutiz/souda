import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import type { BreadcrumbItem } from '@/types';

type Serial = {
    id: number;
    serial_number: string;
    status: string;
    order_reference: string | null;
    sold_at: string | null;
    warranty_expires_at: string | null;
    notes: string | null;
    created_at: string;
    product: { id: string; name: string; sku: string } | null;
    warehouse: { id: number; name: string } | null;
    batch: { id: number; batch_number: string } | null;
};

type PageProps = { serial: Serial };

export default function SerialShow() {
    const { serial } = usePage<PageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Serial Numbers', href: '/inventory/serials' },
        { title: serial.serial_number, href: `/inventory/serials/${serial.id}` },
    ];

    function markSold() {
        const ref = prompt('Order reference:');
        if (ref) {
            router.post(`/inventory/serials/${serial.id}/sold`, { order_reference: ref }, { preserveScroll: true });
        }
    }

    function markReturned() {
        if (confirm('Mark this serial as returned?')) {
            router.post(`/inventory/serials/${serial.id}/return`, {}, { preserveScroll: true });
        }
    }

    function statusColor(status: string) {
        switch (status) {
            case 'available': return 'bg-green-100 text-green-700';
            case 'reserved': return 'bg-blue-100 text-blue-700';
            case 'sold': return 'bg-gray-100 text-gray-600';
            case 'returned': return 'bg-purple-100 text-purple-700';
            case 'quarantined': return 'bg-red-100 text-red-700';
            case 'disposed': return 'bg-gray-100 text-gray-400';
            default: return 'bg-gray-100 text-gray-500';
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Serial ${serial.serial_number}`} />

            <div className="mb-4 flex items-center justify-between">
                <PageHeader title={`Serial ${serial.serial_number}`} description={serial.product?.name ?? '-'} />
                <div className="flex gap-2">
                    {serial.status === 'available' && (
                        <button onClick={markSold} className="rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-primary/90">
                            Mark Sold
                        </button>
                    )}
                    {serial.status === 'sold' && (
                        <button onClick={markReturned} className="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-purple-700">
                            Mark Returned
                        </button>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-3 gap-4">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Status</span>
                    <div className="mt-1">
                        <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${statusColor(serial.status)}`}>
                            {serial.status}
                        </span>
                    </div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Product</span>
                    <p className="mt-1 text-sm font-medium">{serial.product?.name ?? serial.product_id}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">SKU</span>
                    <p className="mt-1 text-sm font-medium">{serial.product?.sku ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Warehouse</span>
                    <p className="mt-1 text-sm font-medium">{serial.warehouse?.name ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Batch</span>
                    <p className="mt-1 text-sm font-medium">{serial.batch?.batch_number ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Order Reference</span>
                    <p className="mt-1 text-sm font-medium">{serial.order_reference ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Sold At</span>
                    <p className="mt-1 text-sm font-medium">{serial.sold_at ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Warranty Expires</span>
                    <p className="mt-1 text-sm font-medium">{serial.warranty_expires_at ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Registered</span>
                    <p className="mt-1 text-sm font-medium">{serial.created_at ? new Date(serial.created_at).toLocaleDateString() : '-'}</p>
                </div>
            </div>

            {serial.notes && (
                <div className="mt-6 rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Notes</span>
                    <p className="mt-1 text-sm">{serial.notes}</p>
                </div>
            )}
        </AppLayout>
    );
}
