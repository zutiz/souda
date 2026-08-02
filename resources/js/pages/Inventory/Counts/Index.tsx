import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type Count = {
    id: number;
    reference: string;
    type: string;
    status: string;
    counted_at: string | null;
    verified_at: string | null;
    completed_at: string | null;
    items_count: number;
    warehouse: { id: number; name: string };
    counted_by_user: { id: number; name: string } | null;
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type CountIndexPageProps = {
    counts: {
        data: Count[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

const statusColors: Record<string, string> = {
    draft: 'bg-muted text-muted-foreground',
    in_progress: 'bg-blue-100 text-blue-800',
    verified: 'bg-indigo-100 text-indigo-800',
    adjusted: 'bg-amber-100 text-amber-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

export default function CountIndex() {
    const { counts } = usePage<CountIndexPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Counts', href: '/inventory/counts' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Physical Counts" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title="Physical Counts" description="Cycle counting and full inventory counts">
                <Link href="/inventory/counts/create">
                    <Button>New Count</Button>
                </Link>
            </PageHeader>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Reference</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Warehouse</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Type</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Items</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Created</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {counts.data.map((c) => (
                            <tr key={c.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm font-medium">
                                    <Link href={`/inventory/counts/${c.id}`} className="hover:underline">
                                        {c.reference}
                                    </Link>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{c.warehouse.name}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{c.type}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{c.items_count}</td>
                                <td className="py-3 px-3 text-sm">
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[c.status] ?? 'bg-muted'}`}>
                                        {c.status.replace('_', ' ')}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{c.created_at}</td>
                                <td className="py-3 px-3 text-right text-sm">
                                    <Link href={`/inventory/counts/${c.id}`} className="text-sm text-blue-600 hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {counts.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="py-12 text-center text-sm text-muted-foreground">No counts found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={counts.links}
                currentPage={counts.current_page}
                lastPage={counts.last_page}
                perPage={counts.per_page}
                total={counts.total}
            />
            </div>
        </AppLayout>
    );
}
