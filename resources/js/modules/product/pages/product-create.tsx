import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { ProductFormPage } from '../components/product-form-page';
import type { BreadcrumbItem } from '@/types';
import type { CategoryOption, BrandOption } from '../types';

type CreatePageProps = {
    categories: CategoryOption[];
    brands: BrandOption[];
};

export default function ProductCreate() {
    const { categories = [], brands = [] } = usePage<CreatePageProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Products', href: '/products' },
        { title: 'Create', href: '/products/create' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Product" />

            <ProductFormPage
                mode="create"
                submitRoute="/products"
                method="post"
                categories={categories}
                brands={brands}
                onCancel={() => window.history.back()}
                onSuccess={() => {
                    // redirect handled by server
                }}
            />
        </AppLayout>
    );
}
