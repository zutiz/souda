# Coding Standards

## Overview

Consistent coding standards enforced by automated tools and code review. All code must pass linting and formatting checks before merging.

## PHP Standards

### Laravel Pint (Code Formatter)

Configuration: `pint.json`

```json
{
    "preset": "laravel"
}
```

Run: `composer run lint` or `vendor/bin/pint --format agent`

### PHP Version

- **Minimum**: PHP 8.4
- **Features to use**: Constructor property promotion, readonly classes, enums, match expressions, nullsafe operator, named arguments

### General PHP Rules

1. **Strict types** - `declare(strict_types=1);` in all PHP files
2. **Type declarations** - Always use explicit return types and parameter types
3. **Curly braces** - Always use curly braces for control structures, even single-line
4. **No inline comments** - Use PHPDoc blocks instead
5. **Named arguments** - Use for clarity with 3+ parameters

```php
<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Models\Product;
use App\Modules\Product\DTOs\ProductData;

class ProductService
{
    public function createProduct(ProductData $dto): Product
    {
        return Product::create([
            'name' => $dto->name,
            'sku' => $dto->sku,
            'price' => $dto->price,
        ]);
    }

    public function getProduct(string $id): ?Product
    {
        return Product::query()->find($id);
    }
}
```

### Constructor Property Promotion

```php
// ✓ CORRECT
class ProductService
{
    public function __construct(
        protected ProductRepository $repository,
        protected EventDispatcher $dispatcher,
    ) {}
}

// ✗ WRONG
class ProductService
{
    protected ProductRepository $repository;
    protected EventDispatcher $dispatcher;

    public function __construct(
        ProductRepository $repository,
        EventDispatcher $dispatcher,
    ) {
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
    }
}
```

### Readonly Classes for DTOs

```php
readonly class ProductDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $sku,
        public int $price,
        public ?string $description,
        public array $categories,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            sku: $product->sku,
            price: $product->price,
            description: $product->description,
            categories: $product->categories->pluck('id')->toArray(),
        );
    }
}
```

### Enums

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function isAccessible(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Processing,
            self::Shipped,
        ]);
    }
}
```

### Match Expressions

```php
// ✓ CORRECT
return match ($status) {
    OrderStatus::Pending => 'Awaiting processing',
    OrderStatus::Processing => 'Being prepared',
    OrderStatus::Shipped => 'On the way',
    OrderStatus::Delivered => 'Delivered successfully',
    OrderStatus::Cancelled => 'Order cancelled',
};

// ✗ WRONG
if ($status === OrderStatus::Pending) {
    return 'Awaiting processing';
} elseif ($status === OrderStatus::Processing) {
    return 'Being prepared';
}
// ...
```

### Nullsafe Operator

```php
// ✓ CORRECT
$country = $order?->customer?->address?->country;

// ✗ WRONG
$country = null;
if ($order) {
    if ($order->customer) {
        if ($order->customer->address) {
            $country = $order->customer->address->country;
        }
    }
}
```

## PHPDoc Standards

### Method Documentation

```php
/**
 * Create a new product and dispatch indexing job.
 *
 * @param ProductDTO $dto Product data
 * @return Product The created product model
 * @throws ProductCreationException If creation fails
 */
public function create(ProductDTO $dto): Product
{
    // ...
}
```

### Array Shape Types

```php
/**
 * @return array{
 *     id: int,
 *     name: string,
 *     price: int,
 *     categories: array<int, string>
 * }
 */
public function getProductSummary(int $id): array
{
    // ...
}
```

### Property Documentation

```php
class Product extends Model
{
    /**
     * The product's unique SKU identifier.
     *
     * @var string
     */
    protected $sku;
}
```

## Eloquent Standards

### Query Building

```php
// ✓ CORRECT
Product::query()
    ->where('is_active', true)
    ->where('price', '>', 0)
    ->with(['category', 'variants'])
    ->orderBy('name')
    ->paginate();

// ✗ WRONG
Product::where('is_active', true)
    ->where('price', '>', 0)
    ->get();
```

### Relationships

```php
// ✓ CORRECT
public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}

// ✗ WRONG
public function orders()
{
    return $this->hasMany(Order::class);
}
```

### Avoid DB:: Facade

```php
// ✓ CORRECT
Product::query()->where('stock', '>', 0)->get();

// ✗ WRONG
DB::table('products')->where('stock', '>', 0)->get();
```

### Eager Loading

```php
// ✓ CORRECT - prevents N+1
Order::query()
    ->with(['items.product', 'customer'])
    ->where('status', OrderStatus::Pending)
    ->get();

// ✗ WRONG - causes N+1
$orders = Order::query()->where('status', OrderStatus::Pending)->get();
foreach ($orders as $order) {
    echo $order->customer->name; // N+1 query
}
```

### Model Creation

```php
// ✓ CORRECT
Product::create([
    'name' => $dto->name,
    'sku' => $dto->sku,
    'price' => $dto->price,
]);

