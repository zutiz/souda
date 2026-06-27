import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Plus, Building2 } from 'lucide-react';
import { create, switchMethod } from '@/routes/tenant';

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
    const [open, setOpen] = useState(false);

    if (!currentTenant && tenants.length === 0) {
        return null;
    }

    const handleSwitch = (tenantId: string) => {
        setOpen(false);
        router.post(switchMethod.url(), { tenant_id: tenantId }, {
            preserveState: false,
            preserveScroll: true,
        });
    };

    return (
        <div className="relative mb-2">
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="flex w-full items-center gap-x-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800"
                aria-expanded={open}
                aria-haspopup="true"
            >
                <Building2 className="h-4 w-4 shrink-0 text-gray-400" />
                <span className="truncate">{currentTenant?.name ?? 'Select business'}</span>
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
                            {tenants.map((tenant) => (
                                <button
                                    key={tenant.id}
                                    type="button"
                                    onClick={() => handleSwitch(tenant.id)}
                                    className={`flex w-full items-center gap-x-2 rounded-md px-3 py-2 text-sm ${
                                        tenant.id === currentTenant?.id
                                            ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'
                                            : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'
                                    }`}
                                >
                                    <span className="truncate">{tenant.name}</span>
                                    {tenant.business_type && (
                                        <span className="ml-auto text-xs text-gray-400 capitalize">
                                            {tenant.business_type.replace('_', ' ')}
                                        </span>
                                    )}
                                </button>
                            ))}
                        </div>
                        <div className="border-t border-gray-100 dark:border-gray-800 p-1">
                            <a
                                href={create.url()}
                                className="flex w-full items-center gap-x-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                <Plus className="h-5 w-5 text-gray-400" />
                                New Business
                            </a>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
