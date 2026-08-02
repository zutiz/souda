import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { StatusBadge } from '@/modules/shared/components/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

export default function ReturnsShow({ return: returnData }: { return: Record<string, unknown> }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Orders', href: '/orders' },
        { label: 'Returns', href: '/orders/returns' },
        { label: `#${returnData.id ?? ''}`, href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Return #${returnData.id ?? ''}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader
                    title={`Return #${returnData.id ?? ''}`}
                    description={returnData.reason as string}
                    actions={
                        <Button variant="outline" asChild>
                            <Link href="/orders/returns">
                                <ArrowLeft className="size-4" />
                                Back
                            </Link>
                        </Button>
                    }
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2">
                        <p><strong>Reason:</strong> {returnData.reason as string}</p>
                        <p><strong>Status:</strong> <StatusBadge status={(returnData.status as 'pending' | 'completed' | 'cancelled') ?? 'pending'} label={returnData.status as string} /></p>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
