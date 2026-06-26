import { Head, router, usePage } from '@inertiajs/react';
import { PlusIcon, TrashIcon } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type AttributeValue = {
    id: number;
    value: string;
    swatch_color: string | null;
};

type Attribute = {
    id: number;
    name: string;
    slug: string;
    type: string;
    sort_order: number;
    values: AttributeValue[];
};

type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type AttributeIndexPageProps = {
    attributes: {
        data: Attribute[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

export default function AttributeIndex() {
    const { attributes } = usePage<AttributeIndexPageProps>().props;
    const [name, setName] = useState('');
    const [type, setType] = useState('select');
    const [open, setOpen] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Products', href: '/products' },
        { title: 'Attributes', href: '/products/attributes' },
    ];

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        router.post(
            '/products/attributes',
            { name, type },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setName('');
                    setType('select');
                    setOpen(false);
                },
            },
        );
    }

    function addValue(attribute: Attribute) {
        const value = prompt('Enter attribute value:');
        if (!value) return;

        router.post(
            `/products/attributes/${attribute.id}/values`,
            { value },
            { preserveScroll: true },
        );
    }

    function removeValue(valueId: number) {
        if (!confirm('Delete this attribute value?')) return;

        router.delete(`/products/attributes/values/${valueId}`, {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Attributes" />

            <PageHeader title="Attributes" description="Manage product attributes and their values">
                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogTrigger asChild>
                        <Button>
                            <PlusIcon className="mr-2 h-4 w-4" />
                            Add Attribute
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create Attribute</DialogTitle>
                        </DialogHeader>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="type">Type</Label>
                                <select
                                    id="type"
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    value={type}
                                    onChange={(e) => setType(e.target.value)}
                                >
                                    <option value="select">Select</option>
                                    <option value="radio">Radio</option>
                                    <option value="color">Color</option>
                                    <option value="text">Text</option>
                                </select>
                            </div>
                            <Button type="submit" className="w-full">
                                Create Attribute
                            </Button>
                        </form>
                    </DialogContent>
                </Dialog>
            </PageHeader>

            <div className="space-y-4">
                {attributes.data.map((attribute) => (
                    <div
                        key={attribute.id}
                        className="rounded-lg border border-sidebar-border p-4"
                    >
                        <div className="mb-3 flex items-center justify-between">
                            <div>
                                <h3 className="text-sm font-semibold">{attribute.name}</h3>
                                <p className="text-xs text-muted-foreground">
                                    {attribute.slug} &middot; {attribute.type}
                                </p>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => addValue(attribute)}
                            >
                                <PlusIcon className="mr-1 h-3 w-3" />
                                Add Value
                            </Button>
                        </div>

                        {attribute.values.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {attribute.values.map((val) => (
                                    <div
                                        key={val.id}
                                        className="inline-flex items-center gap-1.5 rounded-md border border-sidebar-border bg-muted/30 px-2.5 py-1 text-xs"
                                    >
                                        {val.swatch_color && (
                                            <span
                                                className="inline-block h-3 w-3 rounded-full"
                                                style={{ backgroundColor: val.swatch_color }}
                                            />
                                        )}
                                        {val.value}
                                        <button
                                            type="button"
                                            onClick={() => removeValue(val.id)}
                                            className="ml-0.5 text-muted-foreground hover:text-destructive"
                                        >
                                            <TrashIcon className="h-3 w-3" />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                ))}

                {attributes.data.length === 0 && (
                    <div className="py-12 text-center text-sm text-muted-foreground">
                        No attributes yet.
                    </div>
                )}
            </div>

            <Pagination
                links={attributes.links}
                currentPage={attributes.current_page}
                lastPage={attributes.last_page}
                perPage={attributes.per_page}
                total={attributes.total}
            />
        </AppLayout>
    );
}
