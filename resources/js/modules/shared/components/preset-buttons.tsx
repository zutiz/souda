import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

// Action button presets
interface ActionButtonProps extends React.ComponentProps<typeof Button> {
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
}

// Primary action button (submit, save)
export function PrimaryButton({
    label,
    icon: Icon,
    className,
    ...props
}: ActionButtonProps) {
    return (
        <Button className={cn('gap-2', className)} {...props}>
            {Icon && <Icon className="size-4" />}
            {label}
        </Button>
    );
}

// Secondary action button
export function SecondaryButton({
    label,
    icon: Icon,
    className,
    ...props
}: ActionButtonProps) {
    return (
        <Button variant="secondary" className={cn('gap-2', className)} {...props}>
            {Icon && <Icon className="size-4" />}
            {label}
        </Button>
    );
}

// Outline action button
export function OutlineButton({
    label,
    icon: Icon,
    className,
    ...props
}: ActionButtonProps) {
    return (
        <Button variant="outline" className={cn('gap-2', className)} {...props}>
            {Icon && <Icon className="size-4" />}
            {label}
        </Button>
    );
}

// Ghost action button
export function GhostButton({
    label,
    icon: Icon,
    className,
    ...props
}: ActionButtonProps) {
    return (
        <Button variant="ghost" className={cn('gap-2', className)} {...props}>
            {Icon && <Icon className="size-4" />}
            {label}
        </Button>
    );
}

// Destructive action button (delete)
export function DestructiveButton({
    label,
    icon: Icon,
    className,
    ...props
}: ActionButtonProps) {
    return (
        <Button variant="destructive" className={cn('gap-2', className)} {...props}>
            {Icon && <Icon className="size-4" />}
            {label}
        </Button>
    );
}

// Link button
export function LinkButton({
    label,
    icon: Icon,
    className,
    ...props
}: ActionButtonProps) {
    return (
        <Button variant="link" className={cn('gap-1.5', className)} {...props}>
            {label}
            {Icon && <Icon className="size-4" />}
        </Button>
    );
}

// Icon-only button with tooltip style
interface IconButtonProps extends Omit<ActionButtonProps, 'label'> {
    label: string;
    size?: 'sm' | 'default' | 'lg' | 'icon';
}

export function IconButton({
    label,
    icon: Icon,
    className,
    size = 'icon',
    ...props
}: IconButtonProps) {
    const sizeClasses = {
        sm: 'size-8',
        default: 'size-9',
        lg: 'size-10',
        icon: 'size-8',
    };

    return (
        <Button
            variant="ghost"
            size={size === 'icon' ? 'icon' : 'default'}
            className={cn(
                size !== 'icon' && sizeClasses[size],
                size === 'icon' && 'size-8',
                className
            )}
            aria-label={label}
            title={label}
            {...props}
        >
            {Icon && <Icon className="size-4" />}
        </Button>
    );
}

// Loading button with spinner
interface LoadingButtonProps extends Omit<ActionButtonProps, 'loading'> {
    loading?: boolean;
    loadingText?: string;
}

export function LoadingButton({
    label,
    icon: Icon,
    loading = false,
    loadingText,
    className,
    children,
    ...props
}: LoadingButtonProps) {
    return (
        <Button
            className={cn('gap-2', loading && 'cursor-wait', className)}
            disabled={loading}
            {...props}
        >
            {loading ? (
                <>
                    <svg className="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    {loadingText ?? 'Loading...'}
                </>
            ) : (
                <>
                    {Icon && <Icon className="size-4" />}
                    {children ?? label}
                </>
            )}
        </Button>
    );
}

// Split button (dropdown with action)
interface SplitButtonProps {
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
    options: Array<{
        label: string;
        onClick: () => void;
        icon?: React.ComponentType<{ className?: string }>;
    }>;
    onClick?: () => void;
    className?: string;
}

export function SplitButton({
    label,
    icon: Icon,
    options,
    onClick,
    className,
}: SplitButtonProps) {
    // For simplicity, just show a button (dropdown would need more UI)
    return (
        <div className={cn('flex gap-1', className)}>
            <Button onClick={onClick} className="gap-2 rounded-r-none">
                {Icon && <Icon className="size-4" />}
                {label}
            </Button>
            {options.length > 0 && (
                <Button variant="outline" size="icon" className="rounded-l-none border-l-0">
                    <svg className="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                </Button>
            )}
        </div>
    );
}

// Button groups for related actions
interface ButtonGroupProps {
    children: React.ReactNode;
    className?: string;
}

export function ButtonGroup({ children, className }: ButtonGroupProps) {
    return (
        <div className={cn('inline-flex rounded-md shadow-sm', className)} role="group">
            {children}
        </div>
    );
}

export function ButtonGroupItem({
    children,
    className,
    ...props
}: React.ComponentProps<typeof Button>) {
    return (
        <Button
            variant="outline"
            className={cn(
                'rounded-none first:rounded-l-md last:rounded-r-md border-r-0 last:border-r',
                className
            )}
            {...props}
        >
            {children}
        </Button>
    );
}

// Common action button combinations
interface ActionBarProps {
    primary?: { label: string; onClick: () => void; icon?: React.ComponentType<{ className?: string }> };
    secondary?: { label: string; onClick: () => void; icon?: React.ComponentType<{ className?: string }> };
    cancel?: { label?: string; onClick: () => void };
    className?: string;
}

export function ActionBar({
    primary,
    secondary,
    cancel,
    className,
}: ActionBarProps) {
    return (
        <div className={cn('flex items-center justify-end gap-3', className)}>
            {cancel && (
                <Button variant="ghost" onClick={cancel.onClick}>
                    {cancel.label ?? 'Cancel'}
                </Button>
            )}
            {secondary && (
                <Button
                    variant="outline"
                    onClick={secondary.onClick}
                    className="gap-2"
                >
                    {secondary.icon && <secondary.icon className="size-4" />}
                    {secondary.label}
                </Button>
            )}
            {primary && (
                <Button onClick={primary.onClick} className="gap-2">
                    {primary.icon && <primary.icon className="size-4" />}
                    {primary.label}
                </Button>
            )}
        </div>
    );
}

// Floating action bar (sticky bottom)
export function FloatingActionBar({
    children,
    className,
}: {
    children: React.ReactNode;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'sticky bottom-0 z-20 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 px-6 py-4',
                className
            )}
        >
            <div className="flex items-center justify-end gap-3">
                {children}
            </div>
        </div>
    );
}

// Empty state with action
interface EmptyActionProps {
    icon?: React.ComponentType<{ className?: string }>;
    title: string;
    description?: string;
    action?: { label: string; onClick: () => void; icon?: React.ComponentType<{ className?: string }> };
}

export function EmptyAction({
    icon: Icon,
    title,
    description,
    action,
}: EmptyActionProps) {
    return (
        <div className="flex flex-col items-center justify-center py-12 text-center">
            {Icon && (
                <div className="mb-4 flex size-14 items-center justify-center rounded-full bg-muted">
                    <Icon className="size-7 text-muted-foreground" />
                </div>
            )}
            <h3 className="mb-1 text-lg font-semibold">{title}</h3>
            {description && (
                <p className="mb-4 text-sm text-muted-foreground max-w-sm">{description}</p>
            )}
            {action && (
                <Button onClick={action.onClick} className="gap-2">
                    {action.icon && <action.icon className="size-4" />}
                    {action.label}
                </Button>
            )}
        </div>
    );
}