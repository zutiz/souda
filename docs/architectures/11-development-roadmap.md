# Development Roadmap

## Overview

Phased development plan for building the multi-tenant SaaS ERP platform. Each phase delivers working, testable functionality.

## Phase 0: Foundation (Current State)

**Status:** Partially Complete

### Completed
- [x] Laravel 12 setup with PHP 8.4
- [x] Multi-tenancy configuration (stancl/tenancy, hybrid mode)
- [x] Authentication (Fortify + Socialite)
- [x] Roles & permissions (custom Permission/Role models extending Spatie)
- [x] Billing module (custom + Cashier with strategy pattern)
- [x] Frontend scaffold (React + Inertia + Tailwind + shadcn/ui)
- [x] CI/CD basics (Pint, Pest, ESLint)
- [x] Product module (17 models, 19 tenant migrations, 10 controllers, 20 services)
- [x] Tenant config system (TenantConfig DTO, TenantConfigService, caching)
- [x] Onboarding pipeline (10-step provisioning, 16 TenantTemplates, 15 IndustryPacks)
- [x] Dashboard with industry-specific widgets
- [x] Module navigation registry (11 modules)
- [x] Social auth (Google, GitHub)
- [x] Admin tenant management (list, show, deactivate, restore, force-delete)
- [x] Subscription management (trial, grace, expired, cancelled states)
- [x] Seat-based pricing (SeatPricingStrategy, allocation lifecycle)
- [x] Two-phase migration pattern (Phase 1 skeleton → Phase 2 fill)
- [x] Wayfinder v0 + Vite plugin (auto-generated @/actions/, @/routes/)
- [x] Frontend architecture (React Query, react-hook-form + Zod, TanStack Table v8)

### Remaining

- [ ] Complete Stripe/SSLCommerz/BKash/Nagad/PortWallet gateway drivers (stubs exist)
- [ ] Add comprehensive test coverage
- [ ] Set up Horizon for queue monitoring
- [ ] Configure Redis for queues and cache
- [ ] Set up staging environment

**Duration:** 1-2 weeks (completion)

---

## Phase 1: Product Management Module

**Goal:** Complete product catalog with categories, variants, and media.

### Week 1-2: Core Product Structure ✅ (Complete)

- [x] Create `Product` module structure
- [x] Define `Product`, `Category`, `Brand`, `Variant`, `Warehouse`, `StockMovement`, `StockReservation`, `Attribute`, `TaxCategory`, `TaxRate`, `PricingRule`, `AuditLog`, `ProductMedia`, `AttributeValue`, `ProductAttributeValue`, `ProductAttributeTextValue`, `WarehouseStock` models (17)
- [x] Create 19 tenant migrations for product tables
- [x] Implement `ProductService` with CRUD operations
- [x] Create `ProductData`, `ProductSummaryDTO`, `ProductWithStockDTO`, `VariantDTO`
- [x] Create `StoreProductRequest`, `UpdateProductRequest`, `StoreCategoryRequest`, `StoreBrandRequest`, `StoreVariantRequest`, `StoreWarehouseRequest`, `StockAdjustmentRequest`, `StockTransferRequest`, `StoreAttributeRequest`, `StorePricingRuleRequest`
- [x] Write unit tests for services

### Week 3: Variants & Inventory Base ✅ (Complete)

- [x] Implement variant management (size, color, custom options)
- [x] Create SKU generation service (`DefaultSKUGenerator`)
- [x] Implement bulk product import (`ImportProductsJob`)
- [x] Create `ProductCreated`, `ProductUpdated`, `ProductDeleted`, `ProductArchived`, `ProductPublished`, `VariantCreated`, `VariantUpdated`, `VariantDeleted`, `StockUpdated`, `StockDepleted`, `LowStockAlert`, `StockReservationCreated`, `StockReservationExpired`, `StockTransferCompleted` events (14)
- [x] Implement 13 listeners + 8 queued jobs
- [x] Write feature tests for product CRUD

### Week 4: Media & Search ✅ (Complete)

