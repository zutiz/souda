# SOUDA — UI/UX Design Agent Guide

This file is for AI coding agents working on frontend UI/UX. It defines the complete design system, component standards, theming architecture, navigation rules, accessibility requirements, and responsive behavior for the entire SOUDA platform.

---

## Project Identity

- **App**: SOUDA — multi-tenant, multi-vertical business management platform
- **Frontend**: React 19 + Inertia.js v2 + Tailwind CSS v4 + shadcn/ui
- **TypeScript**: Strict mode enabled
- **Design Goal**: Production-grade SaaS UI comparable to Shopify Admin, Stripe Dashboard, Linear, Notion

---

## Global Design Agent Guardrails

### NEVER
- Hardcode colors — use CSS variables/tailwind tokens only
- Create one-off components without checking existing patterns
- Duplicate UI patterns already in `resources/js/components/ui/` or `resources/js/modules/shared/`
- Break existing business logic — UI changes only
- Rename routes unnecessarily — keep backend contracts intact
- Remove functionality — preserve all features
- Use inconsistent component patterns — follow established patterns
- Hardcode spacing values — use the design token scale
- Use inline styles — use Tailwind classes or CSS variables
- Create components without considering accessibility

### ALWAYS
- Follow shadcn/ui component patterns exactly
- Reuse existing components in `resources/js/components/ui/` and `resources/js/modules/shared/`
- Create reusable components when beneficial (extract to shared when used 2+ times)
- Use CSS variables for theming (never hardcode theme values)
- Maintain keyboard navigation and focus management
- Ensure WCAG 2.2 AA accessibility compliance
- Keep responsive behavior at all breakpoints
- Run `npm run build` after major changes to verify no errors
- Use Lucide React icons exclusively (already configured)
- Follow the existing component API patterns (props, variants, composition)

---

## Architecture Overview

```
┌──────────────────────────────────────────────────────────────────────┐
│                    SOUDA Design System                               │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │   Colors     │  │  Typography  │  │   Spacing    │  │  Radius  │ │
│  │   Tokens     │  │    Tokens    │  │    Scale     │  │   Scale  │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │   Shadows    │  │    Icons     │  │    Motion    │  │  Layout  │ │
│  │    Scale     │  │   Library    │  │   Guidelines │  │   Rules  │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                     Component Library                                │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │    Forms     │  │    Tables    │  │    Cards     │  │ Dialogs  │ │
│  │              │  │              │  │              │  │          │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │   Drawers    │  │   Toasts     │  │    Badges    │  │ Buttons  │ │
│  │              │  │              │  │              │  │          │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                    Theme Engine                                      │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │     Logo     │  │   Primary    │  │   Accent     │  │  Sidebar │ │
│  │              │  │    Color     │  │    Color     │  │   Style  │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │    Border    │  │     Font     │  │    Mode      │  │  Density │ │
│  │    Radius    │  │   Family     │  │ (Dark/Light) │  │ (Compact)│ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Phase 1 — Design System Tokens

### 1.1 Color System

**Philosophy**: Use oklch for perceptual uniformity. All colors are CSS variables in `resources/css/app.css`.

**Base Color Palette:**

```css
/* Light Mode */
--background: oklch(1 0 0);           /* Page background */
--foreground: oklch(0.145 0 0);       /* Primary text */

/* Brand Colors */
--primary: oklch(0.205 0 0);          /* Primary actions, links */
--primary-foreground: oklch(0.985 0 0); /* Text on primary */

/* Secondary */
--secondary: oklch(0.97 0 0);         /* Secondary elements */
--secondary-foreground: oklch(0.205 0 0);

/* Muted */
--muted: oklch(0.97 0 0);             /* Subdued backgrounds */
--muted-foreground: oklch(0.556 0 0); /* Secondary text */

/* Accent */
--accent: oklch(0.97 0 0);            /* Highlights */
--accent-foreground: oklch(0.205 0 0);

/* Destructive */
--destructive: oklch(0.577 0.245 27.325); /* Errors, danger */
--destructive-foreground: oklch(0.985 0 0);

/* Borders & Rings */
--border: oklch(0.922 0 0);
--input: oklch(0.922 0 0);
--ring: oklch(0.708 0 0);
```

**Status Colors (Semantic):**

```css
/* Success - use .text-positive utility class */
--positive: oklch(0.627 0.194 149.214);  /* Green - stock in, paid, delivered */
--positive-foreground: oklch(0.985 0 0);

/* Warning - use .text-warning utility class */
--warning: oklch(0.769 0.188 70.08);     /* Yellow/Orange - low stock, pending */
--warning-foreground: oklch(0.205 0 0);

