import type { Auth } from '@/types/auth';

export interface TenantConfig {
    business_type: string;
    modules: string[];
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            logo: string | null;
            favicon: string | null;
            auth: Auth;
            sidebarOpen: boolean;
            tenant_config: TenantConfig | null;
            [key: string]: unknown;
        };
    }
}
