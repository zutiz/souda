import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    ShoppingCart, Pill, UtensilsCrossed, Coffee, Cake,
    Scissors, Sparkles, Monitor, Shirt, Palette,
    Hammer, Warehouse, Truck, Sprout, BookOpen,
    Store, type LucideIcon,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { PageProps } from '@/types';
import { store } from '@/routes/tenant';

const iconMap: Record<string, LucideIcon> = {
    ShoppingCart, Pill, UtensilsCrossed, Coffee, Cake,
    Scissors, Sparkles, Monitor, Shirt, Palette,
    Hammer, Warehouse, Truck, Sprout, BookOpen,
};

interface BusinessType {
    id: number;
    slug: string;
    name: string;
    description: string;
    icon: string;
}

interface CreateProps extends PageProps {
    businessTypes: BusinessType[];
}

export default function TenantCreate({ businessTypes }: CreateProps) {
    const [name, setName] = useState('');
    const [businessTypeSlug, setBusinessTypeSlug] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);

        if (!name.trim()) {
            setError('Please enter a business name.');
            return;
        }

        if (!businessTypeSlug) {
            setError('Please select a business type.');
            return;
        }

        setSubmitting(true);

        router.post(store.url(), {
            name: name.trim(),
            business_type_slug: businessTypeSlug,
        }, {
            onError: (errors) => {
                setError(Object.values(errors).join(', '));
                setSubmitting(false);
            },
            onFinish: () => setSubmitting(false),
        });
    };

    const selectedType = businessTypes.find((t) => t.slug === businessTypeSlug);
    const SelectedIcon = selectedType ? (iconMap[selectedType.icon] ?? Store) : Store;

    return (
        <>
            <Head title="New Business" />

            <div className="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 py-12 dark:bg-gray-950">
                <div className="mb-8 shrink-0">
                    <Link href="/dashboard">
                        <AppLogo />
                    </Link>
                </div>

                <div className="w-full max-w-md rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900">
                    <h1 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                        Start a New Business
                    </h1>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Add a separate workspace with its own subscription and settings.
                    </p>

                    {error && (
                        <div className="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                            {error}
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                        <div>
                            <label
                                htmlFor="name"
                                className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Business Name
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                placeholder="e.g., Sakura Bakery"
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Business Type
                            </label>
                            <Select value={businessTypeSlug} onValueChange={setBusinessTypeSlug}>
                                <SelectTrigger className="mt-1 w-full">
                                    <SelectValue placeholder="Select business type...">
                                        <span className="flex items-center gap-2">
                                            <SelectedIcon className="h-4 w-4 text-gray-500" />
                                            {selectedType?.name}
                                        </span>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    {businessTypes.map((type) => {
                                        const Icon = iconMap[type.icon] ?? Store;
                                        return (
                                            <SelectItem key={type.id} value={type.slug}>
                                                <span className="flex items-center gap-2">
                                                    <Icon className="h-4 w-4 text-gray-500" />
                                                    {type.name}
                                                </span>
                                            </SelectItem>
                                        );
                                    })}
                                </SelectContent>
                            </Select>
                        </div>

                        {selectedType && (
                            <div className="rounded-lg bg-indigo-50/60 px-3 py-2.5 text-xs text-indigo-600 dark:bg-indigo-900/15 dark:text-indigo-400">
                                A <strong>{selectedType.name.toLowerCase()}</strong> workspace
                                will be created with the {selectedType.name.toLowerCase()} template,
                                including default categories, POS settings, and product fields.
                            </div>
                        )}

                        <div className="flex items-center justify-between pt-2">
                            <Link
                                href="/dashboard"
                                className="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={submitting}
                                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                            >
                                {submitting ? 'Creating...' : 'Create Business'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