/* Info - use .text-info utility class */
--info: oklch(0.546 0.245 229.331);      /* Blue - processing, info */
--info-foreground: oklch(0.985 0 0);
```

**Chart Colors:**

```css
--chart-1: Primary brand color
--chart-2: Secondary brand color
--chart-3: Accent brand color
--chart-4: Muted brand color
--chart-5: Secondary muted
```

**Dark Mode:**

```css
.dark {
    --background: oklch(0.145 0 0);
    --foreground: oklch(0.985 0 0);
    --primary: oklch(0.922 0 0);
    --primary-foreground: oklch(0.205 0 0);
    /* ... inverted with richer values */
}
```

**Industry-Specific Brand Colors (Tenant Customizable):**

| Industry | Primary Color | Use Case |
|----------|---------------|----------|
| Restaurant | Orange/Warm | Brand, buttons, highlights |
| Pharmacy | Blue/Clean | Trust, clinical feel |
| Salon | Purple/Luxury | Premium, soft |
| Electronics | Dark/Blue | Tech, sharp |
| Grocery | Green/Fresh | Fresh, natural |
| Fashion | Black/Elegant | Luxury, minimal |

**Usage Rules:**
- Never use raw hex colors in components — always use CSS variables or Tailwind utilities
- Text colors: `text-foreground`, `text-muted-foreground`, `text-primary`
- Backgrounds: `bg-background`, `bg-muted`, `bg-secondary`
- Borders: `border-border`, `border-input`
- Status: `text-positive`, `text-warning`, `text-destructive`, `text-info`

### 1.2 Typography

**Font Stack:**
```css
--font-sans: 'Instrument Sans', system-ui, -apple-system, sans-serif;
--font-mono: 'JetBrains Mono', 'Fira Code', monospace;
```

**Type Scale:**

| Token | Size | Line Height | Use Case |
|-------|------|-------------|----------|
| `--text-xs` | 0.75rem (12px) | 1rem | Labels, badges, helper text |
| `--text-sm` | 0.875rem (14px) | 1.25rem | Secondary text, table cells |
| `--text-base` | 1rem (16px) | 1.5rem | Body text, form inputs |
| `--text-lg` | 1.125rem (18px) | 1.75rem | Card titles, section headers |
| `--text-xl` | 1.25rem (20px) | 1.75rem | Page titles |
| `--text-2xl` | 1.5rem (24px) | 2rem | Page headers, dashboard titles |
| `--text-3xl` | 1.875rem (30px) | 2.25rem | Large statistics |
| `--text-4xl` | 2.25rem (36px) | 2.5rem | Hero numbers, KPIs |

**Font Weights:**
- `font-normal` (400): Body text
- `font-medium` (500): Labels, secondary headings
- `font-semibold` (600): Headings, important UI
- `font-bold` (700): KPI numbers, primary actions

**Usage Rules:**
- Page titles: `text-2xl font-semibold`
- Section headers: `text-lg font-semibold`
- Card titles: `text-base font-medium`
- Body text: `text-sm`
- Helper text: `text-xs text-muted-foreground`

### 1.3 Spacing Scale

**Base Scale (Tailwind defaults):**
- `1` = 0.25rem (4px)
- `2` = 0.5rem (8px)
- `3` = 0.75rem (12px)
- `4` = 1rem (16px)
- `5` = 1.25rem (20px)
- `6` = 1.5rem (24px)
- `8` = 2rem (32px)
- `10` = 2.5rem (40px)
- `12` = 3rem (48px)
- `16` = 4rem (64px)

**Usage Guidelines:**

| Context | Spacing |
|---------|---------|
| Between form fields | `space-y-4` (16px) |
| Between form sections | `space-y-6` (24px) |
| Card padding | `p-4` (16px) or `p-6` (24px) |
| Table cell padding | `px-3 py-2` (12px horizontal, 8px vertical) |
| Page content margin | `p-6` in main content area |
| Sidebar item padding | `px-3 py-2` |
| Dialog padding | `p-6` |
| Card gap in grid | `gap-4` (16px) |

### 1.4 Border Radius

**Scale:**

| Token | Value | Use Case |
|-------|-------|----------|
| `--radius-sm` | 0.375rem (6px) | Small elements: badges, chips |
| `--radius-md` | 0.5rem (8px) | Inputs, small cards |
| `--radius-lg` | 0.625rem (10px) | Cards, dialogs, buttons |
| `--radius-xl` | 0.75rem (12px) | Large cards, modals |
| `--radius-2xl` | 1rem (16px) | Full-width cards |
| `--radius-full` | 9999px | Avatars, pills |

**Usage Rules:**
- Buttons: `rounded-lg` (default), `rounded-md` (small variant)
- Cards: `rounded-lg` or `rounded-xl`
- Inputs: `rounded-md`
- Badges: `rounded-full` or `rounded-sm`
- Dialogs: `rounded-xl`
- Drawers: `rounded-l-lg` (left side only)

### 1.5 Shadows

**Scale:**

```css
--shadow-sm: 0 1px 2px 0 oklch(0 0 0 / 0.05);
--shadow: 0 1px 3px 0 oklch(0 0 0 / 0.1), 0 1px 2px -1px oklch(0 0 0 / 0.1);
--shadow-md: 0 4px 6px -1px oklch(0 0 0 / 0.1), 0 2px 4px -2px oklch(0 0 0 / 0.1);
--shadow-lg: 0 10px 15px -3px oklch(0 0 0 / 0.1), 0 4px 6px -4px oklch(0 0 0 / 0.1);
--shadow-xl: 0 20px 25px -5px oklch(0 0 0 / 0.1), 0 8px 10px -6px oklch(0 0 0 / 0.1);
```

**Usage Rules:**
- Cards: `shadow-sm` (default), `shadow-md` (elevated)
- Dropdowns/Menus: `shadow-lg`
- Dialogs/Modals: `shadow-xl`
- Hover states: `hover:shadow-md transition-shadow`
- No shadow on backgrounds: `bg-muted`, `bg-secondary`

### 1.6 Icons

**Library:** Lucide React (exclusively)

**Usage:**
```typescript
import { IconName } from 'lucide-react';

// Standard usage
<IconName className="h-4 w-4" />

// Sizes
- Icon only (buttons): `h-4 w-4`
- List items: `h-5 w-5`
- Section headers: `h-6 w-6`
- Empty state illustrations: `h-12 w-12` to `h-16 w-16`

// Colors
- Default: `text-foreground`
- Muted: `text-muted-foreground`
- Accent: `text-primary`
```

**Navigation Icons Map:**

| Module | Icon |
|--------|------|
| Dashboard | `LayoutDashboard` |
| Products | `Package` |
| Inventory | `Warehouse` |
| Orders | `ShoppingCart` |
| POS | `CreditCard` |
| CRM | `Users` |
| Billing | `Receipt` |
| Team | `UserCog` |
| Suppliers | `Truck` |
| Kitchen | `ChefHat` |
| Appointments | `Calendar` |
| Reports | `BarChart3` |
| Settings | `Settings` |

### 1.7 Motion Guidelines

**Philosophy:** Subtle, purposeful animations. Never overdo.

**Duration Scale:**
- Instant: 0ms (immediate feedback)
- Fast: 150ms (hover, small state changes)
- Normal: 200ms (standard transitions)
- Slow: 300ms (page transitions, large elements)

**Easing:**
```css
--ease-in: cubic-bezier(0.4, 0, 1, 1);
--ease-out: cubic-bezier(0, 0, 0.2, 1);
--ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
--spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
```

**Animation Patterns:**

| Element | Animation | Duration | Easing |
|---------|-----------|----------|--------|
| Button hover | `scale(1.02)` | 150ms | ease-out |
| Dropdown open | `opacity 0→1` + `translateY(-4px→0)` | 200ms | ease-out |
| Dialog open | `opacity 0→1` + `scale(0.95→1)` | 200ms | ease-out |
| Drawer open | `translateX(100%→0)` | 300ms | ease-in-out |
| Sidebar collapse | `width` transition | 200ms | ease-in-out |
| Toast enter | `translateY(100%→0)` + `opacity` | 200ms | spring |
| Toast exit | `opacity 1→0` | 150ms | ease-in |
| Page transition | Fade in | 200ms | ease-out |
| Skeleton pulse | `opacity 0.5→1` loop | 1.5s | ease-in-out |
| Loading spinner | `rotate(0→360)` | 1s | linear (infinite) |

**Reduced Motion:**
```css
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

