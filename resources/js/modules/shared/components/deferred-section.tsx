import type { ReactNode } from 'react';
import { Skeleton } from '@/components/ui/skeleton';

type DeferredSectionProps = {
    loading: boolean;
    children: ReactNode;
    fallback?: ReactNode;
};

export function DeferredSection({ loading, children, fallback }: DeferredSectionProps) {
    if (loading) {
        return fallback ?? <Skeleton className="h-32 w-full rounded-xl" />;
    }

    return <>{children}</>;
}
