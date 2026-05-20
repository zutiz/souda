# Form Architecture

## Overview

Forms use a three-layer architecture: **validation schemas** (Zod) at the bottom, **field components** in the middle, and **form composition** at the top. All forms integrate with Inertia for submission.

```
Layer 3: Form Composition
  product-form.tsx, category-form.tsx, brand-form.tsx
  │  Orchestrates fields, sections, and submission
  │  Uses useForm from react-hook-form + zodResolver
  │  Submits via Inertia router (post/put)
  ▼
Layer 2: Shared Form Components
  FormField, FormSection, FormActions, FormDescription, FormMessage
  │  Generic wrappers around shadcn/ui primitives
  │  Handles error display, layout, accessibility
  ▼
Layer 1: Validation & Types
  Zod schemas + TypeScript inferred types
  │  Mirrors backend FormRequest validation rules
  │  Single source of truth for form shape
  ▼
Backend: Laravel FormRequest
  Validates again server-side (defense in depth)
```

## Schema Strategy

1. **Define a Zod schema per form** in the module's `types/` directory
2. **Infer TypeScript types** from the schema using `z.infer`
3. **Mirror Laravel FormRequest rules** — same field names, same constraints
4. **Keep schemas in sync** by referencing backend DTOs when they exist

```
// types/product.ts
export const productSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255),
  sku: z.string().min(1, 'SKU is required'),
  barcode: z.string().optional(),
  price: z.number().positive('Price must be positive'),
  compare_price: z.number().optional(),
  cost_price: z.number().optional(),
  description: z.string().optional(),
  category_id: z.string().uuid().nullable(),
  brand_id: z.string().uuid().nullable(),
  status: z.enum(['draft', 'active', 'archived']),
  weight: z.number().optional(),
  is_taxable: z.boolean().default(true),
  metadata: z.record(z.unknown()).optional(),
});

export type ProductFormData = z.input<typeof productSchema>;
export type ProductFormOutput = z.output<typeof productSchema>;
```

## Form Types

| Form | Schema Location | Fields | Complexity |
|---|---|---|---|
| Product create/edit | `modules/product/types/product.ts` | 15+ fields, nested variants | High |
| Category create/edit | `modules/product/types/category.ts` | Name, slug, parent, description, image | Medium |
| Brand create/edit | `modules/product/types/brand.ts` | Name, slug, logo, description | Medium |
| Variant create/edit | `modules/product/types/variant.ts` | SKU, barcode, price, attributes, images | High |
| Attribute create/edit | `modules/product/types/attribute.ts` | Name, type, values | Low |
| Bulk status update | Inline schema | Status selector | Low |

## Form Submission Flow

```
User fills form
  │
  ▼
react-hook-form validates against Zod schema
  │
  ├── Client error → Show inline validation messages
  │
  └── Valid → Submit via Inertia
                │
                ├── Server validates (FormRequest)
                │   ├── Error → Inertia returns errors → display inline
                │   └── Success → Redirect + toast
                │
                └── Server error (5xx) → Display generic error banner
```

## Complex Form Patterns

### Multi-Section Forms (Product Create/Edit)
- Sections are independent `FormSection` components: "General", "Pricing", "Inventory", "Images", "Variants"
- Sections can be collapsed/expanded individually
- Each section uses the same `useForm` instance (same form context)
- Progress indicator shows completion per section

### Nested Forms (Variants)
- Variant editor uses `useFieldArray` from react-hook-form
- Each variant row: SKU, barcode, price, quantity, attributes
- Add/remove rows dynamically
- Bulk price editing across selected variants
- Validate each row independently while maintaining form-level errors

### Image Upload
- Upload via Inertia file input (no separate API endpoint needed)
- Show local preview immediately after file selection
- Track upload progress via Inertia's `progress` callback
- Support drag-and-drop zone
- Allow reordering and deletion before submission

## Form Field Components

Each field component wraps a shadcn/ui primitive with form state:

```
FormField (label + control + description + error)
├── TextInputField
├── NumberInputField
├── SelectField (searchable dropdown)
├── MultiSelectField
├── DatePickerField
├── SwitchField (boolean toggle)
├── TextareaField
├── FileUploadField
├── ColorPickerField
└── RichTextField (description/HTML)
```

## Error Handling

- **Client-side**: Zod validation errors show on blur and on submit
- **Server-side**: Inertia validation errors merge into the same field error display
- **Non-field errors**: Generic error banner at the top of the form
- **Timeout/network**: Retry button + "Connection lost" message
- **Dirty state**: Warn before navigating away with unsaved changes (Inertia `useRemember`)

## Form Performance Rules

- Field components are lazy-loaded for complex forms
- `useFieldArray` rows are virtualized when exceeding 20 rows
- Section state (collapsed/expanded) does not unmount — uses CSS visibility
- Schema validation is memoized; does not re-create on every render