---

## Phase 2 — Theme Engine

### 2.1 Architecture

The theme engine enables tenant customization without separate CSS builds. All theme values come from tenant settings and are applied via CSS variables.

**Theme Configuration Flow:**
```
Tenant Settings
    ↓
TenantConfig API (backend)
    ↓
Inertia Shared Data (frontend)
    ↓
CSS Variables (app.css)
    ↓
All Components
```

### 2.2 Customizable Properties

| Property | CSS Variable | Default | Industry Override |
|----------|--------------|---------|-------------------|
| Logo | `--logo-url` | Default SOUDA logo | Per-tenant |
| Primary Color | `--primary` | `oklch(0.205 0 0)` | Industry packs |
| Primary Foreground | `--primary-foreground` | `oklch(0.985 0 0)` | Auto-calculated |
| Accent Color | `--accent` | `oklch(0.97 0 0)` | Per-tenant |
| Sidebar Style | `--sidebar-*` | Default styles | Industry packs |
| Sidebar Width | `--sidebar-width` | 16rem (256px) | Per-tenant |
| Border Radius | `--radius` | `0.625rem` | Industry packs |
| Font Family | `--font-sans` | Instrument Sans | Per-tenant |
| Mode | `dark` class | Light | User preference |

### 2.3 Industry Theme Presets

**Restaurant (Warm, Orange, Rounded):**
```css
--primary: oklch(0.65 0.19 40);  /* Warm orange */
--accent: oklch(0.95 0.15 60);   /* Light warm */
--radius: 0.75rem;               /* More rounded */
```

**Pharmacy (Clean, Blue, Clinical):**
```css
--primary: oklch(0.55 0.2 250);  /* Medical blue */
--accent: oklch(0.95 0.05 250);  /* Light blue */
--radius: 0.5rem;                /* Standard */
```

**Salon (Luxury, Purple, Soft):**
```css
--primary: oklch(0.55 0.18 310); /* Luxurious purple */
--accent: oklch(0.95 0.1 310);   /* Light purple */
--radius: 1rem;                  /* Soft rounded */
```

**Electronics (Tech, Dark, Sharp):**
```css
--primary: oklch(0.55 0.2 250);  /* Tech blue */
--accent: oklch(0.15 0.1 250);   /* Dark accent */
--radius: 0.375rem;              /* Sharp corners */
```

### 2.4 Tenant Branding & Industry Themes

**Three-Level Branding System:**

1. **Industry Pack Defaults** — Each industry pack (`app/Modules/BusinessType/Packs/`) defines default branding via `branding()` method:
   - Primary color (e.g., Restaurant = warm orange, Pharmacy = medical blue)
   - Accent color
   - Sidebar colors
   - Border radius

2. **Tenant Custom Override** — Per-tenant settings in `tenant_settings` table:
   - `brand_primary_color` — Custom primary hex color (overrides industry default)
   - `brand_accent_color` — Custom accent hex color (overrides industry default)
   - `brand_logo_url` — Custom logo URL (overrides tenant.logo)

   **Model accessors** (`app/Models/TenantSetting.php`) expose these with `HasTenantScope`-safe names so the frontend/`TenantBrandingProvider` can read them without touching the DB directly:
   - `getPrimaryColorAttribute()` → `$settings->primary_color`
   - `getAccentColorAttribute()` → `$settings->accent_color`
   - `getBrandLogoUrlAttribute()` → `$settings->brand_logo_url`
   These map to the `brand_primary_color` / `brand_accent_color` / `brand_logo_url` columns added by `database/migrations/shared/2026_08_01_000001_add_branding_to_tenant_settings.php`.

3. **Fallback** — Default SOUDA theme (gray/blue)

**Frontend Application:**
- `TenantBrandingProvider` injects CSS variables dynamically via `<style>` tag
- Variables: `--primary`, `--primary-foreground`, `--accent`, `--accent-foreground`, `--sidebar`, `--sidebar-foreground`, `--sidebar-accent`, `--radius`
- Logo fallback: `tenantLogo` → `props.logo` (global) → default icon

**All 15 Industry Packs Implemented:**

| Pack | Primary Color | Feel |
|------|---------------|------|
| Restaurant | Warm orange | Rounded, inviting |
| Cafe | Warm brown | Cream, cozy |
| Bakery | Warm gold | Cream, artisanal |
| Salon | Luxurious purple | Soft, elegant |
| Spa | Calming teal | Soft, serene |
| Grocery | Fresh green | Natural, clean |
| Pharmacy | Medical blue | Clinical, trusted |
| Fashion | Black elegant | Sharp, minimal |
| Electronics | Tech blue | Sharp, modern |
| Cosmetics | Soft pink | Feminine, gentle |
| Hardware | Industrial orange | Strong, reliable |
| Wholesale | Navy business | Professional |
| Distribution | Logistics blue | Efficient, amber accent |
| AgroShop | Earthy green | Natural, warm |
| Bookstore | Warm brown | Classic, cream |

### 2.5 Theme Implementation Rules

**DO:**
- Use CSS variables for all colors, not Tailwind color values directly
- Reference variables: `bg-[var(--primary)]` or `bg-primary` (built-in)
- Allow theme overrides via class on `<html>` element
- Support dark mode via `dark` class on `<html>`

**DO NOT:**
- Hardcode any color values
- Use hex colors in components
- Create industry-specific components
- Build separate theme CSS files

---

## Phase 3 — Navigation System

### 3.1 Dynamic Navigation Architecture

Navigation is generated dynamically based on:
1. Tenant configuration (`tenant_config`)
2. Enabled modules
3. Industry pack menus
4. User permissions
5. Current store context

**Generation Flow:**
```
TenantConfig.enabledModules
    ↓
moduleNavItems (buildModuleNavItems)
    ↓
Filtered by permissions
    ↓
IndustryPack.menus() overlay
    ↓
Rendered NavMain component
```

