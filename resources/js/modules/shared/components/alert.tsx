import { type ReactNode } from 'react';
import { AlertCircle, AlertTriangle, CheckCircle, Info, X } from 'lucide-react';
import { cn } from '@/lib/utils';

type AlertVariant = 'default' | 'success' | 'warning' | 'error' | 'info';

const alertStyles: Record<AlertVariant, string> = {
    default: 'bg-muted border-muted-foreground/20',
    success: 'bg-green-50 border-green-200 dark:bg-green-950 dark:border-green-900',
    warning: 'bg-yellow-50 border-yellow-200 dark:bg-yellow-950 dark:border-yellow-900',
    error: 'bg-red-50 border-red-200 dark:bg-red-950 dark:border-red-900',
    info: 'bg-blue-50 border-blue-200 dark:bg-blue-950 dark:border-blue-900',
};

const alertIconStyles: Record<AlertVariant, string> = {
    default: 'text-muted-foreground',
    success: 'text-green-600 dark:text-green-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    error: 'text-red-600 dark:text-red-400',
    info: 'text-blue-600 dark:text-blue-400',
};

const alertIcons: Record<AlertVariant, React.ComponentType<{ className?: string }>> = {
    default: Info,
    success: CheckCircle,
    warning: AlertTriangle,
    error: AlertCircle,
    info: Info,
};

type AlertProps = {
    variant?: AlertVariant;
    title?: string;
    description?: string;
    children?: ReactNode;
    onDismiss?: () => void;
    className?: string;
    icon?: React.ComponentType<{ className?: string }>;
};

export function Alert({
    variant = 'default',
    title,
    description,
    children,
    onDismiss,
    className,
    icon,
}: AlertProps) {
    const Icon = icon ?? alertIcons[variant];

    return (
        <div
            className={cn(
                'relative flex gap-3 rounded-lg border p-4 text-sm',
                alertStyles[variant],
                className
            )}
            role="alert"
        >
            <Icon className={cn('size-5 shrink-0 mt-0.5', alertIconStyles[variant])} />
            <div className="flex-1 space-y-1.5">
                {title && (
                    <p className={cn(
                        'font-medium',
                        variant !== 'default' && 'text-foreground'
                    )}>
                        {title}
                    </p>
                )}
                {(description || children) && (
                    <div className="text-muted-foreground">
                        {description && <p>{description}</p>}
                        {children}
                    </div>
                )}
            </div>
            {onDismiss && (
                <button
                    onClick={onDismiss}
                    className="absolute right-3 top-3 rounded-sm p-0.5 opacity-60 hover:opacity-100 transition-opacity"
                    aria-label="Dismiss"
                >
                    <X className="size-4" />
                </button>
            )}
        </div>
    );
}

// Compact inline alert for form validation
export function FieldAlert({
    message,
    className,
}: {
    message: string;
    className?: string;
}) {
    return (
        <p
            className={cn(
                'text-sm text-destructive flex items-center gap-1.5',
                className
            )}
            role="alert"
        >
            <AlertCircle className="size-3.5 shrink-0" />
            {message}
        </p>
    );
}

// Success alert with checkmark
export function SuccessAlert({
    title,
    description,
    onDismiss,
    className,
}: {
    title: string;
    description?: string;
    onDismiss?: () => void;
    className?: string;
}) {
    return (
        <Alert
            variant="success"
            title={title}
            description={description}
            onDismiss={onDismiss}
            className={className}
        />
    );
}

// Warning alert
export function WarningAlert({
    title,
    description,
    onDismiss,
    className,
}: {
    title: string;
    description?: string;
    onDismiss?: () => void;
    className?: string;
}) {
    return (
        <Alert
            variant="warning"
            title={title}
            description={description}
            onDismiss={onDismiss}
            className={className}
        />
    );
}

// Error alert
export function ErrorAlert({
    title,
    description,
    onDismiss,
    className,
}: {
    title: string;
    description?: string;
    onDismiss?: () => void;
    className?: string;
}) {
    return (
        <Alert
            variant="error"
            title={title}
            description={description}
            onDismiss={onDismiss}
            className={className}
        />
    );
}

// Info alert
export function InfoAlert({
    title,
    description,
    onDismiss,
    className,
}: {
    title: string;
    description?: string;
    onDismiss?: () => void;
    className?: string;
}) {
    return (
        <Alert
            variant="info"
            title={title}
            description={description}
            onDismiss={onDismiss}
            className={className}
        />
    );
}