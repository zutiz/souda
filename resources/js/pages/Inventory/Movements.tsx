import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type Movement = {
    id: number;
    product_id: string;
    warehouse_id: number;
    variant_id: string | null;
    quantity: number;
    type: string;
    reason: string | null;
    reference_type: string | null;
    reference_id: string | null;
    created_at: string;
    product: { id: string; name: string; sku: string } | null;
    warehouse: { id: number; name: string } | null;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type MovementsPageProps = {
    movements: {
        data: Movement[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

export default function InventoryMovements() {
    const { movements } = usePage<MovementsPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Movements', href: '/inventory/movements' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Stock Movements" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title="Movements" description="All stock movements across warehouses" />

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Date</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Type</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Qty</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        {movements.data.map((m) => (
                            <tr key={m.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm text-muted-foreground whitespace-nowrap">{m.created_at}</td>
                                <td className="py-3 px-3 text-sm font-medium">{m.product?.name ?? m.product_id}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{m.warehouse?.name ?? '—'}</td>
                                <td className="py-3 px-3 text-sm">
                                    <span className="inline-flex items-center rounded-full bg-muted px-2 py-0.5 text-xs font-medium">
                                        {m.type}
                                    </span>
                                </td>
                                <td className={`py-3 px-3 text-right text-sm font-medium ${m.quantity > 0 ? 'text-green-600' : 'text-red-600'}`}>
                                    {m.quantity > 0 ? '+' : ''}{m.quantity}
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{m.reason ?? '—'}</td>
                            </tr>
                        ))}
                        {movements.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="py-12 text-center text-sm text-muted-foreground">No movements recorded.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={movements.links}
                currentPage={movements.current_page}
                lastPage={movements.last_page}
                perPage={movements.per_page}
                total={movements.total}
            />
            </div>
        </AppLayout>
    );
}
