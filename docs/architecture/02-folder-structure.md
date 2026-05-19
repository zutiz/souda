# Folder Structure

## Root Structure

```
souda/
├── .cursor/                          # AI agent skills configuration
├── agents/                           # AI agent domain documentation
├── app/                              # Application source code
├── bootstrap/                        # Framework bootstrapping
├── config/                           # Configuration files
├── database/                         # Migrations, factories, seeders
├── docs/                             # Project documentation
├── public/                           # Web root (index.php, assets)
├── resources/                        # Frontend source (JS, CSS, views)
├── routes/                           # Route definitions
├── storage/                          # Logs, cache, uploads
├── tests/                            # Test suites
├── AGENTS.md                         # AI agent guidelines
├── composer.json                     # PHP dependencies
├── package.json                      # Node dependencies
├── pint.json                         # Code formatter config
├── phpunit.xml                       # Test configuration
├── tsconfig.json                     # TypeScript config
├── vite.config.ts                    # Vite build config
└── eslint.config.js                  # ESLint config
```

## Application Structure (`app/`)

```
app/
├── Actions/                          # Application-level action classes
│   ├── Auth/
│   │   ├── CreateNewUser.php
│   │   └── CreateSocialUser.php
│   └── Fortify/
│       ├── CreateNewUser.php
│       ├── ResetUserPassword.php
│       └── ...
├── Concerns/                         # Reusable PHP traits
│   ├── PasswordValidationRules.php
│   └── ProfileValidationRules.php
├── Console/Commands/                 # Artisan commands
│   └── ImportStripeData.php
├── Contracts/                        # Application-level interfaces
│   └── (cross-module contracts)
├── DTOs/                             # Application-level DTOs
├── Enums/                            # Application-level enums
├── Events/                           # Application-level events
├── Exceptions/                       # Application-level exceptions
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                    # Admin area controllers
│   │   ├── Auth/                     # Auth controllers
│   │   ├── Settings/                 # User settings controllers
│   │   └── (top-level controllers)
│   ├── Middleware/                   # HTTP middleware
│   ├── Requests/                     # Form request classes
│   │   ├── Admin/
│   │   ├── Settings/
│   │   ├── InviteTeamMemberRequest.php
│   │   └── (top-level requests)
│   ├── Resources/                    # API resource classes
│   └── Responses/                    # Response overrides
│       ├── LoginResponse.php
│       └── RegisterResponse.php
├── Jobs/                             # Queueable jobs
├── Listeners/                        # Application-level listeners
│   └── StripeEventListener.php
├── Mail/                             # Mailable classes
├── Models/                           # Central-database Eloquent models
│   ├── AppSetting.php
│   ├── Plan.php
│   ├── PlanPrice.php
│   ├── SocialAccount.php
│   ├── Task.php
│   ├── Tenant.php
│   └── User.php
├── Modules/                          # Domain modules (modular monolith)
│   ├── Billing/                      # Billing bounded context
│   ├── Products/                     # Product management
│   ├── Orders/                       # Order management
│   ├── Inventory/                    # Inventory tracking
│   ├── CRM/                          # Customer relationship management
│   └── (future modules)
├── Notifications/                    # Notification classes
├── Observers/                        # Eloquent observers
├── Policies/                         # Authorization policies
├── Providers/                        # Service providers
│   ├── AppServiceProvider.php
│   ├── BillingServiceProvider.php
│   ├── FortifyServiceProvider.php
│   └── TenancyServiceProvider.php
├── Rules/                            # Custom validation rules
├── Services/                         # Application-level services
│   ├── BillingEmailService.php
│   └── SocialAuthService.php
└── Shared/                           # Shared kernel (cross-cutting)
    ├── Traits/
    ├── Helpers/
    └── Constants/
```

## Module Structure (`app/Modules/{Domain}/`)

Every module follows this consistent internal structure:

```
app/Modules/{Domain}/
├── Actions/                          # Single-responsibility action classes
├── Contracts/                        # Module interfaces
├── DTOs/                             # Data transfer objects
├── Drivers/                          # External service drivers (strategy pattern)
├── Enums/                            # PHP 8.1 backed enums
├── Events/                           # Domain events
├── Exceptions/                       # Module-specific exceptions
├── Listeners/                        # Domain event listeners
├── Models/                           # Eloquent models (tenant or central)
├── Observers/                        # Model observers
├── Policies/                         # Authorization policies
├── Requests/                         # Form request validation
├── Resources/                        # API resources
├── Rules/                            # Custom validation rules
├── Services/                         # Business logic services
├── Tests/                            # Module-specific tests
│   ├── Feature/
│   └── Unit/
└── Webhooks/                         # Webhook handlers
```

> **Note:** This project intentionally does NOT use the Repository pattern. Services operate directly on Eloquent models, which serve as the data access layer. This reduces boilerplate while maintaining testability through service-level abstraction.

## Frontend Structure (`resources/js/`)

