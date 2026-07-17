import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import type { BreadcrumbItem } from '@/types';

type SerialNumber = {
    id: number;
    serial_number: string;
    status: string;
    sold_at: string | null;
    warranty_expires_at: string | null;
};

type Batch = {
    id: number;
    batch_number: string;
    supplier_batch: string | null;
    status: string;
    initial_quantity: number;
    remaining_quantity: number;
    unit_cost: number;
    manufacturing_date: string | null;
    expiry_date: string | null;
    best_before: string | null;
    created_at: string;
    product: { id: string; name: string; sku: string } | null;
    warehouse: { id: number; name: string } | null;
    serial_numbers: SerialNumber[];
};

type PageProps = { batch: Batch };

export default function BatchShow() {
    const { batch } = usePage<PageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Batches', href: '/inventory/batches' },
        { title: batch.batch_number, href: `/inventory/batches/${batch.id}` },
    ];

    function quarantine() {
        if (confirm('Quarantine this batch?')) {
            router.post(`/inventory/batches/${batch.id}/quarantine`, {}, { preserveScroll: true });
        }
    }

    function statusColor(status: string) {
        switch (status) {
            case 'active': return 'bg-green-100 text-green-700';
            case 'depleted': return 'bg-gray-100 text-gray-600';
            case 'quarantined': return 'bg-red-100 text-red-700';
            case 'expired': return 'bg-yellow-100 text-yellow-700';
            default: return 'bg-gray-100 text-gray-500';
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Batch ${batch.batch_number}`} />

            <div className="mb-4 flex items-center justify-between">
                <PageHeader title={`Batch ${batch.batch_number}`} description={batch.supplier_batch ?? 'No supplier batch reference'} />
                {batch.status === 'active' && (
                    <button onClick={quarantine} className="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                        Quarantine
                    </button>
                )}
            </div>

            <div className="grid grid-cols-3 gap-4 mb-6">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Status</span>
                    <div className="mt-1">
                        <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${statusColor(batch.status)}`}>
                            {batch.status}
                        </span>
                    </div>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Product</span>
                    <p className="mt-1 text-sm font-medium">{batch.product?.name ?? batch.product_id}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Warehouse</span>
                    <p className="mt-1 text-sm font-medium">{batch.warehouse?.name ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Initial Qty</span>
                    <p className="mt-1 text-sm font-medium">{batch.initial_quantity}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Remaining Qty</span>
                    <p className="mt-1 text-sm font-medium">{batch.remaining_quantity}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Unit Cost</span>
                    <p className="mt-1 text-sm font-medium">${(batch.unit_cost / 100).toFixed(2)}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Manufacturing Date</span>
                    <p className="mt-1 text-sm font-medium">{batch.manufacturing_date ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Expiry Date</span>
                    <p className="mt-1 text-sm font-medium">{batch.expiry_date ?? '-'}</p>
                </div>
                <div className="rounded-lg border border-sidebar-border p-4">
                    <span className="text-xs text-muted-foreground">Best Before</span>
                    <p className="mt-1 text-sm font-medium">{batch.best_before ?? '-'}</p>
                </div>
            </div>

            {batch.serial_numbers.length > 0 && (
                <div>
                    <h3 className="mb-3 text-sm font-medium">Serial Numbers</h3>
                    <div className="rounded-lg border border-sidebar-border">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-sidebar-border bg-muted/50">
                                    <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Serial #</th>
                                    <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                                    <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Sold At</th>
                                    <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warranty</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-sidebar-border">
                                {batch.serial_numbers.map((sn) => (
                                    <tr key={sn.id} className="hover:bg-muted/30">
                                        <td className="py-3 px-3 text-sm font-mono">{sn.serial_number}</td>
                                        <td className="py-3 px-3 text-sm">{sn.status}</td>
                                        <td className="py-3 px-3 text-sm text-muted-foreground">{sn.sold_at ?? '-'}</td>
                                        <td className="py-3 px-3 text-sm text-muted-foreground">{sn.warranty_expires_at ?? '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
