import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

type StatusBadgeProps = {
    status: string;
    size?: 'sm' | 'md';
};

type StatusStyle = {
    bg: string;
    text: string;
};

const statusStyles: Record<string, StatusStyle> = {
    pending: { bg: 'bg-warning/10', text: 'text-warning' },
    confirmed: { bg: 'bg-info/10', text: 'text-info' },
    processing: { bg: 'bg-info/10', text: 'text-info' },
    ready_for_pickup: { bg: 'bg-primary/10', text: 'text-primary' },
    out_for_delivery: { bg: 'bg-warning/10', text: 'text-warning' },
    partially_shipped: { bg: 'bg-info/10', text: 'text-info' },
    shipped: { bg: 'bg-info/10', text: 'text-info' },
    delivered: { bg: 'bg-positive/10', text: 'text-positive' },
    completed: { bg: 'bg-positive/10', text: 'text-positive' },
    cancelled: { bg: 'bg-destructive/10', text: 'text-destructive' },
    refunded: { bg: 'bg-muted', text: 'text-muted-foreground' },
    partially_refunded: { bg: 'bg-muted', text: 'text-muted-foreground' },
    on_hold: { bg: 'bg-muted', text: 'text-muted-foreground' },
    failed: { bg: 'bg-destructive/10', text: 'text-destructive' },
    label_created: { bg: 'bg-info/10', text: 'text-info' },
    picked_up: { bg: 'bg-info/10', text: 'text-info' },
    in_transit: { bg: 'bg-primary/10', text: 'text-primary' },
    delivery_failed: { bg: 'bg-destructive/10', text: 'text-destructive' },
    returned_to_sender: { bg: 'bg-muted', text: 'text-muted-foreground' },
};

export function StatusBadge({ status, size = 'md' }: StatusBadgeProps) {
    const style = statusStyles[status];
    const defaultStyle: StatusStyle = { bg: 'bg-muted', text: 'text-muted-foreground' };

    return (
        <Badge
            variant="outline"
            className={cn(
                'rounded-full font-medium capitalize',
                style?.bg,
                style?.text,
            )}
        >
            {status.replace(/_/g, ' ')}
        </Badge>
    );
}
