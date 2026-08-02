import { Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type LoadingButtonProps = {
    children: React.ReactNode;
    loading?: boolean;
    loadingText?: string;
    className?: string;
} & React.ComponentProps<typeof Button>;

export function LoadingButton({
    children,
    loading = false,
    loadingText,
    className,
    disabled,
    ...props
}: LoadingButtonProps) {
    return (
        <Button
            className={cn('gap-2', loading && 'cursor-wait', className)}
            disabled={loading || disabled}
            {...props}
        >
            {loading && <Loader2 className="size-4 animate-spin" />}
            {loading && loadingText ? loadingText : children}
        </Button>
    );
}

// Icon-only loading button
export function LoadingIconButton({
    loading = false,
    className,
    ...props
}: Omit<LoadingButtonProps, 'children'> & { 'aria-label': string }) {
    return (
        <Button
            className={cn('size-8 p-0', loading && 'cursor-wait', className)}
            disabled={loading}
            {...props}
        >
            {loading ? (
                <Loader2 className="size-4 animate-spin" />
            ) : props.children}
        </Button>
    );
}

// Progress bar component
export function ProgressBar({
    value,
    max = 100,
    variant = 'default',
    size = 'default',
    showLabel = false,
    className,
}: {
    value: number;
    max?: number;
    variant?: 'default' | 'success' | 'warning' | 'error';
    size?: 'sm' | 'default' | 'lg';
    showLabel?: boolean;
    className?: string;
}) {
    const percentage = Math.min(100, Math.max(0, (value / max) * 100));

    const sizeClasses = {
        sm: 'h-1',
        default: 'h-2',
        lg: 'h-3',
    };

    const variantClasses = {
        default: 'bg-primary',
        success: 'bg-green-500',
        warning: 'bg-yellow-500',
        error: 'bg-red-500',
    };

    return (
        <div className={cn('space-y-1.5', className)}>
            {showLabel && (
                <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">Progress</span>
                    <span className="font-medium">{Math.round(percentage)}%</span>
                </div>
            )}
            <div className={cn('w-full rounded-full bg-muted overflow-hidden', sizeClasses[size])}>
                <div
                    className={cn('h-full rounded-full transition-all duration-300', variantClasses[variant])}
                    style={{ width: `${percentage}%` }}
                    role="progressbar"
                    aria-valuenow={value}
                    aria-valuemin={0}
                    aria-valuemax={max}
                />
            </div>
        </div>
    );
}

// Animated loading skeleton
export function LoadingSkeleton({
    className,
    variant = 'text',
    lines = 1,
}: {
    className?: string;
    variant?: 'text' | 'circle' | 'square' | 'card';
    lines?: number;
}) {
    const { cn } = { cn: (c: string) => c };

    const variants = {
        text: 'h-4 rounded',
        circle: 'size-10 rounded-full',
        square: 'h-10 rounded-md',
        card: 'h-32 rounded-lg',
    };

    if (lines > 1) {
        return (
            <div className={cn('space-y-2', className)}>
                {Array.from({ length: lines }).map((_, i) => (
                    <div
                        key={i}
                        className={cn(
                            'bg-muted animate-pulse',
                            variants[variant],
                            i === lines - 1 ? 'w-3/4' : 'w-full'
                        )}
                    />
                ))}
            </div>
        );
    }

    return (
        <div
            className={cn('bg-muted animate-pulse', variants[variant], className)}
        />
    );
}

// Inline spinner for small loading states
export function Spinner({
    size = 'md',
    className,
}: {
    size?: 'sm' | 'md' | 'lg';
    className?: string;
}) {
    const sizeClasses = {
        sm: 'size-3',
        md: 'size-4',
        lg: 'size-6',
    };

    return (
        <Loader2
            className={cn('animate-spin text-muted-foreground', sizeClasses[size], className)}
        />
    );
}

// Full page loading overlay
export function LoadingOverlay({
    message = 'Loading...',
    className,
}: {
    message?: string;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm',
                className
            )}
        >
            <div className="flex flex-col items-center gap-3">
                <Loader2 className="size-8 animate-spin text-primary" />
                <p className="text-sm text-muted-foreground">{message}</p>
            </div>
        </div>
    );
}

// Inline loading indicator (for buttons, inputs, etc.)
export function InlineLoader({
    className,
}: {
    className?: string;
}) {
    return (
        <Loader2 className={cn('size-4 animate-spin', className)} />
    );
}