```
resources/js/
├── app.tsx                           # Inertia entry point
├── ssr.tsx                           # SSR entry point
├── css/
│   └── app.css                       # Tailwind imports
├── actions/                          # Wayfinder-generated controller actions
│   └── (auto-generated, do not edit)
├── routes/                           # Wayfinder-generated named routes
│   └── (auto-generated, do not edit)
├── components/
│   ├── ui/                           # shadcn-style UI primitives
│   │   ├── alert-dialog.tsx
│   │   ├── avatar.tsx
│   │   ├── badge.tsx
│   │   ├── button.tsx
│   │   ├── card.tsx
│   │   ├── dialog.tsx
│   │   ├── dropdown-menu.tsx
│   │   ├── form.tsx
│   │   ├── input.tsx
│   │   ├── label.tsx
│   │   ├── select.tsx
│   │   ├── table.tsx
│   │   ├── tabs.tsx
│   │   └── ...
│   ├── app-*.tsx                     # App shell components
│   ├── nav-*.tsx                     # Navigation components
│   └── (shared components)
├── hooks/                            # Custom React hooks
│   ├── use-appearance.tsx
│   ├── use-clipboard.ts
│   ├── use-current-url.ts
│   ├── use-initials.tsx
│   ├── use-mobile.tsx
│   └── ...
├── layouts/                          # Page layout components
│   ├── app-layout.tsx
│   ├── auth-layout.tsx
│   ├── app/
│   ├── auth/
│   ├── settings/
│   └── admin/
├── pages/                            # Inertia page components
│   ├── welcome.tsx
│   ├── dashboard.tsx
│   ├── auth/
│   ├── settings/
│   ├── admin/
│   ├── billing/
│   ├── team/
│   └── (module pages)
├── lib/                              # Utility functions
│   └── utils.ts
└── types/                            # TypeScript type definitions
    ├── auth.ts
    ├── navigation.ts
    ├── ui.ts
    ├── index.ts
    └── ...
```

## Database Structure (`database/`)

```
database/
├── factories/                        # Model factories
│   ├── PlanFactory.php
│   ├── SocialAccountFactory.php
│   ├── TaskFactory.php
│   ├── TenantFactory.php
│   └── UserFactory.php
├── migrations/
│   ├── 0001_01_01_000000_...         # Central: users, sessions
│   ├── 0001_01_01_000001_...         # Central: cache
│   ├── 0001_01_01_000002_...         # Central: jobs
│   ├── 2019_09_15_000010_...         # Central: tenants
│   ├── 2019_09_15_000020_...         # Central: domains
│   ├── (central migrations)          # Central: all shared tables
│   └── tenant/                       # Tenant-specific migrations
│       └── 2026_02_22_094519_...     # Tenant: tasks
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── PlanSeeder.php
│   └── (module seeders)
└── (module seeders can live in app/Modules/{Domain}/Database/)
```

## Route Structure (`routes/`)

```
routes/
├── web.php                           # Public routes (landing, pricing)
├── tenant.php                        # Tenant-scoped routes (behind tenancy)
├── admin.php                         # Admin routes (/admin prefix)
├── settings.php                      # User settings routes
├── console.php                       # Artisan console routes
└── api.php                           # API routes (if enabled)
```

## Config Structure (`config/`)

```
config/
├── app.php                           # Application config
├── auth.php                          # Authentication config
├── billing.php                       # Billing gateway config
├── cache.php                         # Cache config
├── cashier.php                       # Stripe Cashier config
├── database.php                      # Database connections
├── filesystems.php                   # File storage config
├── fortify.php                       # Fortify auth config
├── inertia.php                       # Inertia.js config
├── logging.php                       # Logging config
├── mail.php                          # Email config
├── permission.php                    # Spatie Permission config
├── queue.php                         # Queue config
├── services.php                      # Third-party services
├── session.php                       # Session config
├── social-auth.php                   # Social auth providers
└── tenancy.php                       # Stancl Tenancy config
```

## Test Structure (`tests/`)

```
tests/
├── Feature/                          # Feature/integration tests
│   ├── Auth/
│   ├── Billing/
│   ├── Admin/
│   ├── Settings/
│   └── (module tests)
├── Unit/                             # Unit tests
├── Support/
│   └── RefreshMultiDatabase.php      # Custom trait for multi-DB testing
├── Pest.php                          # Pest configuration
└── TestCase.php                      # Base test case
```

## Module Database Structure (Optional)

For larger modules, database files can live inside the module:

```
app/Modules/{Domain}/Database/
├── Migrations/
│   └── 2026_05_19_000001_...
├── Seeders/
│   └── DomainSeeder.php
└── Factories/
    └── DomainModelFactory.php
```

Register these in the module's service provider:

```php
$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
```

## Key Conventions

1. **Central models** live in `app/Models/` and use `CentralConnection` trait
2. **Tenant models** live in `app/Modules/{Domain}/Models/` and use tenant connection
3. **Module tests** can live in `tests/Feature/{Domain}/` or `app/Modules/{Domain}/Tests/`
4. **Wayfinder-generated** files (`actions/`, `routes/`) are auto-generated, never edited
5. **UI components** in `components/ui/` are shadcn primitives, do not modify directly
6. **Shared components** go in `components/` at the appropriate level
7. **Module-specific frontend components** go in `resources/js/components/{domain}/`
