'use client';

import { useState, useEffect, useCallback } from 'react';
import { Link, router } from '@inertiajs/react';
import {
    Search,
    Package,
    ShoppingCart,
    Users,
    Settings,
    BarChart3,
    CreditCard,
    Warehouse,
    Calendar,
    Plus,
    Command,
    ArrowRight,
} from 'lucide-react';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import { cn } from '@/lib/utils';
import type { LucideIcon } from 'lucide-react';

type SearchCategory = {
    label: string;
    items: SearchResult[];
};

type SearchResult = {
    id: string;
    title: string;
    description?: string;
    href: string;
    icon?: LucideIcon;
    category?: string;
};

interface CommandPaletteProps {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
    recentSearches?: string[];
}

// Quick actions available from command palette
const quickActions: SearchResult[] = [
    { id: 'create-product', title: 'Create Product', description: 'Add a new product to catalog', href: '/products/create', icon: Package },
    { id: 'create-order', title: 'Create Order', description: 'Start a new order', href: '/orders/create', icon: ShoppingCart },
    { id: 'view-reports', title: 'View Reports', description: 'Open reports dashboard', href: '/reports', icon: BarChart3 },
    { id: 'manage-team', title: 'Manage Team', description: 'View team members', href: '/team', icon: Users },
    { id: 'settings', title: 'Settings', description: 'Open app settings', href: '/settings', icon: Settings },
    { id: 'view-billing', title: 'Billing', description: 'View subscriptions', href: '/billing', icon: CreditCard },
];

// Navigation shortcuts
const navigationShortcuts: SearchResult[] = [
    { id: 'nav-dashboard', title: 'Dashboard', description: 'Go to dashboard', href: '/dashboard', icon: Search },
    { id: 'nav-products', title: 'Products', description: 'Manage products', href: '/products', icon: Package },
    { id: 'nav-inventory', title: 'Inventory', description: 'Track inventory', href: '/inventory', icon: Warehouse },
    { id: 'nav-orders', title: 'Orders', description: 'View orders', href: '/orders', icon: ShoppingCart },
    { id: 'nav-customers', title: 'Customers', description: 'Manage customers', href: '/crm/customers', icon: Users },
    { id: 'nav-reports', title: 'Reports', description: 'View analytics', href: '/reports', icon: BarChart3 },
    { id: 'nav-appointments', title: 'Appointments', description: 'Manage bookings', href: '/appointments', icon: Calendar },
];

