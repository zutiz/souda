import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type Serial = {
    id: number;
    serial_number: string;
    status: string;
    order_reference: string | null;
    sold_at: string | null;
    warranty_expires_at: string | null;
    created_at: string;
    product: { id: string; name: string; sku: string } | null;
    warehouse: { id: number; name: string } | null;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type PageProps = {
    serials: {
        data: Serial[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
    filters: { status?: string; search?: string };
};

export default function SerialIndex() {
    const { serials, filters } = usePage<PageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Serial Numbers', href: '/inventory/serials' },
    ];

    function applyFilters() {
        router.get('/inventory/serials', { search, status: statusFilter }, { preserveState: true });
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
            <Head title="Serial Numbers" />

            <PageHeader title="Serial Numbers" description="Track individual units by serial number" />

            <div className="mb-4 flex items-center gap-3">
                <input
                    type="text"
                    placeholder="Search serial number..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                    className="rounded-lg border border-sidebar-border bg-white px-3 py-2 text-sm w-64"
                />
                <select
                    value={statusFilter}
                    onChange={(e) => { setStatusFilter(e.target.value); router.get('/inventory/serials', { status: e.target.value, search }, { preserveState: true }); }}
                    className="rounded-lg border border-sidebar-border bg-white px-3 py-2 text-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="sold">Sold</option>
                    <option value="returned">Returned</option>
                    <option value="quarantined">Quarantined</option>
                    <option value="disposed">Disposed</option>
                </select>
            </div>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Serial #</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-center text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Order Ref</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Sold At</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warranty</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sidebar-border">
                        {serials.data.map((serial) => (
                            <tr key={serial.id} className="hover:bg-muted/30">
                                <td className="py-3 px-3">
                                    <a href={`/inventory/serials/${serial.id}`} className="font-mono text-sm text-primary hover:underline">
                                        {serial.serial_number}
                                    </a>
                                </td>
                                <td className="py-3 px-3 text-sm">{serial.product?.name ?? serial.product_id}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{serial.warehouse?.name ?? '-'}</td>
                                <td className="py-3 px-3 text-center">
                                    <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${statusColor(serial.status)}`}>
                                        {serial.status}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{serial.order_reference ?? '-'}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{serial.sold_at ?? '-'}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{serial.warranty_expires_at ?? '-'}</td>
                            </tr>
                        ))}
                        {serials.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="py-8 text-center text-sm text-muted-foreground">No serial numbers found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination meta={serials} />
        </AppLayout>
    );
}
