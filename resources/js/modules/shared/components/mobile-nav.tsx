import { useEffect, useState, useCallback } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { useSidebar } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Home,
    Users,
    Package,
    ShoppingCart,
    Settings,
    Bell,
    Search,
    Menu,
    X,
    ChevronRight,
    HelpCircle,
    LogOut,
    BarChart3,
    FileText,
    Workflow,
} from 'lucide-react';
import type { PageProps } from '@inertiajs/core';

type NavItem = {
    label: string;
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    badge?: string | number;
    description?: string;
};

type NavGroup = {
    label: string;
    items: NavItem[];
};

// Define navigation structure based on modules
const getNavigation = (): NavGroup[] => [
    {
        label: 'Main',
        items: [
            { label: 'Dashboard', href: '/dashboard', icon: Home, description: 'Overview & quick stats' },
            { label: 'Analytics', href: '/analytics', icon: BarChart3, description: 'Reports & insights' },
        ],
    },
    {
        label: 'Management',
        items: [
            { label: 'Products', href: '/products', icon: Package, badge: 24 },
            { label: 'Orders', href: '/orders', icon: ShoppingCart, badge: '3' },
            { label: 'Customers', href: '/customers', icon: Users },
            { label: 'Invoices', href: '/invoices', icon: FileText },
        ],
    },
    {
        label: 'Settings',
        items: [
            { label: 'Settings', href: '/settings', icon: Settings },
            { label: 'Help', href: '/help', icon: HelpCircle },
        ],
    },
];

// Mobile menu item with larger touch target
export function MobileNavItem({
    item,
    isActive,
    onClick,
}: {
    item: NavItem;
    isActive?: boolean;
    onClick?: () => void;
}) {
    const Icon = item.icon;

    return (
        <Link
            href={item.href}
            onClick={onClick}
            className={cn(
                'flex items-center gap-3 rounded-lg px-4 py-3.5 text-base font-medium transition-colors',
                'touch-manipulation', // Disable double-tap zoom
                'active:scale-[0.98] active:bg-sidebar-accent',
                isActive
                    ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                    : 'text-sidebar-foreground hover:bg-sidebar-accent/50'
            )}
        >
            <Icon className="size-5" />
            <span className="flex-1">{item.label}</span>
            {item.badge && (
                <Badge variant="secondary" className="text-xs px-2 py-0.5">
                    {item.badge}
                </Badge>
            )}
            <ChevronRight className="size-4 text-muted-foreground" />
        </Link>
    );
}

