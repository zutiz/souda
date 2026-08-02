import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import type { BreadcrumbItem } from '@/types';

type Bin = {
    id: number;
    name: string;
    code: string | null;
    zone: string | null;
};

type Transfer = {
    id: number;
    reference: string;
    status: string;
    created_at: string;
};

type Warehouse = {
    id: number;
    name: string;
    code: string | null;
    address: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    country: string | null;
    is_active: boolean;
    bins: Bin[];
    outgoing_transfers: Transfer[];
    incoming_transfers: Transfer[];
};

type ShowWarehousePageProps = {
    warehouse: Warehouse;
};

export default function ShowWarehouse() {
    const { warehouse } = usePage<ShowWarehousePageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Warehouses', href: '/inventory/warehouses' },
        { title: warehouse.name, href: `/inventory/warehouses/${warehouse.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={warehouse.name} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title={warehouse.name} description={warehouse.code ?? ''} />

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    <div className="rounded-lg border border-sidebar-border">
                        <div className="border-b border-sidebar-border px-4 py-3 font-semibold text-sm">Bins</div>
                        {warehouse.bins.length === 0 && (
                            <div className="px-4 py-8 text-center text-sm text-muted-foreground">No bins configured.</div>
                        )}
                        {warehouse.bins.length > 0 && (
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-sidebar-border bg-muted/50">
                                        <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Name</th>
                                        <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Code</th>
                                        <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">Zone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {warehouse.bins.map((bin) => (
                                        <tr key={bin.id} className="border-b border-sidebar-border">
                                            <td className="py-3 px-3 text-sm font-medium">{bin.name}</td>
                                            <td className="py-3 px-3 text-sm text-muted-foreground">{bin.code ?? '—'}</td>
                                            <td className="py-3 px-3 text-sm text-muted-foreground">{bin.zone ?? '—'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>

                <div className="space-y-4">
                    <div className="rounded-lg border border-sidebar-border p-4 space-y-3">
                        <h3 className="font-semibold text-sm">Details</h3>
                        <DetailRow label="Name" value={warehouse.name} />
                        <DetailRow label="Code" value={warehouse.code ?? '—'} />
                        <DetailRow label="Address" value={warehouse.address ?? '—'} />
                        <DetailRow label="City" value={warehouse.city ?? '—'} />
                        <DetailRow label="State" value={warehouse.state ?? '—'} />
                        <DetailRow label="Postal Code" value={warehouse.postal_code ?? '—'} />
                        <DetailRow label="Country" value={warehouse.country ?? '—'} />
                        <DetailRow label="Status" value={warehouse.is_active ? 'Active' : 'Inactive'} />
                    </div>
                </div>
            </div>
            </div>
        </AppLayout>
    );
}

function DetailRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">{label}</span>
            <span className="font-medium">{value}</span>
        </div>
    );
}
