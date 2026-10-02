> 🌐 **Language:** [🇻🇳 Tiếng Việt](./tax_vi.md) · 🇬🇧 English (current)

# Tax in GP247

## Introduction
This document explains how the **tax** feature works in GP247 (from version 2) and guides store owners in
configuring tax for their own shop. It is written for **non-technical readers**: after reading, you will
know how tax is calculated, where to create tax rates, how to assign tax to products, and on which pages
tax appears.

---

## 1. How does tax work in GP247?

Four key points to keep in mind:

1. **Tax is assigned per product.** Each product can carry its own tax rate (e.g. 8%, 10%). You create
   "tax rates" first, then assign them to products.
2. **The listed price is the price WITHOUT tax** (*exclusive*). Tax is **added on top** at checkout; it is
   not baked into the sale price. Example: a product priced 100,000 with 8% tax → the customer pays 108,000.
3. **It can be turned on/off for the whole shop.** You can disable tax entirely, or set a **default** tax
   rate applied across the board.
4. **Each product uses only one tax rate** (no stacking of multiple taxes). GP247 (v2) calculates tax per
   product — **not** by country/region/shipping address.

> **Term:** A *tax rate* is a row with a **Name** (e.g. "VAT 8%") and a **Value** (a percentage number,
> e.g. `8`). You create a tax rate once, then assign it to many products.

Each product has 3 tax choices:

| Choice | Meaning |
| --- | --- |
| **No tax** (None) | This product is not taxed. |
| **Automatic** (Auto) | Uses the shop's **default tax rate** (which you set in Step 2 below). |
| **A specific rate** | Always uses the exact rate you pick for this product. |

---

## 2. Steps to configure tax (do them in order)

### Step 1 — Create a tax rate
1. Log in to your shop's **admin** area.
2. Open the **Tax** section in the admin menu.
3. Click add-new and fill in:
   - **Name:** an easy-to-understand name, e.g. `VAT 8%` or `Standard tax`.
   - **Value:** enter the percentage number, e.g. `8` (meaning 8%). Fractional values such as `8.5` are allowed.
4. Save.

   If it succeeds, the new tax rate appears in the list on the right side of the Tax screen.

> You can create several tax rates (e.g. 5%, 8%, 10%) and assign them to different groups of products.

### Step 2 — Set the shop's default tax rate
1. Open **Shop Configuration** in the admin menu.
2. Find the tax setting (the config key is **`product_tax`**).
3. Choose one of:
   - **A specific tax rate** (e.g. "VAT 8%") → this becomes the default applied to products set to **Automatic (Auto)**.
   - **None** → **disables tax for the whole shop** (no product is taxed, even ones set to Auto).
4. Save.

> This is the most important switch: if set to **None**, the entire shop charges no tax no matter which
> rate a product is assigned.

### Step 3 — Assign tax to a product
1. Open **Product**, then edit the product you want to change.
2. Find the **Tax** selector in the product form and choose:
   - **Automatic (Auto)** — use the default from Step 2 (recommended for most products).
   - **None** — this product is tax-free.
   - **A specific rate** — force this exact rate for this product.
3. Save the product.

### Step 4 — Verify on the storefront
1. Open the product you just edited on the **storefront** (customer-facing site).
2. Add it to the cart and go to the **Cart / Checkout** page.
3. Check the **Tax** line in the totals table.

   If correct, the grand total equals: *Subtotal + Tax + Shipping − Discount*.

---

## 3. Where does tax appear?

- **Product page (storefront):** the displayed price already includes tax (when tax is on).
- **Cart & checkout page:** a separate **Tax** line in the totals table.
- **Customer order (account):** the order totals include a tax line.
- **Admin — order detail & invoice:** a tax line in the totals table.

---

## 4. A few important notes

- **Changing a tax rate does not change old orders.** Previously placed orders keep the tax that was
  recorded; changes affect **new** orders only.
- **Disable tax for the whole shop** fastest by setting `product_tax` = **None** (Step 2) — no need to edit
  each product.
- **Tax amounts are rounded to 2 decimals** per product line, so the order's total tax always matches the
  sum of the line taxes.

---

## 5. Processing logic & tax functions (for developers)

> This section is for **developers**. Regular users can skip it.

### Flow: create → resolve → compute → persist

