import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type WarehouseStock = {
    id: number;
    quantity: number;
    reserved_quantity: number;
    reorder_level: number;
    product: { id: number; name: string } | null;
    variant: { id: number; name: string; sku: string } | null;
    warehouse: { id: number; name: string };
};

type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type LowStockPageProps = {
    stocks: {
        data: WarehouseStock[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

export default function LowStock() {
    const { stocks } = usePage<LowStockPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Products', href: '/products' },
        { title: 'Inventory', href: '/products/inventory' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Low Stock Inventory" />

            <PageHeader
                title="Inventory"
                description="Products with low or below-reorder-level stock"
            />

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Product
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                SKU
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Warehouse
                            </th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">
                                On Hand
                            </th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">
                                Reserved
                            </th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">
                                Available
                            </th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">
                                Reorder Level
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {stocks.data.map((stock) => {
                            const available = stock.quantity - stock.reserved_quantity;
                            return (
                                <tr
                                    key={stock.id}
                                    className="border-b border-sidebar-border"
                                >
                                    <td className="py-3 px-3 text-sm font-medium">
                                        {stock.product?.name ?? stock.variant?.name ?? '—'}
                                    </td>
                                    <td className="py-3 px-3 text-sm text-muted-foreground">
                                        {stock.variant?.sku ?? '—'}
                                    </td>
                                    <td className="py-3 px-3 text-sm text-muted-foreground">
                                        {stock.warehouse.name}
                                    </td>
                                    <td className="py-3 px-3 text-right text-sm">{stock.quantity}</td>
                                    <td className="py-3 px-3 text-right text-sm text-muted-foreground">
                                        {stock.reserved_quantity}
                                    </td>
                                    <td className="py-3 px-3 text-right text-sm font-medium">
                                        <span
                                            className={
                                                available <= 0
                                                    ? 'text-destructive'
                                                    : available <= stock.reorder_level
                                                      ? 'text-amber-500'
                                                      : undefined
                                            }
                                        >
                                            {available}
                                        </span>
                                    </td>
                                    <td className="py-3 px-3 text-right text-sm text-muted-foreground">
                                        {stock.reorder_level}
                                    </td>
                                </tr>
                            );
                        })}
                        {stocks.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="py-12 text-center text-sm text-muted-foreground">
                                    All stock levels are healthy.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={stocks.links}
                currentPage={stocks.current_page}
                lastPage={stocks.last_page}
                perPage={stocks.per_page}
                total={stocks.total}
            />
        </AppLayout>
    );
}