// Mobile navigation drawer
export function MobileNavDrawer({
    isOpen,
    onClose,
    children,
}: {
    isOpen: boolean;
    onClose: () => void;
    children: React.ReactNode;
}) {
    const navigation = getNavigation();
    const { url } = usePage();
    const [activeGroup, setActiveGroup] = useState<string | null>(null);

    // Close on escape key
    useEffect(() => {
        const handleEscape = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        if (isOpen) {
            document.addEventListener('keydown', handleEscape);
            document.body.style.overflow = 'hidden';
        }
        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = '';
        };
    }, [isOpen, onClose]);

    return (
        <>
            {/* Backdrop */}
            <div
                className={cn(
                    'fixed inset-0 z-40 bg-black/50 backdrop-blur-sm transition-opacity md:hidden',
                    isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'
                )}
                onClick={onClose}
                aria-hidden="true"
            />

            {/* Drawer */}
            <div
                className={cn(
                    'fixed inset-y-0 left-0 z-50 w-[85vw] max-w-sm bg-sidebar transition-transform duration-300 ease-out md:hidden',
                    isOpen ? 'translate-x-0' : '-translate-x-full'
                )}
                role="dialog"
                aria-modal="true"
                aria-label="Navigation menu"
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b border-sidebar-border p-4">
                    <div className="flex items-center gap-3">
                        <div className="size-9 rounded-lg bg-primary flex items-center justify-center">
                            <span className="text-primary-foreground font-bold text-lg">S</span>
                        </div>
                        <div>
                            <p className="font-semibold text-sidebar-foreground">Souda</p>
                            <p className="text-xs text-sidebar-foreground/60">Business Manager</p>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={onClose}
                        className="size-9"
                    >
                        <X className="size-5" />
                    </Button>
                </div>

                {/* Search */}
                <div className="p-4">
                    <div className="relative">
                        <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            type="search"
                            placeholder="Search..."
                            className="w-full rounded-lg border border-sidebar-border bg-sidebar-accent/50 py-2.5 pl-10 pr-4 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                    </div>
                </div>

                {/* Navigation */}
                <nav className="flex-1 overflow-y-auto p-4 space-y-6">
                    {navigation.map((group) => (
                        <div key={group.label}>
                            <h3 className="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/40">
                                {group.label}
                            </h3>
                            <div className="space-y-1">
                                {group.items.map((item) => {
                                    const Icon = item.icon;
                                    const isActive = url === item.href || url.startsWith(item.href + '/');

                                    return (
                                        <Link
                                            key={item.href}
                                            href={item.href}
                                            onClick={onClose}
                                            className={cn(
                                                'flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition-colors',
                                                'touch-manipulation active:scale-[0.98]',
                                                isActive
                                                    ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                                    : 'text-sidebar-foreground/80 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground'
                                            )}
                                        >
                                            <Icon className="size-5" />
                                            <span className="flex-1">{item.label}</span>
                                            {item.badge && (
                                                <Badge variant="secondary" className="text-xs">
                                                    {item.badge}
                                                </Badge>
                                            )}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </nav>

                {/* Footer */}
                <div className="border-t border-sidebar-border p-4 space-y-2">
                    <Link
                        href="/settings"
                        onClick={onClose}
                        className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-sidebar-foreground/80 hover:bg-sidebar-accent/50 transition-colors"
                    >
                        <Settings className="size-5" />
                        <span>Settings</span>
                    </Link>
                    <button
                        className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-destructive hover:bg-destructive/10 transition-colors"
                    >
                        <LogOut className="size-5" />
                        <span>Sign out</span>
                    </button>
                </div>
            </div>
        </>
    );
}

// Mobile header with hamburger menu
export function MobileHeader({
    title,
    showBackButton = false,
    onBack,
    actions,
    className,
}: {
    title: string;
    showBackButton?: boolean;
    onBack?: () => void;
    actions?: React.ReactNode;
    className?: string;
}) {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    return (
        <>
            <header
                className={cn(
                    'sticky top-0 z-30 flex h-14 items-center gap-2 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 px-4',
                    className
                )}
            >
                {showBackButton ? (
                    <Button variant="ghost" size="icon" onClick={onBack} className="size-9">
                        <ChevronRight className="size-5 rotate-180" />
                    </Button>
                ) : (
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => setIsMenuOpen(true)}
                        className="size-9 md:hidden"
                        aria-label="Open navigation menu"
                    >
                        <Menu className="size-5" />
                    </Button>
                )}

                <h1 className="flex-1 text-lg font-semibold truncate">{title}</h1>

                {actions && (
                    <div className="flex items-center gap-1">
                        {actions}
                    </div>
                )}
            </header>

            <MobileNavDrawer
                isOpen={isMenuOpen}
                onClose={() => setIsMenuOpen(false)}
            />
        </>
    );
}

// Bottom tab bar for mobile navigation
export function MobileBottomNav({ className }: { className?: string }) {
    const { url } = usePage();

    const tabs = [
        { label: 'Home', href: '/dashboard', icon: Home },
        { label: 'Products', href: '/products', icon: Package },
        { label: 'Orders', href: '/orders', icon: ShoppingCart },
        { label: 'More', href: '/more', icon: Menu },
    ];

    return (
        <nav
            className={cn(
                'fixed bottom-0 left-0 right-0 z-40 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 md:hidden',
                className
            )}
        >
            <div className="flex items-center justify-around py-2 px-2 safe-area-bottom">
                {tabs.map((tab) => {
                    const Icon = tab.icon;
                    const isActive = url === tab.href || url.startsWith(tab.href + '/');

                    return (
                        <Link
                            key={tab.href}
                            href={tab.href}
                            className={cn(
                                'flex flex-col items-center gap-1 py-1.5 px-3 rounded-lg transition-colors min-w-[60px]',
                                isActive
                                    ? 'text-primary'
                                    : 'text-muted-foreground hover:text-foreground'
                            )}
                        >
                            <Icon className="size-5" />
                            <span className="text-xs font-medium">{tab.label}</span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}

// Swipe gesture handler for closing drawers
export function useSwipeToClose({
    isOpen,
    onClose,
    threshold = 50,
}: {
    isOpen: boolean;
    onClose: () => void;
    threshold?: number;
}) {
    const [startX, setStartX] = useState<number | null>(null);
    const [currentX, setCurrentX] = useState<number | null>(null);

    const handleTouchStart = useCallback((e: React.TouchEvent) => {
        if (isOpen) {
            setStartX(e.touches[0].clientX);
            setCurrentX(e.touches[0].clientX);
        }
    }, [isOpen]);

    const handleTouchMove = useCallback((e: React.TouchEvent) => {
        if (startX !== null) {
            setCurrentX(e.touches[0].clientX);
        }
    }, [startX]);

    const handleTouchEnd = useCallback(() => {
        if (startX !== null && currentX !== null) {
            const diff = startX - currentX;
            if (diff > threshold) {
                onClose();
            }
        }
        setStartX(null);
        setCurrentX(null);
    }, [startX, currentX, threshold, onClose]);

    return {
        handlers: {
            onTouchStart: handleTouchStart,
            onTouchMove: handleTouchMove,
            onTouchEnd: handleTouchEnd,
        },
        progress: startX !== null && currentX !== null
            ? Math.min(1, (startX - currentX) / 200)
            : 0,
    };
}

// Responsive visibility utilities
export function MobileOnly({ children, className }: { children: React.ReactNode; className?: string }) {
    return (
        <div className={cn('md:hidden', className)}>
            {children}
        </div>
    );
}

export function DesktopOnly({ children, className }: { children: React.ReactNode; className?: string }) {
    return (
        <div className={cn('hidden md:block', className)}>
            {children}
        </div>
    );
}

// Touch-friendly button with ripple effect
export function TouchButton({
    children,
    className,
    onClick,
    ...props
}: React.ComponentProps<'button'> & { onClick?: () => void }) {
    return (
        <button
            className={cn(
                'relative overflow-hidden rounded-lg transition-transform active:scale-[0.98]',
                className
            )}
            onClick={onClick}
            {...props}
        >
            {children}
        </button>
    );
}