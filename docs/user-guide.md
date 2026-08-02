# Souda User Guide

Souda is a business management platform that adapts to your industry — whether you run a restaurant, retail store, salon, pharmacy, or any other business type. This guide walks through everything from account setup to day-to-day operations.

---

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Dashboard & Navigation](#2-dashboard--navigation)
3. [Onboarding & Business Setup](#3-onboarding--business-setup)
4. [Products & Catalog](#4-products--catalog)
5. [Inventory & Stock](#5-inventory--stock)
6. [Sales & Orders](#6-sales--orders)
7. [Customers (CRM)](#7-customers-crm)
8. [Team Management](#8-team-management)
9. [Tasks](#9-tasks)
10. [Billing & Subscriptions](#10-billing--subscriptions)
11. [Settings](#11-settings)
12. [Admin Panel](#12-admin-panel)
13. [Industry-Specific Features](#13-industry-specific-features)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Getting Started

### Creating an Account

1. Go to the login page and click **"Create account"**.
2. Fill in your name, email, and password.
3. Select your **business type** from the dropdown (e.g., Restaurant, Grocery, Fashion, Pharmacy, etc.).
4. Click **"Register"**.

After registration, you'll enter the onboarding flow where your account is provisioned for your selected industry.

### Signing In

Enter your email and password on the login page. You can also sign in with **Google** or **GitHub** if you've connected those accounts.

### Password Reset

If you forget your password, click **"Forgot your password?"** on the login page. Enter your email and you'll receive a reset link.

---

## 2. Dashboard & Navigation

### The Sidebar

The sidebar is your main navigation hub. It has several sections:

**Platform** (always visible):
- **Dashboard** — Summary of key metrics, charts, and recent activity
- **Tasks** — Simple to-do list

**Modules** (shown based on your business type and subscription):
- Products, Inventory, Orders, CRM, Team, Billing, POS, and more
- Only modules relevant to your industry appear here

**Admin** (visible to admin users only):
- Dashboard, Users, Pricing, Settings

Click the sidebar collapse button to switch to icon-only mode for more screen space.

### Dashboard Widgets

Your dashboard shows:
- Key performance indicators (revenue, orders, stock alerts)
- Recent activity
- Charts and trends

Widgets vary by industry (a restaurant sees kitchen metrics; a salon sees appointment stats).

---

## 3. Onboarding & Business Setup

When you first register, the system guides you through setup steps. The provisioning process configures your account with the correct modules, permissions, and defaults for your industry.

During onboarding you may be asked to:
- Confirm your business type
- Provide additional business details
- Wait while your account is provisioned (usually a few seconds)

Once complete, you'll land on your dashboard ready to use the platform.

---

## 4. Products & Catalog

The Products module lets you manage everything you sell.

### Products

Navigate to **Products > All Products** to see your product list. You can:

- **Create a product**: Click "Add Product" and fill in the form. Required fields include name, price, and category. You can also add images, descriptions, SKUs, and variants.
- **Edit a product**: Click on any product to edit its details.
- **Search & filter**: Use the search bar to find products by name or SKU. Filter by category, brand, or status.
- **Product types**: Simple (single item), Configurable (has variants like size/color), Bundle (multiple items sold together), Virtual (services or digital goods).
- **Product statuses**: Draft (not yet published), Active (available for sale), Archived (no longer sold).

### Categories

Organize products into categories. You can:

- **Create nested categories** (e.g., Beverages > Soft Drinks > Cola)
- **Reorder** categories by dragging them
- **View products** within each category

### Brands

Manage brands and attach them to products. Each brand can have a name and logo.

### Attributes

Define custom attributes for product variants — for example:
- **Size**: Small, Medium, Large
- **Color**: Red, Blue, Green
- **Material**: Cotton, Polyester, Wool

Attributes let you create variant combinations (e.g., a t-shirt available in Red/Small, Red/Medium, Blue/Small, etc.).

---

## 5. Inventory & Stock

The Inventory module helps you track stock levels across warehouses, including batch and serial-number tracking for industries that need it.

### Warehouses

Manage your locations at **Inventory > Warehouses**. Each warehouse has a name, slug, and type. Mark a warehouse as **default** to make it the primary stock location for new products.

### Stock Overview

Navigate to **Inventory > Stock** to see current stock levels for all products. Each product shows:
- Current quantity in each warehouse
- Low stock indicators (products below minimum threshold are highlighted)
- Stock value and average unit cost

### Stock Movements

Every stock change is logged in the inventory ledger for full traceability:

| Movement Type | Description |
|---------------|-------------|
| Received | Stock received from supplier |
| Sold | Stock sold to customer |
| Return | Customer return |
| Adjustment | Manual stock correction |
| Transfer Out | Moved to another warehouse |
| Transfer In | Received from another warehouse |
| Damaged | Marked as damaged |
| Expired | Marked as expired |

### Stock Transfers

Move stock between warehouses:
1. Go to **Inventory > Stock Transfers**
2. Select source and destination warehouses
3. Choose products and quantities
4. Complete the transfer — stock is moved automatically, and stock reservations are handled

### Stock Counts

Run periodic stocktakes at **Inventory > Counts**. Create a count for a warehouse, enter expected vs. counted quantities per item, and finalize to post adjustments to stock automatically.

### Batches & Serial Numbers

- **Batches** track stock by production/receipt batch (with optional expiry dates) for industries like pharmacy, bakery, and restaurant.
- **Serial Numbers** track individual units end-to-end (received → sold) for industries like electronics, hardware, and cosmetics.

### Low Stock Alerts & Rules

Products below their minimum stock threshold appear in the low stock view. Restock them directly from this screen. Configure per-product rules (reorder points, suggested quantities) at **Inventory > Rules**; the module surfaces purchase suggestions based on demand forecasts.

---

## 6. Sales & Orders

### Creating Orders

Process customer orders through the Orders module. You can:
- Create new orders manually
- Select products and quantities
- Apply discounts
- Choose payment method
- Complete the sale

### POS (Point of Sale)

If your business uses a physical register, the POS module provides a streamlined checkout interface optimized for fast transactions. Features include:
- Barcode scanning
- Product search
- Quick-add buttons for popular items
- Multiple payment methods

### Order Types (Industry-Specific)

Some industries have specialized order types:
- **Restaurant**: Dine-in, Takeaway, Delivery
- **Wholesale**: Bulk orders, Quotations
- **General**: Standard sales orders

---

## 7. Customers (CRM)

The CRM module helps you manage customer relationships.

### Customer Management

View and manage your customer list:
- Contact details (name, email, phone, address)
- Purchase history
- Notes and tags

### Segments

Group customers into segments for targeted marketing and analysis. For example:
- VIP customers
- New customers (last 30 days)
- Inactive customers (no purchase in 90 days)

---

## 8. Team Management

The Team module lets you add team members to your account.

### Inviting Members

1. Go to the **Team** page
2. Click **"Invite Member"**
3. Enter their email address
4. They'll receive an invitation email
5. Once accepted, they can access your account based on their role

### Managing Members

- View all current team members
- Resend invitations if needed
- Remove members who no longer need access

### Roles & Permissions

Roles vary by industry. Common roles include:
- **Admin**: Full access
- **Manager**: Operations management
- **Cashier**: Register and sales
- **Staff**: Limited operational access

---

## 9. Tasks

A simple built-in task manager:
- Create tasks with descriptions
- Mark tasks as complete
- Delete completed or unnecessary tasks
- View all tasks in the sidebar

---

## 10. Billing & Subscriptions

### Plans & Pricing

Your subscription plan determines which features and limits are available. The billing page shows:
- Your current plan and status
- Available plans for upgrade or downgrade
- Monthly and yearly pricing options

### Subscription Statuses

| Status | Description |
|--------|-------------|
| Trial | Free trial period — full access, limited time |
| Active | Paid subscription — full access |
| Grace | Payment overdue — service still available temporarily |
| Expired | Subscription ended — access may be limited |
| Cancelled | Manually cancelled — access until billing period ends |
| Pending | Awaiting payment confirmation |

### Subscribing to a Plan

1. Go to **Billing** in the sidebar
2. Browse available plans
3. Select a plan and billing cycle (monthly or yearly)
4. Choose your payment method
5. Complete payment on the payment gateway's page
6. Your subscription activates immediately

### Managing Your Subscription

- **Change plans**: Upgrade or downgrade from the billing page
- **Cancel subscription**: Cancel anytime — access continues until the current period ends
- **View invoices**: See payment history at **Billing > Invoices**

### Payment Methods

Supported payment gateways:
- **Stripe** — Credit/debit cards
- **SSLCommerz** — Bangladeshi payment gateway
- **bKash** — Mobile money (Bangladesh)
- **Nagad** — Mobile money (Bangladesh)
- **PortWallet** — Digital wallet
- **Manual** — Offline/manual payment

---

## 11. Settings

### Profile

Update your name, email, and profile photo at **Settings > Profile**.

### Password

Change your password at **Settings > Password**.

### Two-Factor Authentication

Add an extra layer of security:
1. Go to **Settings > Two-Factor Authentication**
2. Click **"Enable"**
3. Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.)
4. Enter the verification code
5. Save your recovery codes in a safe place

### Appearance

Choose between Light, Dark, or System theme at **Settings > Appearance**.

### Connected Accounts

Link your Google or GitHub account for easy sign-in at **Settings > Connected Accounts**.

---

## 12. Admin Panel

The admin panel is available to users with admin privileges.

### Admin Dashboard

Overview of platform-wide metrics:
- Total users and tenants
- Subscription revenue
- Growth charts
- Active vs. inactive tenants

### User Management

View all platform users. You can:
- Soft-delete a user (they can be restored)
- Restore a soft-deleted user
- Permanently delete a user

### Pricing Management

Create and manage subscription plans:
- Add new plans with features and pricing
- Set monthly and yearly prices
- Configure feature limits (e.g., max products, max team members)
- Set trial parameters
- Reorder plans by dragging
- Toggle plans active/inactive

### App Settings

Configure global application settings:

**General**:
- Application name and logo
- Timezone and currency
- Enable/disable new registrations

**Email**:
- SMTP server configuration
- Sender name and address

**Social Authentication**:
- Google OAuth credentials
- GitHub OAuth credentials

---

## 13. Industry-Specific Features

Souda adapts to your industry. Here are features available for each business type:

### Grocery
- Perishable item tracking with expiry dates
- Unit conversions (kg to g, liter to ml)
- Supplier management with purchase orders

### Restaurant
- Menu management with modifiers and add-ons
- Kitchen Display System (KDS) — orders appear on kitchen screens
- Table management with floor plan
- Dine-in, Takeaway, and Delivery order types
- Recipe costing

### Cafe
- Beverage customization (size, temperature, extras)
- Recipe costing
- Quick-service checkout

### Fashion
- Size/color matrix for product variants
- Seasonal collections
- Multi-variant management

### Electronics
- IMEI number tracking per device
- Warranty management
- Serial number tracking

### Pharmacy
- Prescription management
- Batch/lot tracking with expiry monitoring
- GST/HSN code support

### Salon
- Service menu with duration and pricing
- Appointment booking with staff scheduling
- Staff commission tracking

### Spa
- Package management (multiple services bundled)
- Therapist scheduling
- Membership plans

### Bakery
- Recipe costing with ingredient tracking
- Batch production management
- Freshness tracking with sell-by dates

### Bookstore
- ISBN-based cataloging
- Author and publisher tracking
- Genre classification

### Hardware
- Unit conversions (meters, inches, pieces)
- Measurement-based inventory
- Supplier management

### Cosmetics
- Shade/color management
- Ingredient tracking
- Batch/lot tracking

### Wholesale
- Tiered pricing (price breaks by volume)
- Minimum Order Quantity (MOQ)
- Bulk distribution management

### Distribution
- Fleet management
- Route planning
- Delivery tracking

### Agro Shop
- Seed and fertilizer tracking
- Batch/lot tracking
- Seasonal inventory planning

---

## 14. Troubleshooting

### Menus or Features Not Showing

If sidebar menus are missing after signing up:

1. Make sure you've completed the **subscription process** — most modules require an active subscription.
2. Try **refreshing the page** (Cmd+R / Ctrl+R).
3. If you just subscribed, do a **hard refresh** (Cmd+Shift+R / Ctrl+Shift+R) to clear any cached page data.
4. Check your **subscription status** at the Billing page — is it Active or Trial?

### Payment Issues

- Ensure your payment method has sufficient funds.
- If a payment fails, try a different payment gateway.
- Contact support if issues persist.

### Can't Access a Feature

- Check if your current plan includes that feature (compare plans on the Billing page).
- Some features are industry-specific and only appear for certain business types.
- Verify your subscription is active.

### Need More Help

Contact your system administrator or refer to the application support resources.
