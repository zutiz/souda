# API Integration Strategy

## Data Fetching Models

The application uses two complementary data fetching models:

| Model | When to Use | Mechanism |
|---|---|---|
| **Inertia-driven** | Page loads, form submissions, navigation | Server returns full page props via Inertia |
| **React Query** | Supplementary data, table pagination, autocomplete, background refresh | Client fetches from API or Inertia routes |

## Inertia-Driven Data (Primary)

Inertia is the primary data transport. On every page visit or form submission, the server returns all data the page needs as props:

```
// Controller
public function index(Request $request)
{
    return Inertia::render('product/ProductIndex', [
        'products' => Product::query()
            ->paginate($request->per_page ?? 25),
        'categories' => fn () => Category::selectOptions(), // lazy
        'brands' => fn () => Brand::selectOptions(),         // lazy
        'filters' => $request->only(['search', 'status', 'category', 'brand']),
    ]);
}
```

```
// Page component receives
const { products, categories, brands, filters } = usePage().props;
```

### Inertia Data Flow

```
User navigates to /products
  → Inertia visit to Laravel route
  → Controller renders Inertia page with props
  → Page component receives typed props
  → Renders data table with initial data
  → User changes page/sort → new Inertia visit (GET with query params)
  → Server returns new page with new data
  → Component re-renders
```

## React Query Data (Supplementary)

Use React Query when data needs to be fetched independently of page navigation:

| Scenario | Example |
|---|---|
| **Table with server-side pagination** | Product list with sorting, filtering, page changes |
| **Autocomplete dropdowns** | Category picker, brand picker |
| **Data that changes independently** | Stock counts, order status |
| **Background polling** | Dashboard stats, notifications |
| **Optimistic mutations** | Toggle product status, bulk delete |

### React Query + Inertia Integration

React Query fetches data by making GET visits to Inertia routes (which return only the data, no layout):

```
// products/index — Inertia controller returns paginated products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
```

```
// Hook
export function useProducts(filters: ProductFilters) {
  return useQuery({
    queryKey: ['products', filters],
    queryFn: () => {
      // Inertia GET returns just the data (no full page)
      return router.get(route('products.index'), filters)
        .then(r => r.props.products);
    },
    placeholderData: keepPreviousData,
  });
}
```

### When NOT to use React Query

- Form submissions (use Inertia `useForm` / `router.post`)
- Page transitions (use Inertia `Link` / `router.visit`)
- Initial page data (already provided by Inertia props)
- Data that should be indexed by search engines (use Inertia SSR)

## Wayfinder Integration

Laravel Wayfinder generates typed route functions. Use them instead of hardcoded URLs:

```
import { index as productsIndex } from '@/actions/.../ProductController';

// In a component
<Link href={productsIndex({ page: 2, sort: 'name:asc' })}>
  Products
</Link>

// In a mutation
router.post(StoreProduct(), form.data, { ... });
```

## Error Handling Strategy

| Error Type | Detection | UI Response |
|---|---|---|
| **Validation errors** | Inertia returns `errors` prop | Show inline field errors + error summary |
| **Authorization (403)** | Inertia redirects to error page | Show "Access denied" page |
| **Not found (404)** | Inertia redirects to error page | Show "Not found" page with back link |
| **Server error (500)** | Inertia error page or Axios interceptor | Show error state with retry button |
| **Network error** | React Query `isError` / Axios error | Show error state with retry button |
| **Timeout** | React Query `isStale` | Show stale data with refresh prompt |

### Error Display Hierarchy

```
Page Level
  └── <ErrorState /> (full page error with retry)
Component Level
  └── DataTable shows error state within its container
Field Level
  └── FormField shows error message below the input
Toast Level
  └── Ephemeral errors (bulk action failed, connection lost)
```

## Cache Invalidation Rules

| Mutation | Invalidates |
|---|---|
| Create product | `['products']` |
| Update product | `['products']`, `['products', id]` |
| Delete product | `['products']` |
| Bulk status change | `['products']` |
| Update stock | `['products']`, `['products', id]`, `['stock']` |
| Upload image | `['products', id]` |
| Create/update category | `['categories']`, `['categories', 'tree']` |
| Create/update brand | `['brands']` |

## File Upload Strategy

- Upload through Inertia (not separate AJAX) to maintain form atomicity
- Show local preview before submission (URL.createObjectURL)
- Track upload progress via Inertia's `onProgress` callback
- Server returns uploaded file URL in the response
- For drag-and-drop zones, use a separate lightweight upload API (optional)

## Request Deduplication

React Query automatically deduplicates requests with the same key. This prevents the same product list from being fetched twice when multiple components mount simultaneously.

## Offline Strategy

- React Query returns cached data when network is unavailable (stale-while-revalidate)
- Mutations are not queued offline (ERP apps require server confirmation)
- Show a "You appear offline" banner when navigator.onLine is false
- Display last-synced timestamp on data tables
