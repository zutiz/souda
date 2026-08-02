import { router, usePage } from '@inertiajs/react';
import { Building2, Check, Plus } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { create, switchMethod } from '@/routes/tenant';
import { cn } from '@/lib/utils';

interface Tenant {
    id: string;
    name: string;
    business_type?: string;
}

interface TenantPageProps {
    currentTenant?: Tenant | null;
    tenants?: Tenant[];
}

export function TenantSwitcher() {
    const { props } = usePage<TenantPageProps>();
    const { currentTenant, tenants = [] } = props;

    if (!currentTenant && tenants.length === 0) {
        return null;
    }

    const handleSwitch = (tenantId: string) => {
        router.post(switchMethod.url(), { tenant_id: tenantId }, {
            preserveState: false,
            preserveScroll: true,
        });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className="w-full justify-start gap-2 px-3 py-2 h-auto font-medium text-left"
                >
                    <div className="flex size-6 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground">
                        <Building2 className="size-3.5" />
                    </div>
                    <span className="truncate flex-1">{currentTenant?.name ?? 'Select business'}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="w-56">
                <div className="px-2 py-1.5 text-xs font-medium text-muted-foreground">
                    Switch Business
                </div>
                {tenants.map((tenant) => (
                    <DropdownMenuItem
                        key={tenant.id}
                        onClick={() => handleSwitch(tenant.id)}
                        className={cn(
                            'gap-2 cursor-pointer',
                            tenant.id === currentTenant?.id && 'bg-muted'
                        )}
                    >
                        <div className="flex size-5 items-center justify-center rounded bg-muted text-xs font-medium text-muted-foreground">
                            {tenant.name.charAt(0)}
                        </div>
                        <span className="flex-1 truncate">{tenant.name}</span>
                        {tenant.id === currentTenant?.id && (
                            <Check className="size-4 text-primary" />
                        )}
                        {tenant.business_type && (
                            <span className="ml-auto text-xs text-muted-foreground capitalize">
                                {tenant.business_type.replace('_', ' ')}
                            </span>
                        )}
                    </DropdownMenuItem>
                ))}
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild className="gap-2">
                    <a href={create.url()}>
                        <Plus className="size-4 text-muted-foreground" />
                        New Business
                    </a>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
