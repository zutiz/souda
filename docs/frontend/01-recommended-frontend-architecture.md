# Recommended Frontend Architecture

## System Overview

Feature-based, modular frontend architecture for the Product module within a multi-tenant SaaS ERP. The frontend is a React SPA delivered via Inertia.js v2, styled with Tailwind CSS v4 and shadcn/ui primitives.

```
┌──────────────────────────────────────────────────────────────────────┐
│                        Inertia.js v2 SPA                             │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │                    App Shell (Tenant-aware)                   │   │
│  │  ┌─────────┐  ┌──────────────────────────────────────────┐   │   │
│  │  │ Sidebar │  │              Content Area                 │   │   │
│  │  │ (Nav)   │  │  ┌────────────────────────────────────┐  │   │   │
│  │  │         │  │  │  Page Header (Title + Breadcrumbs) │  │   │   │
│  │  │         │  │  ├────────────────────────────────────┤  │   │   │
│  │  │         │  │  │  Feature Components                 │  │   │   │
│  │  │         │  │  │  ┌────────┐ ┌────────┐ ┌────────┐ │  │   │   │
│  │  │         │  │  │  │ Data   │ │ Form   │ │ Charts │ │  │   │   │
│  │  │         │  │  │  │Table   │ │        │ │        │ │  │   │   │
│  │  │         │  │  │  └────────┘ └────────┘ └────────┘ │  │   │   │
│  │  └─────────┘  │  └────────────────────────────────────┘  │   │   │
│  │               │  Footer (notifications, status)          │   │   │
│  └───────────────┴──────────────────────────────────────────┘   │   │
└──────────────────────────────────────────────────────────────────────┘
```

## Layer Architecture

```
resources/js/
│
├── modules/                          # Feature-based domain modules
│   ├── product/                      # Product module (self-contained)
│   ├── order/                        # Order module
│   ├── billing/                      # Billing module
│   ├── crm/                          # CRM module
│   └── inventory/                    # Inventory module
│
├── components/                       # App-wide shared UI primitives
│   └── ui/                           # shadcn/ui Radix wrappers
│
├── layouts/                          # App shell layouts
├── hooks/                            # App-wide hooks
├── types/                            # Shared TypeScript types
├── lib/                              # Utilities
├── routes/                           # Wayfinder-generated routes
└── actions/                          # Wayfinder-generated actions
```

## Architectural Principles

### 1. Feature-Based Organization
- Each module owns its pages, components, hooks, types, and utilities
- Modules import only from `modules/shared/` or `components/ui/`, never from sibling modules
- The `modules/shared/` directory provides cross-cutting composites (DataTable, Form, FilterPanel)

### 2. Tenant-Aware Every Layer
- Every page component receives tenant context via Inertia shared props
- API requests include tenant scope implicitly through Inertia's request pipeline
- UI components display tenant-scoped data (logo, branding, store name)

### 3. Store-Aware UI
- Product data is always scoped to the current store
- Store selection in the header filters all product queries
- Product forms include store_id as a hidden field (set from context)

### 4. Strict Typing
- Every API response has a corresponding TypeScript type in the module's `types/`
- Form schemas (Zod) share types with API DTOs
- Table columns are strongly typed via TanStack Table generics

### 5. Separation of Concerns
- Pages orchestrate: compose layout, data fetching, and feature components
- Components render: present data, handle user interactions, emit events
- Hooks manage: data fetching, form state, side effects
- Types define: contracts between layers

### 6. Server-Driven UI
- Primary data flow is server-to-client via Inertia props
- React Query augments for data-heavy views (tables, infinite scroll)
- Form submissions go through Inertia (progressive enhancement, no API duplication)

## Data Flow

```
User Interaction
      │
      ▼
Feature Component
      │
      ├── Simple action → Inertia router (visit, post, put, delete)
      │                    └── Server responds with new page props
      │
      ├── Data fetching  → React Query (useQuery/useMutation)
      │                    └── Cache managed client-side
      │
      └── Form submit    → Inertia useForm
                           └── Validation errors come back as props
```

## Rendering Model

- **Page transitions**: Full Inertia visits (no full page reload, but component remount)
- **Data refreshes**: React Query background refetching (stale-while-revalidate)
- **Optimistic updates**: React Query `onMutate` for instant UI response
- **Deferred data**: Inertia v2 deferred props for non-critical sections (stats, charts)
- **Preloading**: Inertia prefetch on hover for nav links
