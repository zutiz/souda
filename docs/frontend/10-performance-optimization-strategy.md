# Performance Optimization Strategy

## Performance Budget

| Metric | Target | Measurement |
|---|---|---|
| Time to First Paint (TTFP) | < 1.5s | Lighthouse, WebPageTest |
| Time to Interactive (TTI) | < 3.0s | Lighthouse |
| Largest Contentful Paint (LCP) | < 2.5s | Lighthouse |
| First Input Delay (FID) | < 100ms | Lighthouse, RUM |
| Cumulative Layout Shift (CLS) | < 0.1 | Lighthouse |
| Bundle size (initial) | < 200KB gzip | `vite-bundle-visualizer` |
| Bundle size (total) | < 500KB gzip | `vite-bundle-visualizer` |
| API response time (p95) | < 200ms | Server-side monitoring |
| React Query cache hit rate | > 80% | DevTools |

## 1. Inertia-Level Optimizations

### Deferred Props (Inertia v2)
Non-critical page data loads after the page renders:

```
// Controller: non-critical data deferred
Inertia::render('Products/Index', [
    'products' => Product::paginate(25),        // Critical: loads immediately
    'stats' => Inertia::defer(fn () => [        // Deferred: loads after paint
        'total_products' => Product::count(),
        'low_stock_count' => Product::lowStock()->count(),
        'total_value' => Product::sum('price'),
    ]),
    'categories' => Inertia::defer(fn () =>     // Deferred: loads after paint
        Category::selectOptions()
    ),
]);
```

```
// Page: wrap deferred sections in Suspense
<>
  <ProductTable products={products} />
  <Suspense fallback={<CardSkeleton />}>
    <StatsPanel stats={stats} />
  </Suspense>
</>
```

### Prefetching
Navigation links prefetch data on hover:

```
// NavMain already prefetches on hover (default: true)
<Link href="/products" prefetch>Products</Link>
```

### Form Submissions
Inertia handles form submission without full page reload. Use `preserveScroll` and `preserveState` to avoid unnecessary re-renders:

```
router.put(`/products/${id}`, data, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => showToast('Product updated'),
});
```

## 2. React Query Optimizations

### Smart Stale Times

| Data Type | staleTime | gcTime | Reason |
|---|---|---|---|
| Product list | 30s | 5min | Changes moderately often |
| Single product | 60s | 10min | Edit page needs fresh data |
| Categories dropdown | 5min | 30min | Rarely changes |
| Brands dropdown | 5min | 30min | Rarely changes |
| Product stats | 2min | 10min | Dashboard data |
| Stock counts | 15s | 2min | Changes frequently |

### Keep Previous Data
Use `placeholderData: keepPreviousData` on all paginated queries to prevent layout shift when changing pages:

```
useQuery({
    queryKey: ['products', { page: currentPage }],
    queryFn: () => fetchProducts(currentPage),
    placeholderData: keepPreviousData,
});
```

### Query Invalidation Batching
Batch invalidations to avoid cascade refetches:

```
// Instead of:
queryClient.invalidateQueries({ queryKey: ['products'] });
queryClient.invalidateQueries({ queryKey: ['products', id] });
queryClient.invalidateQueries({ queryKey: ['stats'] });

// Use:
queryClient.invalidateQueries({
    queryKey: ['products'],  // prefix matches both list and detail
    refetchType: 'all',
});
queryClient.invalidateQueries({ queryKey: ['stats'] });
```

### Optimistic Updates
For frequent mutations (status toggle, inline edit), update the cache immediately:

```
useMutation({
    mutationFn: (data) => router.patch(`/products/${data.id}/status`, data),
    onMutate: async (newData) => {
        await queryClient.cancelQueries({ queryKey: ['products'] });
        const previous = queryClient.getQueryData(['products']);
        queryClient.setQueryData(['products'], (old) => ({
            ...old,
            data: old.data.map(p =>
                p.id === newData.id ? { ...p, status: newData.status } : p
            ),
        }));
        return { previous };
    },
    onError: (err, newData, context) => {
        queryClient.setQueryData(['products'], context.previous);
    },
    onSettled: () => {
        queryClient.invalidateQueries({ queryKey: ['products'] });
    },
});
```

