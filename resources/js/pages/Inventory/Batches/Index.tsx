import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type Batch = {
    id: number;
    batch_number: string;
    supplier_batch: string | null;
    product_id: string;
    warehouse_id: number;
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
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type PageProps = {
    batches: {
        data: Batch[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
    filters: { status?: string; search?: string };
};

export default function BatchIndex() {
    const { batches, filters } = usePage<PageProps>().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Batches', href: '/inventory/batches' },
    ];

    function applyFilters() {
        router.get('/inventory/batches', { search, status: statusFilter }, { preserveState: true });
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
            <Head title="Batches" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title="Batches" description="Manage batch/lot inventory tracking" />

            <div className="mb-4 flex items-center gap-3">
                <input
                    type="text"
                    placeholder="Search by batch number..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                    className="rounded-lg border border-sidebar-border bg-white px-3 py-2 text-sm w-64"
                />
                <select
                    value={statusFilter}
                    onChange={(e) => { setStatusFilter(e.target.value); router.get('/inventory/batches', { status: e.target.value, search }, { preserveState: true }); }}
                    className="rounded-lg border border-sidebar-border bg-white px-3 py-2 text-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="depleted">Depleted</option>
                    <option value="quarantined">Quarantined</option>
                    <option value="expired">Expired</option>
                </select>
            </div>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Batch #</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-center text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Initial</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Remaining</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Expiry</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sidebar-border">
                        {batches.data.map((batch) => (
                            <tr key={batch.id} className="hover:bg-muted/30">
                                <td className="py-3 px-3">
                                    <a href={`/inventory/batches/${batch.id}`} className="font-mono text-sm text-primary hover:underline">
                                        {batch.batch_number}
                                    </a>
                                </td>
                                <td className="py-3 px-3 text-sm">{batch.product?.name ?? batch.product_id}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{batch.warehouse?.name ?? '-'}</td>
                                <td className="py-3 px-3 text-center">
                                    <span className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${statusColor(batch.status)}`}>
                                        {batch.status}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-sm text-right">{batch.initial_quantity}</td>
                                <td className="py-3 px-3 text-sm text-right">{batch.remaining_quantity}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{batch.expiry_date ?? '-'}</td>
                            </tr>
                        ))}
                        {batches.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="py-8 text-center text-sm text-muted-foreground">No batches found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={batches.links}
                currentPage={batches.current_page}
                lastPage={batches.last_page}
                perPage={batches.per_page}
                total={batches.total}
            />
            </div>
        </AppLayout>
    );
}
