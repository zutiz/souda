import { router } from '@inertiajs/react';
import { useState } from 'react';
import { useStoreContext } from '@/hooks/use-store-context';
import { index, switchMethod } from '@/routes/stores';

export function StoreSwitcher() {
    const { currentStore, stores } = useStoreContext();
    const [open, setOpen] = useState(false);

    if (!currentStore && stores.length === 0) {
        return null;
    }

    const handleSwitch = (slug: string) => {
        setOpen(false);
        router.post(switchMethod.url(slug), {}, {
            preserveState: false,
            preserveScroll: true,
        });
    };

    return (
        <div className="relative">
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="flex w-full items-center gap-x-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800"
                aria-expanded={open}
                aria-haspopup="true"
            >
                {currentStore ? (
                    <>
                        <span className="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-600 text-xs font-bold text-white">
                            {currentStore.name.charAt(0)}
                        </span>
                        <span className="truncate">{currentStore.name}</span>
                    </>
                ) : (
                    <span className="text-gray-500">No store selected</span>
                )}
                <svg className="ml-auto h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clipRule="evenodd" />
                </svg>
            </button>

            {open && (
                <>
                    <div
                        className="fixed inset-0 z-10"
                        onClick={() => setOpen(false)}
                        aria-hidden="true"
                    />
                    <div className="absolute left-0 z-20 mt-1 w-full rounded-lg bg-white dark:bg-gray-900 shadow-lg ring-1 ring-gray-900/5 dark:ring-gray-700">
                        <div className="p-1">
                            {stores.map((store) => (
                                <button
                                    key={store.id}
                                    type="button"
                                    onClick={() => handleSwitch(store.slug)}
                                    className={`flex w-full items-center gap-x-2 rounded-md px-3 py-2 text-sm ${
                                        store.id === currentStore?.id
                                            ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'
                                            : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'
                                    }`}
                                >
                                    <span className={`flex h-5 w-5 items-center justify-center rounded text-xs font-bold ${
                                        store.id === currentStore?.id
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                    }`}>
                                        {store.name.charAt(0)}
                                    </span>
                                    <span className="truncate">{store.name}</span>
                                    {store.is_default && (
                                        <span className="ml-auto text-xs text-gray-400">Default</span>
                                    )}
                                </button>
                            ))}
                        </div>
                        <div className="border-t border-gray-100 dark:border-gray-800 p-1">
                            <a
                                href={index.url()}
                                className="flex w-full items-center gap-x-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                                </svg>
                                Manage Stores
                            </a>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
