import { Link, router } from '@inertiajs/react';
import { createColumnHelper } from '@tanstack/react-table';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreHorizontalIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import { formatCurrency } from '@/modules/shared/lib/formatters';
import { ConfirmDialog } from '@/modules/shared/components/confirm-dialog';
import { useState } from 'react';
import type { Product } from '../types';

const helper = createColumnHelper<Product>();

const statusVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    active: 'default',
    draft: 'secondary',
    archived: 'outline',
};

const stockBadgeClass: Record<string, string> = {
    in_stock: 'bg-positive/10 text-positive hover:bg-positive/20',
    low_stock: 'bg-warning/10 text-warning hover:bg-warning/20',
    out_of_stock: 'bg-destructive/10 text-destructive hover:bg-destructive/20',
};

function ProductActionsCell({ product }: { product: Product }) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="size-8" onClick={(e) => e.stopPropagation()}>
                        <MoreHorizontalIcon className="size-4" />
                        <span className="sr-only">Open menu</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                    <DropdownMenuItem asChild>
                        <Link href={`/products/${product.id}`}>View</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={`/products/${product.id}/edit`}>Edit</Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <Link href={`/products/${product.id}/duplicate`}>Duplicate</Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        onClick={(e) => {
                            e.stopPropagation();
                            setDeleteOpen(true);
                        }}
                        className="text-destructive focus:text-destructive"
                    >
                        Delete
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <ConfirmDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Delete product?"
                description={`This will permanently delete "${product.name}" and all associated data. This action cannot be undone.`}
                confirmLabel="Delete"
                variant="destructive"
                loading={deleting}
                onConfirm={() => {
                    setDeleting(true);
                    router.delete(`/products/${product.id}`, {
                        onFinish: () => setDeleting(false),
                    });
                    setDeleteOpen(false);
                }}
            />
        </>
    );
}

export const columns = [
    helper.display({
        id: 'select',
        header: ({ table }) => (
            <Checkbox
                checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
                aria-label="Select all"
            />
        ),
        cell: ({ row }) => (
            <Checkbox
                checked={row.getIsSelected()}
                onCheckedChange={(value) => row.toggleSelected(!!value)}
                aria-label="Select row"
                onClick={(e) => e.stopPropagation()}
            />
        ),
        enableSorting: false,
        enableHiding: false,
        meta: { label: 'Select' },
    }),
    helper.display({
        id: 'image',
        header: '',
        cell: ({ row }) => (
            <div className="bg-muted flex size-10 items-center justify-center overflow-hidden rounded-md">
                {row.original.thumbnail_url ? (
                    <img
                        src={row.original.thumbnail_url}
                        alt={row.original.name}
                        className="size-full object-cover"
                        loading="lazy"
                    />
                ) : (
                    <div className="text-muted-foreground text-xs">N/A</div>
                )}
            </div>
        ),
        enableSorting: false,
        enableHiding: false,
        meta: { label: 'Image' },
    }),
    helper.accessor('name', {
        header: 'Name',
        cell: ({ row }) => (
            <div className="flex flex-col">
                <Link
                    href={`/products/${row.original.id}`}
                    className="hover:text-primary font-medium underline-offset-4 hover:underline"
                >
                    {row.original.name}
                </Link>
                <span className="text-muted-foreground text-xs">{row.original.sku}</span>
            </div>
        ),
        meta: { label: 'Name' },
    }),
    helper.accessor('category_name', {
        header: 'Category',
        cell: ({ getValue }) => {
            const value = getValue();
            return value ? (
                <span className="text-muted-foreground text-sm">{value}</span>
            ) : (
                <span className="text-muted-foreground/50 text-sm">—</span>
            );
        },
        meta: { label: 'Category' },
    }),
    helper.accessor('price', {
        header: 'Price',
        cell: ({ getValue }) => (
            <span className="font-medium tabular-nums">{formatCurrency(getValue())}</span>
        ),
        meta: { label: 'Price' },
    }),
    helper.accessor('stock_quantity', {
        header: 'Stock',
        cell: ({ row }) => {
            const quantity = row.original.stock_quantity;
            const status = row.original.stock_status;
            return (
                <Badge variant="outline" className={cn('font-normal', stockBadgeClass[status])}>
                    {quantity}
                </Badge>
            );
        },
        meta: { label: 'Stock' },
    }),
    helper.accessor('status', {
        header: 'Status',
        cell: ({ getValue }) => {
            const value = getValue();
            return (
                <Badge variant={statusVariant[value] ?? 'outline'} className="capitalize">
                    {value}
                </Badge>
            );
        },
        meta: { label: 'Status' },
    }),
    helper.display({
        id: 'actions',
        header: '',
        cell: ({ row }) => <ProductActionsCell product={row.original} />,
        enableSorting: false,
        enableHiding: false,
        meta: { label: 'Actions' },
    }),
];
