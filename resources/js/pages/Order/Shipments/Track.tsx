import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

export default function ShipmentsTrack({ order, shipment }: { order: Record<string, unknown>; shipment: Record<string, unknown> }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Orders', href: '/orders' },
        { label: 'Shipments', href: '/orders/shipments' },
        { label: 'Track', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tracking" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader
                    title="Tracking"
                    description={`Tracking for ${shipment.shipment_number ?? ''}`}
                    actions={
                        <Button variant="outline" asChild>
                            <Link href="/orders/shipments">
                                <ArrowLeft className="size-4" />
                                Back
                            </Link>
                        </Button>
                    }
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Tracking Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2">
                        <p><strong>Courier:</strong> {shipment.courier as string ?? '-'}</p>
                        <p><strong>Tracking Number:</strong> {shipment.tracking_number as string ?? '-'}</p>
                        <p><strong>Status:</strong> {shipment.status as string ?? '-'}</p>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
