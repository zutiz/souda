import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type TransferItem = {
    id: number;
    product_id: string;
    variant_id: string | null;
    quantity: number;
    unit_cost: number;
};

type Transfer = {
    id: number;
    reference: string;
    status: string;
    description: string | null;
    sent_at: string | null;
    received_at: string | null;
    cancelled_at: string | null;
    from_warehouse: { id: number; name: string };
    to_warehouse: { id: number; name: string };
    items: TransferItem[];
};

type ShowTransferPageProps = {
    transfer: Transfer;
};

const statusLabels: Record<string, string> = {
    draft: 'Draft',
    sent: 'In Transit',
    received: 'Completed',
    cancelled: 'Cancelled',
};

const statusColors: Record<string, string> = {
    draft: 'bg-muted text-muted-foreground',
    sent: 'bg-blue-100 text-blue-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

export default function ShowTransfer() {
    const { transfer } = usePage<ShowTransferPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Transfers', href: '/inventory/transfers' },
        { title: transfer.reference, href: `/inventory/transfers/${transfer.id}` },
    ];

    const canSend = transfer.status === 'draft';
    const canReceive = transfer.status === 'sent';
    const canCancel = transfer.status === 'draft' || transfer.status === 'sent';

    const handleSend = () => router.post(`/inventory/transfers/${transfer.id}/send`);
    const handleReceive = () => router.post(`/inventory/transfers/${transfer.id}/receive`);
    const handleCancel = () => router.post(`/inventory/transfers/${transfer.id}/cancel`);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Transfer ${transfer.reference}`} />

            <PageHeader title={`Transfer ${transfer.reference}`} description={transfer.description ?? ''}>
                {canSend && <Button onClick={handleSend}>Send Transfer</Button>}
                {canReceive && <Button onClick={handleReceive}>Receive Transfer</Button>}
                {canCancel && <Button variant="outline" onClick={handleCancel}>Cancel Transfer</Button>}
            </PageHeader>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    <div className="rounded-lg border border-sidebar-border">
                        <div className="border-b border-sidebar-border px-4 py-3 font-semibold text-sm">Items</div>
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-sidebar-border bg-muted/50">
                                    <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                                    <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transfer.items.map((item) => (
                                    <tr key={item.id} className="border-b border-sidebar-border">
                                        <td className="py-3 px-3 text-sm">{item.product_id}</td>
                                        <td className="py-3 px-3 text-right text-sm font-medium">{item.quantity}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="space-y-4">
                    <div className="rounded-lg border border-sidebar-border p-4 space-y-3">
                        <h3 className="font-semibold text-sm">Details</h3>
                        <DetailRow label="Status" value={statusLabels[transfer.status] ?? transfer.status} />
                        <DetailRow label="From" value={transfer.from_warehouse.name} />
                        <DetailRow label="To" value={transfer.to_warehouse.name} />
                        <DetailRow label="Created" value={new Date(transfer.created_at ?? '').toLocaleDateString()} />
                        {transfer.sent_at && <DetailRow label="Sent" value={new Date(transfer.sent_at).toLocaleDateString()} />}
                        {transfer.received_at && <DetailRow label="Received" value={new Date(transfer.received_at).toLocaleDateString()} />}
                    </div>

                    <StatusBadge status={transfer.status} />
                </div>
            </div>
        </AppLayout>
    );
}

function DetailRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">{label}</span>
            <span className="font-medium">{value}</span>
        </div>
    );
}

function StatusBadge({ status }: { status: string }) {
    return (
        <div className={`rounded-lg border p-4 ${statusColors[status] ?? ''}`}>
            <div className="text-xs font-medium uppercase tracking-wider">Status</div>
            <div className="mt-1 text-lg font-bold">{statusLabels[status] ?? status}</div>
        </div>
    );
}
