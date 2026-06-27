import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ExternalLink, Edit, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { index, edit, destroy, switchMethod } from '@/routes/stores';

interface Store {
    id: string;
    name: string;
    slug: string;
    code: string;
    email: string | null;
    phone: string | null;
    addressLine1: string | null;
    addressLine2: string | null;
    city: string | null;
    state: string | null;
    postalCode: string | null;
    country: string | null;
    timezone: string;
    currency: string;
    locale: string;
    status: string;
    isDefault: boolean;
    sortOrder: number;
    businessHours: Record<string, unknown> | null;
    config: Record<string, unknown> | null;
    posSettings: Record<string, unknown> | null;
}

interface Props {
    store: Store;
}

export default function StoreShow({ store }: Props) {
    const [confirmDelete, setConfirmDelete] = useState(false);

    function handleDelete() {
        router.delete(destroy.url(store.id), {
            onSuccess: () => setConfirmDelete(false),
        });
    }

    return (
        <>
            <Head title={store.name} />

            <div className="py-6">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link
                            href={index.url()}
                            className="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-800"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back to Stores
                        </Link>
                    </div>

                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="flex items-start justify-between">
                            <div>
                                <h1 className="text-2xl font-semibold text-gray-900">{store.name}</h1>
                                <p className="text-sm text-gray-500">{store.code}</p>
                            </div>
                            <div className="flex items-center gap-2">
                                <button
                                    onClick={() => router.post(switchMethod.url(store.id))}
                                    className="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <ExternalLink className="h-3 w-3" />
                                    Open Store
                                </button>
                                <Link
                                    href={edit.url(store.id)}
                                    className="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    <Edit className="h-3 w-3" />
                                    Edit
                                </Link>
                            </div>
                        </div>

                        <div className="mt-6 grid grid-cols-2 gap-4">
                            <div>
                                <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</h3>
                                <span className={`mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                    store.status === 'active'
                                        ? 'bg-green-50 text-green-700'
                                        : store.status === 'paused'
                                        ? 'bg-yellow-50 text-yellow-700'
                                        : 'bg-gray-50 text-gray-700'
                                }`}>
                                    {store.status}
                                </span>
                            </div>
                            <div>
                                <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider">Default</h3>
                                <p className="mt-1 text-sm text-gray-900">{store.isDefault ? 'Yes' : 'No'}</p>
                            </div>
                            <div>
                                <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider">Timezone</h3>
                                <p className="mt-1 text-sm text-gray-900">{store.timezone}</p>
                            </div>
                            <div>
                                <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</h3>
                                <p className="mt-1 text-sm text-gray-900">{store.currency}</p>
                            </div>
                            {store.email && (
                                <div>
                                    <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</h3>
                                    <p className="mt-1 text-sm text-gray-900">{store.email}</p>
                                </div>
                            )}
                            {store.phone && (
                                <div>
                                    <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</h3>
                                    <p className="mt-1 text-sm text-gray-900">{store.phone}</p>
                                </div>
                            )}
                        </div>

                        {(store.addressLine1 || store.city) && (
                            <div className="mt-6">
                                <h3 className="text-xs font-medium text-gray-500 uppercase tracking-wider">Address</h3>
                                <p className="mt-1 text-sm text-gray-900">
                                    {[store.addressLine1, store.addressLine2].filter(Boolean).join(', ')}
                                </p>
                                <p className="text-sm text-gray-900">
                                    {[store.city, store.state, store.postalCode, store.country].filter(Boolean).join(', ')}
                                </p>
                            </div>
                        )}

                        <div className="mt-6 border-t border-gray-100 pt-4">
                            <button
                                onClick={() => setConfirmDelete(true)}
                                className="inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-800"
                            >
                                <Trash2 className="h-3 w-3" />
                                Delete Store
                            </button>
                        </div>
                    </div>

                    {confirmDelete && (
                        <div className="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                            <p className="text-sm text-red-800">
                                Are you sure you want to delete this store? This action cannot be undone.
                            </p>
                            <div className="mt-3 flex items-center gap-2">
                                <button
                                    onClick={handleDelete}
                                    className="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                                >
                                    Delete
                                </button>
                                <button
                                    onClick={() => setConfirmDelete(false)}
                                    className="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