- [x] `ProductMedia` model + `MediaService` for product images (in-house solution)
- [x] Implement Scout-based search indexing (`IndexProductForSearch`, `IndexProductJob`, `ReindexAllProductsJob`)
- [x] Frontend product listing page with filters via Inertia + React Query
- [x] Product create/edit forms with react-hook-form + Zod validation
- [x] Image upload with validation (MediaController)

### Week 5: Frontend & Polish ✅ (Complete)

- [x] Product list page with TanStack Table (sorting, filtering, pagination)
- [x] Product detail page with variant selection
- [x] Category management UI
- [x] Brand management UI
- [x] Attribute management UI
- [x] Stock management pages
- [x] Warehouse management pages
- [x] Write integration tests

**Deliverables:**
- Full product catalog management
- Product search and filtering
- Image management
- Bulk import/export
- Stock tracking with reservations
- Multi-warehouse support
- Complete test coverage

**Duration:** Complete

---

## Phase 2: Order Management Module

**Goal:** Complete order processing from creation to fulfillment.

### Week 6-7: Core Order Structure

- [ ] Create `Orders` module structure
- [ ] Define `Order`, `OrderItem` models
- [ ] Create tenant migrations for order tables
- [ ] Implement `OrderService` with CRUD operations
- [ ] Create `OrderDTO`, `OrderItemDTO`, `OrderCreatedDTO`
- [ ] Implement order number generation
- [ ] Create `StoreOrderRequest`, `UpdateOrderRequest`
- [ ] Implement order status workflow (Pending → Processing → Shipped → Delivered)
- [ ] Write unit tests

### Week 8: Order Processing

- [ ] Implement `OrderCreated` event dispatching
- [ ] Create `DeductStock` listener (Inventory module integration)
- [ ] Create `UpdateCustomerActivity` listener (CRM module integration)
- [ ] Implement order cancellation flow
- [ ] Implement order refund flow
- [ ] Create order email notifications
- [ ] Write feature tests

### Week 9: Frontend

- [ ] Order list page with status filters
- [ ] Order detail page with line items
- [ ] Order creation form (manual order entry)
- [ ] Order status update workflow
- [ ] Order printing (invoice/packing slip)

### Week 10: Advanced Features

- [ ] Order export (CSV, PDF)
- [ ] Order search and filtering
- [ ] Bulk order actions
- [ ] Order notes and activity log
- [ ] Integration tests for cross-module communication

**Deliverables:**
- Complete order management
- Order status workflow
- Cross-module event integration
- Order printing and export
- Complete test coverage

**Duration:** 5 weeks

---

## Phase 3: Inventory Tracking Module

**Goal:** Real-time stock tracking with movement history and alerts.

### Week 11-12: Core Inventory

- [ ] Create `Inventory` module structure
- [ ] Define `Stock`, `StockMovement`, `Warehouse` models
- [ ] Create tenant migrations for inventory tables
- [ ] Implement `InventoryService` with stock operations
- [ ] Create `StockDTO`, `StockMovementDTO`
- [ ] Implement stock deduction (from OrderCreated listener)
- [ ] Implement stock adjustment (manual)
- [ ] Implement stock transfer between warehouses
- [ ] Write unit tests

### Week 13: Alerts & Reporting

- [ ] Implement low stock threshold per product
- [ ] Create `StockDepleted`, `LowStockAlert` events
- [ ] Implement `MarkProductUnavailable` listener
- [ ] Create stock alert notifications
- [ ] Implement stock valuation report
- [ ] Implement stock movement history

### Week 14: Frontend

- [ ] Stock overview dashboard
- [ ] Stock movement history table
- [ ] Low stock alerts page
- [ ] Stock adjustment form
- [ ] Warehouse management UI

### Week 15: Advanced Features

- [ ] Barcode/QR code support
- [ ] Batch/lot tracking
- [ ] Expiry date tracking
- [ ] Stock forecasting (basic)
- [ ] Integration tests

**Deliverables:**
- Real-time stock tracking
- Multi-warehouse support
- Low stock alerts
- Stock movement history
- Stock valuation reports
- Complete test coverage

**Duration:** 5 weeks

---

## Phase 4: CRM Module

**Goal:** Customer relationship management with contact tracking and deal pipeline.

### Week 16-17: Core CRM

