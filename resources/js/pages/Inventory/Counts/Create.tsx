import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';

type Warehouse = { id: number; name: string };

type CreateCountPageProps = {
    warehouses: Warehouse[];
};

export default function CreateCount() {
    const { warehouses } = usePage<CreateCountPageProps>().props;

    const [warehouseId, setWarehouseId] = useState('');
    const [type, setType] = useState('full');
    const [processing, setProcessing] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Counts', href: '/inventory/counts' },
        { title: 'Create', href: '/inventory/counts/create' },
    ];

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        router.post('/inventory/counts', {
            warehouse_id: Number(warehouseId),
            type,
        }, {
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Count" />

            <PageHeader title="New Count" description="Create a physical inventory count" />

            <form onSubmit={submit} className="max-w-lg space-y-6">
                <div className="rounded-lg border border-sidebar-border p-4 space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="warehouse_id">Warehouse</Label>
                        <Select value={warehouseId} onValueChange={setWarehouseId}>
                            <SelectTrigger><SelectValue placeholder="Select warehouse" /></SelectTrigger>
                            <SelectContent>
                                {warehouses.map((w) => (
                                    <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="type">Count Type</Label>
                        <Select value={type} onValueChange={setType}>
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="full">Full Count</SelectItem>
                                <SelectItem value="cycle">Cycle Count</SelectItem>
                                <SelectItem value="partial">Partial Count</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="flex gap-3">
                    <Button type="submit" disabled={processing || !warehouseId}>
                        {processing ? 'Creating...' : 'Create Count'}
                    </Button>
                    <Button type="button" variant="outline" onClick={() => window.history.back()}>Cancel</Button>
                </div>
            </form>
        </AppLayout>
    );
}
