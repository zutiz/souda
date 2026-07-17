import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type Transfer = {
    id: number;
    reference: string;
    status: string;
    description: string | null;
    sent_at: string | null;
    received_at: string | null;
    cancelled_at: string | null;
    from_warehouse: { id: number; name: string };
    to_warehouse: { id: number; name: string };
};

type PaginatorLink = { url: string | null; label: string; active: boolean };

type TransferIndexPageProps = {
    transfers: {
        data: Transfer[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

const statusColors: Record<string, string> = {
    draft: 'bg-muted text-muted-foreground',
    sent: 'bg-blue-100 text-blue-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

export default function TransferIndex() {
    const { transfers } = usePage<TransferIndexPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Transfers', href: '/inventory/transfers' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Transfers" />

            <PageHeader title="Transfers" description="Stock transfers between warehouses">
                <Link href="/inventory/transfers/create">
                    <Button>New Transfer</Button>
                </Link>
            </PageHeader>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Reference</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">From</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">To</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Status</th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Created</th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {transfers.data.map((t) => (
                            <tr key={t.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm font-medium">
                                    <Link href={`/inventory/transfers/${t.id}`} className="hover:underline">
                                        {t.reference}
                                    </Link>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{t.from_warehouse.name}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{t.to_warehouse.name}</td>
                                <td className="py-3 px-3 text-sm">
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[t.status] ?? 'bg-muted'}`}>
                                        {t.status}
                                    </span>
                                </td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{t.created_at}</td>
                                <td className="py-3 px-3 text-right text-sm">
                                    <Link href={`/inventory/transfers/${t.id}`} className="text-sm text-blue-600 hover:underline">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {transfers.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="py-12 text-center text-sm text-muted-foreground">No transfers found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={transfers.links}
                currentPage={transfers.current_page}
                lastPage={transfers.last_page}
                perPage={transfers.per_page}
                total={transfers.total}
            />
        </AppLayout>
    );
}
