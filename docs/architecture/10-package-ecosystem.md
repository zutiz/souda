# Suggested Package Ecosystem

## Overview

Recommended Laravel packages for building a production-grade multi-tenant SaaS ERP platform. Packages are categorized by necessity and purpose.

## Core Packages (Already Installed)

| Package | Version | Purpose | Status |
|---------|---------|---------|--------|
| `laravel/framework` | ^13.0 | Core framework | Installed |
| `inertiajs/inertia-laravel` | ^2.0 | Server-side Inertia | Installed |
| `laravel/fortify` | ^1.30 | Authentication scaffolding | Installed |
| `laravel/cashier` | ^16.3 | Stripe billing | Installed |
| `laravel/socialite` | ^5.24 | OAuth authentication | Installed |
| `laravel/wayfinder` | ^0.1.9 | TypeScript route generation | Installed |
| `spatie/laravel-permission` | ^7.2 | Roles & permissions | Installed |
| `stancl/tenancy` | ^3.9 | Multi-tenancy | Installed |

## Recommended Additions

### Queue & Performance

| Package | Purpose | Priority |
|---------|---------|----------|
| `laravel/horizon` | Redis queue monitoring dashboard | **High** |
| `laravel/telescope` | Local debugging and monitoring | **High** (dev only) |
| `laravel/octane` | High-performance application server | Medium |

```bash
composer require laravel/horizon
composer require laravel/telescope --dev
composer require laravel/octane spiral/roadrunner-laravel
```

### Data Management

| Package | Purpose | Priority |
|---------|---------|----------|
| `spatie/laravel-activitylog` | Activity logging and audit trails | **High** |
| `spatie/laravel-medialibrary` | File/media management | **High** |
| `spatie/laravel-data` | DTO generation and validation | **High** |
| `spatie/laravel-query-builder` | API filtering, sorting, pagination | Medium |
| `spatie/laravel-tags` | Tagging system for products/contacts | Medium |
| `spatie/laravel-enum` | Enhanced enum support | Low (PHP 8.4 has native enums) |

```bash
composer require spatie/laravel-activitylog
composer require spatie/laravel-medialibrary
composer require spatie/laravel-data
```

### API & Integration

| Package | Purpose | Priority |
|---------|---------|----------|
| `laravel/sanctum` | API token authentication | **High** |
| `spatie/laravel-webhook-client` | Webhook handling and verification | **High** |
| `spatie/laravel-ray` | Local debugging | Medium (dev only) |

```bash
composer require laravel/sanctum
composer require spatie/laravel-webhook-client
```

### Validation & Security

| Package | Purpose | Priority |
|---------|---------|----------|
| `spatie/laravel-rules` | Additional validation rules | Medium |
| `propaganistas/laravel-phone` | Phone number validation | Medium |
| `laravel/ui` | Additional auth scaffolding | Low |

```bash
composer require spatie/laravel-rules
composer require propaganistas/laravel-phone
```

### Testing & Quality

| Package | Purpose | Priority |
|---------|---------|----------|
| `pestphp/pest` | Testing framework | Installed |
| `laravel/pint` | Code formatter | Installed |
| `larastan/larastan` | Static analysis | **High** |
| `spatie/laravel-ignition` | Error page | Installed (Laravel 13) |
| `barryvdh/laravel-debugbar` | Debug toolbar | Medium (dev only) |

```bash
composer require larastan/larastan --dev
composer require barryvdh/laravel-debugbar --dev
```

### Email & Notifications

| Package | Purpose | Priority |
|---------|---------|----------|
| `laravel/postmark-mail` | Postmark driver | Medium |
| `resend/resend-php` | Resend email service | Medium |
| `laravel/slack-notification-channel` | Slack notifications | Medium |

### Search

| Package | Purpose | Priority |
|---------|---------|----------|
| `meilisearch/meilisearch-php` | Full-text search | **High** |
| `laravel/scout` | Laravel search abstraction | **High** |

```bash
composer require laravel/scout
composer require meilisearch/meilisearch-php
```

### Backup & Maintenance

| Package | Purpose | Priority |
|---------|---------|----------|
| `spatie/laravel-backup` | Database and file backups | **High** |
| `spatie/laravel-health` | Application health checks | Medium |

```bash
composer require spatie/laravel-backup
composer require spatie/laravel-health
```

### Frontend Packages (Already Installed)

| Package | Version | Purpose | Status |
|---------|---------|---------|--------|
| `react` / `react-dom` | ^19.2.0 | UI library | Installed |
| `@inertiajs/react` | ^2.3.7 | SPA framework | Installed |
| `tailwindcss` | ^4.0.0 | Utility CSS | Installed |
| `radix-ui` | ^1.4.3 | Headless UI primitives | Installed |
| `@headlessui/react` | ^2.2.0 | Additional headless components | Installed |
| `lucide-react` | ^0.475.0 | Icons | Installed |
| `recharts` | ^3.7.0 | Charts | Installed |
| `class-variance-authority` | ^0.7.1 | Component variants | Installed |
| `date-fns` | ^4.1.0 | Date utilities | Installed |
| `react-day-picker` | ^9.13.2 | Calendar | Installed |
| `input-otp` | ^1.4.2 | OTP input | Installed |

