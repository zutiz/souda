import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type StockMovement = {
    id: number;
    type: string;
    quantity: number;
    reference_type: string | null;
    reference_id: number | null;
    notes: string | null;
    created_at: string;
    product: { id: number; name: string } | null;
    variant: { id: number; name: string; sku: string } | null;
    fromWarehouse: { id: number; name: string } | null;
    toWarehouse: { id: number; name: string } | null;
};

type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type MovementsPageProps = {
    movements: {
        data: StockMovement[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

const movementTypeLabels: Record<string, string> = {
    receive: 'Received',
    deduct: 'Deducted',
    adjust: 'Adjusted',
    transfer: 'Transferred',
    sale: 'Sold',
    return: 'Returned',
};

const movementTypeColors: Record<string, string> = {
    receive: 'text-green-600 bg-green-50',
    deduct: 'text-red-600 bg-red-50',
    adjust: 'text-blue-600 bg-blue-50',
    transfer: 'text-purple-600 bg-purple-50',
    sale: 'text-orange-600 bg-orange-50',
    return: 'text-teal-600 bg-teal-50',
};

export default function Movements() {
    const { movements } = usePage<MovementsPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Products', href: '/products' },
        { title: 'Stock Transfers', href: '/products/stock-transfers' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Stock Movements" />

            <PageHeader
                title="Stock Movements"
                description="History of all stock movements including transfers"
            />

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Type
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Product
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                SKU
                            </th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">
                                Qty
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                From
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                To
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Notes
                            </th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {movements.data.map((movement) => (
                            <tr key={movement.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3">
                                    <span
                                        className={`inline-block rounded-full px-2 py-0.5 text-xs font-medium ${
                                            movementTypeColors[movement.type] ?? 'text-gray-600 bg-gray-50'
                                        }`}
                                    >
                                        {movementTypeLabels[movement.type] ?? movement.type}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-sm">
                                    {movement.product?.name ?? movement.variant?.name ?? '—'}
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">
                                    {movement.variant?.sku ?? '—'}
                                </td>
                                <td className="py-3 px-3 text-right text-sm font-medium">
                                    {movement.quantity}
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">
                                    {movement.fromWarehouse?.name ?? '—'}
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">
                                    {movement.toWarehouse?.name ?? '—'}
                                </td>
                                <td className="max-w-[200px] truncate py-3 px-3 text-sm text-muted-foreground">
                                    {movement.notes ?? '—'}
                                </td>
                                <td className="py-3 px-3 text-right text-sm text-muted-foreground">
                                    {movement.created_at}
                                </td>
                            </tr>
                        ))}
                        {movements.data.length === 0 && (
                            <tr>
                                <td
                                    colSpan={8}
                                    className="py-12 text-center text-sm text-muted-foreground"
                                >
                                    No stock movements yet.
                                </td>
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
        </AppLayout>
    );
}
