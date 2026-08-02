import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

interface TenantBrandingProps {
    children: React.ReactNode;
}

export function TenantBrandingProvider({ children }: TenantBrandingProps) {
    const { props } = usePage();
    const styleRef = useRef<HTMLStyleElement | null>(null);

    const branding = props.tenant_config?.branding as Record<string, string> | undefined;

    useEffect(() => {
        // Create or update the style element
        if (!styleRef.current) {
            styleRef.current = document.createElement('style');
            styleRef.current.id = 'tenant-branding-vars';
            document.head.appendChild(styleRef.current);
        }

        if (branding && Object.keys(branding).length > 0) {
            // Brand identity vars apply in both light and dark mode
            const brandMapping: Record<string, string> = {
                primary: '--primary',
                primary_foreground: '--primary-foreground',
                accent: '--accent',
                accent_foreground: '--accent-foreground',
                radius: '--radius',
            };

            // Surface vars apply in light mode only so dark mode keeps its dark palette
            const surfaceMapping: Record<string, string> = {
                sidebar: '--sidebar',
                sidebar_foreground: '--sidebar-foreground',
                sidebar_accent: '--sidebar-accent',
                sidebar_accent_foreground: '--sidebar-accent-foreground',
                sidebar_primary: '--sidebar-primary',
                sidebar_primary_foreground: '--sidebar-primary-foreground',
            };

            const brandVars: string[] = [];
            const surfaceVars: string[] = [];

            for (const [key, cssVar] of Object.entries(brandMapping)) {
                if (branding[key]) {
                    brandVars.push(`${cssVar}: ${branding[key]};`);
                }
            }

            for (const [key, cssVar] of Object.entries(surfaceMapping)) {
                if (branding[key]) {
                    surfaceVars.push(`${cssVar}: ${branding[key]};`);
                }
            }

            const blocks: string[] = [];

            if (brandVars.length > 0) {
                blocks.push(`:root { ${brandVars.join(' ')} }`);
            }

            if (surfaceVars.length > 0) {
                blocks.push(`:root:not(.dark) { ${surfaceVars.join(' ')} }`);
            }

            styleRef.current.textContent = blocks.join('\n');
        } else {
            // Clear branding if none
            if (styleRef.current) {
                styleRef.current.textContent = '';
            }
        }

        // Cleanup
        return () => {
            if (styleRef.current) {
                styleRef.current.textContent = '';
            }
        };
    }, [branding]);

    return <>{children}</>;
}