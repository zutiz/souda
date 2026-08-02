import { router } from '@inertiajs/react';
import { Check, Plus, Store } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { useStoreContext } from '@/hooks/use-store-context';
import { index, switchMethod } from '@/routes/stores';
import { cn } from '@/lib/utils';

export function StoreSwitcher() {
    const { currentStore, stores } = useStoreContext();

    if (!currentStore && stores.length === 0) {
        return null;
    }

    const handleSwitch = (slug: string) => {
        router.post(switchMethod.url(slug), {}, {
            preserveState: false,
            preserveScroll: true,
        });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className="w-full justify-start gap-2 px-3 py-2 h-auto text-left"
                >
                    {currentStore ? (
                        <>
                            <div className="flex size-6 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground text-xs font-bold">
                                {currentStore.name.charAt(0)}
                            </div>
                            <span className="truncate flex-1 text-sm font-medium">
                                {currentStore.name}
                            </span>
                        </>
                    ) : (
                        <>
                            <Store className="size-4 text-muted-foreground" />
                            <span className="text-sm text-muted-foreground">No store</span>
                        </>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="w-56">
                <div className="px-2 py-1.5 text-xs font-medium text-muted-foreground">
                    Switch Store
                </div>
                {stores.map((store) => (
                    <DropdownMenuItem
                        key={store.id}
                        onClick={() => handleSwitch(store.slug)}
                        className={cn(
                            'gap-2 cursor-pointer',
                            store.id === currentStore?.id && 'bg-muted'
                        )}
                    >
                        <div className={cn(
                            'flex size-5 items-center justify-center rounded text-xs font-bold',
                            store.id === currentStore?.id
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground'
                        )}>
                            {store.name.charAt(0)}
                        </div>
                        <span className="flex-1 truncate text-sm">{store.name}</span>
                        {store.is_default && (
                            <span className="text-xs text-muted-foreground">Default</span>
                        )}
                        {store.id === currentStore?.id && (
                            <Check className="size-4 text-primary" />
                        )}
                    </DropdownMenuItem>
                ))}
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild className="gap-2">
                    <a href={index.url()}>
                        <Plus className="size-4 text-muted-foreground" />
                        Manage Stores
                    </a>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
