# Recommended Complete Architecture

## System Overview

Multi-tenant SaaS ERP platform for SME F-Commerce and E-Commerce businesses, built on a modular monolith with domain-driven design principles.

```
┌─────────────────────────────────────────────────────────────────┐
│                        Client Layer                              │
│  React SPA (Inertia.js v2) + Tailwind CSS v4 + shadcn/ui        │
└────────────────────────────┬────────────────────────────────────┘
                             │ HTTP / WebSocket
┌────────────────────────────▼────────────────────────────────────┐
│                     Presentation Layer                           │
│  Controllers (Inertia + API)  │  Form Requests  │  Resources     │
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                     Application Layer                            │
│  Actions  │  Services  │  DTOs  │  Validators  │  Notifications │
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                      Domain Layer                                │
│  Modules (Billing, Products, Orders, Inventory, CRM, etc.)      │
│  ├── Models  │  Enums  │  Events  │  Listeners  │  Exceptions   │
│  ├── Contracts (Interfaces)  │  DTOs  │  Actions  │  Services   │
│  └── Repositories  │  Rules  │  Policies  │  Observers           │
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                   Infrastructure Layer                           │
│  Eloquent ORM  │  Queue (Redis)  │  Cache (Redis)  │  Storage   │
│  Payment Gateways  │  Email (Postmark/Resend)  │  Webhooks       │
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                      Data Layer                                  │
│  Central DB (MySQL)  │  Tenant DBs (MySQL)  │  Redis             │
└─────────────────────────────────────────────────────────────────┘
```

## Architectural Principles

### 1. Modular Monolith

- Each business domain is a self-contained module under `app/Modules/{Domain}/`
- Modules communicate via events and service interfaces, never direct model access
- Modules can be extracted into packages later without rewriting
- Shared kernel (`app/Shared/`) for cross-cutting concerns (planned)

### 2. Domain-Driven Design

- Modules represent bounded contexts
- Aggregates enforce invariants within modules
- Domain events for cross-module communication
- Ubiquitous language in code (class/method naming)

### 3. Strict Tenant Isolation

- Multi-database tenancy: each tenant gets isolated MySQL database
- Central database holds shared data (plans, subscriptions, users, tenants)
- Tenant databases hold business data (products, orders, inventory, CRM)
- `InitializeTenancyByUser` middleware enforces tenant context
- Models use `CentralConnection` trait to explicitly stay central
- Queue jobs are tenant-aware via `QueueTenancyBootstrapper`

### 4. Layered Architecture

| Layer | Location | Responsibility |
|-------|----------|----------------|
| **Presentation** | `app/Http/Controllers/`, `resources/js/` | HTTP handling, UI rendering, request validation |
| **Application** | `app/Actions/`, `app/Services/`, `app/DTOs/` | Use cases, orchestration, data transformation |
| **Domain** | `app/Modules/{Domain}/` | Business rules, entities, domain events |
| **Infrastructure** | `app/Modules/{Domain}/Drivers/`, `app/Providers/` | External integrations, service providers |

### 5. SOLID Principles

- **Single Responsibility**: One class, one reason to change
- **Open/Closed**: Extend via interfaces, not modification
- **Liskov Substitution**: Interface implementations are interchangeable
- **Interface Segregation**: Small, focused contracts
- **Dependency Inversion**: Depend on abstractions, resolved via container

## Core Architecture Components

### Multi-Tenancy Strategy

| Aspect | Decision |
|--------|----------|
| **Mode** | Multi-database (one DB per tenant) |
| **Identification** | User-based (`InitializeTenancyByUser`) |
| **Database Naming** | `souda_tenant_{uuid}` |
| **Bootstrappers** | Database, Cache, Filesystem, Queue |
| **Central Data** | Users, tenants, plans, subscriptions, billing, settings, roles/permissions |
| **Tenant Data** | Products, orders, inventory, CRM, tasks, business operations |

### Module Communication

| Pattern | Use Case |
|---------|----------|
| **Domain Events** | Cross-module notifications (e.g., OrderCreated → Inventory deduction) |
| **Service Interfaces** | Module-to-module queries (e.g., Billing queries Plan features) |
| **Action Classes** | Single-purpose operations callable across modules |
| **DTOs** | Data transfer between modules without exposing models |

### Data Flow

```
User Request → Middleware (Tenancy + Auth + Subscription)
  → Controller (validates via Form Request)
  → Action/Service (orchestrates use case)
  → Repository/Model (data access)
  → Domain Event dispatched (if applicable)
  → Listener handles side effects (async via queue)
  → Response (Inertia redirect/props or API Resource)
```

## Security Architecture

| Layer | Mechanism |
|-------|-----------|
| **Authentication** | Laravel Fortify (sessions) + Socialite (OAuth) |
| **Authorization** | Spatie Permission (roles/policies) + Feature middleware |
| **Tenant Isolation** | Database separation + middleware enforcement |
| **Subscription Gating** | `EnsureSubscribed` middleware + `PlanFeatureService` |
| **Input Validation** | Form Request classes + DTOs |
| **CSRF Protection** | Laravel CSRF + exempted webhook routes |
| **Rate Limiting** | Fortify + custom limits |

## Scalability Considerations

| Concern | Strategy |
|---------|----------|
| **Horizontal Scaling** | Stateless app servers, shared Redis, separate DBs |
| **Queue Processing** | Redis driver, worker pools per queue priority |
| **Caching** | Redis with tenant-tagged cache |
| **Database** | Per-tenant isolation allows independent scaling |
| **Asset Delivery** | Vite build + CDN for static assets |
| **Session Storage** | Database-backed sessions |
