import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeftIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import type { BreadcrumbItem } from '@/types';

type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    parent: Category | null;
    children: Category[];
    products_count: number;
};

type CategoryShowPageProps = {
    category: Category;
};

export default function CategoryShow() {
    const { category } = usePage<CategoryShowPageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Products', href: '/products' },
        { title: 'Categories', href: '/products/categories' },
        { title: category.name, href: `/products/categories/${category.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={category.name} />

            <PageHeader title={category.name} description={category.description ?? undefined}>
                <Button variant="outline" asChild>
                    <Link href="/products/categories">
                        <ArrowLeftIcon className="mr-2 h-4 w-4" />
                        Back to Categories
                    </Link>
                </Button>
            </PageHeader>

            <div className="grid gap-6 md:grid-cols-2">
                <div className="rounded-lg border border-sidebar-border p-4">
                    <h3 className="mb-3 text-sm font-semibold">Details</h3>
                    <dl className="space-y-2 text-sm">
                        <div className="flex justify-between">
                            <dt className="text-muted-foreground">Slug</dt>
                            <dd>{category.slug}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-muted-foreground">Sort Order</dt>
                            <dd>{category.sort_order}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-muted-foreground">Products</dt>
                            <dd>{category.products_count}</dd>
                        </div>
                        {category.parent && (
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Parent</dt>
                                <dd>
                                    <Link
                                        href={`/products/categories/${category.parent.id}`}
                                        className="text-primary hover:underline"
                                    >
                                        {category.parent.name}
                                    </Link>
                                </dd>
                            </div>
                        )}
                    </dl>
                </div>
            </div>
        </AppLayout>
    );
}
