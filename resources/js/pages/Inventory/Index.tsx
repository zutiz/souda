import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type Balance = {
    id: number;
    product_id: string;
    warehouse_id: number;
    quantity: number;
    reserved_quantity: number;
    available_quantity: number;
    average_unit_cost: number;
    total_stock_value: number;
    last_movement_at: string | null;
    product: { id: string; name: string; sku: string } | null;
    warehouse: { id: number; name: string } | null;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type BalanceIndexPageProps = {
    balances: {
        data: Balance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

export default function InventoryIndex() {
    const { balances } = usePage<BalanceIndexPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Balances', href: '/inventory/balances' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inventory Balances" />

            <PageHeader title="Balances" description="Current stock levels across all warehouses" />

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Product</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">SKU</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">On Hand</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Reserved</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Available</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        {balances.data.map((b) => {
                            const unitCost = b.average_unit_cost / 100;
                            return (
                                <tr key={b.id} className="border-b border-sidebar-border">
                                    <td className="py-3 px-3 text-sm font-medium">{b.product?.name ?? b.product_id}</td>
                                    <td className="py-3 px-3 text-sm text-muted-foreground">{b.product?.sku ?? '—'}</td>
                                    <td className="py-3 px-3 text-sm text-muted-foreground">{b.warehouse?.name ?? '—'}</td>
                                    <td className="py-3 px-3 text-right text-sm">{b.quantity}</td>
                                    <td className="py-3 px-3 text-right text-sm text-muted-foreground">{b.reserved_quantity}</td>
                                    <td className="py-3 px-3 text-right text-sm font-medium">{b.available_quantity}</td>
                                    <td className="py-3 px-3 text-right text-sm text-muted-foreground">${unitCost.toFixed(2)}</td>
                                </tr>
                            );
                        })}
                        {balances.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="py-12 text-center text-sm text-muted-foreground">No balances found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={balances.links}
                currentPage={balances.current_page}
                lastPage={balances.last_page}
                perPage={balances.per_page}
                total={balances.total}
            />
        </AppLayout>
    );
}
