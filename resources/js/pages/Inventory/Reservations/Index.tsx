import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type Reservation = {
    id: number;
    product_id: string;
    warehouse_id: number;
    quantity: number;
    status: string;
    reference: string;
    reference_type: string;
    expires_at: string | null;
    consumed_at: string | null;
    created_at: string;
    product: { id: string; name: string; sku: string } | null;
    warehouse: { id: number; name: string } | null;
    variant: { id: string; name: string } | null;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type PageProps = {
    reservations: {
        data: Reservation[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
    filters: { status?: string };
};

export default function ReservationIndex() {
    const { reservations, filters } = usePage<PageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Reservations', href: '/inventory/reservations' },
    ];

    function cancelReservation(id: number) {
        if (confirm('Cancel this reservation?')) {
            router.post(`/inventory/reservations/${id}/cancel`, {}, { preserveScroll: true });
        }
    }

    function statusColor(status: string) {
        switch (status) {
            case 'active': return 'bg-blue-100 text-blue-700';
            case 'consumed': return 'bg-green-100 text-green-700';
            case 'expired': return 'bg-yellow-100 text-yellow-700';
            case 'cancelled': return 'bg-gray-100 text-gray-500';
            default: return 'bg-gray-100 text-gray-500';
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reservations" />

            <PageHeader title="Stock Reservations" description="Active and historical stock reservations" />

            <div className="mb-4 flex items-center gap-3">
                <select
                    value={filters.status ?? ''}
                    onChange={(e) => router.get('/inventory/reservations', { status: e.target.value }, { preserveState: true })}
                    className="rounded-lg border border-sidebar-border bg-white px-3 py-2 text-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="consumed">Consumed</option>
                    <option value="expired">Expired</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Variant</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Qty</th>
                            <th className="py-3 px-3 text-center text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Reference</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Expires</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sidebar-border">
                        {reservations.data.map((res) => (
                            <tr key={res.id} className="hover:bg-muted/30">
                                <td className="py-3 px-3 text-sm">{res.product?.name ?? res.product_id}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{res.variant?.name ?? '-'}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{res.warehouse?.name ?? '-'}</td>
                                <td className="py-3 px-3 text-sm text-right">{res.quantity}</td>
                                <td className="py-3 px-3 text-center">
                                    <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${statusColor(res.status)}`}>
                                        {res.status}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-sm font-mono text-muted-foreground">{res.reference}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{res.expires_at ?? '-'}</td>
                                <td className="py-3 px-3 text-right">
                                    {res.status === 'active' && (
                                        <button onClick={() => cancelReservation(res.id)} className="text-xs text-red-600 hover:underline">
                                            Cancel
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {reservations.data.length === 0 && (
                            <tr>
                                <td colSpan={8} className="py-8 text-center text-sm text-muted-foreground">No reservations found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination meta={reservations} />
        </AppLayout>
    );
}
