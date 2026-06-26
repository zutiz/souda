import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { ProductFormPage } from '../components/product-form-page';
import { mapProductToFormData } from '../lib/map-product';
import type { BreadcrumbItem } from '@/types';
import type { CategoryOption, BrandOption } from '../types';

type EditPageProps = {
    product: Record<string, any>;
    categories: CategoryOption[];
    brands: BrandOption[];
};

export default function ProductEdit() {
    const { product, categories = [], brands = [] } = usePage<EditPageProps>().props;

    const initialData = mapProductToFormData(product);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Products', href: '/products' },
        { title: product.name ?? 'Edit', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${product.name ?? 'Product'}`} />

            <ProductFormPage
                mode="edit"
                initialData={initialData}
                submitRoute={`/products/${product.id}`}
                method="put"
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