### 3.2 Sidebar Structure

**Component: `AppSidebar.tsx`**

```
┌─────────────────────────────────────────┐
│  Tenant Switcher                        │
│  ├─ Business Name (current)             │
│  └─ Dropdown with all tenants           │
├─────────────────────────────────────────┤
│  Store Switcher                         │
│  ├─ Store Name (current)                │
│  └─ Dropdown with all stores            │
├─────────────────────────────────────────┤
│  Navigation                             │
│  ├─ Dashboard                           │
│  ├─ Products                            │
│  │   ├─ All Products                    │
│  │   ├─ Categories                      │
│  │   ├─ Brands                          │
│  │   └─ Attributes                      │
│  ├─ Inventory                           │
│  ├─ Orders                              │
│  ├─ POS                                 │
│  ├─ CRM                                 │
│  └─ ... (dynamic based on modules)      │
├─────────────────────────────────────────┤
│  Footer                                 │
│  ├─ Help & Support                      │
│  ├─ Settings                            │
│  └─ User Profile                        │
└─────────────────────────────────────────┘
```

### 3.3 Navigation Component Patterns

**Sidebar Item (Active State):**
```tsx
<nav-item
  href="/products"
  icon={Package}
  label="Products"
  isActive={isActive('/products')}
  badge="42"  // Optional: count badge
/>
```

**Collapsible Group:**
```tsx
<nav-group label="Products" icon={Package}>
  <nav-item href="/products" label="All Products" />
  <nav-item href="/products/categories" label="Categories" />
  <nav-item href="/products/brands" label="Brands" />
</nav-group>
```

### 3.4 Topbar Components

**Always Visible:**
- Breadcrumb navigation (auto-generated from route)
- Global search trigger (Cmd/Ctrl + K)
- Notification center
- User profile dropdown

**Context-Aware:**
- Current store indicator (when in multi-store context)
- Current tenant indicator (when user has multiple tenants)

### 3.5 Breadcrumbs

**Pattern:** Auto-generated from route hierarchy
- Dashboard > Products > Create Product
- Dashboard > Orders > #12345

**Component: `Breadcrumb />` from shadcn/ui**

---

## Phase 4 — Adaptive Dashboard

### 4.1 Widget-Based Architecture

The main dashboard is composed of industry-specific widgets loaded from `TenantConfig.dashboardWidgets`.

**Widget Types:**
| Widget | Permission | Industry |
|--------|------------|----------|
| TodaySalesSummary | orders.view | Restaurant, Retail, POS |
| KitchenQueue | kitchen.view | Restaurant |
| TablesView | restaurant.tables | Restaurant |
| ExpiringMedicines | inventory.view | Pharmacy |
| LowStockAlerts | inventory.view | All |
| TodaysAppointments | appointments.view | Salon, Spa |
| StaffSchedule | team.view | Salon, Spa |
| RecentOrders | orders.view | All |
| QuickStats | Various | All |

### 4.2 Dashboard Layout System

**Grid System:** CSS Grid with 12-column layout

**Widget Sizes:**
- `full`: 12 columns (100%)
- `half`: 6 columns (50%)
- `third`: 4 columns (33%)
- `quarter`: 3 columns (25%)

**Default Layout:**
```
┌────────────────────┬────────────────────┐
│   Widget 1 (half)  │   Widget 2 (half)  │
├────────────────────┴────────────────────┤
│        Widget 3 (full width)            │
├──────────┬──────────┬───────────────────┤
│ Widget 4 │ Widget 5 │      Widget 6     │
└──────────┴──────────┴───────────────────┘
```

### 4.3 Widget Component Pattern

```tsx
interface DashboardWidgetProps {
  title: string;
  permission?: string;
  width: 'full' | 'half' | 'third' | 'quarter';
  refreshInterval?: number;  // Auto-refresh in ms
}

function DashboardWidget({ title, permission, width, children }: DashboardWidgetProps) {
  return (
    <Card className={cn('col-span-12', {
      'lg:col-span-6': width === 'half',
      'lg:col-span-4': width === 'third',
      'lg:col-span-3': width === 'quarter',
    })}>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium">{title}</CardTitle>
        <RefreshButton onRefresh={...} />
      </CardHeader>
      <CardContent>
        {children}
      </CardContent>
    </Card>
  );
}
```

---

## Phase 5 — Forms

### 5.1 Form Layout Patterns

**Standard Form Layout:**
```
┌────────────────────────────────────────────────────────────┐
│  Page Header                                               │
│  ┌────────────────────────────────────────────────────────┐│
│  │ Title                           [Cancel] [Save]        ││
│  └────────────────────────────────────────────────────────┘│
├────────────────────────────────────────────────────────────┤
│  Form Section                                              │
│  ┌────────────────────────────────────────────────────────┐│
│  │ Section Title                              [Toggle]    ││
│  │ ───────────────────────────────────────────────────────││
│  │                                                            │
│  │  ┌─────────────────────┐  ┌─────────────────────┐       ││
│  │  │ Field Label         │  │ Field Label         │       ││
│  │  │ [Input]             │  │ [Input]             │       ││
│  │  │ Helper text here    │  │ Helper text here    │       ││
│  │  └─────────────────────┘  └─────────────────────┘       ││
│  │                                                            │
│  └────────────────────────────────────────────────────────┘│
├────────────────────────────────────────────────────────────┤
│  Form Actions (Sticky Footer)                              │
│  ┌────────────────────────────────────────────────────────┐│
│  │                              [Cancel] [Save & Continue]││
│  └────────────────────────────────────────────────────────┘│
└────────────────────────────────────────────────────────────┘
```

### 5.2 Form Components

**Available Components in `resources/js/components/ui/`:**
- `input.tsx` - Text input with validation states
- `select.tsx` - Dropdown select (Radix-based)
- `checkbox.tsx` - Checkbox control
- `toggle.tsx` - Toggle switch
- `textarea.tsx` - Multi-line text
- `label.tsx` - Form labels
- `form.tsx` - Form wrapper with validation

**Shared Form Components in `resources/js/modules/shared/`:**
- `FormSection.tsx` - Collapsible form sections
- `FormActions.tsx` - Sticky form footer with buttons

### 5.3 Form Patterns

