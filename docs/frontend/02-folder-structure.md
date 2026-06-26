# Frontend Folder Structure

## Top-Level

```
resources/js/
├── app.tsx                            # Inertia bootstrap + provider wiring
├── ssr.tsx                            # Server-side rendering entry
├── modules/                           # Feature domains (self-contained)
│   ├── shared/                        # Cross-module shared code
│   │   ├── components/                # DataTable, Form, FilterPanel, PageHeader
│   │   ├── hooks/                     # useTenant, useDebounce, usePermissions
│   │   ├── providers/                 # QueryClientProvider, TenantProvider
│   │   ├── types/                     # Pagination, Sort, Filter generics
│   │   └── lib/                       # formatCurrency, formatDate, cn
│   ├── product/                       # Product module
│   │   ├── components/                # Module-specific components
│   │   ├── pages/                     # Page components (one per route)
│   │   ├── hooks/                     # Data fetching + business logic hooks
│   │   └── types/                     # Product, Variant, Category, Brand types
│   ├── order/
│   ├── billing/
│   ├── crm/
│   └── inventory/
├── components/                        # App-wide UI primitives
│   ├── ui/                            # shadcn/ui wrappers (button, input, dialog, etc.)
│   ├── app-shell.tsx                  # Shell orchestrator
│   ├── app-sidebar.tsx                # Navigation sidebar
│   ├── nav-main.tsx                   # Main nav group
│   ├── nav-user.tsx                   # User menu
│   ├── breadcrumbs.tsx                # Breadcrumb trail
│   └── ...                            # Other app-level composites
├── layouts/                           # Page layout templates
│   ├── app/                           # Authenticated app layouts
│   │   ├── app-header-layout.tsx
│   │   └── app-sidebar-layout.tsx
│   ├── auth/                          # Authentication layouts
│   └── settings/                      # Settings layouts
├── hooks/                             # App-wide hooks
│   ├── use-appearance.tsx             # Light/dark mode
│   ├── use-mobile.tsx                 # Mobile detection
│   └── use-initials.tsx               # Avatar initials
├── types/                             # Shared TypeScript types
│   ├── auth.ts                        # User, Authentication types
│   ├── navigation.ts                  # NavItem, BreadcrumbItem
│   ├── ui.ts                          # UI component prop types
│   └── index.ts                       # Re-exports
├── lib/
│   └── utils.ts                       # cn(), toUrl()
├── routes/                            # Wayfinder-generated route functions
├── actions/                           # Wayfinder-generated controller actions
└── wayfinder/                         # Wayfinder configuration
```

## Product Module Structure

```
resources/js/modules/product/
├── components/
│   ├── product-table.tsx              # TanStack Table for product listing
│   ├── product-form.tsx               # Create/edit form composition
│   ├── product-card.tsx               # Product card (grid view)
│   ├── product-status-badge.tsx       # Active/Draft/Archived badge
│   ├── product-image-upload.tsx       # Image upload with preview
│   ├── product-variant-manager.tsx    # Variant grid (multi-row form)
│   ├── product-sku-input.tsx          # SKU generator + input
│   ├── product-barcode-input.tsx      # Barcode scanner + input
│   ├── product-price-input.tsx        # Price with currency formatting
│   ├── category-select.tsx            # Hierarchical category selector
│   ├── brand-select.tsx               # Brand dropdown
│   ├── attribute-manager.tsx          # Attribute assignment interface
│   ├── inventory-quantity-badge.tsx   # Stock level indicator
│   ├── product-filter-bar.tsx         # Filter controls for listing
│   └── product-bulk-actions.tsx       # Bulk status/delete bar
│
├── pages/
│   ├── product-index.tsx              # Product listing page
│   ├── product-create.tsx             # Create product page
│   ├── product-edit.tsx               # Edit product page
│   ├── product-show.tsx               # Product detail page
│   └── (variant-pages as needed)
│
├── hooks/
│   ├── use-products.ts                # Product list query
│   ├── use-product.ts                 # Single product query
│   ├── use-product-form.ts            # Form setup + submission
│   ├── use-categories.ts              # Category options
│   ├── use-brands.ts                  # Brand options
│   ├── use-attributes.ts              # Attribute options
│   └── use-product-mutations.ts       # Create/update/delete mutations
│
└── types/
    ├── product.ts                     # Product, ProductFormData
    ├── category.ts                    # Category, CategoryTree
    ├── brand.ts                       # Brand
    ├── variant.ts                     # Variant, VariantFormData
    ├── attribute.ts                   # Attribute, AttributeValue
    └── index.ts                       # Re-exports
```

## Shared Module Structure

```
resources/js/modules/shared/
├── components/
│   ├── data-table.tsx                 # Generic TanStack Table component
│   ├── table-skeleton.tsx             # Skeleton loading rows
│   ├── data-table-pagination.tsx      # Pagination controls
│   ├── data-table-toolbar.tsx         # Search + filter toolbar
│   ├── data-table-faceted-filter.tsx  # Column filter dropdown
│   ├── data-table-column-toggle.tsx   # Column visibility toggle
│   ├── form-field.tsx                 # Generic form field wrapper
│   ├── form-section.tsx               # Form section with heading
│   ├── form-actions.tsx               # Submit + cancel buttons
│   ├── confirm-dialog.tsx             # Delete/confirm modal
│   ├── empty-state.tsx                # Empty state illustration
│   ├── error-state.tsx                # Error state with retry
│   ├── page-header.tsx                # Title + description + actions
│   ├── filter-bar.tsx                 # Generic filter controls
│   ├── search-input.tsx               # Debounced search input
│   └── status-badge.tsx              # Generic status indicator
│
├── hooks/
│   ├── use-debounce.ts                # Debounce hook
│   ├── use-tenant.ts                  # Current tenant context
│   ├── use-permissions.ts             # Permission check hook
│   └── use-pagination.ts              # URL-driven pagination
│
├── providers/
│   └── query-provider.tsx             # React Query client setup
│
├── types/
│   ├── pagination.ts                  # PaginationState, PaginatedResponse
│   ├── sorting.ts                     # SortState, SortingState
│   └── filtering.ts                   # FilterState, FilterConfig
│
└── lib/
    ├── formatters.ts                  # Currency, date, number formatting
    └── validators.ts                  # Shared Zod schemas
```

## Key Principles

- **One component per file**: Every component is a single file with a PascalCase name
- **Colocation**: Pages, components, and hooks that belong together sit in the same module
- **No barrel exports from modules**: Modules export nothing — they are internal to the feature
- **Shared code moves to `shared/`**: When three or more modules need the same pattern, extract
- **Types mirror backend DTOs**: ProductFormData matches the backend FormRequest structure
