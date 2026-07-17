import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type Warehouse = {
    id: number;
    name: string;
    code: string | null;
    city: string | null;
    country: string | null;
    is_active: boolean;
    bins_count: number;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type WarehouseIndexPageProps = {
    warehouses: {
        data: Warehouse[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

export default function WarehouseIndex() {
    const { warehouses } = usePage<WarehouseIndexPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Warehouses', href: '/inventory/warehouses' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Warehouses" />

            <PageHeader title="Warehouses" description="Manage inventory warehouses" />

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Name</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Code</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">City</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Bins</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {warehouses.data.map((w) => (
                            <tr key={w.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm font-medium">
                                    <Link href={`/inventory/warehouses/${w.id}`} className="hover:underline">
                                        {w.name}
                                    </Link>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{w.code ?? '—'}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{w.city ?? '—'}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{w.bins_count}</td>
                                <td className="py-3 px-3 text-sm">
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${w.is_active ? 'bg-green-100 text-green-800' : 'bg-muted text-muted-foreground'}`}>
                                        {w.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-right text-sm">
                                    <Link href={`/inventory/warehouses/${w.id}`} className="text-sm text-blue-600 hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {warehouses.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="py-12 text-center text-sm text-muted-foreground">No warehouses found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={warehouses.links}
                currentPage={warehouses.current_page}
                lastPage={warehouses.last_page}
                perPage={warehouses.per_page}
                total={warehouses.total}
            />
        </AppLayout>
    );
}