**Text Input:**
```tsx
<div className="space-y-2">
  <Label htmlFor="name">Product Name</Label>
  <Input
    id="name"
    placeholder="Enter product name"
    {...register('name')}
  />
  {errors.name && (
    <p className="text-sm text-destructive">{errors.name.message}</p>
  )}
  <p className="text-xs text-muted-foreground">
    This name will appear on receipts and invoices.
  </p>
</div>
```

**Inline Validation:**
- Show error state after blur: `border-destructive focus:ring-destructive`
- Show error message below field
- Clear error on valid input

**Progressive Disclosure:**
```tsx
<Collapsible>
  <CollapsibleTrigger asChild>
    <Button variant="ghost" size="sm">
      Advanced Options
      <ChevronDown className="ml-2 h-4 w-4" />
    </Button>
  </CollapsibleTrigger>
  <CollapsibleContent className="space-y-4 pt-4">
    {/* Advanced fields here */}
  </CollapsibleContent>
</Collapsible>
```

**Keyboard Shortcuts:**
- `Tab`: Move to next field
- `Shift+Tab`: Move to previous field
- `Enter`: Submit form (when not in textarea)
- `Escape`: Cancel/close modal

### 5.4 Wizard Forms

For complex forms (product creation, onboarding), use wizard pattern:

```
Step 1: Basic Info    [●]────────[○]────────[○]    Step 2: Pricing       Step 3: Inventory

┌─────────────────────────────────────────────────────┐
│  Basic Information                                   │
│  ─────────────────────────────────────────────────  │
│                                                      │
│  Name:    [________________]                         │
│  SKU:     [________________]                         │
│  Brand:   [▼ Select Brand]                          │
│                                                      │
│                              [Cancel] [Next →]       │
└─────────────────────────────────────────────────────┘
```

**Wizard Component Pattern:**
- Progress indicator showing current step
- Navigation buttons (Previous, Next, Save Draft)
- Validation per step before proceeding
- Auto-save draft on step change

---

## Phase 6 — Tables

### 6.1 Data Table Pattern

All data tables must use `DataTable` component from `resources/js/modules/shared/components/data-table.tsx` with TanStack Table.

**Required Features:**
- Sticky header
- Column sorting
- Row selection (checkbox)
- Pagination
- Column visibility toggle
- Search/filter
- Bulk actions

### 6.2 Table Component Pattern

```tsx
<DataTable
  data={products}
  columns={columns}
  searchColumn="name"
  searchPlaceholder="Search products..."
  loading={isLoading}
  error={error}
  onRowClick={(row) => router.visit(`/products/${row.id}`)}
>
  <DataTableToolbar>
    <Button onClick={() => setOpen(true)}>
      <Plus className="mr-2 h-4 w-4" />
      Add Product
    </Button>
    <BulkActionsDropdown>
      <DropdownMenuItem>Delete Selected</DropdownMenuItem>
      <DropdownMenuItem>Export Selected</DropdownMenuItem>
    </BulkActionsDropdown>
  </DataTableToolbar>

  <DataTablePagination>
    <DataTablePageSize />
    <DataTablePageInfo />
  </DataTablePagination>
</DataTable>
```

### 6.3 Table Cell Types

| Cell Type | Use Case | Component |
|-----------|----------|-----------|
| Text | Default | `TableCell` with text |
| Badge | Status | `Badge` variant |
| Avatar | Users | `Avatar` with image/name |
| Currency | Prices | Formatted with currency symbol |
| Date | Dates | Formatted with date-fns |
| Actions | Row actions | `DropdownMenu` with icon |
| Checkbox | Selection | `Checkbox` for bulk select |

### 6.4 Column Definitions Pattern

```tsx
const columns: ColumnDef<Product>[] = [
  {
    id: 'select',
    header: ({ table }) => (
      <Checkbox
        checked={table.getIsAllRowsSelected()}
        onCheckedChange={(value) => table.toggleAllRowsSelected(!!value)}
        aria-label="Select all"
      />
    ),
    cell: ({ row }) => (
      <Checkbox
        checked={row.getIsSelected()}
        onCheckedChange={(value) => row.toggleSelected(!!value)}
        aria-label="Select row"
      />
    ),
  },
  {
    accessorKey: 'name',
    header: 'Name',
    cell: ({ row }) => (
      <div className="flex items-center gap-3">
        <Avatar src={row.original.image} alt={row.original.name} />
        <div>
          <p className="font-medium">{row.original.name}</p>
          <p className="text-xs text-muted-foreground">{row.original.sku}</p>
        </div>
      </div>
    ),
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) => (
      <Badge variant={getBadgeVariant(row.original.status)}>
        {row.original.status}
      </Badge>
    ),
  },
  {
    id: 'actions',
    cell: ({ row }) => (
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button variant="ghost" size="sm">
            <MoreHorizontal className="h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuItem onClick={() => edit(row.original)}>Edit</DropdownMenuItem>
          <DropdownMenuItem onClick={() => duplicate(row.original)}>Duplicate</DropdownMenuItem>
          <DropdownMenuSeparator />
          <DropdownMenuItem className="text-destructive" onClick={() => delete(row.original)}>
            Delete
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    ),
  },
];
```

---

## Phase 7 — Global Search

### 7.1 Command Palette (Cmd/Ctrl + K)

**Implementation:** Use existing command palette pattern from the project.

**Search Targets:**
- Products (by name, SKU, barcode)
- Orders (by order number, customer)
- Customers (by name, email, phone)
- Suppliers (by name)
- Reports (by name)
- Settings (by page name)
- Stores (by name)
- Team members (by name, email)

**Search Results Organization:**
```
┌─────────────────────────────────────────┐
│ 🔍 Search SOUDA...                      │
├─────────────────────────────────────────┤
│  PRODUCTS                               │
│  ├─ Blue T-Shirt (SKU: TSH-001)        │
│  └─ Red Widget (SKU: WDG-002)          │
│  CUSTOMERS                              │
│  ├─ John Smith (john@example.com)      │
│  ORDERS                                 │
│  └─ #12345 (John Smith - $199.00)      │
├─────────────────────────────────────────┤
│  ↑↓ Navigate   ↵ Select   Esc Close    │
└─────────────────────────────────────────┘
```

### 7.2 Quick Actions

