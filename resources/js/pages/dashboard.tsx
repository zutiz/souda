import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { IndustryGreeting, ModuleQuickActions } from '@/modules/dashboard/components/industry-widgets';
import type { BreadcrumbItem } from '@/types';

export default function Dashboard() {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <IndustryGreeting />
                <ModuleQuickActions />
            </div>
        </AppLayout>
    );
}