### Recommended Frontend Additions

| Package | Purpose | Priority |
|---------|---------|----------|
| `@tanstack/react-table` | Advanced data tables | **High** |
| `@tanstack/react-query` | Server state management | **High** |
| `zod` | Schema validation | **High** |
| `react-hook-form` | Form handling | Medium (Inertia useForm may suffice) |
| `sonner` | Toast notifications | Medium |
| `cmdk` | Command palette | Medium |
| `vaul` | Drawer component | Low |

```bash
npm install @tanstack/react-table @tanstack/react-query zod sonner cmdk
```

## Package Selection Criteria

### Must-Have Criteria

1. **Active maintenance** - Regular updates, responsive maintainers
2. **Laravel compatibility** - Supports Laravel 13
3. **PHP 8.4 support** - Compatible with latest PHP
4. **Good documentation** - Clear usage examples
5. **Test coverage** - Well-tested package
6. **Community adoption** - Widely used, trusted

### Evaluation Checklist

- [ ] GitHub stars > 1000 (or growing rapidly)
- [ ] Last commit within 3 months
- [ ] Open issues < 50 (or well-managed)
- [ ] Compatible with Laravel 13
- [ ] Compatible with PHP 8.4
- [ ] Has test suite
- [ ] Has documentation
- [ ] License is permissive (MIT)

## Package Conflict Considerations

### Tenancy Compatibility

| Package | Compatible | Notes |
|---------|------------|-------|
| `spatie/laravel-medialibrary` | Yes | Configure filesystem per tenant |
| `spatie/laravel-activitylog` | Yes | Add tenant_id to activity log |
| `laravel/scout` | Yes | Index per tenant |
| `laravel/horizon` | Yes | Central monitoring |
| `spatie/laravel-backup` | Yes | Backup tenant databases individually |
| `laravel/telescope` | Yes | Central only |

### Configuration for Tenancy

```php
// spatie/laravel-medialibrary - config/media-library.php
'default_filesystem' => 'public',
// Use tenant-aware filesystem in TenancyServiceProvider

// spatie/laravel-activitylog - config/activitylog.php
'caused_by_model' => User::class,
// Add tenant_id column to activity_log table
```

## Development vs Production Packages

### Development Only

```bash
composer require --dev larastan/larastan
composer require --dev barryvdh/laravel-debugbar
composer require --dev laravel/telescope
composer require --dev spatie/laravel-ray
```

### Production Only

```bash
composer require laravel/horizon
composer require spatie/laravel-backup
composer require laravel/octane
```

## Package Version Locking

```json
{
    "require": {
        "spatie/laravel-activitylog": "^4.8",
        "spatie/laravel-medialibrary": "^11.0",
        "spatie/laravel-data": "^4.0",
        "laravel/horizon": "^5.0",
        "laravel/sanctum": "^4.0",
        "spatie/laravel-webhook-client": "^3.0",
        "laravel/scout": "^10.0",
        "meilisearch/meilisearch-php": "^1.0",
        "spatie/laravel-backup": "^9.0",
        "larastan/larastan": "^3.0"
    }
}
```

## Alternatives Considered

| Need | Chosen | Alternative | Reason |
|------|--------|-------------|--------|
| Activity logging | `spatie/laravel-activitylog` | `owen-it/laravel-auditing` | More features, better maintained |
| Media management | `spatie/laravel-medialibrary` | Custom solution | Battle-tested, feature-rich |
| Queue monitoring | `laravel/horizon` | Custom dashboard | Official Laravel package |
| Static analysis | `larastan/larastan` | `phpstan/phpstan` | Laravel-specific rules |
| Search | `laravel/scout` + Meilisearch | Algolia | Self-hosted, cost-effective |
| Backup | `spatie/laravel-backup` | Custom scripts | Comprehensive, reliable |

## Installation Order

### Phase 1: Foundation

```bash
composer require laravel/sanctum
composer require spatie/laravel-activitylog
composer require spatie/laravel-data
```

### Phase 2: Media & Search

```bash
composer require spatie/laravel-medialibrary
composer require laravel/scout
composer require meilisearch/meilisearch-php
```

### Phase 3: Monitoring & Maintenance

```bash
composer require laravel/horizon
composer require spatie/laravel-backup
composer require spatie/laravel-health
```

### Phase 4: Development Tools

```bash
composer require --dev larastan/larastan
composer require --dev barryvdh/laravel-debugbar
composer require --dev laravel/telescope
```