**Available via Command Palette:**
- `Create Product` → Opens product creation form
- `Open POS` → Redirects to POS page
- `Transfer Stock` → Opens stock transfer dialog
- `Create Purchase` → Opens purchase order form
- `Open Settings` → Opens settings page
- `Switch Store` → Opens store switcher
- `Switch Tenant` → Opens tenant switcher

---

## Phase 8 — Notification Center

### 8.1 Notification Types

| Type | Icon | Color | Examples |
|------|------|-------|----------|
| Inventory | `Package` | Warning | Low stock, expiry alerts |
| Payment | `CreditCard` | Positive | Payment received, invoice |
| Approval | `CheckCircle` | Info | Approval requests |
| Task | `CheckSquare` | Primary | Assigned tasks, mentions |
| Team | `Users` | Secondary | Team updates, mentions |

### 8.2 Notification Panel Pattern

```
┌──────────────────────────────────────────────────┐
│  Notifications                      [Mark All ✓] │
├──────────────────────────────────────────────────┤
│  Today                                           │
│  ┌────────────────────────────────────────────┐  │
│  │ 🟡 Low Stock Alert                         │  │
│  │   Blue Widget is running low (5 remaining) │  │
│  │   2 hours ago                              │  │
│  └────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────┐  │
│  │ 🟢 Payment Received                        │  │
│  │   Order #12345 - $199.00                   │  │
│  │   5 hours ago                              │  │
│  └────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────┤
│  View All Notifications                          │
└──────────────────────────────────────────────────┘
```

---

## Phase 9 — Multi-Store UX

### 9.1 Context Indicator Pattern

**Always Visible in Topbar:**
```
┌─────────────────────────────────────────────────────────────────┐
│  🏪 Downtown Store  ▼  │  Dashboard  │  Notifications  │ [User] │
└─────────────────────────────────────────────────────────────────┘
```

**Store Switcher Dropdown:**
```
┌─────────────────────────────────────────┐
│  Select Store                           │
├─────────────────────────────────────────┤
│  ✓ Downtown Store (Current)            │
│    Main Street • 2.5km away            │
├─────────────────────────────────────────┤
│    Branch Office                       │
│    Business Park • 5km away            │
├─────────────────────────────────────────┤
│    Warehouse                            │
│    Industrial Zone                      │
└─────────────────────────────────────────┘
```

### 9.2 Tenant/Store/Warehouse Hierarchy

**Always Show:**
1. Current Tenant (top of sidebar)
2. Current Store (below tenant, with switcher)
3. Current Warehouse (when in inventory context)

---

## Phase 10 — Production UX Patterns

### 10.1 Loading States

**Skeleton Loaders:**
```tsx
// Table skeleton
<TableSkeleton rows={5} columns={4} />

// Card skeleton
<Card>
  <CardHeader><Skeleton className="h-4 w-1/3" /></CardHeader>
  <CardContent><Skeleton className="h-20" /></CardContent>
</Card>
```

**Spinner:**
```tsx
<div className="flex items-center justify-center">
  <Spinner className="h-8 w-8" />
</div>
```

### 10.2 Empty States

**Pattern:**
```tsx
<EmptyState
  icon={Package}
  title="No products yet"
  description="Get started by adding your first product to the catalog."
  action={
    <Button onClick={() => setOpen(true)}>
      <Plus className="mr-2 h-4 w-4" />
      Add Product
    </Button>
  }
/>
```

### 10.3 Error States

**Pattern:**
```tsx
<ErrorState
  title="Failed to load products"
  description="Something went wrong while fetching your products."
  onRetry={() => refetch()}
/>
```

### 10.4 Success Feedback

**Toast Notifications:**
```tsx
// Success
toast.success('Product created successfully');

// Error
toast.error('Failed to create product. Please try again.');

// Info
toast.info('Product saved as draft.');
```

---

## Phase 11 — Responsive Strategy

### 11.1 Breakpoints

| Breakpoint | Width | Target Device |
|------------|-------|---------------|
| `sm` | 640px+ | Large phones |
| `md` | 768px+ | Tablets |
| `lg` | 1024px+ | Desktop |
| `xl` | 1280px+ | Large desktop |
| `2xl` | 1536px+ | Extra large |

### 11.2 Responsive Patterns

**Sidebar Behavior:**
- Desktop (lg+): Persistent sidebar
- Tablet (md-lg): Collapsible sidebar with overlay
- Mobile (-md): Hidden sidebar, hamburger menu in topbar, sheet drawer

**Table Behavior:**
- Desktop (lg+): Full columns
- Tablet (md-lg): Horizontal scroll, essential columns
- Mobile (-md): Card-based list view instead of table

**Forms:**
- Desktop (lg+): 2-column layout for side-by-side fields
- Tablet (md-lg): Single column, wider inputs
- Mobile (-md): Single column, full-width inputs

### 11.3 Mobile Patterns

**Navigation:**
```
┌─────────────────────────────────────────────────┐
│  ☰  SOUDA    [Search]    🔔  [Avatar]          │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │  Content Area                           │   │
│  │                                         │   │
│  │  - Full width                           │   │
│  │  - Touch-friendly targets (44px min)    │   │
│  │  - Larger tap areas                     │   │
│  │                                         │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
├─────────────────────────────────────────────────┤
│  [Home]  [Products]  [+Add]  [Orders]  [More]  │
└─────────────────────────────────────────────────┘
```

**Bottom Navigation Bar (Mobile):**
- 5 items max: Home, module icons, Add button, more modules
- Add button is prominent (larger, primary color)
- Current tab highlighted

---

## Phase 12 — Accessibility (WCAG 2.2 AA)

### 12.1 Keyboard Navigation

**Required:**
- All interactive elements focusable via Tab
- Visible focus indicators (ring or outline)
- Skip to main content link
- Escape closes modals/drawers/dropdowns
- Arrow keys navigate within components
- Enter activates buttons/links

**Focus Styles:**
```css
.focus-visible {
  outline: 2px solid var(--ring);
  outline-offset: 2px;
}
```

### 12.2 Screen Reader Support

**Requirements:**
- All images have alt text
- Icon-only buttons have aria-label
- Form inputs have associated labels
- Tables have proper headers
- Status badges have sr-only text alternatives

**Pattern:**
```tsx
<Badge variant="success">
  Active
  <span className="sr-only">: Product is active</span>
</Badge>
```

### 12.3 Touch Targets

