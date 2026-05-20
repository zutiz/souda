# Component Structure

## Component Hierarchy

```
Page (orchestrator)
├── PageHeader (title, description, breadcrumbs, action buttons)
├── FilterBar (search + filters)
│   └── SearchInput (debounced)
│   └── FacetedFilter (dropdown multi-select)
├── DataTable (data display)
│   ├── TableSkeleton (loading state)
│   ├── TableToolbar (column toggle, export)
│   ├── TablePagination (page navigation)
│   └── EmptyState / ErrorState
├── Form (create/edit)
│   ├── FormSection (grouped fields with heading)
│   ├── FormField (label + input + error)
│   │   ├── Input / Select / DatePicker / etc.
│   │   └── FormDescription / FormMessage
│   └── FormActions (submit + cancel)
├── Dialogs (overlays)
│   ├── ConfirmDialog (delete confirmation)
│   ├── ImageUploadDialog
│   └── VariantEditorDialog
└── Badges / Indicators (inline)
    ├── StatusBadge
    ├── InventoryBadge
    └── PriceDisplay
```

## Component Classification

| Category | Purpose | Location | Examples |
|---|---|---|---|
| **Page** | Route-level orchestrator, composes layout + data + components | `modules/{domain}/pages/` | `product-index.tsx`, `product-create.tsx` |
| **Feature** | Domain-specific business logic + UI | `modules/{domain}/components/` | `product-table.tsx`, `product-form.tsx` |
| **Composite** | Reusable cross-domain patterns | `modules/shared/components/` | `data-table.tsx`, `confirm-dialog.tsx` |
| **Primitive** | Atomic UI elements | `components/ui/` | `button.tsx`, `input.tsx`, `dialog.tsx` |
| **Layout** | Page shell wrappers | `layouts/` | `app-sidebar-layout.tsx` |

## Naming Conventions

| Type | Convention | Example |
|---|---|---|
| Page | `{entity}-{action}.tsx` | `product-index.tsx`, `product-create.tsx` |
| Feature component | `{entity}-{purpose}.tsx` | `product-table.tsx`, `product-status-badge.tsx` |
| Composite | `{purpose}.tsx` | `data-table.tsx`, `confirm-dialog.tsx` |
| Hook | `use-{entity}-{action}.ts` | `use-products.ts`, `use-product-form.ts` |
| Type | `{entity}.ts` | `product.ts`, `variant.ts` |
| Primitive | `{name}.tsx` | `button.tsx`, `input.tsx` |

## Page Component Pattern

Every page component follows this structure:

```
Props received from Inertia (server-rendered data)
  │
  ▼
usePage().props → destructure server data
  │
  ▼
Compose layout (AppLayout with breadcrumbs)
  │
  ▼
Render sections:
  ├── <Head> for page title
  ├── <PageHeader> with title + primary action
  ├── <FilterBar> or <SearchInput> for list pages
  ├── <DataTable> with columns definition
  ├── Suspense boundaries for deferred content
  └── <ConfirmDialog> if destructive actions exist
```

## Data Flow Within Components

```
Page Component
  │
  ├── Server props (usePage) → initial data from Inertia
  │
  ├── Client hooks (useQuery) → supplementary data
  │   └── Returns { data, isLoading, error }
  │
  ├── URL state (useSearchParams) → filters, page, sort
  │
  └── Passes down as props to child components
      ├── Feature components receive typed props
      └── Composites receive generic type parameters
```

## Prop Design Rules

- **Page-to-component**: Pass specific props, never full page props
- **Component-to-component**: Favor composition over prop drilling (children, render props)
- **Primitive components**: Accept standard HTML attributes + `className` for styling extension
- **Composite components**: Accept generic type parameters + specific configuration objects
- **No magic strings**: All variant/state values come from TypeScript enums or string unions

## State Ownership

| State | Owner | Why |
|---|---|---|
| Server data (list, item) | React Query cache | Automatic refetching, deduplication |
| URL state (page, sort, search) | URL search params | Shareable, bookmarkable, back-button |
| Form state | React Hook Form | Per-field validation, dirty tracking |
| UI state (open/close, selected) | Component useState | Ephemeral, no reason to hoist |
| Tenant / Store / User | Inertia shared props | Available on every page load |
| Permissions | React Context | Checked across many components |
