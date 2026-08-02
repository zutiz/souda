import { useState, useEffect, useCallback } from 'react';
import { Filter, Plus, Trash2, Check } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

type SavedView = {
    id: string;
    name: string;
    columns: string[];
    filters?: Record<string, unknown>;
    sort?: { column: string; direction: 'asc' | 'desc' };
};

interface DataTableViewsProps {
    views: SavedView[];
    currentViewId?: string;
    onSaveView?: (name: string, columns: string[], filters?: Record<string, unknown>) => void;
    onDeleteView?: (viewId: string) => void;
    onApplyView?: (viewId: string) => void;
    availableColumns: { id: string; label: string }[];
    className?: string;
}

export function DataTableViews({
    views,
    currentViewId,
    onSaveView,
    onDeleteView,
    onApplyView,
    availableColumns,
    className,
}: DataTableViewsProps) {
    const [saveDialogOpen, setSaveDialogOpen] = useState(false);
    const [newViewName, setNewViewName] = useState('');

    const handleSaveView = useCallback(() => {
        if (!newViewName.trim() || !onSaveView) return;

        // Save current visible columns
        const visibleColumns = availableColumns
            .filter((col) => document.querySelector(`[data-column-id="${col.id}"]`) !== null)
            .map((col) => col.id);

        onSaveView(newViewName.trim(), visibleColumns);
        setNewViewName('');
        setSaveDialogOpen(false);
    }, [newViewName, onSaveView, availableColumns]);

    const currentView = views.find((v) => v.id === currentViewId);

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm" className="h-8 gap-1">
                        <Filter className="size-4" />
                        {currentView ? (
                            <>
                                <span className="hidden sm:inline">{currentView.name}</span>
                                <Check className="size-3 ml-1" />
                            </>
                        ) : (
                            <span className="hidden sm:inline">Views</span>
                        )}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" className="w-48">
                    <DropdownMenuItem
                        onClick={() => setSaveDialogOpen(true)}
                        className="gap-2"
                    >
                        <Plus className="size-4" />
                        Save current view
                    </DropdownMenuItem>

                    {views.length > 0 && (
                        <>
                            <DropdownMenuSeparator />
                            <div className="px-2 py-1.5 text-xs font-medium text-muted-foreground">
                                Saved Views
                            </div>
                            {views.map((view) => (
                                <DropdownMenuItem
                                    key={view.id}
                                    onClick={() => onApplyView?.(view.id)}
                                    className="gap-2"
                                >
                                    <span className="flex-1">{view.name}</span>
                                    {view.id === currentViewId && (
                                        <Check className="size-3 text-primary" />
                                    )}
                                    {onDeleteView && (
                                        <button
                                            className="ml-2 rounded p-0.5 hover:bg-muted"
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                onDeleteView(view.id);
                                            }}
                                        >
                                            <Trash2 className="size-3 text-muted-foreground" />
                                        </button>
                                    )}
                                </DropdownMenuItem>
                            ))}
                        </>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>

            <Dialog open={saveDialogOpen} onOpenChange={setSaveDialogOpen}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Save View</DialogTitle>
                        <DialogDescription>
                            Save the current column visibility and filters as a view for quick access.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4 py-4">
                        <div className="space-y-2">
                            <Label htmlFor="view-name">View Name</Label>
                            <Input
                                id="view-name"
                                value={newViewName}
                                onChange={(e) => setNewViewName(e.target.value)}
                                placeholder="e.g., Active Products"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setSaveDialogOpen(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleSaveView} disabled={!newViewName.trim()}>
                            Save View
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

// Hook to manage table view state
export function useDataTableViews(tableId: string) {
    const [views, setViews] = useState<SavedView[]>([]);
    const [currentViewId, setCurrentViewId] = useState<string | undefined>();

    // Load saved views from localStorage
    useEffect(() => {
        const saved = localStorage.getItem(`data-table-views-${tableId}`);
        if (saved) {
            try {
                setViews(JSON.parse(saved));
            } catch {
                // Invalid JSON, ignore
            }
        }
    }, [tableId]);

    const saveView = useCallback((name: string, columns: string[], filters?: Record<string, unknown>) => {
        const newView: SavedView = {
            id: `${Date.now()}`,
            name,
            columns,
            filters,
        };
        const updatedViews = [...views, newView];
        setViews(updatedViews);
        localStorage.setItem(`data-table-views-${tableId}`, JSON.stringify(updatedViews));
        setCurrentViewId(newView.id);
    }, [views, tableId]);

    const deleteView = useCallback((viewId: string) => {
        const updatedViews = views.filter((v) => v.id !== viewId);
        setViews(updatedViews);
        localStorage.setItem(`data-table-views-${tableId}`, JSON.stringify(updatedViews));
        if (currentViewId === viewId) {
            setCurrentViewId(undefined);
        }
    }, [views, tableId, currentViewId]);

    return { views, currentViewId, saveView, deleteView };
}