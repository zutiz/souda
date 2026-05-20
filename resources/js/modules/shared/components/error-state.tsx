import { AlertTriangle, RefreshCw } from 'lucide-react';
import { Button } from '@/components/ui/button';

type ErrorStateProps = {
    title?: string;
    message?: string;
    onRetry?: () => void;
};

export function ErrorState({
    title = 'Failed to load data',
    message = 'Something went wrong. Please try again.',
    onRetry,
}: ErrorStateProps) {
    return (
        <div className="flex flex-col items-center gap-3 py-16 text-center">
            <div className="bg-destructive/10 flex size-16 items-center justify-center rounded-full">
                <AlertTriangle className="text-destructive size-8" />
            </div>
            <div className="max-w-sm space-y-1">
                <p className="font-medium">{title}</p>
                <p className="text-muted-foreground text-sm">{message}</p>
            </div>
            {onRetry && (
                <Button variant="outline" onClick={onRetry} className="mt-2 gap-2">
                    <RefreshCw className="size-4" />
                    Try again
                </Button>
            )}
        </div>
    );
}
