import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { TenantBrandingProvider } from '@/providers/tenant-branding-provider';
import type { AppLayoutProps } from '@/types';

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <TenantBrandingProvider>
        <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
            {children}
        </AppLayoutTemplate>
    </TenantBrandingProvider>
);
