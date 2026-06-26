import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import type { TenantConfig } from '@/types/global';

export function useTenantConfig(): TenantConfig | null {
    const page = usePage<{ tenant_config: TenantConfig | null }>();

    return useMemo(() => page.props.tenant_config ?? null, [page.props.tenant_config]);
}

export function useModuleEnabled(moduleSlug: string): boolean {
    const config = useTenantConfig();

    return config?.modules.includes(moduleSlug) ?? false;
}

export function useEnabledModules(): string[] {
    const config = useTenantConfig();

    return config?.modules ?? [];
}
