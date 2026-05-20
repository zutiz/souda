import { ChevronDownIcon } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({
    items = [],
    label = 'Platform',
}: {
    items: NavItem[];
    label?: string;
}) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>{label}</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) =>
                    item.items && item.items.length > 0 ? (
                        <CollapsibleGroup key={item.title} item={item} isCurrentUrl={isCurrentUrl} />
                    ) : (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isCurrentUrl(item.href)}
                                tooltip={{ children: item.title }}
                            >
                                <Link
                                    href={item.href}
                                    prefetch={item.prefetch !== false}
                                >
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ),
                )}
            </SidebarMenu>
        </SidebarGroup>
    );
}

function CollapsibleGroup({
    item,
    isCurrentUrl,
}: {
    item: NavItem;
    isCurrentUrl: (href: unknown) => boolean;
}) {
    const anyChildActive = (item.items ?? []).some((sub) => isCurrentUrl(sub.href));

    return (
        <Collapsible defaultOpen={anyChildActive} className="group/collapsible">
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton
                        tooltip={{ children: item.title }}
                        isActive={anyChildActive}
                        className="[&[data-state=open]>svg:last-child]:rotate-0"
                    >
                        {item.icon && <item.icon />}
                        <span>{item.title}</span>
                        <ChevronDownIcon className="ml-auto rotate-[-90deg] transition-transform duration-200 group-data-[state=open]/collapsible:rotate-0" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {(item.items ?? []).map((sub) => (
                            <SidebarMenuSubItem key={sub.title}>
                                <SidebarMenuSubButton
                                    asChild
                                    isActive={isCurrentUrl(sub.href)}
                                >
                                    <Link
                                        href={sub.href}
                                        prefetch={sub.prefetch !== false}
                                    >
                                        <span>{sub.title}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}
