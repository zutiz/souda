# Table Architecture

## Overview

All data tables use TanStack Table (`@tanstack/react-table`) v8 wrapped by a generic `DataTable` component in `modules/shared/components/`. Tables are fully server-driven: sorting, filtering, and pagination happen on the backend.

```
DataTable (generic wrapper)
├── TableToolbar
│   ├── SearchInput (global search, debounced)
│   ├── FacetedFilter (column-specific multi-select)
│   ├── ColumnToggle (show/hide columns)
│   └── ExportButton (CSV/PDF)
├── Table (shadcn/ui)
│   ├── TableHeader (sortable column headers)
│   ├── TableBody
│   │   ├── Row (with selection checkbox + click handler)
│   │   └── RowDetail (expandable detail panel)
│   └── TableFooter (summary row, optional)
├── TablePagination
│   ├── Page size selector
│   ├── Page navigation (prev/next + page numbers)
│   └── Total count display
└── EmptyState / ErrorState / LoadingSkeleton
```

## Column Definition Pattern

Every module defines columns inline in a `columns` variable or factory function:

```
// columns.ts (colocated with page or component)
import { createColumnHelper } from '@tanstack/react-table';
import { Product } from '../types';

const helper = createColumnHelper<Product>();

export const columns = [
  helper.accessor('name', {
    header: 'Name',
    cell: (info) => <ProductCell product={info.row.original} />,
  }),
  helper.accessor('sku', { header: 'SKU' }),
  helper.accessor('price', {
    header: 'Price',
    cell: (info) => formatCurrency(info.getValue()),
  }),
  helper.accessor('stock', {
    header: 'Stock',
    cell: (info) => <InventoryBadge quantity={info.getValue()} />,
  }),
  helper.accessor('status', {
    header: 'Status',
    cell: (info) => <StatusBadge status={info.getValue()} />,
    filterFn: 'equals',
  }),
  helper.display({
    id: 'actions',
    cell: (info) => <ActionDropdown item={info.row.original} />,
  }),
];
```

## Table State Ownership

| State | Source | Persistence | Why |
|---|---|---|---|
| Page number | URL search param | URL / bookmark | Shareable, back-button |
| Sort column + direction | URL search param | URL | Shareable |
| Search query | URL search param | URL | Shareable |
| Column filters | URL search param | URL | Shareable |
| Selected rows | React useState | Component | Ephemeral |
| Column visibility | React useState | localStorage | User preference |
| Expanded rows | React useState | Component | Ephemeral |
| Page size | React useState | localStorage | User preference |

## Server-Side Integration

All data fetching for tables goes through React Query:

```
URL state (search params)
  │
  ▼
Module hook (e.g., useProducts)
  ├── Reads URL: { page, sort, search, filters }
  ├── Passes to React Query key: ['products', { page, sort, search, filters }]
  └── Fetches via Inertia visit or API call
        │
        ▼
Backend returns: { data: Product[], total, currentPage, perPage }
  │
  ▼
Table renders with new data
```

```
// Hook pattern
function useProducts() {
  const [searchParams] = useSearchParams();
  const page = Number(searchParams.get('page') ?? '1');
  const sort = searchParams.get('sort');
  const search = searchParams.get('search');

  return useQuery({
    queryKey: ['products', { page, sort, search }],
    queryFn: () => router.get('/products', { page, sort, search }),
    placeholderData: keepPreviousData, // preserves last page while loading next
  });
}
```

## Filter Architecture

| Filter Type | UI Component | Behavior |
|---|---|---|
| **Global search** | SearchInput with debounce | Single text input searching name, SKU, barcode |
| **Status** | FacetedFilter (dropdown) | Multi-select: draft, active, archived |
| **Category** | FacetedFilter (dropdown) | Hierarchical select with search |
| **Brand** | Select | Single select |
| **Price range** | Range slider or two inputs | Min/max values |
| **Stock status** | Select | In stock, low stock, out of stock |
| **Date range** | Date range picker | Created/updated date |

## Bulk Actions

Selected rows enable a floating action bar:

```
[■ Selected 3]  [Change Status]  [Delete]  [Export Selected]  [Clear Selection]
```

- Selection state is stored in `useState` on the page component
- Bulk actions submit via Inertia POST to `/products/bulk/{action}`
- On success, React Query invalidates the product list query
- Optimistic UI: remove selected items from cache immediately

## Export Strategy

- Export button triggers a server-side job that generates CSV/Excel
- Inertia download response or temporary download URL
- Show progress notification while job runs
- For small datasets (<1000 rows), generate client-side CSV

## Mobile Adaptation

- On small screens, collapse less important columns into a detail row
- Show a card layout instead of a scrollable table (optional, togglable)
- Filters become a slide-out panel instead of inline
- Action dropdown becomes a full-screen action sheet