export function CommandPalette({
    open = false,
    onOpenChange,
    recentSearches = [],
}: CommandPaletteProps) {
    const [isOpen, setIsOpen] = useState(open);
    const [search, setSearch] = useState('');

    // Sync with external state
    useEffect(() => {
        setIsOpen(open);
    }, [open]);

    // Listen for keyboard shortcut
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                setIsOpen((prev) => !prev);
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, []);

    const handleOpenChange = useCallback((open: boolean) => {
        setIsOpen(open);
        onOpenChange?.(open);
        if (!open) {
            setSearch('');
        }
    }, [onOpenChange]);

    const handleSelect = useCallback((href: string) => {
        handleOpenChange(false);
        router.visit(href);
    }, [handleOpenChange]);

    // Filter results based on search
    const filteredQuickActions = search
        ? quickActions.filter(
              (item) =>
                  item.title.toLowerCase().includes(search.toLowerCase()) ||
                  item.description?.toLowerCase().includes(search.toLowerCase())
          )
        : quickActions;

    const filteredNav = search
        ? navigationShortcuts.filter(
              (item) =>
                  item.title.toLowerCase().includes(search.toLowerCase()) ||
                  item.description?.toLowerCase().includes(search.toLowerCase())
          )
        : navigationShortcuts;

    return (
        <CommandDialog open={isOpen} onOpenChange={handleOpenChange}>
            <div className="border-b px-3 py-2">
                <div className="flex items-center gap-2">
                    <Search className="size-4 text-muted-foreground" />
                    <CommandInput
                        placeholder="Search SOUDA... (Cmd+K)"
                        value={search}
                        onValueChange={setSearch}
                        className="flex-1 border-0 bg-transparent outline-none placeholder:text-muted-foreground"
                    />
                    <kbd className="pointer-events-none hidden h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium text-muted-foreground sm:flex">
                        esc
                    </kbd>
                </div>
            </div>
            <CommandList>
                <CommandEmpty className="py-6 text-center text-sm text-muted-foreground">
                    No results found.
                </CommandEmpty>

                <CommandGroup heading="Quick Actions">
                    {filteredQuickActions.map((action) => {
                        const Icon = action.icon ?? Plus;
                        return (
                            <CommandItem
                                key={action.id}
                                value={action.title}
                                onSelect={() => handleSelect(action.href)}
                                className="gap-2"
                            >
                                <Icon className="size-4 text-muted-foreground" />
                                <div className="flex-1">
                                    <span>{action.title}</span>
                                    {action.description && (
                                        <span className="ml-2 text-xs text-muted-foreground">
                                            {action.description}
                                        </span>
                                    )}
                                </div>
                                <ArrowRight className="size-3 text-muted-foreground opacity-0 group-hover:opacity-100" />
                            </CommandItem>
                        );
                    })}
                </CommandGroup>

                {search && filteredNav.length > 0 && (
                    <>
                        <CommandSeparator />
                        <CommandGroup heading="Navigation">
                            {filteredNav.map((nav) => {
                                const Icon = nav.icon ?? Search;
                                return (
                                    <CommandItem
                                        key={nav.id}
                                        value={nav.title}
                                        onSelect={() => handleSelect(nav.href)}
                                        className="gap-2"
                                    >
                                        <Icon className="size-4 text-muted-foreground" />
                                        <span className="flex-1">{nav.title}</span>
                                        {nav.description && (
                                            <span className="text-xs text-muted-foreground">
                                                {nav.description}
                                            </span>
                                        )}
                                    </CommandItem>
                                );
                            })}
                        </CommandGroup>
                    </>
                )}

                {!search && (
                    <>
                        <CommandSeparator />
                        <CommandGroup heading="Navigation">
                            {navigationShortcuts.slice(0, 5).map((nav) => {
                                const Icon = nav.icon ?? Search;
                                return (
                                    <CommandItem
                                        key={nav.id}
                                        value={nav.title}
                                        onSelect={() => handleSelect(nav.href)}
                                        className="gap-2"
                                    >
                                        <Icon className="size-4 text-muted-foreground" />
                                        <span className="flex-1">{nav.title}</span>
                                        <span className="text-xs text-muted-foreground">
                                            {nav.description}
                                        </span>
                                    </CommandItem>
                                );
                            })}
                        </CommandGroup>
                    </>
                )}
            </CommandList>
            <div className="border-t px-3 py-2">
                <div className="flex items-center gap-4 text-xs text-muted-foreground">
                    <span className="flex items-center gap-1">
                        <kbd className="rounded bg-muted px-1.5 py-0.5 font-mono">↑↓</kbd>
                        Navigate
                    </span>
                    <span className="flex items-center gap-1">
                        <kbd className="rounded bg-muted px-1.5 py-0.5 font-mono">↵</kbd>
                        Select
                    </span>
                    <span className="flex items-center gap-1">
                        <kbd className="rounded bg-muted px-1.5 py-0.5 font-mono">esc</kbd>
                        Close
                    </span>
                </div>
            </div>
        </CommandDialog>
    );
}

// Hook to manage command palette state
export function useCommandPalette() {
    const [isOpen, setIsOpen] = useState(false);

    const open = useCallback(() => setIsOpen(true), []);
    const close = useCallback(() => setIsOpen(false), []);
    const toggle = useCallback(() => setIsOpen((prev) => !prev), []);

    return { isOpen, open, close, toggle };
}