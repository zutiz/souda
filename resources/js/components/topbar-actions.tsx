'use client';

import { Search, Bell, Command } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import type { LucideIcon } from 'lucide-react';

type NotificationType = 'inventory' | 'order' | 'payment' | 'system';

type Notification = {
    id: string;
    type: NotificationType;
    title: string;
    description: string;
    time: string;
    read: boolean;
    actionUrl?: string;
};

interface TopbarActionsProps {
    notifications?: Notification[];
    onSearchClick?: () => void;
    onNotificationClick?: (notification: Notification) => void;
    onMarkAllRead?: () => void;
    className?: string;
}

function getNotificationIcon(type: NotificationType): LucideIcon {
    switch (type) {
        case 'inventory':
            return Bell;
        case 'order':
            return Bell;
        case 'payment':
            return Bell;
        default:
            return Bell;
    }
}

function getNotificationStyles(type: NotificationType) {
    switch (type) {
        case 'inventory':
            return 'text-warning bg-warning/10';
        case 'order':
            return 'text-primary bg-primary/10';
        case 'payment':
            return 'text-positive bg-positive/10';
        default:
            return 'text-muted-foreground bg-muted';
    }
}

function formatRelativeTime(time: string): string {
    const now = new Date();
    const then = new Date(time);
    const diffMs = now.getTime() - then.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    return `${diffDays}d ago`;
}

export function TopbarActions({
    notifications = [],
    onSearchClick,
    onNotificationClick,
    onMarkAllRead,
    className,
}: TopbarActionsProps) {
    const unreadCount = notifications.filter((n) => !n.read).length;

    return (
        <div className={cn('flex items-center gap-2', className)}>
            {/* Search Trigger */}
            <Button
                variant="outline"
                size="sm"
                className="hidden h-10 w-full max-w-[280px] items-center gap-2.5 text-muted-foreground md:flex"
                onClick={onSearchClick}
            >
                <Search className="size-4 shrink-0" />
                <span className="flex-1 text-left">Search...</span>
                <kbd className="pointer-events-none hidden h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium text-muted-foreground lg:flex">
                    <Command className="size-3" />K
                </kbd>
            </Button>

            {/* Mobile Search Button */}
            <Button
                variant="ghost"
                size="icon"
                className="md:hidden"
                onClick={onSearchClick}
            >
                <Search className="size-4" />
                <span className="sr-only">Search</span>
            </Button>

            {/* Notifications */}
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="relative">
                        <Bell className="size-4" />
                        {unreadCount > 0 && (
                            <Badge
                                variant="destructive"
                                className="absolute -right-1 -top-1 size-4 justify-center p-0 text-[10px]"
                            >
                                {unreadCount > 9 ? '9+' : unreadCount}
                            </Badge>
                        )}
                        <span className="sr-only">Notifications</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-80">
                    <div className="flex items-center justify-between px-4 py-3 border-b">
                        <div className="font-semibold text-sm">Notifications</div>
                        {unreadCount > 0 && onMarkAllRead && (
                            <button
                                className="text-xs text-primary hover:underline"
                                onClick={onMarkAllRead}
                            >
                                Mark all read
                            </button>
                        )}
                    </div>
                    <div className="max-h-[300px] overflow-y-auto">
                        {notifications.length === 0 ? (
                            <div className="px-4 py-8 text-center">
                                <Bell className="mx-auto mb-2 size-8 text-muted-foreground/50" />
                                <p className="text-sm text-muted-foreground">
                                    No notifications
                                </p>
                            </div>
                        ) : (
                            notifications.map((notification) => {
                                const Icon = getNotificationIcon(notification.type);
                                const styles = getNotificationStyles(notification.type);

                                return (
                                    <DropdownMenuItem
                                        key={notification.id}
                                        className="flex flex-col items-start gap-1 p-3 cursor-pointer"
                                        onClick={() => onNotificationClick?.(notification)}
                                    >
                                        <div className="flex w-full items-start gap-2">
                                            <div className={cn(
                                                'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full',
                                                styles
                                            )}>
                                                <Icon className="size-4" />
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between gap-2">
                                                    <p className={cn(
                                                        'text-sm font-medium truncate',
                                                        !notification.read && 'font-semibold'
                                                    )}>
                                                        {notification.title}
                                                    </p>
                                                    {!notification.read && (
                                                        <span className="size-2 shrink-0 rounded-full bg-primary" />
                                                    )}
                                                </div>
                                                <p className="text-xs text-muted-foreground line-clamp-1">
                                                    {notification.description}
                                                </p>
                                                <p className="text-xs text-muted-foreground/70 mt-1">
                                                    {formatRelativeTime(notification.time)}
                                                </p>
                                            </div>
                                        </div>
                                    </DropdownMenuItem>
                                );
                            })
                        )}
                    </div>
                    {notifications.length > 0 && (
                        <>
                            <DropdownMenuSeparator />
                            <div className="p-2">
                                <button className="w-full text-center text-xs text-primary hover:underline">
                                    View all notifications
                                </button>
                            </div>
                        </>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}