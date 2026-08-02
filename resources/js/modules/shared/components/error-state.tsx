import { AlertTriangle, RefreshCw, Home } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type ErrorStateProps = {
    title?: string;
    message?: string;
    onRetry?: () => void;
    onGoHome?: () => void;
    className?: string;
    variant?: 'default' | 'compact' | 'inline';
};

export function ErrorState({
    title = 'Failed to load data',
    message = 'Something went wrong. Please try again.',
    onRetry,
    onGoHome,
    className,
    variant = 'default',
}: ErrorStateProps) {
    const sizeClasses = {
        compact: 'py-8',
        default: 'py-12',
        inline: 'py-4',
    };

    const iconSizeClasses = {
        compact: 'size-12',
        default: 'size-16',
        inline: 'size-8',
    };

    const iconInnerSize = {
        compact: 'size-5',
        default: 'size-8',
        inline: 'size-4',
    };

    return (
        <div className={cn(
            'flex flex-col items-center gap-4 text-center px-4',
            sizeClasses[variant],
            className
        )}>
            <div className={cn(
                'bg-destructive/10 flex items-center justify-center rounded-full',
                iconSizeClasses[variant]
            )}>
                <AlertTriangle className={cn('text-destructive', iconInnerSize[variant])} />
            </div>

            <div className="max-w-sm space-y-1.5">
                <h3 className="text-base font-semibold">{title}</h3>
                {message && (
                    <p className="text-muted-foreground text-sm">{message}</p>
                )}
            </div>

            {(onRetry || onGoHome) && (
                <div className="flex items-center gap-2 mt-1">
                    {onRetry && (
                        <Button variant="outline" onClick={onRetry} className="gap-2">
                            <RefreshCw className="size-4" />
                            Try again
                        </Button>
                    )}
                    {onGoHome && (
                        <Button variant="ghost" onClick={onGoHome} className="gap-2">
                            <Home className="size-4" />
                            Go to dashboard
                        </Button>
                    )}
                </div>
            )}
        </div>
    );
}

// Table-specific error state
export function TableErrorState({
    onRetry,
    className,
}: {
    onRetry?: () => void;
    className?: string;
}) {
    return (
        <div className="rounded-md border">
            <div className={cn('flex flex-col items-center justify-center py-16 px-4', className)}>
                <div className="bg-destructive/10 flex size-12 items-center justify-center rounded-full mb-4">
                    <AlertTriangle className="size-6 text-destructive" />
                </div>
                <h3 className="text-base font-semibold mb-1">Failed to load data</h3>
                <p className="text-muted-foreground text-sm mb-4">
                    There was a problem fetching the data.
                </p>
                {onRetry && (
                    <Button variant="outline" onClick={onRetry} className="gap-2">
                        <RefreshCw className="size-4" />
                        Try again
                    </Button>
                )}
            </div>
        </div>
    );
}