- [ ] Create `CRM` module structure
- [ ] Define `Contact`, `Interaction`, `Deal`, `Pipeline` models
- [ ] Create tenant migrations for CRM tables
- [ ] Implement `CRMService` with CRUD operations
- [ ] Create `ContactDTO`, `InteractionDTO`, `DealDTO`
- [ ] Implement contact import (CSV)
- [ ] Implement interaction logging
- [ ] Create `ContactCreated`, `InteractionLogged` events
- [ ] Write unit tests

### Week 18: Deal Pipeline

- [ ] Implement deal pipeline stages
- [ ] Implement deal progression workflow
- [ ] Create `DealWon`, `DealLost` events
- [ ] Implement deal value tracking
- [ ] Implement pipeline analytics
- [ ] Write feature tests

### Week 19: Frontend

- [ ] Contact list with search and filters
- [ ] Contact detail page with activity timeline
- [ ] Deal pipeline board (Kanban-style)
- [ ] Interaction logging form
- [ ] Contact import wizard

### Week 20: Advanced Features

- [ ] Contact segmentation
- [ ] Deal forecasting
- [ ] Activity reports
- [ ] Email integration (send/receive)
- [ ] Integration tests

**Deliverables:**
- Contact management
- Interaction tracking
- Deal pipeline
- Pipeline analytics
- Contact import
- Complete test coverage

**Duration:** 5 weeks

---

## Phase 5: Billing Enhancements

**Goal:** Complete billing module with all gateway drivers and advanced features.

> **Note:** Seat-based pricing (strategy pattern, seat allocation, overage invoicing, team invitation routes, middleware gating) has been implemented ahead of schedule.

### Completed

- [x] Seat pricing strategy pattern (`PricingStrategy` interface + `SeatPricingStrategy`/`FlatPricingStrategy`)
- [x] Seat allocation lifecycle (allocate, activate pending, release, resend invite)
- [x] Overage invoicing service (`OverageInvoiceService`)
- [x] `EnsureSeatAvailable` middleware (aliased `seat`)
- [x] Team invitation routes + controller + frontend page
- [x] Seat events + listeners (`SeatAllocated`, `SeatReleased`, `SeatOverageInvoiced`, `RecalculateSeatUsage`)
- [x] `SyncSeatAllocations` queued job for user→seat reconciliation

### Remaining

### Week 21-22: Gateway Completion

- [ ] Complete `StripeDriver` implementation
- [ ] Complete `BKashDriver` implementation
- [ ] Complete `NagadDriver` implementation
- [ ] Complete `PortWalletDriver` implementation
- [ ] Implement webhook signature verification for all gateways
- [ ] Write gateway integration tests

### Week 23: Advanced Billing

- [ ] Implement subscription upgrades/downgrades
- [ ] Implement proration calculations
- [ ] Implement coupon/discount system
- [ ] Implement invoice PDF generation
- [ ] Implement payment history page
- [ ] Write feature tests

### Week 24: Frontend

- [ ] Subscription management page
- [ ] Invoice history page (enhance existing)
- [ ] Payment method management
- [ ] Upgrade/downgrade flow
- [ ] Coupon redemption UI

**Deliverables:**
- All payment gateways functional
- Subscription management
- Invoice generation
- Payment history
- Complete test coverage

**Duration:** 4 weeks

---

## Phase 6: Platform Features

**Goal:** Cross-cutting platform features and infrastructure.

### Week 25-26: Dashboard & Analytics

- [ ] Tenant dashboard with KPIs
- [ ] Revenue analytics
- [ ] Order analytics
- [ ] Product analytics
- [ ] Inventory analytics
- [ ] CRM analytics
- [ ] Custom report builder

### Week 27-28: Notifications System

- [ ] Notification preferences per tenant
- [ ] Email notification templates
- [ ] In-app notification center
- [ ] Push notification support (optional)
- [ ] Notification scheduling

### Week 29-30: Settings & Configuration

- [ ] Tenant settings page
- [ ] Feature toggle management
- [ ] Email template customization
- [ ] Branding customization (logo, colors)
- [ ] Timezone and locale settings

### Week 31-32: API Layer