```
CREATE a tax rate (admin)        TaxManager → ShopTax (name + value%)
        │
RESOLVE a product's tax          config product_tax → ShopTax::checkStatus() (on/off + default rate)
        │                        product.tax_id (none|auto|<id>) → getTaxId() → getTaxValue() = %
        ▼
COMPUTE (line-level, 1 helper)   gp247_line_tax(price+option, qty, rate) = round(...×rate/100, 2)
        │
   ├─ Cart            ShopCurrency::sumCartCheckout() → subTotal, subTotalWithTax
   ├─ Order totals    ShopOrderTotal::processDataTotal() → row code='tax' = subTotalWithTax − subTotal
   ├─ Persist (SF)    ShopOrder::createOrder() → shop_order_detail.tax per-line + shop_order.tax
   └─ Edit (admin)    AdminOrder::updateSubTotal() → shop_order.tax = Σ detail.tax
```

### Tax functions / methods

**Create & manage tax rates**
- `TaxManager` (Livewire) — tax-rate CRUD screen; validates `value: numeric|min:0`; writes to `ShopTax`.
- `ShopTax` — model for the `shop_tax` table (`name`, `value` as `decimal(8,4)`, cast `float`):
  - `getListAll()` — list of tax rates plus `none`/`auto` (used by dropdowns).
  - `getArrayId()` / `getArrayValue()` — `id → value%` maps for fast lookup.
  - `checkStatus()` — reads `gp247_config('product_tax')`; returns `0` when off/`none`/invalid, otherwise the default rate `id`.

**Resolve a product's tax** (`ShopProduct`)
- `getTaxId()` — picks the tax rate `id`: off → `0`; `auto` → `checkStatus()`; specific `id` → returned.
- `getTaxValue()` — returns the `%` from `getArrayValue()[$taxId]`.
- `getFinalPriceTax()` — a **single unit's** price including tax (for display).

**Rounding formula (shared helper)** — `Library/Helpers/cart_store.php`
- `gp247_line_tax($unitPriceWithOption, $qty, $rate)` — rounds **per line** (line-level); the **single source** of the formula.
- `gp247_tax_price($price, $tax)` — a single unit's price including tax = `round(price×(100+tax)/100, 2)` (display only).

**Compute & aggregate**
- `ShopCurrency::sumCartCheckout()` — iterates the cart; returns `subTotal` (pre-tax) and `subTotalWithTax` (with per-line `gp247_line_tax` added).
- `ShopOrderTotal::processDataTotal()` — builds the total rows; `tax = subTotalWithTax − subTotal`.
- `ShopOrderTotal::sumValueTotal($code, $dataTotal)` — reads one total row's value (e.g. `'tax'`).

**Persist to the order**
- `ShopOrder::createOrder()` — writes `shop_order_detail.tax` **per line** via `gp247_line_tax` (all order-creation paths, including storefront).
- `AdminOrder::updateSubTotal()` — recomputes `shop_order.tax = Σ detail.tax` when admin adds/edits order lines.

**Configuration**
- `gp247_config('product_tax')` — the on/off switch + default tax rate (group `gp247_cart`), edited on the Shop Configuration screen.

### Invariant
For new orders:

```
shop_order.tax  ==  Σ shop_order_detail.tax  ==  (subTotalWithTax − subTotal)
```

---

## Q&A

**Q1: How do I turn off tax for the whole shop?**

→ In Shop Configuration, set `product_tax` = **None**. The whole shop stops charging tax immediately, without
editing each product.

**Q2: What does "Automatic (Auto)" mean on a product's tax field?**

→ It means that product uses the shop's **default tax rate** (the one you pick in Step 2). Change the default
and every Auto product follows automatically.

**Q3: Does the price I enter for a product already include tax?**

→ No. GP247 uses **add-on** (exclusive) tax: the price you enter is the **pre-tax** price, and tax is added
at checkout.

**Q4: My tax is 8.5% (a fractional rate) — can I enter that?**

→ Yes. The **Value** field accepts fractional numbers, e.g. enter `8.5` for 8.5%.

**Q5: Can a single product carry multiple taxes at once?**

→ No. Each product uses **one** tax rate only.

**Q6: Does GP247 charge different tax by the customer's country/region?**

→ No. In v2, tax is calculated **per product**, not by shipping address or country.

**Q7: If I change a tax rate, are old orders recalculated?**

→ No. Old orders keep the tax from when they were placed; changes apply to new orders only.

**Q8: My product is set to "None" but the shop has a default tax — what happens?**

→ A product set to **None** is **not** taxed, even when the shop has a default rate.

**Q9: I created a tax rate but don't see it applied to any product?**

→ Creating a new tax rate does **not** auto-apply it. You must make it the default (Step 2) or assign it
directly to a product (Step 3).

**Q10: Does the order's total tax match the individual product lines?**

→ Yes. Tax is calculated and rounded **per line**, so the order's total tax always equals the sum of the
line taxes.

---

<sub>📅 **Last updated:** 2026-08-04 · ✍️ **Author:** GP247</sub>
