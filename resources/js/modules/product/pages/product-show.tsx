import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeftIcon, EditIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type ProductShow = {
    id: string;
    name: string;
    slug: string;
    sku: string;
    barcode: string | null;
    barcode_type: string | null;
    description: string | null;
    short_description: string | null;
    type: string;
    status: string;
    base_price: string | number;
    compare_at_price: string | number | null;
    cost_price: string | number | null;
    tax_inclusive: boolean;
    track_inventory: boolean;
    low_stock_threshold: number | null;
    total_quantity: number;
    total_reserved: number;
    weight: number | null;
    length: number | null;
    width: number | null;
    height: number | null;
    metadata: Record<string, unknown> | null;
    published_at: string | null;
    created_at: string;
    updated_at: string;
    category: { id: string; name: string } | null;
    brand: { id: string; name: string } | null;
    variants: { id: string; name: string; sku: string; price: string; stock: number }[];
    media: { id: string; url: string; alt: string }[];
    warehouseStock: { warehouse: { id: string; name: string }; quantity: number }[];
};

type ShowPageProps = {
    product: ProductShow;
};

function statusVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
    switch (status) {
        case 'active':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'archived':
            return 'outline';
        default:
            return 'secondary';
    }
}

function InfoRow({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="flex items-start justify-between gap-4 py-2">
            <dt className="text-muted-foreground shrink-0 text-sm font-medium">{label}</dt>
            <dd className="text-right text-sm">{value ?? <span className="text-muted-foreground italic">—</span>}</dd>
        </div>
    );
}

function InfoSection({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <dl className="divide-y">{children}</dl>
            </CardContent>
        </Card>
    );
}

export default function ProductShow() {
    const { product } = usePage<ShowPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Products', href: '/products' },
        { title: product.name, href: '#' },
    ];

    const warehouseStock = product.warehouseStock ?? [];
    const variants = product.variants ?? [];
    const available = product.total_quantity - product.total_reserved;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={product.name} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-y-auto p-4 lg:p-6">
                <PageHeader title={product.name} description={`SKU: ${product.sku}`}>
                    <Button variant="outline" asChild>
                        <Link href="/products">
                            <ArrowLeftIcon className="size-4" />
                            Back
                        </Link>
                    </Button>
                    <Button asChild>
                        <Link href={`/products/${product.id}/edit`}>
                            <EditIcon className="size-4" />
                            Edit
                        </Link>
                    </Button>
                </PageHeader>

                <div className="flex flex-wrap items-center gap-2">
                    <Badge variant={statusVariant(product.status)}>{product.status}</Badge>
                    <Badge variant="secondary">{product.type}</Badge>
                    {product.tax_inclusive && <Badge variant="outline">Tax Inclusive</Badge>}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="space-y-6">
                        <InfoSection title="General">
                            <InfoRow label="Name" value={product.name} />
                            <InfoRow label="Slug" value={product.slug} />
                            <InfoRow label="SKU" value={product.sku} />
                            <InfoRow label="Barcode" value={product.barcode ?? product.barcode_type ? `${product.barcode} (${product.barcode_type})` : null} />
                            <InfoRow label="Category" value={product.category?.name} />
                            <InfoRow label="Brand" value={product.brand?.name} />
                        </InfoSection>

                        <InfoSection title="Pricing">
                            <InfoRow label="Base Price" value={product.base_price ? `$${product.base_price}` : null} />
                            <InfoRow label="Compare At Price" value={product.compare_at_price ? `$${product.compare_at_price}` : null} />
                            <InfoRow label="Cost Price" value={product.cost_price ? `$${product.cost_price}` : null} />
                            <InfoRow label="Tax Inclusive" value={product.tax_inclusive ? 'Yes' : 'No'} />
                        </InfoSection>

                        <InfoSection title="Shipping">
                            <InfoRow label="Weight" value={product.weight ? `${product.weight} kg` : null} />
                            <InfoRow label="Dimensions" value={product.length || product.width || product.height ? `${product.length ?? '—'} × ${product.width ?? '—'} × ${product.height ?? '—'}` : null} />
                        </InfoSection>
                    </div>

                    <div className="space-y-6">
                        <InfoSection title="Inventory">
                            <InfoRow label="Track Inventory" value={product.track_inventory ? 'Yes' : 'No'} />
                            <InfoRow label="Total Quantity" value={product.total_quantity} />
                            <InfoRow label="Reserved" value={product.total_reserved} />
                            <InfoRow label="Available" value={available} />
                            <InfoRow label="Low Stock Threshold" value={product.low_stock_threshold} />
                        </InfoSection>

                        {warehouseStock.length > 0 && (
                            <InfoSection title="Warehouse Stock">
                                {warehouseStock.map((ws) => (
                                    <InfoRow key={ws.warehouse.id} label={ws.warehouse.name} value={ws.quantity} />
                                ))}
                            </InfoSection>
                        )}

                        {product.description && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base">Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm leading-relaxed">{product.description}</p>
                                    {product.short_description && (
                                        <>
                                            <Separator className="my-3" />
                                            <p className="text-muted-foreground text-sm">{product.short_description}</p>
                                        </>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {variants.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base">Variants ({product.variants.length})</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        {variants.map((v) => (
                                            <div key={v.id} className="flex items-center justify-between rounded-md border px-3 py-2 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <span className="font-medium">{v.name}</span>
                                                    <span className="text-muted-foreground">{v.sku}</span>
                                                </div>
                                                <div className="flex items-center gap-4">
                                                    <span>${v.price}</span>
                                                    <span className="text-muted-foreground">Stock: {v.stock}</span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