- [ ] API authentication (Sanctum)
- [ ] API versioning strategy
- [ ] Product API endpoints
- [ ] Order API endpoints
- [ ] Inventory API endpoints
- [ ] CRM API endpoints
- [ ] API documentation (OpenAPI/Swagger)

**Deliverables:**
- Analytics dashboard
- Notification system
- Tenant customization
- RESTful API
- API documentation

**Duration:** 8 weeks

---

## Phase 7: Production Readiness

**Goal:** Security, performance, monitoring, and deployment.

### Week 33-34: Security Hardening

- [ ] Security audit
- [ ] Rate limiting configuration
- [ ] CSRF protection review
- [ ] XSS prevention review
- [ ] SQL injection prevention review
- [ ] File upload security
- [ ] API security review
- [ ] Penetration testing

### Week 35-36: Performance Optimization

- [ ] Database query optimization
- [ ] Redis caching strategy
- [ ] Queue optimization
- [ ] Asset optimization (Vite)
- [ ] CDN configuration
- [ ] Database indexing review
- [ ] Load testing

### Week 37-38: Monitoring & Observability

- [ ] Horizon dashboard configuration
- [ ] Error tracking (Sentry)
- [ ] Application metrics
- [ ] Log aggregation
- [ ] Health check endpoints
- [ ] Uptime monitoring
- [ ] Alert configuration

### Week 39-40: Deployment & Documentation

- [ ] CI/CD pipeline
- [ ] Staging environment
- [ ] Production deployment
- [ ] Database backup strategy
- [ ] Disaster recovery plan
- [ ] Runbook documentation
- [ ] User documentation
- [ ] API documentation

**Deliverables:**
- Production-ready application
- Monitoring and alerting
- CI/CD pipeline
- Documentation
- Backup and recovery

**Duration:** 8 weeks

---

## Total Timeline

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Phase 0: Foundation | 1-2 weeks | 2 weeks |
| Phase 1: Products | 5 weeks | 7 weeks |
| Phase 2: Orders | 5 weeks | 12 weeks |
| Phase 3: Inventory | 5 weeks | 17 weeks |
| Phase 4: CRM | 5 weeks | 22 weeks |
| Phase 5: Billing | 4 weeks | 26 weeks |
| Phase 6: Platform | 8 weeks | 34 weeks |
| Phase 7: Production | 8 weeks | 42 weeks |

**Total: ~42 weeks (10-11 months)**

## Parallel Development Opportunities

| Phase | Can Run In Parallel With |
|-------|-------------------------|
| Phase 1 (Products) | Phase 5 (Billing enhancements) |
| Phase 2 (Orders) | Phase 3 (Inventory) - with coordination |
| Phase 4 (CRM) | Phase 6 (Platform features) |
| Phase 7 (Production) | All phases (ongoing) |

## Milestone Checkpoints

| Milestone | Week | Deliverable |
|-----------|------|-------------|
| M1: Product Catalog | 7 | Full product management |
| M2: Order Processing | 12 | Complete order lifecycle |
| M3: Inventory Control | 17 | Stock tracking and alerts |
| M4: CRM System | 22 | Contact and deal management |
| M5: Complete Billing | 26 | All gateways + subscriptions |
| M6: Platform Complete | 34 | Dashboard, API, notifications |
| M7: Production Launch | 42 | Deployed and monitored |

## Risk Mitigation

| Risk | Mitigation |
|------|------------|
| Scope creep | Strict phase boundaries, no feature additions mid-phase |
| Tenant isolation bugs | Comprehensive multi-DB testing in every phase |
| Performance issues | Load testing at each milestone |
| Integration failures | Contract tests between modules |
| Timeline slippage | Buffer weeks built into each phase |
| Package incompatibility | Test packages in isolation before integration |

## Definition of Done (Per Phase)

- [ ] All features implemented and tested
- [ ] Unit tests written (>80% coverage)
- [ ] Feature tests written
- [ ] Integration tests for cross-module communication
- [ ] Code reviewed and formatted (Pint)
- [ ] TypeScript type-checked
- [ ] ESLint passes
- [ ] Documentation updated
- [ ] Demo/staging deployed
- [ ] Stakeholder sign-off