**Requirements:**
- Minimum 44x44px for touch targets
- Adequate spacing between interactive elements
- No overlap with other targets

### 12.4 Color Contrast

**Requirements:**
- Text: Minimum 4.5:1 ratio (normal), 3:1 (large text)
- UI components: Minimum 3:1 against adjacent colors
- Never use color alone to convey information

---

## Phase 13 — Component Standards

### 13.1 Button Variants

| Variant | Use Case |
|---------|----------|
| `default` | Primary actions (submit, save) |
| `destructive` | Danger actions (delete, cancel) |
| `outline` | Secondary actions |
| `secondary` | Less prominent actions |
| `ghost` | Subtle actions, navigation |
| `link` | Inline links |

**Sizes:** `sm`, `default`, `lg`, `icon`

### 13.2 Card Patterns

**Standard Card:**
```tsx
<Card>
  <CardHeader>
    <CardTitle>Card Title</CardTitle>
    <CardDescription>Supporting description text.</CardDescription>
  </CardHeader>
  <CardContent>
    {/* Content */}
  </CardContent>
  <CardFooter>
    <Button variant="outline">Cancel</Button>
    <Button>Save</Button>
  </CardFooter>
</Card>
```

### 13.3 Dialog/Modal Patterns

**Confirmation Dialog:**
```tsx
<AlertDialog>
  <AlertDialogTrigger asChild>
    <Button variant="destructive">Delete</Button>
  </AlertDialogTrigger>
  <AlertDialogContent>
    <AlertDialogHeader>
      <AlertDialogTitle>Are you sure?</AlertDialogTitle>
      <AlertDialogDescription>
        This action cannot be undone.
      </AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter>
      <AlertDialogCancel>Cancel</AlertDialogCancel>
      <AlertDialogAction onClick={handleDelete}>Delete</AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</AlertDialog>
```

### 13.4 Drawer Patterns

**Side Panel:**
```tsx
<Drawer open={open} onOpenChange={setOpen}>
  <DrawerContent>
    <DrawerHeader>
      <DrawerTitle>Add Product</DrawerTitle>
      <DrawerDescription>Create a new product in your catalog.</DrawerDescription>
    </DrawerHeader>
    <DrawerBody>
      {/* Form content */}
    </DrawerBody>
    <DrawerFooter>
      <DrawerClose>Cancel</DrawerClose>
      <Button onClick={handleSave}>Save</Button>
    </DrawerFooter>
  </DrawerContent>
</Drawer>
```

### 13.5 Badge Patterns

**Status Badges:**
```tsx
// Use consistent status variants
<Badge variant="default">Active</Badge>
<Badge variant="secondary">Draft</Badge>
<Badge variant="outline">Pending</Badge>
<Badge variant="destructive">Cancelled</Badge>

// Custom status colors via cn()
<Badge className={cn(
  status === 'in_stock' && "bg-positive/10 text-positive border-positive/20"
)}>
  In Stock
</Badge>
```

---

## Phase 14 — File Organization

### 14.1 Component Location Rules

| Component Type | Location |
|----------------|----------|
| Base UI component | `resources/js/components/ui/` |
| Module-specific component | `resources/js/modules/{module}/components/` |
| Shared across modules | `resources/js/modules/shared/components/` |
| Page component | `resources/js/pages/` (or re-export from modules) |
| Industry-specific widget | `resources/js/modules/dashboard/components/industry-widgets.tsx` |

### 14.2 Page Structure Pattern

```
resources/js/pages/
├── dashboard.tsx              # Main dashboard
├── Product/
│   ├── Index.tsx             # Re-export from modules/product
│   ├── Create.tsx            # Product creation page
│   ├── Edit.tsx              # Product editing page
│   └── Show.tsx              # Product detail page
└── ...
```

### 14.3 Module Structure Pattern

```
resources/js/modules/{module}/
├── components/
│   ├── ModuleSpecificComponent.tsx
│   └── index.ts              # Export all components
├── hooks/
│   ├── use-{module}.ts       # API hooks
│   └── use-{module}-actions.ts
├── pages/
│   └── {module}-index.tsx    # Main page component
├── types/
│   └── index.ts              # TypeScript interfaces
└── lib/
    └── utils.ts              # Utilities
```

### 14.4 Content Padding Standard

**All pages MUST use consistent content padding after the sidebar header.**

**Required Pattern:**
```tsx
return (
    <AppLayout breadcrumbs={breadcrumbs}>
        <Head title="Page Title" />

        <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
            <PageHeader title="..." description="..." />
            {/* Page content */}
        </div>
    </AppLayout>
);
```

**Rules:**
- All pages (list/detail, form, dashboard): use `p-4 lg:p-6` wrapper
- Use `gap-6` on the wrapper for section spacing (not individual `mt-*` on sections)
- Never start page content directly after `<PageHeader>` without padding wrapper

---

## Phase 15 — Implementation Workflow

### 15.1 Before Making Changes

1. **Read this document** (`docs/ui-ux-design-agent.md`)
2. **Read `docs/agents.md`** for backend context
3. **Check existing components** in `resources/js/components/ui/`
4. **Check shared patterns** in `resources/js/modules/shared/`
5. **Review similar pages** for consistent patterns

### 15.2 During Implementation

1. **Follow design tokens** (colors, spacing, typography)
2. **Use existing components** (never recreate)
3. **Maintain accessibility** (keyboard nav, focus management)
4. **Keep responsive behavior** (test at breakpoints)
5. **Extract reusable components** when beneficial

### 15.3 After Implementation

1. **Verify no design regressions** (check nearby components)
2. **Test keyboard navigation**
3. **Check dark mode support**
4. **Run `npm run build`** to verify no errors
5. **Check browser console** for warnings

### 15.4 Page Refactoring Checklist

For each page refactoring:

```
□ Read existing page code
□ Identify UX problems
□ Plan proposed improvements
□ Refactor incrementally (small changes)
□ Preserve all functionality
□ Extract reusable components
□ Test interactive elements
□ Verify accessibility
□ Check responsive behavior
```

---

## Critical Gotchas

### Tailwind v4 CSS Variables

- All theme colors are CSS variables in `resources/css/app.css`
- Use Tailwind utilities like `bg-primary`, `text-foreground` (built-in)
- Custom colors via CSS variables: `bg-[var(--primary)]`

### Dark Mode

