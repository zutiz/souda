# Product Page Breakdown

## Page Inventory

| Route | Page Component | Purpose |
|---|---|---|
| `/products` | `product-index.tsx` | Product listing with search, filter, sort, bulk actions |
| `/products/create` | `product-create.tsx` | New product form |
| `/products/{id}` | `product-show.tsx` | Product detail view |
| `/products/{id}/edit` | `product-edit.tsx` | Edit existing product |
| `/categories` | (in separate module) | Category management |
| `/brands` | (in separate module) | Brand management |

---

## 1. Product Index Page (`product-index.tsx`)

### Purpose
Primary product browsing and management interface. Search, filter, sort, and perform bulk actions.

### Section Breakdown

```
ProductIndex
├── PageHeader
│   ├── Title: "Products"
│   ├── Description: "Manage your product catalog"
│   ├── Breadcrumbs: Home > Products
│   └── Primary Action: [Create Product] button
│
├── FilterBar
│   ├── SearchInput (debounced, searches name + SKU + barcode)
│   ├── StatusFilter (multi-select: active, draft, archived)
│   ├── CategoryFilter (dropdown with hierarchy)
│   ├── BrandFilter (dropdown)
│   └── ViewToggle (table view / card grid view)
│
├── BulkActionBar (visible when rows selected)
│   ├── Selection count: "3 selected"
│   ├── [Change Status] dropdown
│   ├── [Delete] button → ConfirmDialog
│   ├── [Export Selected] button
│   └── [Clear] link
│
├── DataTable
│   ├── Columns:
│   │   ├── Selection checkbox
│   │   ├── Image (thumbnail)
│   │   ├── Name (linked to detail page)
│   │   ├── SKU
│   │   ├── Category
│   │   ├── Price (formatted currency)
│   │   ├── Stock (badge with color)
│   │   ├── Status (badge)
│   │   └── Actions (dropdown: edit, duplicate, archive, delete)
│   ├── Sorted by: created_at DESC (default)
│   └── Row click → navigate to product detail
│
├── TablePagination
│   ├── Page size: 25 / 50 / 100
│   ├── Total count display
│   └── Previous / Next with page numbers
│
└── EmptyState (when no products)
    ├── Icon
    ├── "No products yet"
    ├── "Create your first product to get started"
    └── [Create Product] button
```

### States

| State | Condition | Behavior |
|---|---|---|
| Loading | Initial fetch | Skeleton rows (8 rows × 9 columns) |
| Empty | No products exist | Empty state illustration + CTA |
| Filtered empty | No results match filters | "No products match your search" + clear filters link |
| Error | Network/server failure | Error state with retry button |
| Populated | Products exist | Full data table |

---

## 2. Product Create Page (`product-create.tsx`)

### Purpose
Full product creation form with all fields, variant management, and image upload.

### Section Breakdown

```
ProductCreate
├── PageHeader
│   ├── Title: "Create Product"
│   ├── Description: "Add a new product to your catalog"
│   └── Breadcrumbs: Home > Products > Create
│
├── Form (scrollable, multi-section)
│   │
│   ├── Section: General Information
│   │   ├── Product Name (text input, required)
│   │   ├── Description (rich text editor, optional)
│   │   ├── Category (hierarchical select, optional)
│   │   ├── Brand (dropdown select, optional)
│   │   └── Status (radio group: draft/active)
│   │
│   ├── Section: Pricing
│   │   ├── Price (number input with currency, required)
│   │   ├── Compare Price (number input, optional — for sale display)
│   │   ├── Cost Price (number input, optional — for profit calculation)
│   │   └── Tax Class (select, optional)
│   │
│   ├── Section: Inventory
│   │   ├── SKU (text input with auto-generate button)
│   │   ├── Barcode (text input, optional)
│   │   ├── Track Stock (switch toggle)
│   │   ├── Quantity (number input, visible when track stock on)
│   │   ├── Low Stock Threshold (number input)
│   │   └── Warehouse (select, multi-warehouse support)
│   │
│   ├── Section: Shipping
│   │   ├── Weight (number input, kg/lb)
│   │   ├── Dimensions (3 number inputs: L × W × H)
│   │   └── Free Shipping (switch)
│   │
│   ├── Section: Images
│   │   ├── Drag-and-drop upload zone
│   │   ├── Image previews with reorder (drag)
│   │   ├── Set as primary (star icon)
│   │   └── Remove (trash icon)
│   │
│   ├── Section: Variants (optional)
│   │   ├── [Add Variant Group] button
│   │   ├── For each group:
│   │   │   ├── Attribute selector (e.g., Size, Color)
│   │   │   └── Values (multi-select, e.g., S, M, L)
│   │   └── Generated variant grid:
│   │       ├── SKU | Barcode | Price | Quantity | Image
│   │       └── Inline editing per row
│   │
│   ├── Section: Attributes
│   │   ├── Dynamic key-value pairs
│   │   ├── [Add Attribute] button
│   │   └── Each row: Attribute (select) + Value (input)
│   │
│   ├── Section: SEO (collapsible)
│   │   ├── Meta Title (text input)
│   │   ├── Meta Description (textarea)
│   │   └── URL Slug (auto-generated from name, editable)
│   │
│   └── Section: Organization (collapsible)
│       ├── Tags (tag input with autocomplete)
│       └── Collections/Groups (multi-select)
│
├── FormActions (sticky footer)
│   ├── [Save as Draft] (secondary, left)
│   ├── [Cancel] (ghost)
│   └── [Create Product] (primary, right)
│
└── UnsavedChangesDialog (on navigate away)
```

