import { Head, Link, router, usePage } from '@inertiajs/react';
import { PencilIcon, PlusIcon, TrashIcon } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
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
import type { BreadcrumbItem } from '@/types';

type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    children: Category[];
    products_count: number;
};

type CategoryIndexPageProps = {
    categories: Category[];
};

function CategoryRow({ category, depth = 0 }: { category: Category; depth?: number }) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <tr className="border-b border-sidebar-border">
                <td className="py-2 pr-2" style={{ paddingLeft: `${depth * 24 + 12}px` }}>
                    <span className="text-sm font-medium">{category.name}</span>
                </td>
                <td className="py-2 px-2 text-sm text-muted-foreground">{category.slug}</td>
                <td className="py-2 px-2 text-sm text-muted-foreground">{category.products_count}</td>
                <td className="py-2 px-2 text-right">
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8">
                                <PencilIcon className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem asChild>
                                <Link href={`/products/categories/${category.id}`}>View</Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                onClick={() =>
                                    router.delete(`/products/categories/${category.id}`, {
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
            {category.children?.map((child) => (
                <CategoryRow key={child.id} category={child} depth={depth + 1} />
            ))}
        </>
    );
}

export default function CategoryIndex() {
    const { categories } = usePage<CategoryIndexPageProps>().props;
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [parentId, setParentId] = useState('');
    const [open, setOpen] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Products', href: '/products' },
        { title: 'Categories', href: '/products/categories' },
    ];

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        router.post(
            '/products/categories',
            { name, description, parent_id: parentId || null },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setName('');
                    setDescription('');
                    setParentId('');
                    setOpen(false);
                },
            },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categories" />

            <PageHeader
                title="Categories"
                description="Organize your products with categories"
            >
                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogTrigger asChild>
                        <Button>
                            <PlusIcon className="mr-2 h-4 w-4" />
                            Add Category
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create Category</DialogTitle>
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
                                <Label htmlFor="parent_id">Parent Category</Label>
                                <select
                                    id="parent_id"
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    value={parentId}
                                    onChange={(e) => setParentId(e.target.value)}
                                >
                                    <option value="">None (top level)</option>
                                    {categories.map((cat) => (
                                        <option key={cat.id} value={cat.id}>
                                            {cat.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <Button type="submit" className="w-full">
                                Create Category
                            </Button>
                        </form>
                    </DialogContent>
                </Dialog>
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
                        {categories.map((category) => (
                            <CategoryRow key={category.id} category={category} />
                        ))}
                        {categories.length === 0 && (
                            <tr>
                                <td colSpan={4} className="py-12 text-center text-sm text-muted-foreground">
                                    No categories yet. Create your first category to get started.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
