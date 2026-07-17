import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';

type Suggestion = {
    id: number;
    product_id: string;
    variant_id: string | null;
    warehouse_id: number;
    current_quantity: number;
    reserved_quantity: number;
    available_quantity: number;
    reorder_level: number;
    lead_time_days: number;
    safety_stock: number;
    sales_velocity: number;
    suggested_quantity: number;
    status: string;
    notes: string | null;
    order_reference: string | null;
    created_at: string;
    product: { id: string; name: string; sku: string; lead_time_days: number | null; safety_stock: number | null } | null;
    warehouse: { id: number; name: string } | null;
};

type Warehouse = { id: number; name: string };
type PaginatorLink = { url: string | null; label: string; active: boolean };

type SuggestionIndexPageProps = {
    suggestions: {
        data: Suggestion[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
    stats: {
        total_pending: number;
        total_ordered: number;
        total_products: number;
    };
    warehouses: Warehouse[];
};

export default function SuggestionIndex() {
    const { suggestions, stats, warehouses } = usePage<SuggestionIndexPageProps>().props;

    const [selected, setSelected] = useState<Suggestion | null>(null);
    const [action, setAction] = useState<'ordered' | 'dismissed' | null>(null);
    const [orderRef, setOrderRef] = useState('');
    const [notes, setNotes] = useState('');
    const [processing, setProcessing] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Suggestions', href: '/inventory/suggestions' },
    ];

    const openDialog = (suggestion: Suggestion, status: 'ordered' | 'dismissed') => {
        setSelected(suggestion);
        setAction(status);
        setOrderRef('');
        setNotes('');
    };

    const submitUpdate = () => {
        if (!selected) return;
        setProcessing(true);
        router.put(`/inventory/suggestions/${selected.id}`, {
            status: action,
            order_reference: orderRef,
            notes,
        }, {
            onFinish: () => {
                setProcessing(false);
                setSelected(null);
                setAction(null);
            },
        });
    };

    const statusColors: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-800',
        ordered: 'bg-blue-100 text-blue-800',
        dismissed: 'bg-muted text-muted-foreground',
        cancelled: 'bg-red-100 text-red-800',
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Purchase Suggestions" />

            <PageHeader title="Purchase Suggestions" description="Auto-generated reorder suggestions based on stock levels">
                <Button onClick={() => router.post('/inventory/suggestions/generate')}>
                    Regenerate Suggestions
                </Button>
            </PageHeader>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <StatCard label="Pending" value={String(stats.total_pending)} variant="warning" />
                <StatCard label="Ordered" value={String(stats.total_ordered)} variant="default" />
                <StatCard label="Products" value={String(stats.total_products)} variant="default" />
            </div>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">SKU</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Available</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Reorder Level</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Velocity</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Suggested</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {suggestions.data.map((s) => (
                            <tr key={s.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm font-medium">{s.product?.name ?? s.product_id}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{s.product?.sku ?? '—'}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{s.warehouse?.name ?? '—'}</td>
                                <td className="py-3 px-3 text-right text-sm">{s.available_quantity}</td>
                                <td className="py-3 px-3 text-right text-sm text-muted-foreground">{s.reorder_level}</td>
                                <td className="py-3 px-3 text-right text-sm text-muted-foreground">{s.sales_velocity}/day</td>
                                <td className="py-3 px-3 text-right text-sm font-medium">{s.suggested_quantity}</td>
                                <td className="py-3 px-3 text-sm">
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[s.status] ?? 'bg-muted'}`}>
                                        {s.status}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-right text-sm space-x-2">
                                    {s.status === 'pending' && (
                                        <>
                                            <Button variant="outline" size="sm" onClick={() => openDialog(s, 'ordered')}>
                                                Ordered
                                            </Button>
                                            <Button variant="ghost" size="sm" onClick={() => openDialog(s, 'dismissed')}>
                                                Dismiss
                                            </Button>
                                        </>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {suggestions.data.length === 0 && (
                            <tr>
                                <td colSpan={9} className="py-12 text-center text-sm text-muted-foreground">
                                    No purchase suggestions. Click &quot;Regenerate Suggestions&quot; to analyze stock levels.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={suggestions.links}
                currentPage={suggestions.current_page}
                lastPage={suggestions.last_page}
                perPage={suggestions.per_page}
                total={suggestions.total}
            />

            <Dialog open={selected !== null} onOpenChange={() => setSelected(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {action === 'ordered' ? 'Mark as Ordered' : 'Dismiss Suggestion'}
                        </DialogTitle>
                    </DialogHeader>

                    <div className="space-y-4 py-2">
                        {selected && (
                            <div className="text-sm space-y-1">
                                <p><strong>Product:</strong> {selected.product?.name ?? selected.product_id}</p>
                                <p><strong>Suggested Qty:</strong> {selected.suggested_quantity}</p>
                            </div>
                        )}

                        {action === 'ordered' && (
                            <div className="space-y-2">
                                <Label htmlFor="order_ref">Order Reference (optional)</Label>
                                <Input id="order_ref" value={orderRef} onChange={(e) => setOrderRef(e.target.value)} placeholder="PO-001" />
                            </div>
                        )}

                        {action === 'dismissed' && (
                            <div className="space-y-2">
                                <Label htmlFor="notes">Reason for dismissal</Label>
                                <Textarea id="notes" value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="Not needed at this time..." />
                            </div>
                        )}
                    </div>

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setSelected(null)}>Cancel</Button>
                        <Button onClick={submitUpdate} disabled={processing}>
                            {processing ? 'Saving...' : 'Confirm'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

function StatCard({ label, value, variant = 'default' }: { label: string; value: string; variant?: 'default' | 'warning' | 'danger' }) {
    const colors = {
        default: 'bg-card text-card-foreground',
        warning: 'bg-amber-50 border-amber-200 text-amber-800',
        danger: 'bg-red-50 border-red-200 text-red-800',
    };

    return (
        <div className={`rounded-lg border p-4 ${colors[variant]}`}>
            <div className="text-xs font-medium uppercase tracking-wider opacity-70">{label}</div>
            <div className="mt-1 text-2xl font-bold">{value}</div>
        </div>
    );
}
