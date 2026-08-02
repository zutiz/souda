import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { IndustryGreeting, ModuleQuickActions } from '@/modules/dashboard/components/industry-widgets';
import { QuickStats } from '@/modules/dashboard/components/quick-stats';
import { RecentActivity } from '@/modules/dashboard/components/recent-activity';
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
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <IndustryGreeting />
                <QuickStats />
                <ModuleQuickActions />
                <div className="grid gap-6 lg:grid-cols-2">
                    <RecentActivity />
                </div>
            </div>
        </AppLayout>
    );
}