### Form Architecture

The form uses Inertia's `useForm` hook for state management and submission. Before sending to the server, `mapFormToPayload()` converts camelCase form fields to snake_case:

| Form Field | Server Key | Notes |
|------------|-----------|-------|
| `price` | `base_price` | ×100 (cents) |
| `categoryId` | `category_id` | — |
| `isTaxable` | `tax_inclusive` | — |
| `trackStock` | `track_inventory` | — |
| `freeShipping` | `free_shipping` | — |
| `type` | `type` | hardcoded `'simple'` in current payload |

- `router.post()` / `router.put()` are called **inline** (not assigned to a variable) to preserve `this` binding required by Inertia's internal `visit()`.
- React Compiler auto-memoizes component code; `useWatch()` hook is used instead of `form.watch()` method to ensure field reactivity (hooks are recognized as reactive by the compiler, methods are not).

### Form Complexity
- 8 sections, ~30+ fields
- Nested field arrays for variants
- Conditional field visibility (track stock toggle)
- Dynamic attribute rows
- File upload handling

---

## 3. Product Detail Page (`product-show.tsx`)

### Purpose
Read-only view of a single product with all its data, variants, stock, and history.

### Section Breakdown

```
ProductShow
├── PageHeader
│   ├── Title: Product name
│   ├── Status badge
│   ├── Stock badge
│   ├── Breadcrumbs: Home > Products > Product Name
│   └── Actions: [Edit] [Duplicate] [Archive/Activate] [Delete]
│
├── ProductOverview (top card)
│   ├── Primary image (large)
│   ├── Image gallery (thumbnails)
│   ├── Price display (current + compare)
│   ├── SKU + Barcode
│   └── Category + Brand (linked)
│
├── Grid: 2-column layout
│   │
│   ├── Left Column:
│   │   ├── Description (rendered HTML)
│   │   └── Attributes table (key: value)
│   │
│   └── Right Column:
│       ├── Pricing Summary (cost, margin, profit)
│       ├── Stock Summary (total, reserved, available)
│       └── Shipping Details (weight, dimensions)
│
├── Variants Table (if variants exist)
│   ├── Image | SKU | Barcode | Price | Stock | Status
│   └── Click row → variant detail / edit
│
├── Inventory History (scrollable, deferred loaded)
│   ├── Date | Type | Quantity | Reference | User
│   └── Paginated table
│
└── Audit Log (scrollable, deferred loaded)
    ├── Date | Action | User | Changes
    └── Paginated table
```

---

## 4. Product Edit Page (`product-edit.tsx`)

### Purpose
Edit existing product. Same form as create but pre-populated with current values.

### Differences from Create
- Form title: "Edit Product" with product name
- Same form sections, pre-filled
- Additional section: "Change History" summary at top
- Save button: "Save Changes" instead of "Create Product"
- Cancel navigates back to product detail
- Unsaved changes warning on navigate away

---

## 5. Variant Management (Reusable Component)

`<ProductVariantManager />` is used in both create and edit forms:

```
ProductVariantManager
├── Header
│   ├── Title: "Variants"
│   ├── Description: "Add size, color, or other variations"
│   └── [Add Variant Group] button
│
├── Variant Groups
│   └── Per group:
│       ├── Attribute selector (Size, Color, Material, etc.)
│       ├── Values multi-select (S, M, L or Red, Blue, etc.)
│       └── [Generate Variants] button
│
├── Generated Variants Table
│   ├── Checkbox (select for bulk edit)
│   ├── Image (small thumbnail)
│   ├── SKU (editable)
│   ├── Barcode (editable)
│   ├── Price (editable, with bulk set option)
│   ├── Cost Price (editable)
│   ├── Quantity (editable)
│   ├── Weight (editable)
│   └── Actions (duplicate, delete)
│
├── Bulk Edit Bar (when variants selected)
│   ├── [Set Price] → modal with value
│   ├── [Set Quantity] → modal with value
│   └── [Delete Selected] → confirm
│
└── Empty State (when no groups added)
    └── "Add a variant group to generate product variations"
```

---

## 6. Deleting Products

Handled through a `ConfirmDialog` on both index (bulk) and detail pages:

```
ConfirmDialog
├── Title: Delete Product(s)?
├── Body: "This will permanently delete [N] product(s) and all associated data. This action cannot be undone."
├── Cancel button (secondary)
└── Delete button (destructive, red)
```

- Single delete: Redirect to index on success
- Bulk delete: Stay on index, show success toast, refetch list
- Soft delete with option to restore from archive
