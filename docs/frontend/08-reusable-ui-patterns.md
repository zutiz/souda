# Reusable UI Patterns

## Design Tokens

All colors, spacing, and typography follow Tailwind CSS v4 theme tokens with ERP-specific semantic extensions:

```
Semantic Color Roles:
  ┌───────────────┬──────────────────────────────────────┬──────────────┐
  │ Token         │ Usage                                │ Tailwind      │
  ├───────────────┼──────────────────────────────────────┼──────────────┤
  │ brand         │ Primary actions, active states       │ bg-brand      │
  │ positive      │ Stock available, payment succeeded   │ text-positive │
  │ warning       │ Low stock, payment pending           │ bg-warning    │
  │ danger        │ Stock depleted, payment failed       │ text-danger   │
  │ info          │ Informational badges                 │ bg-info       │
  │ muted         │ Secondary text, disabled states      │ text-muted    │
  └───────────────┴──────────────────────────────────────┴──────────────┘
```

## Page Layout Pattern

Every page follows this consistent layout:

```
┌──────────────────────────────────────────────────────────────────┐
│  PageHeader                                                       │
│  ┌─────────────┬──────────────────────────────────────────────┐  │
│  │ Breadcrumb  │ [Page Title]              [Primary Action]   │  │
│  │ trail       │ Description text                              │  │
│  └─────────────┴──────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────────────┤
│  FilterBar (list pages only)                                      │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │ Search ───────┐  Status: [▼]  Category: [▼]  (N) results   ││
│  └───────────────┴──────────────────────────────────────────────┘│
├──────────────────────────────────────────────────────────────────┤
│  Content Area                                                     │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │  DataTable / CardGrid / Form / Detail                        ││
│  │                                                              ││
│  └──────────────────────────────────────────────────────────────┘│
├──────────────────────────────────────────────────────────────────┤
│  Footer Actions (form pages only)                                 │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │  [Cancel]                                    [Save Product]  ││
│  └──────────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────────┘
```

## Empty States

Every list view has three states: loading, empty, and populated.

```
// Loading state: Skeleton rows
┌────────────────────────────────────────────┐
│  ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │ ░░░░░░░░ │ │ ░░░░░░░░ │ │ ░░░░░░░░ │   │
│  │ ░░░░░░░░ │ │ ░░░░░░░░ │ │ ░░░░░░░░ │   │
│  └──────────┘ └──────────┘ └──────────┘   │
└────────────────────────────────────────────┘

// Empty state: Illustration + message + action
┌────────────────────────────────────────────┐
│                                            │
│              📦 (icon)                     │
│        No products yet                     │
│   Create your first product to get started │
│        [Create Product]                    │
│                                            │
└────────────────────────────────────────────┘
```

## Loading Patterns

| Pattern | When | What to Show |
|---|---|---|
| **Skeleton** | Initial page load, table data | Column-matched skeleton rows |
| **Spinner** | After initial load, background refresh | Small spinner in the data area |
| **Optimistic** | Mutation with instant UI update | Immediate state change, no spinner |
| **Progress bar** | Form submission with file uploads | Inertia progress bar at top |
| **Shimmer** | Card grid loading | Animated placeholder cards |

## Confirmation Dialogs

Destructive actions use a shared `ConfirmDialog`:

```
┌───────────────────────────────────────────────┐
│  Are you absolutely sure?                      │
│                                               │
│  This will permanently delete the product      │
│  "Black T-Shirt" and all its variants.         │
│  This action cannot be undone.                 │
│                                               │
│  ┌─────────────────┐  ┌────────────────────┐  │
│  │    Cancel        │  │  Delete Product    │  │
│  └─────────────────┘  └────────────────────┘  │
│  (secondary/ghost)      (destructive/red)     │
└───────────────────────────────────────────────┘
```

## Status Badge Conventions

| Status | Color | Icon | Example |
|---|---|---|---|
| Active / Enabled | Green | Circle check | Product status: Active |
| Draft / Pending | Gray | Circle | Product status: Draft |
| Archived / Disabled | Slate | Archive | Product status: Archived |
| Low stock | Yellow/Orange | Alert triangle | Stock: 3 remaining |
| Out of stock | Red | X circle | Stock: 0 |
| In stock | Green | Check circle | Stock: 150 |

## Notification Patterns

| Type | Position | Duration | Trigger |
|---|---|---|---|
| Success toast | Bottom-right | 3s | Form submit, mutation success |
| Error toast | Bottom-right | 8s | Mutation failure, server error |
| Warning toast | Bottom-right | 5s | Low stock, approaching limit |
| Info toast | Bottom-right | 4s | Background sync complete |
| Inline error | In form | Until dismissed | Validation errors |

## Responsive Breakpoints

| Breakpoint | Width | Layout |
|---|---|---|
| Mobile | < 640px | Single column, card layout, slide-out filters |
| Tablet | 640px - 1024px | Two column, compact table, inline filters |
| Desktop | > 1024px | Multi-column, full table, sidebar visible |

## Keyboard Navigation

- Tab through form fields in logical order
- Enter to submit forms
- Escape to close dialogs and dropdowns
- Arrow keys for select dropdowns
- Ctrl+S to save (form pages)
- / to focus search input (list pages)

## Accessibility Patterns

- All form fields have associated labels
- Error messages are announced by screen readers (aria-live="polite")
- Dialogs trap focus and restore on close
- Color is never the sole indicator of status (icon + text + color)
- Data tables have proper aria roles and row announcements
- Image uploads have alt text input
