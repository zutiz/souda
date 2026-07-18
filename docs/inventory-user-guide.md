# Souda Inventory User Guide

The Inventory module is the brain of your stock operations. Every sale, purchase, return, transfer, and adjustment is recorded automatically — your stock levels are always up to date without any manual data entry.

---

## Table of Contents

1. [Inventory Dashboard](#1-inventory-dashboard)
2. [Stock Balances](#2-stock-balances)
3. [Stock Movements](#3-stock-movements)
4. [Warehouses & Bins](#4-warehouses--bins)
5. [Stock Transfers](#5-stock-transfers)
6. [Physical Counts & Cycle Counting](#6-physical-counts--cycle-counting)
7. [Alerts & Notifications](#7-alerts--notifications)
8. [Demand Forecasting](#8-demand-forecasting)
9. [Stock Classification (ABC & Velocity)](#9-stock-classification-abc--velocity)
10. [Purchase Suggestions](#10-purchase-suggestions)
11. [Automation Rules](#11-automation-rules)
12. [Batch & Expiry Tracking](#12-batch--expiry-tracking)
13. [Serial Number Tracking](#13-serial-number-tracking)
14. [Costing Methods](#14-costing-methods)
15. [Dashboard Export](#15-dashboard-export)
16. [Integration with Other Modules](#16-integration-with-other-modules)

---

## 1. Inventory Dashboard

Navigate to **Inventory** in the sidebar to see your inventory dashboard. This gives you a real-time snapshot of your entire stock:

- **Total stock value** — the combined value of all products across all warehouses
- **Today's movements** — how many items moved in and out today
- **Low stock items** — products that have fallen below their minimum threshold
- **Out of stock items** — products with zero available quantity
- **Expiring batches** — batches approaching their expiry date

Below the summary cards you'll see recent stock movements so you can quickly spot what's happening. You can also export the dashboard data as a CSV or PDF report (see [Dashboard Export](#15-dashboard-export)).

---

## 2. Stock Balances

Go to **Inventory > Stock** to see current stock levels for every product. Each row shows:

- **Product name** and SKU
- **Warehouse** where the stock is located
- **Current quantity** on hand
- **Reserved quantity** — stock allocated to open orders and not yet picked
- **Available quantity** — stock ready to sell (on hand minus reserved)
- **Average cost** per unit
- **Stock value** — quantity times average cost

Products below their low-stock threshold are highlighted in yellow so you can reorder before you run out. Products with zero available stock are highlighted in red.

You can filter by warehouse, search for a specific product, or sort by any column.

---

## 3. Stock Movements

Every stock change is recorded as a permanent movement. Go to **Inventory > Movements** to see the full history. Each movement shows:

- **Date and time**
- **Product** and warehouse
- **Movement type** — what caused the change
- **Quantity** — positive for stock coming in, negative for stock going out
- **Reference number** — links back to the original transaction (order, transfer, etc.)
- **Unit cost** at the time of the movement
- **Total cost** of the movement

### Movement Types

| Type | Description |
|------|-------------|
| Purchase Receipt | Stock received from a supplier |
| Sale Deduction | Stock sold to a customer |
| Return Restock | Customer return put back into stock |
| Transfer Out | Moved to another warehouse |
| Transfer In | Received from another warehouse |
| Adjustment Increase | Manual stock correction (add) |
| Adjustment Decrease | Manual stock correction (remove) |
| Reservation Deduction | Stock deducted for a reservation |
| Reservation Release | Stock released from a cancelled reservation |
| Initial Stock | Opening balance when you first start tracking |
| Reversal | Offsetting entry to correct a mistake |

Movements are **permanent** — they cannot be edited or deleted. If a mistake is made, record a reversal movement to offset it. This guarantees a complete audit trail that satisfies accounting requirements.

---

## 4. Warehouses & Bins

If you operate from multiple locations, set up a warehouse for each one. Go to **Inventory > Warehouses** to manage them.

### Creating a Warehouse

1. Click **"Add Warehouse"**
2. Enter a name and optional code, address, and contact details
3. Set it as **active** — inactive warehouses won't appear in most dropdowns
4. You can mark one warehouse as the **default** for your business

### Warehouse Bins

Within a warehouse, create bins (shelves, racks, or locations) to organize where products are stored. For example:

- Warehouse: "Main Store"
  - Bin: "Aisle A - Shelf 1"
  - Bin: "Aisle A - Shelf 2"
  - Bin: "Cold Storage"

Bins help your staff find items quickly when picking orders or doing stock counts.

---

## 5. Stock Transfers

Move stock between warehouses when you need to redistribute inventory.

### Creating a Transfer

1. Go to **Inventory > Transfers** and click **"New Transfer"**
2. Select the **source** warehouse (where stock is now)
3. Select the **destination** warehouse (where stock needs to go)
4. Add the products and quantities you want to move
5. Click **"Create Transfer"**

### Completing a Transfer

Transfers go through three stages:

1. **Draft** — just created, nothing has moved yet. You can still edit items.
2. **In Transit** — click **"Send Transfer"** to deduct stock from the source warehouse. The items are now in transit and unavailable for sale at the source.
3. **Completed** — click **"Receive Transfer"** to add the stock to the destination warehouse.

You can **cancel** a transfer at any time before it's received. Cancelling releases any reserved stock back to the source warehouse.

### Partial Receives

If only part of a shipment arrives, you can do a partial receive. The received quantity is added to the destination, and the remaining quantity stays in transit.

---

## 6. Physical Counts & Cycle Counting

Physical counts let you verify that your actual stock matches what the system shows. This is essential for inventory accuracy and is often required for accounting.

### Creating a Count

1. Go to **Inventory > Counts** and click **"New Count"**
2. Select the **warehouse** you want to count
3. Choose the **count type**:
   - **Full Count** — includes every product with stock in that warehouse
   - **Cycle Count** — a smaller, targeted count on a subset of products (ideal for regular rotations)
   - **Partial Count** — count a specific set of products you choose

### Recording Counts

Once the count is created, you'll see a list of items with their **expected quantities** (what the system thinks you have). Enter the **physical quantities** you actually counted in each field. The system automatically calculates the difference.

### Count Workflow

1. **Draft** — count is created, waiting for physical counts to begin
2. **In Progress** — start entering physical quantities
3. **Verified** — a supervisor reviews and confirms the counts
4. **Adjusted** — stock is automatically updated to match the physical count. Adjustment movements are created for any discrepancies.
5. **Completed** — the count is finalized

If you spot a counting error or need to restart, you can **cancel** a count at any point before it's completed.

---

## 7. Alerts & Notifications

The system continuously monitors your stock and alerts you when action is needed. Go to **Inventory > Alerts** to see all active alerts.

### Alert Types

| Alert | Severity | What It Means |
|-------|----------|---------------|
| **Low Stock** | Warning | A product has fallen below its minimum stock threshold |
| **Out of Stock** | Critical | A product has zero available quantity |
| **Expiring Soon** | Warning | A batch is approaching its expiry date |
| **Dead Stock** | Info | A product hasn't had any movement in 90+ days |
| **Overstock** | Warning | A product has more stock than its maximum threshold |

Alerts are color-coded by severity (info, warning, critical) so you can prioritise. Click on any alert to see the full details and take action. You can dismiss alerts once you've dealt with them.

---

## 8. Demand Forecasting

The system uses your historical sales data to predict future demand. Go to **Inventory > Forecasting** to see predictions for your products.

### Forecast Models

Three forecasting models are available:

| Model | Best For |
|-------|----------|
| **Moving Average** | Products with steady, consistent demand |
| **Linear Trend** | Products with growing or declining demand |
| **Seasonal** | Products with seasonal patterns (holiday peaks, rainy season, etc.) |

### Using Forecasts

Each forecast shows:
- **Historical sales** for the past period
- **Predicted demand** for the coming period
- **Confidence level** — how reliable the prediction is

You can switch between forecast models to see which one fits best, and export the data for planning. Forecasts are recalculated daily based on the latest sales data.

---

## 9. Stock Classification (ABC & Velocity)

Not all products are equally important. Stock classification helps you focus your attention on the items that matter most.

### ABC Classification

Products are grouped into three categories based on their value contribution:

| Class | Meaning | Typical Actions |
|-------|---------|-----------------|
| **A** | High-value items (top 20% by value) | Count frequently, tight stock control |
| **B** | Medium-value items (next 30%) | Regular monitoring |
| **C** | Low-value items (bottom 50%) | Simple tracking, less frequent counts |

### Velocity Classification

Products are also classified by how fast they move:

| Class | Meaning |
|-------|---------|
| **Fast** | High sales volume, frequent reordering |
| **Slow** | Low sales volume, occasional orders |
| **Dead** | No sales in the last 90 days — consider discontinuing or discounting |
| **New** | Recently added, not enough sales data yet |

Go to **Inventory > Stock Classification** to see your products sorted by these categories. This helps you decide which products to count more often, which to discount, and which to discontinue.

---

## 10. Purchase Suggestions

The system analyzes your sales history, current stock levels, and lead times to suggest what you should reorder. Go to **Inventory > Suggestions** to see them.

Each suggestion shows:

- **Product** name and SKU
- **Warehouse** where stock is low
- **Current stock** and reserved quantity
- **Suggested quantity** to order
- **Sales velocity** — how fast the product sells per day

### Acting on Suggestions

- **Mark as Ordered** — click this after you've placed a purchase order. Enter the order reference so you can track it.
- **Dismiss** — if you don't need to reorder yet, dismiss the suggestion with a note.
- **Generate Report** — export all current suggestions as a PDF to share with your purchasing team.

Suggestions are generated automatically every night, or you can click **"Generate Now"** to run them immediately. The system learns from your ordering patterns and improves its suggestions over time.

---

## 11. Automation Rules

Save time by letting the system automate common inventory actions. Go to **Inventory > Automation Rules** to create and manage rules.

### How Rules Work

Each rule has two parts:
1. **Condition** — when should this rule trigger?
2. **Action** — what should happen when it does?

### Conditions

| Condition | Triggers When |
|-----------|---------------|
| **Low Stock** | Product quantity falls below its minimum threshold |
| **Dead Stock** | Product has had no movement for 90+ days |
| **Overstock** | Product exceeds its maximum threshold |
| **Expiring Batch** | A batch's expiry date is within the warning period |
| **Slow Moving** | Sales velocity is below the configured threshold |
| **Fast Moving** | Sales velocity exceeds the configured threshold |

### Actions

| Action | What Happens |
|--------|--------------|
| **Create Alert** | An alert appears in the Alerts panel |
| **Send Notification** | An email or notification is sent to managers |
| **Generate Suggestion** | A purchase suggestion is created automatically |

### Example Rules

- *"If stock is below minimum for Product ABC, create a purchase suggestion for 50 units."*
- *"If any batch is expiring within 30 days, send an email to the warehouse manager."*
- *"If a product has had no movement in 90 days, create a dead stock alert and notify the purchasing team."*

Rules are evaluated whenever stock changes. You can enable or disable individual rules at any time.

---

## 12. Batch & Expiry Tracking

For businesses that need to track product lots and expiry dates (pharmacy, grocery, cosmetics, etc.), batch tracking is available.

### Features

- Record **batch numbers** when receiving stock from suppliers
- Set **manufacturing dates** and **expiry dates**
- Track **remaining quantity** in each batch
- Record a **supplier's batch number** for traceability

### Picking Order

The system can pick from batches in two ways:

| Method | Order | Best For |
|--------|-------|----------|
| **FEFO** (First Expiry, First Out) | Earliest expiry first | Pharmacy, grocery, perishables |
| **FIFO** (First In, First Out) | Oldest manufacturing date first | General inventory |

The system alerts you when batches are approaching their expiry date so you can discount, return, or dispose of them in time.

### Batch Lifecycle

- **Active** — batch is available for sale or use
- **Quarantined** — batch is held for quality review (can't be sold)
- **Depleted** — all units have been sold or used

---

## 13. Serial Number Tracking

For high-value or warranty-tracked items (electronics, appliances, equipment), serial number tracking lets you follow each individual unit.

### Features

- Register individual **serial numbers** or **IMEI numbers** when stock arrives
- Register multiple serial numbers at once using bulk entry
- Track each unit through its lifecycle
- Record **warranty expiry dates**
- Look up which customer bought a specific serial number

### Serial Number Lifecycle

```
Available → Sold → Returned
```

Additional states include **Reserved** (allocated to an order but not yet sold) and **Disposed** (defective or discarded).

### Warranty Tracking

For each serial number, you can set a warranty expiry date. The system will alert you when warranties are approaching expiry — useful for service businesses that offer extended warranties.

---

## 14. Costing Methods

The system calculates the cost of your inventory automatically. Two costing methods are available:

### Weighted Average Cost (Default)

This method calculates the average cost of each product every time you receive new stock. For example:

- You have 100 units at $5.00 each ($500 total)
- You receive 50 more units at $7.00 each ($350)
- New average cost = ($500 + $350) ÷ 150 = $5.67 per unit

This is the simplest method and works well for most businesses.

### FIFO (First In, First Out)

This method assumes that the oldest stock is sold first. When you sell items, the cost comes from the earliest purchase batch. This is more accurate for businesses with fluctuating supplier prices.

The costing method can be set per product, and existing stock values are recalculated automatically when the method changes.

---

## 15. Dashboard Export

You can export inventory data as reports for offline analysis, accounting, or sharing with your team.

### Available Exports

| Report | Format | What's Included |
|--------|--------|-----------------|
| **Stock Summary** | CSV, PDF | All products with quantities, values, and warehouse locations |
| **Movement History** | CSV, PDF | All stock movements within a date range |
| **Low Stock Report** | PDF | Products below their minimum threshold |
| **Stock Valuation** | CSV | Total inventory value broken down by product and warehouse |
| **Forecast Report** | CSV, PDF | Demand predictions for selected products |

Click **"Export"** on any inventory page to generate the report. Large exports are processed in the background and you'll be notified when they're ready to download.

---

## 16. Integration with Other Modules

The inventory system is fully integrated with the rest of Souda. Here's how it connects:

### Sales (POS & Orders)

When you make a sale:
1. The sale is recorded in POS or Orders
2. Stock is automatically deducted from the warehouse
3. The inventory balance is updated in real time
4. If stock drops below the threshold, an alert is created

### Purchases

When you receive a purchase order:
1. Stock is increased in the destination warehouse
2. Cost layers are updated (for accurate valuation)
3. If batch tracking is enabled, the batch is recorded
4. Any pending purchase suggestions for this product are updated

### Returns

When a customer returns an item:
1. Stock is added back to the appropriate warehouse
2. If serial numbers were tracked, the serial is marked as returned
3. The inventory balance is recalculated

### Manufacturing / Recipes

For businesses that make their own products:
1. Ingredients are deducted from stock automatically
2. The finished product is added to stock
3. The total ingredient cost is used as the product's unit cost

### Accounting

The movement history provides a complete audit trail for your accountant. Every stock movement includes:
- Before and after quantities (integrity checkpoints)
- Unit cost at the time of movement
- Reference back to the source transaction
- Who performed the action

---

> **Need help?** Contact support or your account manager for assistance with inventory setup and best practices for your industry.
