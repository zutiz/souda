import { Head } from '@inertiajs/react';
import axios from 'axios';
import {
    ShoppingCart,
    Pill,
    UtensilsCrossed,
    Coffee,
    Cake,
    Scissors,
    Sparkles,
    Monitor,
    Shirt,
    Palette,
    Hammer,
    Warehouse,
    Sprout,
    BookOpen,
    type LucideIcon,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';

type BusinessType = {
    id: number;
    slug: string;
    name: string;
    description: string;
    icon: string | null;
};

type Props = {
    businessTypes: BusinessType[];
};

const iconMap: Record<string, LucideIcon> = {
    ShoppingCart,
    Pill,
    UtensilsCrossed,
    Coffee,
    Cake,
    Scissors,
    Sparkles,
    Monitor,
    Shirt,
    Palette,
    Hammer,
    Warehouse,
    Sprout,
    BookOpen,
};

export default function BusinessTypeSelector({ businessTypes }: Props) {
    const [selected, setSelected] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    async function handleContinue() {
        if (!selected) return;
        setLoading(true);
        await axios.post('/onboarding/select-type', {
            business_type_slug: selected,
        });
        window.location.href = '/onboarding/provision';
    }

    return (
        <AuthLayout
            title="What type of business?"
            description="Choose your industry to set up your workspace automatically."
        >
            <Head title="Choose Business Type" />

            <div className="grid gap-3 sm:grid-cols-2">
                {businessTypes.map((type) => {
                    const Icon = type.icon && iconMap[type.icon]
                        ? iconMap[type.icon]
                        : null;

                    return (
                        <Card
                            key={type.id}
                            className={`cursor-pointer transition-all hover:border-primary/50 ${
                                selected === type.slug
                                    ? 'border-primary ring-2 ring-primary/20'
                                    : ''
                            }`}
                            onClick={() => setSelected(type.slug)}
                        >
                            <CardHeader className="flex flex-row items-center gap-3 p-4">
                                {Icon && (
                                    <div className="flex size-10 shrink-0 items-center justify-center rounded-md bg-primary/10">
                                        <Icon className="size-5 text-primary" />
                                    </div>
                                )}
                                <div>
                                    <CardTitle className="text-base">{type.name}</CardTitle>
                                    {type.description && (
                                        <CardDescription className="mt-0.5 text-xs">
                                            {type.description}
                                        </CardDescription>
                                    )}
                                </div>
                            </CardHeader>
                        </Card>
                    );
                })}
            </div>

            <Button
                className="mt-6 w-full"
                size="lg"
                disabled={!selected || loading}
                onClick={handleContinue}
            >
                {loading && <Spinner />}
                {selected ? 'Continue' : 'Select a business type'}
            </Button>
        </AuthLayout>
    );
}
