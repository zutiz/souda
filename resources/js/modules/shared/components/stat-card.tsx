import { type LucideIcon, ArrowUp, ArrowDown, ArrowRight } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

type TrendDirection = 'up' | 'down' | 'neutral';

interface StatCardProps {
    title: string;
    value: string | number;
    icon?: LucideIcon;
    trend?: number;
    trendLabel?: string;
    variant?: 'default' | 'positive' | 'warning' | 'danger';
    className?: string;
}

function formatTrendValue(value: number): string {
    const absValue = Math.abs(value);
    if (absValue >= 1000) {
        return `${(value / 1000).toFixed(1)}k`;
    }
    return absValue.toLocaleString();
}

function TrendBadge({ value, label }: { value: number; label?: string }) {
    const direction: TrendDirection = value > 0 ? 'up' : value < 0 ? 'down' : 'neutral';
    const isPositive = direction === 'up';
    const isNeutral = direction === 'neutral';

    return (
        <div
            className={cn(
                'inline-flex items-center gap-1 text-xs font-medium',
                isNeutral && 'text-muted-foreground',
                isPositive && 'text-positive',
                !isNeutral && !isPositive && 'text-destructive',
            )}
        >
            {isNeutral ? (
                <ArrowRight className="size-3" />
            ) : isPositive ? (
                <ArrowUp className="size-3" />
            ) : (
                <ArrowDown className="size-3" />
            )}
            <span>{formatTrendValue(value)}%</span>
            {label && <span className="text-muted-foreground ml-1">{label}</span>}
        </div>
    );
}

export function StatCard({
    title,
    value,
    icon: Icon,
    trend,
    trendLabel,
    variant = 'default',
    className,
}: StatCardProps) {
    return (
        <Card
            className={cn(
                'transition-shadow hover:shadow-md',
                variant === 'positive' && 'border-positive/30 bg-positive/5',
                variant === 'warning' && 'border-warning/30 bg-warning/5',
                variant === 'danger' && 'border-destructive/30 bg-destructive/5',
                className,
            )}
        >
            <CardHeader className="flex flex-row items-center justify-between pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                {Icon && (
                    <Icon
                        className={cn(
                            'size-4 text-muted-foreground',
                            variant === 'positive' && 'text-positive',
                            variant === 'warning' && 'text-warning',
                            variant === 'danger' && 'text-destructive',
                        )}
                    />
                )}
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold tracking-tight">{value}</div>
                {trend !== undefined && (
                    <div className="mt-1">
                        <TrendBadge value={trend} label={trendLabel} />
                    </div>
                )}
            </CardContent>
        </Card>
    );
}