## 3. Rendering Optimizations

### React Compiler
React Compiler (babel-plugin-react-compiler) is already configured in vite.config.ts. It automatically memoizes components, eliminating the need for manual `useMemo`/`useCallback`/`React.memo`.

### Component Splitting

| Technique | Application | Benefit |
|---|---|---|
| Lazy-loaded routes | Page-level code splitting per module | Smaller initial bundle |
| Lazy-loaded dialogs | ConfirmDialog, VariantEditor | Don't load until opened |
| Dynamic imports | Rich text editor, chart library | Don't block initial render |
| Suspense boundaries | Deferred sections, lazy components | Graceful loading states |

### List Virtualization
Use `@tanstack/react-virtual` for tables exceeding 500 rows:

```
// In DataTable, detect row count and switch to virtualized mode
// Virtualized mode: only render visible rows + overscan
```

### Debounced Search
Search inputs use the shared `useDebounce` hook to avoid firing API calls on every keystroke:

```
function ProductSearch() {
    const [search, setSearch] = useState('');
    const debouncedSearch = useDebounce(search, 300);

    useEffect(() => {
        if (debouncedSearch) {
            // Fire API call
        }
    }, [debouncedSearch]);
}
```

## 4. Network Optimizations

### Bundle Splitting
Vite automatically code-splits by module. The entry point loads only the current page's code:

```
// app.tsx — auto code-splits per page
resolve: (name) => resolvePageComponent(
    `./pages/${name}.tsx`,
    import.meta.glob('./pages/**/*.tsx'),
),
```

### Image Optimization

| Image Type | Max Size | Format | Loading |
|---|---|---|---|
| Product thumbnail | 150×150px | WebP | lazy |
| Product detail | 800×800px | WebP | eager (above fold) |
| Category image | 400×400px | WebP | lazy |
| Brand logo | 200×100px | SVG/WebP | eager |
| Banner images | 1200×400px | WebP | lazy |

### Preconnect to Origins

```
<!-- In app.blade.php head -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://storage.googleapis.com">  <!-- images -->
```

## 5. UI-Specific Optimizations

### Skeleton Loading
Always show skeleton matching the expected content shape during loading:

```
// Column-matched skeleton — not a generic spinner
<TableSkeleton columns={9} rows={8} />
<CardSkeleton count={3} />
```

### Transition Animations
Use CSS transitions (not JS animations) for performance:

```
/* Tailwind transition classes — hardware accelerated */
transition-colors duration-150  /* color changes */
transition-opacity duration-200 /* fade in/out */
transition-transform duration-200 /* slide, scale */
```

### Reduce Re-Renders
- Keep form state local to form components (don't lift to page)
- Use `useShallow` with Zustand/React Query selectors
- Avoid inline function props in list renders
- Keep list item components as leaf nodes (minimal re-render surface)

## 6. Memory Management

- React Query `gcTime` ensures stale data is garbage collected
- Virtualized lists don't keep DOM nodes for off-screen rows
- Component cleanup via `useEffect` return functions
- Image lazy loading via native `loading="lazy"`
- Dialog components unmount when closed (not just hidden)

## 7. Monitoring

### Client-Side Performance

```
// Vite bundle analysis
npm run analyze

// React DevTools profiling
// Measure component re-renders
// Identify unnecessary re-renders
```

### Server-Side Performance

- Monitor Inertia response times per route
- Track deferred prop load times separately
- Alert on p95 response time > 500ms

## Optimization Priority Matrix

| Effort | Impact | Optimization |
|---|---|---|
| Low | High | Deferred props for stats/charts |
| Low | High | staleTime tuning |
| Low | High | Skeleton loading |
| Low | Medium | Prefetch navigation links |
| Medium | High | React Query optimistic updates |
| Medium | Medium | Image optimization (WebP, sizes) |
| Medium | Medium | Debounced search |
| High | High | List virtualization |
| High | Medium | Code splitting by module |
| High | Low | Bundle analyzer CI check |