// ✓ CORRECT for bulk
Product::insert([
    ['name' => 'Product A', 'sku' => 'SKU-A', 'price' => 1000],
    ['name' => 'Product B', 'sku' => 'SKU-B', 'price' => 2000],
]);
```

## Validation Standards

### Form Request Classes

```php
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'unique:products,sku'],
            'price' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'This SKU is already in use.',
            'price.min' => 'Price must be a positive amount.',
        ];
    }
}
```

## Error Handling

### Custom Exceptions

```php
class ProductNotFoundException extends DomainException
{
    public function __construct(int $productId)
    {
        parent::__construct(
            message: "Product not found: {$productId}",
            code: 404,
        );
    }
}
```

### Exception Handling in Services

```php
public function create(ProductDTO $dto): Product
{
    try {
        return Product::create($dto->toArray());
    } catch (QueryException $e) {
        throw new ProductCreationException(
            message: 'Failed to create product',
            previous: $e,
        );
    }
}
```

## Environment Variables

### Rules

1. **Only in config files** - Never use `env()` outside config
2. **Use config() everywhere else** - `config('billing.default_gateway')`
3. **Provide defaults** - `config('billing.grace_period_days', 3)`

```php
// ✓ CORRECT - in config file
'default_gateway' => env('BILLING_DEFAULT_GATEWAY', 'stripe'),

// ✓ CORRECT - in application code
$gateway = config('billing.default_gateway');

// ✗ WRONG - in application code
$gateway = env('BILLING_DEFAULT_GATEWAY');
```

## TypeScript/React Standards

### Component Structure

```tsx
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { router } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { mapFormToPayload } from '@/lib/map-form-to-payload'

const productSchema = z.object({
    name: z.string().min(1, 'Name is required'),
    sku: z.string().min(1, 'SKU is required'),
    price: z.number().min(0),
})

type ProductFormData = z.infer<typeof productSchema>

interface ProductFormProps {
    initialData?: Product
}

export function ProductForm({ initialData }: ProductFormProps) {
    const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<ProductFormData>({
        resolver: zodResolver(productSchema),
        defaultValues: {
            name: initialData?.name ?? '',
            sku: initialData?.sku ?? '',
            price: initialData?.price ?? 0,
        },
    })

    function onSubmit(data: ProductFormData) {
        router.post(route('products.store'), mapFormToPayload(data))
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <Input {...register('name')} error={errors.name?.message} />
            <Button type="submit" disabled={isSubmitting}>
                Save
            </Button>
        </form>
    )
}
```

### Hook Structure

```tsx
import { useQuery } from '@tanstack/react-query'
import { axios } from '@/lib/axios'

export function useProducts(filters?: ProductFilters) {
    return useQuery({
        queryKey: ['products', filters],
        queryFn: () => axios.get('/api/products', { params: filters }).then(r => r.data),
        staleTime: 30_000,
        gcTime: 5 * 60 * 1000,
    })
}
```

## Testing Standards

### Pest Tests

```php
use App\Modules\Product\Models\Product;
use Tests\Support\RefreshMultiDatabase;

uses(RefreshMultiDatabase::class);

test('user can create a product', function () {
    $user = User::factory()->withSubscription()->create();
    $tenant = $user->tenant;

    tenancy()->initialize($tenant);

    actingAs($user);

    $response = $this->post(route('products.store'), [
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'price' => 1000,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseCount('products', 1);
});

test('product requires valid sku', function () {
    $user = User::factory()->withSubscription()->create();
    $tenant = $user->tenant;

    tenancy()->initialize($tenant);

    actingAs($user);

    $response = $this->post(route('products.store'), [
        'name' => 'Test Product',
        'sku' => '',
        'price' => 1000,
    ]);

    $response->assertSessionHasErrors('sku');
});

test('shared model is isolated by tenant scope', function () {
    $tenant1 = Tenant::factory()->create(['tenancy_mode' => 'shared']);
    $tenant2 = Tenant::factory()->create(['tenancy_mode' => 'shared']);

    TenantConfig::factory()->create(['tenant_id' => $tenant1->id]);
    TenantConfig::factory()->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);
    expect(TenantConfig::count())->toBe(1);
});
```

### Test Organization

```
tests/
├── Feature/
│   ├── Auth/
│   ├── Billing/
│   ├── Products/
│   ├── Orders/
│   └── Admin/
└── Unit/
    ├── Services/
    ├── DTOs/
    └── Actions/
```

## Git Standards

### Commit Messages

```
type(scope): description

feat(products): add bulk import functionality
fix(billing): correct subscription expiry calculation
refactor(orders): simplify order creation flow
test(inventory): add stock deduction tests
docs(readme): update installation instructions
chore(deps): update laravel/framework to 13.x
```

### Types

| Type | Description |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `refactor` | Code refactoring |
| `style` | Code style changes |
| `docs` | Documentation |
| `test` | Tests |
| `chore` | Maintenance tasks |

## Import Order (TypeScript)

```tsx
// 1. React imports
import { useState } from 'react'

// 2. Third-party imports
import { useQuery } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { router } from '@inertiajs/react'

// 3. Internal imports (aliased)
import { Button } from '@/components/ui/button'
import { useTenantConfig } from '@/hooks/use-tenant-config'
import StoreProduct from '@/actions/product/StoreProductController'

// 4. Relative imports
import { ProductCard } from './product-card'
import { types } from '../types'
```

## Automated Checks

### Pre-Commit (Recommended)

```bash
#!/bin/bash
# .husky/pre-commit

# PHP formatting
vendor/bin/pint --dirty

# PHP tests
php artisan test --compact --filter="$(git diff --cached --name-only | grep Test.php)"

# TypeScript linting
npx eslint --fix resources/js/

# TypeScript type checking
npx tsc --noEmit
```

### CI Pipeline

```yaml
# .github/workflows/ci.yml
steps:
  - name: Run Pint
    run: composer run lint

  - name: Run Tests
    run: composer run test

  - name: Run ESLint
    run: npx eslint resources/js/

  - name: Type Check
    run: npx tsc --noEmit
```
