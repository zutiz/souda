import { router } from '@inertiajs/react';
import { subDays } from 'date-fns';
import { useEffect, useRef, useState } from 'react';
import type { DateRange } from 'react-day-picker';
import { Button } from '@/components/ui/button';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const RANGE_OPTIONS = [
    { value: '7d', label: 'Last 7 days' },
    { value: '30d', label: 'Last 30 days' },
    { value: '90d', label: 'Last 90 days' },
    { value: 'custom', label: 'Custom range' },
];

function toDateString(d: Date): string {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

type DashboardFiltersProps = {
    days: number;
    warehouseId: number | null;
};

export function DashboardFilters({ days, warehouseId }: DashboardFiltersProps) {
    const initialized = useRef(false);
    const [showCustom, setShowCustom] = useState(false);

    const defaultRange = RANGE_OPTIONS.find((r) => {
        const num = parseInt(r.value, 10);
        return !isNaN(num) && num === days;
    });
    const [rangeValue, setRangeValue] = useState(defaultRange?.value ?? '30d');

    const initialRange: DateRange = {
        from: subDays(new Date(), days),
        to: new Date(),
    };
    const [dateRange, setDateRange] = useState<DateRange | undefined>(initialRange);

    useEffect(() => {
        if (!initialized.current) {
            initialized.current = true;
        }
    }, []);

    function applyFilters(params: Record<string, string>) {
        const cleaned = Object.fromEntries(Object.entries(params).filter(([, v]) => v && v !== '30d'));
        router.get('/inventory', cleaned, { preserveState: true, preserveScroll: true, replace: true });
    }

    function onRangeChange(value: string) {
        setRangeValue(value);
        if (value === 'custom') {
            setShowCustom(true);
            if (dateRange?.from && dateRange?.to) {
                applyFilters({
                    days: 'custom',
                    from: toDateString(dateRange.from),
                    to: toDateString(dateRange.to),
                });
            }
            return;
        }

        setShowCustom(false);
        const daysMatch = value.match(/^(\d+)d$/);
        applyFilters({ days: daysMatch ? daysMatch[1] : '30' });
    }

    function onDateRangeChange(range: DateRange | undefined) {
        setDateRange(range);
        if (range?.from && range?.to) {
            applyFilters({
                days: 'custom',
                from: toDateString(range.from),
                to: toDateString(range.to),
            });
        }
    }

    return (
        <div className="flex flex-wrap items-center gap-3">
            <Select value={rangeValue} onValueChange={onRangeChange}>
                <SelectTrigger className="w-[160px]">
                    <SelectValue placeholder="Time range" />
                </SelectTrigger>
                <SelectContent>
                    {RANGE_OPTIONS.map((opt) => (
                        <SelectItem key={opt.value} value={opt.value}>
                            {opt.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>

            {showCustom && (
                <DateRangePicker value={dateRange} onChange={onDateRangeChange} />
            )}

            <Button variant="outline" size="sm" onClick={() => window.location.reload()}>
                Today
            </Button>
        </div>
    );
}
