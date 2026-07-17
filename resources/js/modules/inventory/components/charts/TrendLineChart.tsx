import { CartesianGrid, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

const TICK_STYLE = { fontSize: 12, fontFamily: 'var(--font-sans)' };

function ChartTooltipContent({
    active,
    payload,
    label,
    formatter,
}: {
    active?: boolean;
    payload?: Array<{ value: number; name: string; color: string }>;
    label?: string;
    formatter?: (value: number) => string;
}) {
    if (!active || !payload?.length) return null;

    return (
        <div className="rounded-md border bg-popover px-3 py-2 text-popover-foreground shadow-md">
            <p className="mb-1 text-xs text-muted-foreground">{label}</p>
            {payload.map((entry, i) => (
                <p key={i} className="text-sm font-medium" style={{ color: entry.color }}>
                    {entry.name}: {formatter ? formatter(entry.value) : entry.value}
                </p>
            ))}
        </div>
    );
}

type Series = {
    key: string;
    color: string;
    name: string;
};

type TrendLineChartProps = {
    data: Record<string, unknown>[];
    xKey: string;
    series: Series[];
    height?: number;
    loading?: boolean;
    yFormatter?: (value: number) => string;
    gradientId?: string;
};

export function TrendLineChart({
    data,
    xKey,
    series,
    height = 260,
    loading = false,
    yFormatter,
}: TrendLineChartProps) {
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
            <LineChart data={data} margin={{ top: 5, right: 10, left: -10, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
                <XAxis dataKey={xKey} tick={TICK_STYLE} className="fill-muted-foreground" />
                <YAxis tick={TICK_STYLE} className="fill-muted-foreground" tickFormatter={yFormatter} />
                <Tooltip content={<ChartTooltipContent formatter={yFormatter} />} />
                {series.map((s) => (
                    <Line
                        key={s.key}
                        type="monotone"
                        dataKey={s.key}
                        stroke={s.color}
                        strokeWidth={2}
                        dot={false}
                        activeDot={{ r: 4 }}
                        name={s.name}
                    />
                ))}
            </LineChart>
        </ResponsiveContainer>
    );
}
