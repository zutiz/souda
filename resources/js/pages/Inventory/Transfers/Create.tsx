import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';

type Warehouse = { id: number; name: string };
type Product = { id: string; name: string; sku: string };

type CreateTransferPageProps = {
    warehouses: Warehouse[];
    products: Product[];
};

type TransferItem = {
    product_id: string;
    quantity: number;
};

export default function CreateTransfer() {
    const { warehouses, products } = usePage<CreateTransferPageProps>().props;

    const [fromWarehouseId, setFromWarehouseId] = useState('');
    const [toWarehouseId, setToWarehouseId] = useState('');
    const [description, setDescription] = useState('');
    const [items, setItems] = useState<TransferItem[]>([{ product_id: '', quantity: 1 }]);
    const [processing, setProcessing] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory', href: '/inventory' },
        { title: 'Transfers', href: '/inventory/transfers' },
        { title: 'Create', href: '/inventory/transfers/create' },
    ];

    const addItem = () => setItems([...items, { product_id: '', quantity: 1 }]);
    const removeItem = (i: number) => setItems(items.filter((_, idx) => idx !== i));
    const updateItem = (i: number, field: keyof TransferItem, value: string | number) => {
        const updated = [...items];
        updated[i] = { ...updated[i], [field]: value };
        setItems(updated);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        router.post('/inventory/transfers', {
            from_warehouse_id: Number(fromWarehouseId),
            to_warehouse_id: Number(toWarehouseId),
            description,
            items: items.map((item) => ({
                product_id: item.product_id,
                quantity: Number(item.quantity),
            })),
        }, {
            onFinish: () => setProcessing(false),
        });
    };

    const warehouseOptions = warehouses.filter((w) => String(w.id) !== fromWarehouseId);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Transfer" />

            <PageHeader title="New Transfer" description="Create a stock transfer between warehouses" />

            <form onSubmit={submit} className="max-w-2xl space-y-6">
                <div className="rounded-lg border border-sidebar-border p-4 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="from_warehouse_id">From Warehouse</Label>
                            <Select value={fromWarehouseId} onValueChange={setFromWarehouseId}>
                                <SelectTrigger><SelectValue placeholder="Select warehouse" /></SelectTrigger>
                                <SelectContent>
                                    {warehouses.map((w) => (
                                        <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="to_warehouse_id">To Warehouse</Label>
                            <Select value={toWarehouseId} onValueChange={setToWarehouseId} disabled={!fromWarehouseId}>
                                <SelectTrigger><SelectValue placeholder="Select destination" /></SelectTrigger>
                                <SelectContent>
                                    {warehouseOptions.map((w) => (
                                        <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Description (optional)</Label>
                        <Textarea id="description" value={description} onChange={(e) => setDescription(e.target.value)} rows={2} />
                    </div>
                </div>

                <div className="rounded-lg border border-sidebar-border p-4 space-y-4">
                    <div className="flex items-center justify-between">
                        <h3 className="font-semibold text-sm">Items</h3>
                        <Button type="button" variant="outline" size="sm" onClick={addItem}>+ Add Item</Button>
                    </div>

                    {items.map((item, i) => (
                        <div key={i} className="flex items-end gap-3">
                            <div className="flex-1 space-y-2">
                                <Label>Product</Label>
                                <Select value={item.product_id} onValueChange={(v) => updateItem(i, 'product_id', v)}>
                                    <SelectTrigger><SelectValue placeholder="Select product" /></SelectTrigger>
                                    <SelectContent>
                                        {products.map((p) => (
                                            <SelectItem key={p.id} value={p.id}>{p.name} ({p.sku})</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="w-24 space-y-2">
                                <Label>Quantity</Label>
                                <Input
                                    type="number"
                                    min={1}
                                    value={item.quantity}
                                    onChange={(e) => updateItem(i, 'quantity', e.target.value)}
                                />
                            </div>
                            {items.length > 1 && (
                                <Button type="button" variant="ghost" size="sm" onClick={() => removeItem(i)}>Remove</Button>
                            )}
                        </div>
                    ))}
                </div>

                <div className="flex gap-3">
                    <Button type="submit" disabled={processing || !fromWarehouseId || !toWarehouseId}>
                        {processing ? 'Creating...' : 'Create Transfer'}
                    </Button>
                    <Button type="button" variant="outline" onClick={() => window.history.back()}>Cancel</Button>
                </div>
            </form>
        </AppLayout>
    );
}
