import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

type CountItem = {
    id: number;
    product_id: string;
    variant_id: string | null;
    bin_id: number | null;
    expected_quantity: number;
    physical_quantity: number | null;
    discrepancy: number | null;
    unit_cost: number;
    status: string;
    notes: string | null;
    product: { id: string; name: string; sku: string } | null;
    bin: { id: number; code: string } | null;
};

type Count = {
    id: number;
    reference: string;
    type: string;
    status: string;
    notes: string | null;
    counted_at: string | null;
    verified_at: string | null;
    adjusted_at: string | null;
    completed_at: string | null;
    created_at: string | null;
    warehouse: { id: number; name: string };
    items: CountItem[];
    counted_by_user: { id: number; name: string } | null;
    verified_by_user: { id: number; name: string } | null;
};

type ShowCountPageProps = {
    count: Count;
};

const statusLabels: Record<string, string> = {
    draft: 'Draft',
    in_progress: 'In Progress',
    verified: 'Verified',
    adjusted: 'Adjusted',
    completed: 'Completed',
    cancelled: 'Cancelled',
};

const statusColors: Record<string, string> = {
    draft: 'bg-muted text-muted-foreground',
    in_progress: 'bg-blue-100 text-blue-800',
    verified: 'bg-indigo-100 text-indigo-800',
    adjusted: 'bg-amber-100 text-amber-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

const itemStatusColors: Record<string, string> = {
    pending: 'bg-muted text-muted-foreground',
    counted: 'bg-blue-100 text-blue-800',
    verified: 'bg-green-100 text-green-800',
};

export default function ShowCount() {
    const { count } = usePage<ShowCountPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Counts', href: '/inventory/counts' },
        { title: count.reference, href: `/inventory/counts/${count.id}` },
    ];

    const [countValues, setCountValues] = useState<Record<number, string>>(
        Object.fromEntries(
            count.items.map((item) => [item.id, String(item.physical_quantity ?? '')])
        )
    );

    const [countNotes, setCountNotes] = useState<Record<number, string>>(
        Object.fromEntries(
            count.items.map((item) => [item.id, item.notes ?? ''])
        )
    );

    const isInProgress = count.status === 'in_progress';
    const isDraft = count.status === 'draft';
    const isVerified = count.status === 'verified';
    const isAdjusted = count.status === 'adjusted';
    const canRecord = isDraft || isInProgress;
    const canVerify = count.status === 'in_progress';

    const handleRecordCounts = () => {
        const items = Object.entries(countValues).map(([id, qty]) => ({
            id: Number(id),
            physical_quantity: Number(qty) || 0,
            notes: countNotes[Number(id)] || null,
        }));

        router.post(`/inventory/counts/${count.id}/record`, { items });
    };

    const handleVerify = () => router.post(`/inventory/counts/${count.id}/verify`);
    const handleApply = () => router.post(`/inventory/counts/${count.id}/apply`);
    const handleComplete = () => router.post(`/inventory/counts/${count.id}/complete`);
    const handleCancel = () => router.post(`/inventory/counts/${count.id}/cancel`);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Count ${count.reference}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title={`Count ${count.reference}`} description={`${count.type} count — ${count.warehouse.name}`}>
                {canVerify && <Button onClick={handleVerify}>Verify Count</Button>}
                {isVerified && <Button onClick={handleApply}>Apply Adjustments</Button>}
                {isAdjusted && <Button onClick={handleComplete}>Complete Count</Button>}
                {canRecord && <Button variant="outline" onClick={handleCancel}>Cancel Count</Button>}
            </PageHeader>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    <div className="rounded-lg border border-sidebar-border">
                        <div className="border-b border-sidebar-border px-4 py-3 flex items-center justify-between">
                            <span className="font-semibold text-sm">Items</span>
                            {canRecord && (
                                <Button size="sm" onClick={handleRecordCounts}>
                                    Save Counts
                                </Button>
                            )}
                        </div>
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-sidebar-border bg-muted/50">
                                    <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                                    <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Expected</th>
                                    <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Physical</th>
                                    <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Diff</th>
                                    <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {count.items.map((item) => (
                                    <tr key={item.id} className="border-b border-sidebar-border">
                                        <td className="py-3 px-3 text-sm">
                                            {item.product?.name ?? item.product_id}
                                            {item.product && <span className="ml-1 text-muted-foreground">({item.product.sku})</span>}
                                        </td>
                                        <td className="py-3 px-3 text-right text-sm">{item.expected_quantity}</td>
                                        <td className="py-3 px-3 text-right text-sm">
                                            {canRecord ? (
                                                <Input
                                                    type="number"
                                                    min={0}
                                                    className="w-24 ml-auto h-8 text-sm"
                                                    value={countValues[item.id] ?? ''}
                                                    onChange={(e) => setCountValues({ ...countValues, [item.id]: e.target.value })}
                                                />
                                            ) : (
                                                <span className={item.discrepancy !== null && item.discrepancy !== 0 ? 'font-bold text-amber-600' : ''}>
                                                    {item.physical_quantity ?? '-'}
                                                </span>
                                            )}
                                        </td>
                                        <td className="py-3 px-3 text-right text-sm font-medium">
                                            {item.discrepancy !== null ? (
                                                <span className={item.discrepancy > 0 ? 'text-green-600' : item.discrepancy < 0 ? 'text-red-600' : ''}>
                                                    {item.discrepancy > 0 ? '+' : ''}{item.discrepancy}
                                                </span>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </td>
                                        <td className="py-3 px-3 text-sm">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${itemStatusColors[item.status] ?? 'bg-muted'}`}>
                                                {item.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="space-y-4">
                    <div className="rounded-lg border border-sidebar-border p-4 space-y-3">
                        <h3 className="font-semibold text-sm">Details</h3>
                        <DetailRow label="Reference" value={count.reference} />
                        <DetailRow label="Type" value={count.type} />
                        <DetailRow label="Warehouse" value={count.warehouse.name} />
                        <DetailRow label="Items" value={String(count.items.length)} />
                        <DetailRow label="Created" value={count.created_at ? new Date(count.created_at).toLocaleDateString() : '-'} />
                        {count.counted_by_user && <DetailRow label="Counted By" value={count.counted_by_user.name} />}
                        {count.verified_by_user && <DetailRow label="Verified By" value={count.verified_by_user.name} />}
                        {count.counted_at && <DetailRow label="Counted At" value={new Date(count.counted_at).toLocaleDateString()} />}
                        {count.verified_at && <DetailRow label="Verified At" value={new Date(count.verified_at).toLocaleDateString()} />}
                        {count.adjusted_at && <DetailRow label="Adjusted At" value={new Date(count.adjusted_at).toLocaleDateString()} />}
                        {count.completed_at && <DetailRow label="Completed At" value={new Date(count.completed_at).toLocaleDateString()} />}
                    </div>

                    <StatusBadge status={count.status} />

                    {isVerified && (
                        <Button className="w-full" onClick={handleApply}>
                            Apply Adjustments
                        </Button>
                    )}
                    {isAdjusted && (
                        <Button className="w-full" onClick={handleComplete}>
                            Complete Count
                        </Button>
                    )}
                </div>
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
