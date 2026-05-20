import { SearchIcon, XIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { useDebounce } from '@/modules/shared/hooks/use-debounce';
import { cn } from '@/lib/utils';

type SearchInputProps = {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    debounceMs?: number;
    className?: string;
};

export function SearchInput({
    value,
    onChange,
    placeholder = 'Search...',
    debounceMs = 300,
    className,
}: SearchInputProps) {
    const [local, setLocal] = useState(value);
    const debounced = useDebounce(local, debounceMs);

    useEffect(() => {
        setLocal(value);
    }, [value]);

    useEffect(() => {
        if (debounced !== value) {
            onChange(debounced);
        }
    }, [debounced]);

    return (
        <div className={cn('relative', className)}>
            <SearchIcon className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" />
            <Input
                value={local}
                onChange={(e) => setLocal(e.target.value)}
                placeholder={placeholder}
                className="h-8 w-[150px] pl-8 pr-8 lg:w-[250px]"
            />
            {local && (
                <Button
                    variant="ghost"
                    size="icon"
                    className="absolute top-1/2 right-1 size-6 -translate-y-1/2"
                    onClick={() => {
                        setLocal('');
                        onChange('');
                    }}
                >
                    <XIcon className="size-3" />
                </Button>
            )}
        </div>
    );
}
