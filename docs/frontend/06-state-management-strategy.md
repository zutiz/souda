# State Management Strategy

## State Classification

All application state falls into one of four categories, each with a specific management strategy:

| Category | Examples | Strategy | Tool |
|---|---|---|---|
| **Server state** | Product list, categories, brands, stock | Cached, auto-refetched, invalidated on mutation | React Query |
| **URL state** | Page number, sort column, search query, filters | Source of truth in URL search params | `useSearchParams` |
| **Form state** | Field values, dirty state, validation errors | Ephemeral, scoped to form lifecycle | React Hook Form |
| **UI state** | Sidebar open, selected tab, modal open | Component-local, short-lived | `useState` / `useReducer` |
| **Shared context** | Tenant, store, user, permissions | Available app-wide, changes infrequently | Inertia shared props + React Context |
| **Page state** | Active filter set, expanded rows | Lives in page component, passed as props | `useState` |

## State Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      Inertia Shared Props                     │
│  tenant, store, user, permissions, notifications             │
│  (available on every page via usePage().props)               │
└───────────────────────┬─────────────────────────────────────┘
                        │
          ┌─────────────┼─────────────┐
          │             │             │
          ▼             ▼             ▼
┌─────────────────┐ ┌──────────┐ ┌──────────────────────┐
│  React Context   │ │  URL     │ │  React Query         │
│  (rare changes)  │ │  State   │ │  (frequent changes)  │
│                  │ │          │ │                      │
│  TenantProvider  │ │ Page     │ │  QueryClient         │
│  StoreProvider   │ │ Sort     │ │  useQuery            │
│  Permission      │ │ Search   │ │  useMutation         │
│    Provider      │ │ Filters  │ │  queryClient         │
└─────────────────┘ └──────────┘ │    .invalidateQueries │
                                 └──────────────────────┘
                                          │
                                 ┌────────┴────────┐
                                 ▼                 ▼
                          ┌──────────┐    ┌──────────────┐
                          │ useState  │    │ react-hook-  │
                          │ (UI)     │    │ form (forms) │
                          └──────────┘    └──────────────┘
```

## React Query Configuration

```
QueryClient default options:
  staleTime: 30_000        // 30s — don't refetch within 30 seconds
  gcTime: 300_000           // 5 min — keep in cache for back navigation
  refetchOnWindowFocus: false  // ERP app, not social media
  retry: 1                 // Don't hammer the API on failure
  placeholderData: keepPreviousData  // Smooth pagination transitions
```

## Query Key Convention

```
['resource']                    → List all
['resource', id]                → Single item
['resource', { filters }]       → Filtered list
['resource', 'stats']           → Aggregate data
['resource', 'dropdown']        → Select options (small subset)
```

Examples:
```
['products']
['products', productId]
['products', { category: '123', status: 'active', page: 2 }]
['products', 'stats']
['categories', 'tree']
```

## Mutation Strategy

```
useMutation({
  mutationFn: (data) => router.post('/products', data),
  onMutate: async (newData) => {
    // 1. Cancel outgoing queries for this list
    await queryClient.cancelQueries({ queryKey: ['products'] });
    // 2. Snapshot previous state for rollback
    const previous = queryClient.getQueryData(['products']);
    // 3. Optimistically update cache
    queryClient.setQueryData(['products'], (old) => optimisticAdd(old, newData));
    return { previous };
  },
  onError: (err, newData, context) => {
    // 4. Rollback on failure
    queryClient.setQueryData(['products'], context.previous);
  },
  onSettled: () => {
    // 5. Always refetch to ensure server consistency
    queryClient.invalidateQueries({ queryKey: ['products'] });
  },
});
```

## URL State Convention

All list pages synchronize their table state with URL search params:

| Param | Example | Purpose |
|---|---|---|
| `page` | `?page=2` | Current page number |
| `per_page` | `?per_page=50` | Page size |
| `sort` | `?sort=name:asc` | Sort column + direction |
| `search` | `?search=black+shirt` | Global search query |
| `status` | `?status=active` | Status filter |
| `category` | `?category=uuid` | Category filter |
| `brand` | `?brand=uuid` | Brand filter |

## State Sync Rule

URL params drive the query → query runs → data renders → user clicks → URL updates → query re-runs.

```
User clicks "Next Page"
  → navigate(`?page=${page + 1}`, { preserveScroll: true })
  → searchParams change
  → React Query key changes (page is in the key)
  → New data fetched
  → Table re-renders
```

## When to Use What

| Situation | Strategy |
|---|---|
| Loading a list of products | React Query `useQuery` |
| Creating a product | React Hook Form + Inertia `router.post` |
| Editing in real-time with preview | React Hook Form + React Query for autocomplete data |
| Updating product status inline | React Query `useMutation` with optimistic update |
| Filtering product list | URL search params |
| Opening a delete confirmation dialog | Component `useState` |
| Switching stores | Inertia shared prop change + full page reload |
| Checking if user can edit products | React Context `usePermissions` |
| Showing unread notification count | Inertia shared prop (polled or pushed) |
