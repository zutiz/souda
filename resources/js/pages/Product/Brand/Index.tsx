import { Head, router, usePage } from '@inertiajs/react';
import { PencilIcon, PlusIcon } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Pagination } from '@/modules/shared/components/pagination';
import type { BreadcrumbItem } from '@/types';

type Brand = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    website: string | null;
    products_count: number;
};

type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type BrandIndexPageProps = {
    brands: {
        data: Brand[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginatorLink[];
    };
};

export default function BrandIndex() {
    const { brands } = usePage<BrandIndexPageProps>().props;
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [website, setWebsite] = useState('');
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState<Brand | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Products', href: '/products' },
        { title: 'Brands', href: '/products/brands' },
    ];

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        const data = { name, description, website: website || null };
        const url = editing ? `/products/brands/${editing.id}` : '/products/brands';
        const method = editing ? 'put' as const : 'post' as const;

        router[method](url, data, {
            preserveScroll: true,
            onSuccess: () => {
                setName('');
                setDescription('');
                setWebsite('');
                setOpen(false);
                setEditing(null);
            },
        });
    }

    function openEdit(brand: Brand) {
        setEditing(brand);
        setName(brand.name);
        setDescription(brand.description ?? '');
        setWebsite(brand.website ?? '');
        setOpen(true);
    }

    function openCreate() {
        setEditing(null);
        setName('');
        setDescription('');
        setWebsite('');
        setOpen(true);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Brands" />

            <PageHeader title="Brands" description="Manage product brands">
                <Button onClick={openCreate}>
                    <PlusIcon className="mr-2 h-4 w-4" />
                    Add Brand
                </Button>
            </PageHeader>

            <div className="rounded-lg border border-sidebar-border">
                <table className="w-full">
                    <thead>
                        <tr className="border-b border-sidebar-border bg-muted/50">
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Name
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Slug
                            </th>
                            <th className="py-3 px-3 text-left text-xs font-medium uppercase text-muted-foreground">
                                Products
                            </th>
                            <th className="py-3 px-3 text-right text-xs font-medium uppercase text-muted-foreground">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {brands.data.map((brand) => (
                            <tr key={brand.id} className="border-b border-sidebar-border">
                                <td className="py-3 px-3 text-sm font-medium">{brand.name}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{brand.slug}</td>
                                <td className="py-3 px-3 text-sm text-muted-foreground">{brand.products_count}</td>
                                <td className="py-3 px-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="icon" className="h-8 w-8">
                                                <PencilIcon className="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem onClick={() => openEdit(brand)}>
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                onClick={() =>
                                                    router.delete(`/products/brands/${brand.id}`, {
                                                        preserveScroll: true,
                                                    })
                                                }
                                                className="text-destructive"
                                            >
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        ))}
                        {brands.data.length === 0 && (
                            <tr>
                                <td colSpan={4} className="py-12 text-center text-sm text-muted-foreground">
                                    No brands yet.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination
                links={brands.links}
                currentPage={brands.current_page}
                lastPage={brands.last_page}
                perPage={brands.per_page}
                total={brands.total}
            />

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editing ? 'Edit Brand' : 'Add Brand'}</DialogTitle>
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
                            <Label htmlFor="description">Description</Label>
                            <Textarea
                                id="description"
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="website">Website</Label>
                            <Input
                                id="website"
                                value={website}
                                onChange={(e) => setWebsite(e.target.value)}
                                placeholder="https://example.com"
                            />
                        </div>
                        <Button type="submit" className="w-full">
                            {editing ? 'Update Brand' : 'Create Brand'}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
