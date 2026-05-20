export type ProductStatus = 'draft' | 'active' | 'archived';

export type StockStatus = 'in_stock' | 'low_stock' | 'out_of_stock';

export type Product = {
    id: string;
    name: string;
    sku: string;
    barcode: string | null;
    price: number;
    compare_price: number | null;
    cost_price: number | null;
    description: string | null;
    status: ProductStatus;
    category_id: string | null;
    category_name: string | null;
    brand_id: string | null;
    brand_name: string | null;
    stock_quantity: number;
    stock_status: StockStatus;
    image_url: string | null;
    thumbnail_url: string | null;
    weight: number | null;
    is_taxable: boolean;
    track_stock: boolean;
    store_id: string;
    created_at: string;
    updated_at: string;
};

export type ProductFilters = {
    page?: number;
    per_page?: number;
    sort?: string;
    search?: string;
    status?: string[];
    category_id?: string;
    brand_id?: string;
    stock_status?: string;
    store_id?: string;
};

export type BulkAction = 'activate' | 'archive' | 'delete';

export type CategoryOption = {
    id: string;
    name: string;
    parent_id: string | null;
    products_count: number;
};

export type BrandOption = {
    id: string;
    name: string;
    products_count: number;
};

export type StoreOption = {
    id: string;
    name: string;
};
