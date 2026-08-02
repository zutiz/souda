import { type ReactNode } from 'react';
import { cn } from '@/lib/utils';
import { MobileBottomNav, MobileOnly, DesktopOnly } from './mobile-nav';
import { useIsMobile, useScreenSize } from '../hooks/use-responsive';

// Main content wrapper that handles bottom nav offset
export function MobileContent({
    children,
    className,
    hideBottomNav = false,
}: {
    children: ReactNode;
    className?: string;
    hideBottomNav?: boolean;
}) {
    const isMobile = useIsMobile();

    return (
        <main
            className={cn(
                'flex-1',
                isMobile && !hideBottomNav && 'pb-20', // Space for bottom nav
                className
            )}
        >
            {children}
        </main>
    );
}

// Responsive container with max-width and padding
export function ResponsiveContainer({
    children,
    className,
    size = 'default',
}: {
    children: ReactNode;
    className?: string;
    size?: 'sm' | 'default' | 'lg' | 'full';
}) {
    const maxWidthClasses = {
        sm: 'max-w-screen-sm',
        default: 'max-w-screen-lg',
        lg: 'max-w-screen-xl',
        full: 'max-w-full',
    };

    return (
        <div
            className={cn(
                'mx-auto w-full px-4 py-4',
                maxWidthClasses[size],
                className
            )}
        >
            {children}
        </div>
    );
}

// Responsive grid system
export function ResponsiveGrid({
    children,
    columns = { mobile: 1, tablet: 2, desktop: 3 },
    gap = 4,
    className,
}: {
    children: ReactNode;
    columns?: {
        mobile?: 1 | 2;
        tablet?: 2 | 3 | 4;
        desktop?: 3 | 4 | 6;
    };
    gap?: 2 | 3 | 4 | 6 | 8;
    className?: string;
}) {
    const gridClasses = {
        mobile: {
            1: 'grid-cols-1',
            2: 'grid-cols-2',
        },
        tablet: {
            2: 'sm:grid-cols-2',
            3: 'sm:grid-cols-3',
            4: 'sm:grid-cols-4',
        },
        desktop: {
            3: 'lg:grid-cols-3',
            4: 'lg:grid-cols-4',
            6: 'lg:grid-cols-6',
        },
    };

    const gapClasses = {
        2: 'gap-2',
        3: 'gap-3',
        4: 'gap-4',
        6: 'gap-6',
        8: 'gap-8',
    };

    return (
        <div
            className={cn(
                'grid',
                gridClasses.mobile[columns.mobile ?? 1],
                columns.tablet && gridClasses.tablet[columns.tablet],
                columns.desktop && gridClasses.desktop[columns.desktop],
                gapClasses[gap],
                className
            )}
        >
            {children}
        </div>
    );
}

// Sticky header for mobile
export function StickyHeader({
    children,
    className,
    showBorder = true,
}: {
    children: ReactNode;
    className?: string;
    showBorder?: boolean;
}) {
    return (
        <header
            className={cn(
                'sticky top-0 z-20 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60',
                showBorder && 'border-b border-border',
                className
            )}
        >
            {children}
        </header>
    );
}

// Stack layout for mobile-first design
export function VStack({
    children,
    gap = 4,
    className,
}: {
    children: ReactNode;
    gap?: 2 | 3 | 4 | 6 | 8;
    className?: string;
}) {
    const gapClasses = {
        2: 'space-y-2',
        3: 'space-y-3',
        4: 'space-y-4',
        6: 'space-y-6',
        8: 'space-y-8',
    };

    return (
        <div className={cn(gapClasses[gap], className)}>
            {children}
        </div>
    );
}

// Horizontal stack with responsive gap
export function HStack({
    children,
    gap = 2,
    align = 'center',
    className,
}: {
    children: ReactNode;
    gap?: 1 | 2 | 3 | 4 | 6;
    align?: 'start' | 'center' | 'end' | 'stretch';
    className?: string;
}) {
    const alignClasses = {
        start: 'items-start',
        center: 'items-center',
        end: 'items-end',
        stretch: 'items-stretch',
    };

    const gapClasses = {
        1: 'gap-1',
        2: 'gap-2',
        3: 'gap-3',
        4: 'gap-4',
        6: 'gap-6',
    };

    return (
        <div className={cn('flex', alignClasses[align], gapClasses[gap], className)}>
            {children}
        </div>
    );
}

// Full-height layout with scroll
export function ScrollLayout({
    children,
    className,
    hideScrollbar = false,
}: {
    children: ReactNode;
    className?: string;
    hideScrollbar?: boolean;
}) {
    return (
        <div
            className={cn(
                'flex-1 overflow-y-auto overscroll-contain',
                hideScrollbar && 'scrollbar-hide',
                className
            )}
            style={{ scrollBehavior: 'smooth' }}
        >
            {children}
        </div>
    );
}

// Page wrapper for consistent layout
export function PageLayout({
    children,
    header,
    footer,
    className,
}: {
    children: ReactNode;
    header?: ReactNode;
    footer?: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('flex min-h-svh flex-col', className)}>
            {header}
            <ScrollLayout>{children}</ScrollLayout>
            {footer}
        </div>
    );
}