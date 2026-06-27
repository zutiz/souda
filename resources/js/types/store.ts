export interface Store {
    id: string;
    name: string;
    slug: string;
    code: string;
    currency: string;
    timezone: string;
    status: string;
    is_default: boolean;
    pivot?: {
        is_visible: boolean;
        is_featured: boolean;
        sort_order: number;
    };
}

export interface StorePageProps {
    currentStore?: Store | null;
    stores?: Store[];
}
