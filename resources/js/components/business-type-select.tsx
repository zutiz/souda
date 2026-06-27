import {
    Store as StoreIcon,
    ShoppingCart, Pill, UtensilsCrossed, Coffee, Cake,
    Scissors, Sparkles, Monitor, Shirt, Palette,
    Hammer, Warehouse, Truck, Sprout, BookOpen,
    type LucideIcon,
} from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const iconMap: Record<string, LucideIcon> = {
    ShoppingCart, Pill, UtensilsCrossed, Coffee, Cake,
    Scissors, Sparkles, Monitor, Shirt, Palette,
    Hammer, Warehouse, Truck, Sprout, BookOpen,
};

export interface BusinessTypeOption {
    id: number;
    slug: string;
    name: string;
    description?: string;
    icon?: string | null;
}

type Props = {
    businessTypes: BusinessTypeOption[];
    value: string;
    onValueChange: (value: string) => void;
    placeholder?: string;
    className?: string;
    tabIndex?: number;
};

export function BusinessTypeSelect({
    businessTypes,
    value,
    onValueChange,
    placeholder = 'Select your industry...',
    className,
    tabIndex,
}: Props) {
    const selected = businessTypes.find((t) => t.slug === value);
    const SelectedIcon = selected ? (iconMap[selected.icon ?? ''] ?? StoreIcon) : StoreIcon;

    return (
        <Select value={value} onValueChange={onValueChange}>
            <SelectTrigger tabIndex={tabIndex} className={className}>
                <SelectValue placeholder={placeholder}>
                    <span className="flex items-center gap-2">
                        <SelectedIcon className="h-4 w-4 text-muted-foreground" />
                        {selected?.name}
                    </span>
                </SelectValue>
            </SelectTrigger>
            <SelectContent>
                {businessTypes.map((type) => {
                    const Icon = iconMap[type.icon ?? ''] ?? StoreIcon;
                    return (
                        <SelectItem key={type.id} value={type.slug}>
                            <span className="flex items-center gap-2">
                                <Icon className="h-4 w-4 text-muted-foreground" />
                                {type.name}
                            </span>
                        </SelectItem>
                    );
                })}
            </SelectContent>
        </Select>
    );
}
