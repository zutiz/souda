import { useCallback, useEffect } from 'react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { TopbarActions } from '@/components/topbar-actions';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';
import AppLogo from './app-logo';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const handleSearchClick = useCallback(() => {
        const event = new KeyboardEvent('keydown', {
            key: 'k',
            metaKey: true,
            bubbles: true,
        });
        document.dispatchEvent(event);
    }, []);

    const handleNotificationClick = useCallback((notification: { actionUrl?: string }) => {
        if (notification.actionUrl) {
            window.location.href = notification.actionUrl;
        }
    }, []);

    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                handleSearchClick();
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [handleSearchClick]);

    return (
        <header className="flex shrink-0 border-b border-sidebar-border/50">
            <div className="flex h-16 w-full items-center justify-between px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-14 md:px-5">
                <div className="flex items-center gap-4">
                    <SidebarTrigger className="-ml-1 size-8" />
                    {breadcrumbs.length > 0 && (
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    )}
                </div>
                <div className="flex items-center gap-4">
                    <TopbarActions
                        onSearchClick={handleSearchClick}
                        onNotificationClick={handleNotificationClick}
                    />
                    <div className="hidden lg:block">
                        <AppLogo />
                    </div>
                </div>
            </div>
        </header>
    );
}
