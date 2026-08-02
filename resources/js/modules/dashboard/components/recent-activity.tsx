import { Activity, ArrowUpRight, ArrowDownRight, Package, ShoppingCart, AlertCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

type ActivityType = 'order' | 'stock' | 'alert' | 'payment';

type ActivityItem = {
    id: string;
    type: ActivityType;
    title: string;
    description: string;
    time: string;
};

interface RecentActivityProps {
    activities?: ActivityItem[];
    maxItems?: number;
}

function ActivityIcon({ type }: { type: ActivityType }) {
    const iconClasses = 'size-4';
    switch (type) {
        case 'order':
            return <ShoppingCart className={iconClasses} />;
        case 'stock':
            return <Package className={iconClasses} />;
        case 'alert':
            return <AlertCircle className={iconClasses} />;
        case 'payment':
            return <ArrowUpRight className={iconClasses} />;
        default:
            return <Activity className={iconClasses} />;
    }
}

function getActivityStyles(type: ActivityType) {
    switch (type) {
        case 'order':
            return 'bg-primary/10 text-primary';
        case 'stock':
            return 'bg-info/10 text-info';
        case 'alert':
            return 'bg-warning/10 text-warning';
        case 'payment':
            return 'bg-positive/10 text-positive';
        default:
            return 'bg-muted text-muted-foreground';
    }
}

export function RecentActivity({ activities = [], maxItems = 5 }: RecentActivityProps) {
    const displayActivities = activities.slice(0, maxItems);

    return (
        <Card>
            <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                    <CardTitle className="text-base font-medium flex items-center gap-2">
                        <Activity className="size-4 text-muted-foreground" />
                        Recent Activity
                    </CardTitle>
                    <span className="text-xs text-muted-foreground">
                        {displayActivities.length} events
                    </span>
                </div>
            </CardHeader>
            <CardContent className="p-0">
                {displayActivities.length === 0 ? (
                    <div className="px-4 py-8 text-center">
                        <Activity className="mx-auto mb-2 size-8 text-muted-foreground/50" />
                        <p className="text-sm text-muted-foreground">
                            No recent activity
                        </p>
                        <p className="text-xs text-muted-foreground/70 mt-1">
                            Activity will appear here as you work
                        </p>
                    </div>
                ) : (
                    <div className="divide-y divide-border">
                        {displayActivities.map((activity) => (
                            <div
                                key={activity.id}
                                className="flex items-start gap-3 px-4 py-3 hover:bg-muted/50 transition-colors"
                            >
                                <div
                                    className={cn(
                                        'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full',
                                        getActivityStyles(activity.type),
                                    )}
                                >
                                    <ActivityIcon type={activity.type} />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-medium truncate">
                                        {activity.title}
                                    </p>
                                    <p className="text-xs text-muted-foreground truncate">
                                        {activity.description}
                                    </p>
                                </div>
                                <span className="shrink-0 text-xs text-muted-foreground">
                                    {activity.time}
                                </span>
                            </div>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}