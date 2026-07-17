type HealthGaugeProps = {
    score: number;
    grade: string;
    size?: number;
    strokeWidth?: number;
};

const GRADE_COLORS: Record<string, string> = {
    healthy: 'stroke-emerald-500 text-emerald-700',
    fair: 'stroke-amber-500 text-amber-700',
    critical: 'stroke-red-500 text-red-700',
};

const GRADE_BG: Record<string, string> = {
    healthy: 'text-emerald-200',
    fair: 'text-amber-200',
    critical: 'text-red-200',
};

const GRADE_LABELS: Record<string, string> = {
    healthy: 'Good',
    fair: 'Needs Attention',
    critical: 'Critical',
};

export function HealthGauge({ score, grade, size = 140, strokeWidth = 12 }: HealthGaugeProps) {
    const radius = (size - strokeWidth) / 2;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (score / 100) * circumference;

    return (
        <div className="flex flex-col items-center gap-2">
            <svg width={size} height={size} className="transform -rotate-90">
                <circle
                    cx={size / 2}
                    cy={size / 2}
                    r={radius}
                    fill="none"
                    strokeWidth={strokeWidth}
                    className={GRADE_BG[grade] ?? 'text-muted'}
                />
                <circle
                    cx={size / 2}
                    cy={size / 2}
                    r={radius}
                    fill="none"
                    strokeWidth={strokeWidth}
                    strokeDasharray={circumference}
                    strokeDashoffset={offset}
                    strokeLinecap="round"
                    className={GRADE_COLORS[grade] ?? 'stroke-muted-foreground'}
                />
            </svg>
            <div className="flex flex-col items-center -mt-2">
                <span className={`text-3xl font-bold ${GRADE_COLORS[grade]?.split(' ')[1] ?? 'text-muted-foreground'}`}>
                    {score}
                </span>
                <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                    {GRADE_LABELS[grade] ?? grade}
                </span>
            </div>
        </div>
    );
}
