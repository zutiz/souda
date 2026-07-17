# Souda Inventory User Guide

The Inventory module helps you track stock levels, move items between warehouses, perform physical counts, and stay on top of low stock and expiring items. Everything is recorded automatically — every sale, purchase, return, and adjustment updates your inventory in real time.

---

## Table of Contents

1. [Inventory Dashboard](#1-inventory-dashboard)
2. [Stock Balances](#2-stock-balances)
3. [Stock Movements](#3-stock-movements)
4. [Warehouses](#4-warehouses)
5. [Stock Transfers](#5-stock-transfers)
6. [Physical Counts & Cycle Counting](#6-physical-counts--cycle-counting)
7. [Alerts & Notifications](#7-alerts--notifications)
8. [Purchase Suggestions](#8-purchase-suggestions)
9. [Batch & Expiry Tracking](#9-batch--expiry-tracking)
10. [Serial Number Tracking](#10-serial-number-tracking)

---

## 1. Inventory Dashboard

Navigate to **Inventory** in the sidebar to see your inventory dashboard. This gives you a snapshot of your entire stock:

- **Total stock value** — the combined value of all products across all warehouses
- **Today's movements** — how many items moved in and out today
- **Low stock items** — products that have fallen below their minimum threshold
- **Expiring batches** — batches approaching their expiry date

Below the summary cards you'll see recent stock movements so you can quickly spot what's happening.

---

## 2. Stock Balances

Go to **Inventory > Stock** to see current stock levels for every product. Each row shows:

- **Product name** and SKU
- **Warehouse** where the stock is located
- **Current quantity** on hand
- **Reserved quantity** — stock allocated to open orders
- **Available quantity** — stock ready to sell (on hand minus reserved)
- **Average cost** per unit
- **Stock value** — quantity times average cost

Products below their low-stock threshold are highlighted so you can reorder before you run out.

---

## 3. Stock Movements

Every stock change is recorded as a movement. Go to **Inventory > Movements** to see the full history. Each movement shows:

- **Date and time**
- **Product** and warehouse
- **Movement type** — what caused the change
- **Quantity** — positive (in) or negative (out)
- **Reference number** — links back to the original transaction (order, transfer, etc.)

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
| Physical Count | Adjustment from a cycle count |

Movements are **permanent** — they cannot be edited or deleted. If a mistake is made, record a correction movement to offset it. This guarantees a complete audit trail.

---

## 4. Warehouses

If you operate from multiple locations, you can set up a warehouse for each one. Go to **Inventory > Warehouses** to manage them.

### Creating a Warehouse

1. Click **"Add Warehouse"**
2. Enter the name and optionally a code, address, and contact details
3. Set it as **active** — inactive warehouses won't appear in most dropdowns
4. You can mark one warehouse as the **default** for your business

### Warehouse Bins

Within a warehouse, you can create bins (shelves, racks, or locations) to organize where products are stored. For example:

- Warehouse: "Main Store"
  - Bin: "Aisle A - Shelf 1"
  - Bin: "Aisle A - Shelf 2"
  - Bin: "Cold Storage"

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

1. **Draft** — just created, nothing has moved yet
2. **In Transit** — click **"Send Transfer"** to deduct stock from the source warehouse. The items are now in transit.
3. **Completed** — click **"Receive Transfer"** to add the stock to the destination warehouse.

You can **cancel** a transfer at any time before it's received. Cancelling releases any reserved stock.

---

## 6. Physical Counts & Cycle Counting

Physical counts let you verify that your actual stock matches what the system shows. This is essential for inventory accuracy.

### Creating a Count

1. Go to **Inventory > Counts** and click **"New Count"**
2. Select the **warehouse** you want to count
3. Choose the **count type**:
   - **Full Count** — includes every product with stock in that warehouse
   - **Cycle Count** — a smaller, targeted count (ideal for regular rotations)
   - **Partial Count** — count a specific set of products

### Recording Counts

Once the count is created, you'll see a list of items with their **expected quantities** (what the system thinks you have). Enter the **physical quantities** you actually counted in each field. The system automatically calculates the difference (discrepancy).

### Count Workflow

1. **Draft** — count is created, waiting for physical counts
2. **In Progress** — start entering physical quantities
3. **Verified** — a supervisor reviews and confirms the counts
4. **Adjusted** — stock is automatically updated to match the physical count
5. **Completed** — the count is finalized

If there are discrepancies, the system creates adjustment movements to correct your stock levels. Counts can be **cancelled** at any point before completion.

---

## 7. Alerts & Notifications

The system continuously monitors your stock and alerts you when action is needed. Go to **Inventory > Alerts** to see all active alerts.

### Alert Types

| Alert | What It Means |
|-------|---------------|
| **Low Stock** | A product has fallen below its minimum stock threshold |
| **Out of Stock** | A product has zero available quantity |
| **Expiring Soon** | A batch is approaching its expiry date |
| **Dead Stock** | A product hasn't had any movement in 90+ days |
| **Overstock** | A product has more stock than its maximum threshold |

You can dismiss alerts once you've taken action. Alerts are also checked automatically and can be emailed to managers.

---

## 8. Purchase Suggestions

The system analyzes your sales history and current stock levels to suggest what you should reorder. Go to **Inventory > Suggestions** to see them.

Each suggestion shows:

- **Product** name and SKU
- **Warehouse** where stock is low
- **Current stock** and reserved quantity
- **Suggested quantity** to order
- **Sales velocity** — how fast the product sells per day

### Acting on Suggestions

- **Mark as Ordered** — click this after you've placed a purchase order. Enter the order reference so you can track it.
- **Dismiss** — if you don't need to reorder, dismiss the suggestion with a note explaining why.

Suggestions are generated automatically every night, or you can click **"Generate Now"** to run them immediately.

---

## 9. Batch & Expiry Tracking

For businesses that need to track product lots and expiry dates (pharmacy, grocery, cosmetics, etc.), the batch tracking feature lets you:

- Record **batch numbers** when receiving stock
- Set **manufacturing dates** and **expiry dates**
- Track **remaining quantity** in each batch
- Pick from the **earliest-expiring batches first** (FEFO — First Expiry, First Out)

The system alerts you when batches are approaching their expiry date so you can discount, return, or dispose of them in time.

---

## 10. Serial Number Tracking

For high-value or warranty-tracked items (electronics, appliances), serial number tracking lets you:

- Register individual **serial numbers** or **IMEI numbers** when stock arrives
- Track each unit through its lifecycle: Available → Sold → Returned
- Record **warranty expiry dates**
- Look up which customer bought a specific serial number

This feature is available for businesses that need unit-level traceability.
