# Souda Order Management User Guide

The Order module manages every sale from start to finish — whether it happens at your counter, on your website, over the phone, or through a wholesale account. Every order flows through a clear status workflow, and everything is tracked automatically so you always know what's happening.

---

## Table of Contents

1. [Orders Dashboard](#1-orders-dashboard)
2. [Creating an Order](#2-creating-an-order)
3. [Order Status Workflow](#3-order-status-workflow)
4. [Order Types](#4-order-types)
5. [Order Actions (Cancel, Refund, Update)](#5-order-actions-cancel-refund-update)
6. [Payments](#6-payments)
7. [Refunds](#7-refunds)
8. [Order Notes & Activity Log](#8-order-notes--activity-log)
9. [Invoice & Receipt Printing](#9-invoice--receipt-printing)
10. [Bulk Actions](#10-bulk-actions)
11. [Order Export](#11-order-export)
12. [Shipments & Courier Tracking](#12-shipments--courier-tracking)
13. [Shipping Rates](#13-shipping-rates)
14. [Public Tracking Page](#14-public-tracking-page)
15. [Search & Filtering](#15-search--filtering)
16. [Integration with Other Modules](#16-integration-with-other-modules)

---

## 1. Orders Dashboard

Navigate to **Orders** in the sidebar to see your orders dashboard. This gives you a complete view of all orders across your store:

- **Total orders** — all orders in the selected period
- **Pending orders** — orders waiting to be confirmed
- **Active orders** — orders currently being processed or shipped
- **Today's revenue** — total sales for today
- **Recent orders** — the latest orders placed

Below the summary you'll see the full order list with columns for order number, customer, status, total, payment status, and date. Each row is colour-coded by status so you can quickly spot orders that need attention.

---

## 2. Creating an Order

You can create orders from any register (POS), from the Orders page, or they can come in automatically from your online store.

### Creating a Manual Order

1. Go to **Orders** and click **"New Order"**
2. Select the **customer** from the customer list, or add their details manually (name, phone, email)
3. Add **line items** — search for products by name, SKU, or barcode. Select the quantity and any variant (size, colour, etc.)
4. Set the **order type** — choose whether this is an in-store sale, delivery, takeaway, or wholesale
5. Add an optional **shipping address** (required for delivery orders)
6. Choose a **payment method** — cash, card, mobile banking, or other
7. Add any **notes** for internal reference
8. Click **"Place Order"**

Products are added to your customer's purchase history automatically, and inventory is updated in real time.

### Line Items

Each line item shows:

- **Product name** and SKU
- **Unit price** and quantity
- **Total price** for the line
- Any **discount** or **tax** applied

You can adjust quantities, apply discounts, or remove items before placing the order. For restaurant and cafe orders, you can also add modifiers (with or without spice, extra toppings, etc.).

---

## 3. Order Status Workflow

Every order moves through a clear set of statuses:

```
Pending → Confirmed → Processing → Shipped → Delivered
                                       ↘ Cancelled
                                               ↘ Refunded
```

| Status | What It Means |
|--------|---------------|
| **Pending** | Just received, waiting to be reviewed |
| **Confirmed** | Payment verified, order is accepted |
| **Processing** | Being picked, packed, or prepared |
| **Shipped** | Handed over to the courier for delivery |
| **Delivered** | Reached the customer |
| **Cancelled** | Stopped before completion |
| **Refunded** | Money returned to the customer |

Each status change is recorded in the **Activity Log** so you have a complete history of every order. Once an order reaches **Delivered**, **Cancelled**, or **Refunded**, it cannot be changed directly — use refunds for corrections instead.

---

## 4. Order Types

Depending on your business type, you can create different kinds of orders:

| Type | When To Use |
|------|-------------|
| **In-Store** | Customer buys at your physical counter (POS) |
| **Online** | Customer orders through your website or social media |
| **Delivery** | Customer orders for home delivery |
| **Takeaway** | Customer picks up from your location |
| **Dine-In** | Restaurant or cafe — order served at the table |
| **Wholesale** | Bulk order for another business customer |

The available order types depend on your industry pack. For example, **Dine-In** and **Takeaway** are available for restaurants and cafes, while **Wholesale** is available for wholesale businesses.

---

## 5. Order Actions (Cancel, Refund, Update)

### Cancelling an Order

You can cancel an order at any point before it's delivered:

1. Open the order details page
2. Click **"Cancel Order"**
3. Enter a reason (required — this helps your team understand why)
4. Confirm the cancellation

When an order is cancelled:
- The order status changes to **Cancelled**
- Inventory stock is automatically restored
- The cancellation is recorded in the activity log
- If a payment was taken, a refund may be needed separately

### Updating Order Status

Move an order forward through its workflow:

1. Open the order details page
2. Click **"Update Status"**
3. Select the new status
4. Add a reason or note (optional)
5. Save

The system will only allow valid transitions — you can't skip from Pending directly to Delivered, for example.

---

## 6. Payments

Track payments for each order. Multiple payments can be recorded against a single order (for example, partial cash and partial card).

### Recording a Payment

1. Open the order details page
2. Go to the **Payments** section and click **"Add Payment"**
3. Select the **payment method** (cash, card, mobile banking, bank transfer)
4. Enter the **amount**
5. Add a **reference** (transaction ID, cheque number, etc.) if available
6. Save

### Payment Statuses

| Status | Meaning |
|--------|---------|
| **Pending** | Payment expected but not yet received |
| **Paid** | Full payment received |
| **Partially Paid** | Some payment received, balance remains |
| **Refunded** | Full amount returned to customer |
| **Partially Refunded** | Only part of the payment was refunded |
| **Failed** | Payment attempt was unsuccessful |

The payment status updates automatically as payments and refunds are recorded.

---

## 7. Refunds

When a customer needs their money back, you can process a full or partial refund.

### Full Refund

1. Open the order details page
2. Click **"Refund Order"**
3. The full order total is pre-filled as the refund amount
4. Enter the **reason** for the refund
5. Confirm

### Partial Refund

1. Open the order details page
2. Click **"Refund Order"**
3. Enter the **amount** (less than the total)
4. Enter the **reason**
5. Confirm

You can also refund individual line items:

1. Open the order and find the item you want to refund
2. Click **"Refund Item"**
3. Enter the amount and reason
4. Confirm

When a refund is processed:
- Inventory stock is automatically restocked
- The order's payment status updates (Partially Refunded or Refunded)
- The refund is recorded in the activity log
- If the order was Delivered, it moves to Refunded status

The system prevents refunds that exceed the total paid amount. Each refund is recorded permanently for your records.

---

## 8. Order Notes & Activity Log

Every order has a complete history of everything that happened. Open an order and scroll to the **Activity Log** (or **Timeline**) section to see:

- When the order was created and by whom
- Every status change with timestamps
- Payment recordings and refunds
- Shipment updates
- Notes added by your team

### Adding Notes

- **Customer notes** — visible to the customer on their invoice or tracking page
- **Internal notes** — only visible to your team, useful for instructions or comments

Notes and activity entries appear in reverse chronological order, with the most recent at the top.

---

## 9. Invoice & Receipt Printing

Generate printable documents for any order.

### Invoice (A4)

Click **"Invoice"** on any order to generate a professional A4 invoice. The invoice includes:

- Your store name, address, and contact details
- Order number and date
- Customer name and shipping address
- Complete list of items with prices and quantities
- Subtotal, discounts, tax, and grand total
- Payment method and status

Invoices are formatted for standard A4 paper and can be printed or saved as PDF from your browser's print dialog.

### Thermal Receipt

For cash registers and thermal printers, click **"Print Receipt"** to generate a compact receipt:

- Your store name at the top
- Order number and date
- Line items with quantities and prices
- Total and payment info
- Compact format designed for 80mm thermal paper roll

Receipts are plain text and print correctly on standard thermal receipt printers.

---

## 10. Bulk Actions

When you need to process multiple orders at once, use bulk actions.

### Bulk Status Update

1. Go to the Orders list
2. Select the orders you want to update (checkboxes)
3. Click **"Bulk Update Status"**
4. Choose the new status
5. Confirm

### Bulk Cancel

1. Select the orders you want to cancel
2. Click **"Bulk Cancel"**
3. Enter a reason for the cancellation
4. Confirm

All selected orders are processed in sequence. If any order can't be updated (for example, it's already delivered), the system skips it and continues with the next one.

---

## 11. Order Export

Export your orders for offline analysis, accounting, or sharing with your team.

### CSV Export

Go to **Orders > Export > CSV** to download all orders as a CSV file. The export includes:

- Order number and date
- Customer name and contact
- Order status and type
- Item count and grand total
- Payment method and status
- Shipping address

### Filtering the Export

Before exporting, you can apply filters so only the orders you need are included:

- By **status** — export only confirmed orders, delivered orders, etc.
- By **date range** — orders placed within a specific period
- By **order type** — only in-store, only delivery, etc.

The CSV file opens in any spreadsheet application (Excel, Google Sheets, Numbers) and can be used for accounting, tax reporting, or further analysis.

---

## 12. Shipments & Courier Tracking

For delivery orders, you can create shipments and track them through the courier's delivery network.

### Creating a Shipment

1. Open an order that's in **Processing** status
2. Go to the **Shipments** section and click **"Create Shipment"**
3. Select the **courier** (Pathao, Steadfast, RedX, Sendo, or Paperfly)
4. Enter the **package details** (weight, dimensions)
5. Select which **items** to include in this shipment
6. Confirm

The shipment is sent to the courier automatically, and a tracking number is returned and stored with the shipment.

### Multiple Shipments Per Order

If an order has many items, you can split them across multiple shipments. For example, if some items are in stock and others need to be ordered from a supplier, you can ship the available items first and the rest later.

The system tracks this as **Fulfillment Status**:

| Status | Meaning |
|--------|---------|
| **Unfulfilled** | No items shipped yet |
| **Partially Fulfilled** | Some items shipped, some remaining |
| **Fulfilled** | All items shipped |

### Shipment Statuses

Once created, a shipment moves through these stages:

```
Pending → Picked Up → In Transit → Out for Delivery → Delivered
                              ↘ Delivery Failed
                                          ↘ Returned to Sender → Cancelled
```

| Status | What It Means |
|--------|---------------|
| **Pending** | Created, waiting for courier pickup |
| **Picked Up** | Courier has collected the package |
| **In Transit** | Package is moving through the courier's network |
| **Out for Delivery** | Package is with the delivery agent, final mile |
| **Delivered** | Customer received the package |
| **Delivery Failed** | Could not be delivered (attempt logged) |
| **Returned to Sender** | Package is coming back to you |
| **Cancelled** | Shipment was cancelled before delivery |

### Tracking Updates

Shipment status updates can happen in three ways:

1. **Automatic** — the courier's webhook sends updates in real time
2. **Scheduled sync** — every few hours, the system checks for updates from the courier
3. **Manual** — you can update the status from the shipment details page

Each delivery attempt is logged so you can see exactly what happened if a delivery failed.

---

## 13. Shipping Rates

Configure shipping rates for each courier and delivery zone. Go to **Settings > Shipping Rates** to manage them.

### Rate Structure

Each shipping rate includes:

- **Courier** — which courier this rate applies to
- **Name** — a label for your reference (e.g., "Pathao Express — Inside Dhaka")
- **Zone** — delivery area (Inside Dhaka, Outside Dhaka, All Bangladesh)
- **Base rate** — the starting fee
- **Per kg rate** — additional fee for each kilogram
- **COD fee** — percentage charged for Cash on Delivery orders
- **Weight range** — minimum and maximum weight this rate covers
- **Estimated delivery days** — how long delivery usually takes

### Free Shipping

You can set up free shipping when the order amount reaches a minimum threshold. For example: "Free shipping for orders over ৳1,000 inside Dhaka."

### How Rates Are Calculated

When a customer places a delivery order, the system automatically calculates the best available shipping rate based on:

1. The delivery zone (inside/outside Dhaka)
2. The package weight
3. The preferred courier (if any)
4. The order amount (for free shipping eligibility)

The cheapest available rate is shown first.

---

## 14. Public Tracking Page

Share a tracking link with your customers so they can follow their shipment without logging in to Souda.

### How It Works

1. After creating a shipment, a unique tracking page URL is generated
2. Share the link with your customer by email or SMS
3. The customer opens the link in any browser and sees:
   - Current shipment status (In Transit, Out for Delivery, etc.)
   - Courier name and tracking number
   - Estimated delivery date
   - Package contents (item names and quantities)
   - Full status timeline (when it was picked up, when it reached the hub, etc.)

### No Login Required

The tracking page is public — customers don't need a Souda account. They just need the tracking number or the link you share.

### When the Shipment is Delivered

Once delivered, the tracking page shows the delivery confirmation with the date and time. The tracking link stays active so customers can refer back to it.

---

## 15. Search & Filtering

Find any order quickly using the search bar and filters on the Orders page.

### Search

Type in the search box to find orders by:

- **Order number**
- **Customer name**
- **Customer phone** or **email**
- **Product name** (finds orders containing that product)

### Filters

Narrow down the order list with filters:

- **Status** — show only Pending, Confirmed, Delivered, etc.
- **Date range** — orders placed today, this week, this month, or a custom range
- **Order type** — in-store, online, delivery, wholesale, etc.
- **Payment status** — paid, unpaid, refunded
- **Fulfillment status** — fulfilled, partially fulfilled, unfulfilled

Filters can be combined. For example: "Show me all delivered online orders from last month."

### Sorting

Click any column header to sort by that column — order date, total amount, customer name, etc. Click again to reverse the sort order.

---

## 16. Integration with Other Modules

The order system works closely with the rest of Souda. Here's how it connects:

### Inventory

When you place an order:
1. The order is created with all line items
2. Inventory stock is automatically deducted for each item
3. If stock drops below the minimum threshold, a low-stock alert is created
4. The inventory balance is updated in real time

When an order is **cancelled**, stock is automatically restored. When an order is **refunded**, items are returned to stock.

### CRM & Customer History

Every order is linked to the customer's profile:
- The customer's **purchase history** is updated automatically
- Total **lifetime value** is recalculated
- **Purchase frequency** and **average order value** are tracked for reporting

Customers with recent orders appear on the customer dashboard so your team can follow up.

### POS

Orders created at your point-of-sale counter flow through the same order system:
- Payment is recorded at the register
- Receipts can be printed on thermal printers
- Returns are processed through the refund system

### Reporting

Order data feeds into business reports:
- **Sales reports** — daily, weekly, and monthly revenue
- **Product performance** — which items sell best
- **Customer analytics** — who your best customers are
- **Payment method analysis** — how customers prefer to pay

---

> **Need help?** Contact support or your account manager for assistance with order management setup and best practices for your industry.
