---
name: wayfinder-development
description: "Activates whenever referencing backend routes in frontend components. Use when importing from @/actions or @/routes, calling Laravel routes from TypeScript, or working with Wayfinder route functions."
license: MIT
metadata:
  author: laravel
---

# Wayfinder Development

## When to Apply

Activate whenever referencing backend routes in frontend components:
- Importing from `@/actions/` or `@/routes/`
- Calling Laravel routes from TypeScript/JavaScript
- Creating links or navigation to backend endpoints

## Documentation

Use `search-docs` for detailed Wayfinder patterns and documentation.

## Quick Reference

### Generate Routes

Run after route changes if Vite plugin isn't installed:
```bash
php artisan wayfinder:generate --no-interaction
```
For form helpers, use `--with-form` flag:
```bash
php artisan wayfinder:generate --with-form --no-interaction
```

### Import Patterns

<!-- Controller Action Imports -->
```typescript
// Named imports for tree-shaking (preferred)...
import { show, store, update } from '@/actions/App/Http/Controllers/PostController'

// Named route imports...
import { show as postShow } from '@/routes/post'
```

### Common Methods

<!-- Wayfinder Methods -->
```typescript
// Get route object...
show(1) // { url: "/posts/1", method: "get" }

// Get URL string...
show.url(1) // "/posts/1"

// Specific HTTP methods...
show.get(1)
store.post()
update.patch(1)
destroy.delete(1)

// Form attributes for HTML forms...
store.form() // { action: "/posts", method: "post" }

// Query parameters...
show(1, { query: { page: 1 } }) // "/posts/1?page=1"
```

## Wayfinder + Inertia

Use Wayfinder with the `<Form>` component:
<!-- Wayfinder Form (React) -->
```typescript
<Form {...store.form()}><input name="title" /></Form>
```

## Verification

1. Run `php artisan wayfinder:generate` to regenerate routes if Vite plugin isn't installed
2. Check TypeScript imports resolve correctly
3. Verify route URLs match expected paths

## Project-Specific Patterns (Souda)

This project uses **Wayfinder v0** with the `@laravel/vite-plugin-wayfinder` Vite plugin. Routes auto-regenerate on save — manual `wayfinder:generate` is not needed during development.

### Import Paths

Generated files are in `resources/js/wayfinder/`. The vite plugin maps:
- `@/actions/` → `resources/js/wayfinder/actions/`
- `@/routes/` → `resources/js/wayfinder/routes/`

### Invokable Controllers

```typescript
import StorePost from '@/actions/App/Http/Controllers/StorePostController';

// POST /posts — calls the invokable controller
router.post(StorePost(), formData);
```

### Named Routes

```typescript
import { show } from '@/routes/post';

// GET /posts/{post:slug}
router.get(show({ slug: 'my-post' }));

// With query merging (Inertia)
import { mergeQuery } from '@/lib/wayfinder';
show(1, { mergeQuery: mergeQuery({ page: 2, sort: null }) });
```

### Wayfinder + Inertia `<Form>` Component

```typescript
import { store } from '@/actions/App/Http/Controllers/TaskController';

<Form {...store.form()}>
    <input name="title" />
</Form>
```

## Common Pitfalls

- Using default imports instead of named imports (breaks tree-shaking)
- Forgetting Vite plugin auto-generates; no need to run `wayfinder:generate` manually
- Not using type-safe parameter objects for route model binding
- Using hardcoded URLs instead of Wayfinder functions