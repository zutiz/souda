import { Bar, BarChart, CartesianGrid, Cell, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

const CHART_COLORS = [
    'var(--chart-1)',
    'var(--chart-2)',
    'var(--chart-3)',
    'var(--chart-4)',
    'var(--chart-5)',
];

const TICK_STYLE = { fontSize: 12, fontFamily: 'var(--font-sans)' };

type BarDef = {
    key: string;
    color: string;
    name: string;
};

type ComparisonBarChartProps = {
    data: Record<string, unknown>[];
    xKey: string;
    bars: BarDef[];
    height?: number;
    loading?: boolean;
    yFormatter?: (value: number) => string;
    barSize?: number;
};

export function ComparisonBarChart({
    data,
    xKey,
    bars,
    height = 260,
    loading = false,
    yFormatter,
    barSize = 20,
}: ComparisonBarChartProps) {
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
                No data available for this period.
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height={height}>
            <BarChart data={data} margin={{ top: 5, right: 10, left: -10, bottom: 0 }} barSize={barSize}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
                <XAxis dataKey={xKey} tick={TICK_STYLE} className="fill-muted-foreground" />
                <YAxis tick={TICK_STYLE} className="fill-muted-foreground" tickFormatter={yFormatter} />
                <Tooltip
                    content={({
                        active,
                        payload,
                        label,
                    }: {
                        active?: boolean;
                        payload?: Array<{ value: number; name: string }>;
                        label?: string;
                    }) => {
                        if (!active || !payload?.length) return null;
                        return (
                            <div className="rounded-md border bg-popover px-3 py-2 text-popover-foreground shadow-md">
                                <p className="mb-1 text-xs text-muted-foreground">{label}</p>
                                {payload.map((entry, i) => (
                                    <p key={i} className="text-sm font-medium">
                                        {entry.name}: {yFormatter ? yFormatter(entry.value) : entry.value}
                                    </p>
                                ))}
                            </div>
                        );
                    }}
                />
                {bars.length === 1 ? (
                    <Bar dataKey={bars[0].key} name={bars[0].name} radius={[4, 4, 0, 0]}>
                        {data.map((_, i) => (
                            <Cell key={i} fill={CHART_COLORS[i % CHART_COLORS.length]} />
                        ))}
                    </Bar>
                ) : (
                    bars.map((bar) => (
                        <Bar
                            key={bar.key}
                            dataKey={bar.key}
                            fill={bar.color}
                            name={bar.name}
                            radius={[4, 4, 0, 0]}
                        />
                    ))
                )}
            </BarChart>
        </ResponsiveContainer>
    );
}
