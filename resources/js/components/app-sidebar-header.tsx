import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';
import AppLogo from './app-logo';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="flex shrink-0 border-b border-sidebar-border/50">
            <div className="flex h-14 w-full items-center justify-between px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
                <div className="flex items-center gap-3">
                    <SidebarTrigger className="-ml-1" />
                    {breadcrumbs.length > 0 && (
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    )}
                </div>
                <span className="flex items-center gap-1.5">
                    <AppLogo />
                </span>
            </div>
        </header>
    );
}
