import { type ReactNode, type forwardRef } from 'react';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type FormFieldProps = {
    label?: string;
    description?: string;
    error?: string;
    required?: boolean;
    children: ReactNode;
    className?: string;
    htmlFor?: string;
};

export function FormField({
    label,
    description,
    error,
    required,
    children,
    className,
    htmlFor,
}: FormFieldProps) {
    return (
        <div className={cn('space-y-2', className)}>
            {label && (
                <Label
                    htmlFor={htmlFor}
                    className={cn(
                        error && 'text-destructive',
                        required && "after:ml-1 after:text-destructive after:content-['*']"
                    )}
                >
                    {label}
                </Label>
            )}
            {children}
            {description && !error && (
                <p className="text-xs text-muted-foreground">{description}</p>
            )}
            {error && (
                <p className="text-sm text-destructive flex items-center gap-1">
                    <span className="size-1 rounded-full bg-destructive" />
                    {error}
                </p>
            )}
        </div>
    );
}

// Input wrapper with error state
export function FormInput({
    error,
    className,
    ...props
}: React.ComponentProps<'input'> & { error?: string }) {
    return (
        <input
            {...props}
            className={cn(
                'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                'placeholder:text-muted-foreground',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
                error
                    ? 'border-destructive focus-visible:border-destructive focus-visible:ring-destructive/20 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive'
                    : 'border-input',
                className
            )}
            aria-invalid={error ? 'true' : undefined}
        />
    );
}

// Textarea wrapper with error state
export function FormTextarea({
    error,
    className,
    ...props
}: React.ComponentProps<'textarea'> & { error?: string }) {
    return (
        <textarea
            {...props}
            className={cn(
                'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                'placeholder:text-muted-foreground',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
                error
                    ? 'border-destructive focus-visible:border-destructive focus-visible:ring-destructive/20 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive'
                    : 'border-input',
                className
            )}
            aria-invalid={error ? 'true' : undefined}
        />
    );
}

// Select wrapper with error state
export function FormSelect({
    error,
    className,
    ...props
}: React.ComponentProps<'select'> & { error?: string }) {
    return (
        <select
            {...props}
            className={cn(
                'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
                error
                    ? 'border-destructive focus-visible:border-destructive focus-visible:ring-destructive/20 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive'
                    : 'border-input',
                className
            )}
            aria-invalid={error ? 'true' : undefined}
        >
            {props.children}
        </select>
    );
}

// Checkbox wrapper
export function FormCheckbox({
    label,
    description,
    className,
}: {
    label?: string;
    description?: string;
    className?: string;
} & React.ComponentProps<'input'>) {
    return (
        <div className={cn('flex items-start gap-3', className)}>
            <input
                type="checkbox"
                className="mt-0.5 size-4 rounded border-input text-primary focus:ring-2 focus:ring-primary/20 cursor-pointer"
                {...{}}
            />
            {(label || description) && (
                <div className="grid gap-1">
                    {label && (
                        <label className="text-sm font-medium cursor-pointer">
                            {label}
                        </label>
                    )}
                    {description && (
                        <p className="text-xs text-muted-foreground">{description}</p>
                    )}
                </div>
            )}
        </div>
    );
}