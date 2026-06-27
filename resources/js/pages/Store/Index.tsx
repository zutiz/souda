import { Head, Link, router } from '@inertiajs/react';
import { Plus, Store as StoreIcon, Settings, ExternalLink } from 'lucide-react';
import type { PaginatedData } from '@/types';
import { create, edit, switchMethod } from '@/routes/stores';

interface Store {
    id: string;
    name: string;
    slug: string;
    code: string;
    email: string | null;
    phone: string | null;
    city: string | null;
    state: string | null;
    country: string | null;
    timezone: string;
    currency: string;
    status: string;
    is_default: boolean;
}

interface Props {
    stores: PaginatedData<Store>;
}

export default function StoreIndex({ stores }: Props) {
    return (
        <>
            <Head title="Stores" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between mb-6">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900">Stores</h1>
                            <p className="mt-1 text-sm text-gray-500">
                                Manage your store locations
                            </p>
                        </div>
                        <Link
                                    href={create.url()}
                            className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            <Plus className="h-4 w-4" />
                            Add Store
                        </Link>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {stores.data.map((store) => (
                            <div
                                key={store.id}
                                className="relative rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow"
                            >
                                <div className="flex items-start justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                            <StoreIcon className="h-5 w-5 text-indigo-600" />
                                        </div>
                                        <div>
                                            <h3 className="text-sm font-medium text-gray-900">
                                                {store.name}
                                            </h3>
                                            <p className="text-xs text-gray-500">{store.code}</p>
                                        </div>
                                    </div>
                                    {store.is_default && (
                                        <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                            Default
                                        </span>
                                    )}
                                </div>

                                <div className="mt-4 space-y-1 text-sm text-gray-500">
                                    {store.city && (
                                        <p>{[store.city, store.state, store.country].filter(Boolean).join(', ')}</p>
                                    )}
                                    <p className="text-xs">
                                        {store.timezone} &middot; {store.currency}
                                    </p>
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                        store.status === 'active'
                                            ? 'bg-green-50 text-green-700'
                                            : store.status === 'paused'
                                            ? 'bg-yellow-50 text-yellow-700'
                                            : 'bg-gray-50 text-gray-700'
                                    }`}>
                                        {store.status}
                                    </span>
                                </div>

                                <div className="mt-4 flex items-center gap-2 border-t border-gray-100 pt-4">
                                    <button
                                        onClick={() => router.post(switchMethod.url(store.id))}
                                        className="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        <ExternalLink className="h-3 w-3" />
                                        Open
                                    </button>
                                    <Link
                                        href={edit.url(store.id)}
                                        className="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-800"
                                    >
                                        <Settings className="h-3 w-3" />
                                        Settings
                                    </Link>
                                </div>
                            </div>
                        ))}

                        {stores.data.length === 0 && (
                            <div className="col-span-full text-center py-12">
                                <StoreIcon className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-semibold text-gray-900">No stores</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Create your first store to get started.
                                </p>
                                <Link
                            href={create.url()}
                                    className="mt-4 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Plus className="h-4 w-4" />
                                    Add Store
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