- Toggle via `dark` class on `<html>` element
- Use custom variant: `@custom-variant dark (&:is(.dark *));`
- Never hardcode dark mode styles — use CSS variables

### Inertia Forms

- Use `useForm` from Inertia for form handling
- Preserve scroll position on redirect
- Handle validation errors with error display

### TypeScript

- Strict mode enabled — no `any` types
- Use proper types for all props
- Export types from component files

### shadcn/ui Components

- All components in `resources/js/components/ui/`
- Follow compound component patterns exactly
- Never modify source files — copy and customize if needed

---

## Key Files Reference

### Core UI Components
| File | Purpose |
|------|---------|
| `resources/js/components/ui/button.tsx` | Button with all variants |
| `resources/js/components/ui/card.tsx` | Card container system |
| `resources/js/components/ui/dialog.tsx` | Modal dialog |
| `resources/js/components/ui/sheet.tsx` | Drawer/side panel |
| `resources/js/components/ui/table.tsx` | Table primitives |
| `resources/js/components/ui/badge.tsx` | Status badges |
| `resources/js/components/ui/sidebar.tsx` | App sidebar |

### Shared Components
| File | Purpose |
|------|---------|
| `resources/js/modules/shared/components/data-table.tsx` | Full data table |
| `resources/js/modules/shared/components/data-table-toolbar.tsx` | Toolbar + `ToolbarSection`/`ToolbarDivider` |
| `resources/js/modules/shared/components/data-table-pagination.tsx` | Pagination controls |
| `resources/js/modules/shared/components/data-table-column-toggle.tsx` | Column visibility toggle |
| `resources/js/modules/shared/components/data-table-views.tsx` | Saved views + `useDataTableViews` hook |
| `resources/js/modules/shared/components/data-table-faceted-filter.tsx` | Faceted filter |
| `resources/js/modules/shared/components/bulk-actions-bar.tsx` | Bulk actions (`commonBulkActions`) |
| `resources/js/modules/shared/components/form-section.tsx` | Form section wrapper |
| `resources/js/modules/shared/components/form-actions.tsx` | Form footer |
| `resources/js/modules/shared/components/form-layout.tsx` | `FormLayout`/`FormFieldGrid`/`FormHint`/`FormDivider`/`RequiredBadge` |
| `resources/js/modules/shared/components/form-field.tsx` | Single form field |
| `resources/js/modules/shared/components/wizard-form.tsx` | `WizardForm`/`WizardSteps` |
| `resources/js/modules/shared/components/page-header.tsx` | Page title section |
| `resources/js/modules/shared/components/empty-state.tsx` | Empty states |
| `resources/js/modules/shared/components/error-state.tsx` | Error state |
| `resources/js/modules/shared/components/alert.tsx` | Alert variants |
| `resources/js/modules/shared/components/toast.tsx` / `toaster.tsx` | Toast notifications |
| `resources/js/modules/shared/components/loading.tsx` | Loading spinners/buttons |
| `resources/js/modules/shared/components/table-skeleton.tsx` | Table/card/stat-card skeletons |
| `resources/js/modules/shared/components/stat-card.tsx` | Stat card |
| `resources/js/modules/shared/components/status-badge.tsx` | Status badges |
| `resources/js/modules/shared/components/search-input.tsx` | Search input |
| `resources/js/modules/shared/components/confirm-dialog.tsx` | Confirmation dialog |
| `resources/js/modules/shared/components/deferred-section.tsx` | Inertia deferred prop skeleton wrapper |
| `resources/js/modules/shared/components/pagination.tsx` | Plain pagination |
| `resources/js/modules/shared/components/preset-*.tsx` | Preset buttons/cards/dialogs |
| `resources/js/modules/shared/components/mobile-nav.tsx` | `MobileNav` family (MobileHeader/MobileBottomNav/MobileOnly/DesktopOnly/TouchButton/useSwipeToClose) |
| `resources/js/modules/shared/components/responsive-layout.tsx` | `MobileContent`/`ResponsiveContainer`/`ResponsiveGrid`/`StickyHeader`/`VStack`/`HStack`/`ScrollLayout` |

### Layout Components
| File | Purpose |
|------|---------|
| `resources/js/components/layouts/app/app-sidebar-layout.tsx` | Sidebar layout wrapper |
| `resources/js/components/layouts/app/app-sidebar.tsx` | App sidebar |
| `resources/js/components/module-nav-items.ts` | Module navigation config |

### Dashboard
| File | Purpose |
|------|---------|
| `resources/js/modules/dashboard/components/industry-widgets.tsx` | Industry-specific widgets |

### Configuration
| File | Purpose |
|------|---------|
| `resources/css/app.css` | Theme tokens, dark mode |
| `tailwind.config.js` | Tailwind configuration |
| `resources/js/lib/utils.ts` | Utility functions (cn) |

---

## Industry-Specific Theming Reference

### Restaurant Theme
```css
--primary: oklch(0.65 0.19 40);   /* Warm orange */
--primary-foreground: oklch(0.985 0 0);
--accent: oklch(0.95 0.15 60);    /* Light warm */
--radius: 0.75rem;                /* More rounded */
--sidebar-accent: oklch(0.95 0.1 40);
```

### Pharmacy Theme
```css
--primary: oklch(0.55 0.2 250);   /* Medical blue */
--primary-foreground: oklch(0.985 0 0);
--accent: oklch(0.95 0.05 250);   /* Light blue */
--radius: 0.5rem;                 /* Clinical */
--sidebar-accent: oklch(0.92 0.05 250);
```

### Salon Theme
```css
--primary: oklch(0.55 0.18 310);  /* Luxurious purple */
--primary-foreground: oklch(0.985 0 0);
--accent: oklch(0.95 0.1 310);    /* Light purple */
--radius: 1rem;                   /* Soft, elegant */
--sidebar-accent: oklch(0.92 0.08 310);
```

### Electronics Theme
```css
--primary: oklch(0.55 0.2 250);   /* Tech blue */
--primary-foreground: oklch(0.985 0 0);
--accent: oklch(0.15 0.1 250);    /* Dark accent */
--radius: 0.375rem;               /* Sharp */
--sidebar-accent: oklch(0.2 0.08 250);
```

---

This document is the authoritative guide for all UI/UX work on SOUDA. Follow it exactly for consistent, production-quality interfaces.