import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';

const CHART_COLORS = [
    'var(--chart-1)',
    'var(--chart-2)',
    'var(--chart-3)',
    'var(--chart-4)',
    'var(--chart-5)',
];

const TICK_STYLE = { fontSize: 12, fontFamily: 'var(--font-sans)' };

type DataItem = {
    name: string;
    value: number;
};

type DistributionPieChartProps = {
    data: DataItem[];
    height?: number;
    innerRadius?: number;
    outerRadius?: number;
    loading?: boolean;
    colors?: string[];
};

export function DistributionPieChart({
    data,
    height = 260,
    innerRadius = 60,
    outerRadius = 90,
    loading = false,
    colors = CHART_COLORS,
}: DistributionPieChartProps) {
    if (loading) {
        return (
            <div
                className="flex items-center justify-center rounded-md bg-muted/20"
                style={{ height }}
            >
                <div className="size-6 animate-spin rounded-full border-2 border-muted-foreground border-t-transparent" />
            </div>
        );
    }

    if (data.length === 0) {
        return (
            <div
                className="flex items-center justify-center rounded-md bg-muted/20 text-sm text-muted-foreground"
                style={{ height }}
            >
                No data available.
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height={height}>
            <PieChart>
                <Pie
                    data={data}
                    dataKey="value"
                    nameKey="name"
                    cx="50%"
                    cy="50%"
                    innerRadius={innerRadius}
                    outerRadius={outerRadius}
                    paddingAngle={2}
                    label={({ name, percent }) =>
                        `${name} ${((percent ?? 0) * 100).toFixed(0)}%`
                    }
                    fontSize={12}
                    style={{ fontFamily: 'var(--font-sans)' }}
                >
                    {data.map((_, i) => (
                        <Cell key={i} fill={colors[i % colors.length]} />
                    ))}
                </Pie>
                <Tooltip
                    content={({
                        active,
                        payload,
                    }: {
                        active?: boolean;
                        payload?: Array<{ value: number; name: string }>;
                    }) => {
                        if (!active || !payload?.length) return null;
                        return (
                            <div className="rounded-md border bg-popover px-3 py-2 text-popover-foreground shadow-md">
                                <p className="text-sm font-medium">
                                    {payload[0].name}: {payload[0].value}
                                </p>
                            </div>
                        );
                    }}
                />
            </PieChart>
        </ResponsiveContainer>
    );
}
