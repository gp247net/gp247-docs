> 🌐 **Language:** [🇻🇳 Tiếng Việt](./currency_vi.md) · 🇬🇧 English (current)

# Currency in GP247

## Introduction
This document explains how the **currency** feature works in GP247/S-Cart and guides store owners in
configuring the currencies for their own shop. Written for **non-technical** readers: after reading you will
know what a currency is made of, how the **base currency** (where product prices are stored) differs from the
display currency, how the exchange rate works, which currency customers see, where to create/edit currencies,
how to **change the base currency** without breaking prices, and why sometimes a currency cannot be
deleted or edited.

> ⭐ **Applies from gp247/shop version 2.1.** The items tagged **(v2.1)** in this document — the **explicit
> base currency**, the **value-preserving base change (rebase)**, and the **currency-code hint beside money
> inputs** — are **new in gp247/shop 2.1**. If your shop runs an older version, the rest still applies, but
> the (v2.1)-tagged items may not appear yet.

---

## 1. How does currency work in GP247?

Six important things to grasp:

1. **A shop can use multiple currencies.** You declare a list of currencies (e.g. USD, VND). Each currency
   can be **enabled** (usable by customers) or **disabled** (hidden).
2. **Product prices are stored in ONE "base currency".** You enter product prices once, in the **base
   currency** (GP247 informally calls it the store's "base"/"bit" unit). When displaying in another currency,
   the system **converts automatically** using that currency's **exchange rate**.
3. **The base currency is explicit (v2.1).** Exactly **one** currency is flagged as the **Base** (a blue
   "Base" badge in the list). The base currency **always has exchange rate `1`**, is **always enabled**, and
   is **locked against edit/delete** — you only change it through the **Change base currency** feature (see
   section 4). Before 2.1 the base was merely "implied" as the currency with rate `1`, which was error-prone
   when a shop had zero or several rate-`1` currencies.
4. **Each currency has its own display format.** Comprising: the **symbol** (e.g. `$`, `₫`), the **symbol
   position** (before or after the amount), the **number of decimal places** (precision — e.g. USD 2 digits
   `10.00`, VND 0 digits `250,000`) and the **thousands separator**.
5. **There is a default display currency for the shop.** This is the currency customers see first when they
   enter the shop. **Note:** this is a **different** concept from the base currency in point 3 — the base is
   *where prices are stored*, while the default is *what customers see first*. They are often the same
   currency, but not necessarily. If the shop enables multiple currencies, customers can **switch** currency
   themselves via the switcher in the interface.
6. **Orders remember the currency at order time.** When a customer places an order, the order **stores** the
   currency and the exchange rate at that moment. Changing the rate later leaves **old orders unchanged** —
   they still display exactly as when the customer bought.

> **Terminology:** The *exchange rate* is the number that converts the **base price** into the currency being
> viewed. The base currency has rate `1`. Example: base prices are in USD (rate `1`), VND has rate `25000` →
> an item with base price `2` displays as `50,000 ₫` when viewed in VND.

---

## 2. Steps to configure currencies (do them in order)

### Step 1 — Open the Currency management screen
1. Log in to the shop's **admin** area.
2. Go to the menu **Configuration → Currency** (coin icon).

   If successful, you see a screen split in two: an **input form on the left** and a **currency list on the
   right**. The currency that is currently the **Base** shows a blue **"Base"** badge in the name column (v2.1).

> You need the **`admin_currency`** permission to access this screen. If you do not see the menu, ask an
> administrator to grant it.

### Step 2 — Add a new currency
1. Click **Add new**.
2. Fill in the following fields:
   - **Name:** an easy-to-read name, e.g. `US Dollar` or `Việt Nam Đồng`.
   - **Code:** a short code, **not duplicated** with another currency, e.g. `USD`, `VND`, `EUR`.
   - **Symbol:** the display symbol, e.g. `$`, `₫`, `€`.
   - **Exchange rate:** the conversion number from the base price, **must be greater than 0**. (The base
     currency is always `1` and this field is locked — see section 3.)
   - **Precision (decimal places):** decimals to show, from `0` to `8`. USD is usually `2`, VND usually `0`.
   - **Symbol first:** yes/no. Yes → `$10.00`; No → `250,000 ₫`.
   - **Thousands separator:** the grouping mark, e.g. `,` (English style) or `.` (Vietnamese style).
   - **Status:** enable to make it usable by customers, disable to hide.
   - **Sort:** the smaller the number, the earlier it appears in the selection list.
3. Click **Save**.

   If successful, the new currency appears in the list on the right.

> **About the decimal separator:** the system derives the decimal separator as the **opposite** of the
> thousands separator. If you set the thousands separator to `,` the decimal is `.` (e.g. `1,234.50`); if you
> set thousands to `.` the decimal is `,` (e.g. `1.234,50`).

### Step 3 — Set the shop's default display currency
1. Go to **Configuration → Website / Store info** (Website Info screen).
2. Find the **Currency** selector and choose the default currency for the shop.
3. Save.

> This is the currency customers **see first** when they open the shop (different from the base currency —
> where prices are stored). This selector only offers **enabled** currencies.

### Step 4 — (Optional) Let customers switch currency
- If the shop enables **two or more currencies**, the customer interface shows a **currency switcher** in the
  header or footer. The customer picks one and all shop prices re-display in that currency.
- The customer's choice is remembered within their session; other customers are not affected.

### Step 5 — Verify on the storefront
1. Open the shop on the **storefront**.
2. Switch to another currency using the switcher.
3. Check that product and cart prices change symbol and decimal places exactly as you configured.

---

## 3. The base currency — why is it locked? (v2.1)

The **base currency** is the unit in which **all product prices, cost, attribute surcharges and promotion
prices** are stored in the database. In other words, every money figure you enter for a product is understood
as "in the base currency". Because it is the "measuring stick" for the whole catalog, GP247 2.1 protects it
tightly:

- **There is always exactly one base**, marked with the **"Base"** badge in the list.
- **The base currency's rate is always `1`** (not editable) — because every other rate is measured *against*
  it.
- **The base currency is always enabled.**
- **You cannot edit or delete the base directly.** The **Edit** and **Delete** controls on the base row are
  hidden/locked. To move the "measuring stick" to another currency, use the **Change base currency** feature
  in section 4 — do not hand-edit the base flag or its rate.

> **Why disallow hand-editing?** If someone changed the base without touching the stored price figures, the
> whole catalog would be "read in the wrong unit" (a product stored as `100`, meaning "100 USD", would
> suddenly be read as "100 VND"). The **Change base currency** feature does this safely — see below.

---

## 4. Change base currency (rebase) — value-preserving (v2.1)

When you need to move the store's "price measuring stick" to another currency (e.g. the shop used to enter
prices in USD but now wants to enter them in VND), use the **Change base currency** feature. The key point:
the system **recomputes the figures automatically** so that the **price customers see in every currency stays
the same** — you do not have to re-enter each product's price.

### Steps
1. Go to **Configuration → Currency**.
2. Click the **Change base** button at the top (the two-arrows ⇄ icon). A warning dialog opens.
3. Check the **Current base** line to make sure you are changing the right one.
4. In the **New base** field, choose the currency to become the base. The list only shows currencies that are
   **enabled** and **not yet the base**.
5. Look at the **New rate for the old base** field: the system **pre-fills a suggested value** (equal to
   `1 ÷ the new currency's current rate`) to keep prices unchanged. Leave the suggestion as is unless you
   deliberately want a different value.
6. Tick the **"I understand..."** confirmation box (required) to unlock the confirm button.
7. Click **Change base currency** to apply.

   If successful, the screen reports a successful base change; the **"Base"** badge moves to the new currency,
   and prices on the storefront remain exactly as before.

### What the system does under the hood (short explanation)
In **one safe transaction** (all-or-nothing — no half-done state):
- Multiply the base-denominated price figures (selling price, cost, attribute surcharge, promotion price) by
  the **new currency's rate**;
- Divide **all** exchange rates by that same number (so other currencies still convert correctly);
- Set the new currency as the base (rate `1`), and the old base receives the new rate you just entered.
- **Old orders are left untouched** — they already "froze" the currency and rate at order time.

> Thanks to this math, a product selling at "2 USD ↔ 50,000 ₫" still displays correctly as "50,000 ₫ ↔ 2 USD"
> after switching the base to VND. The stored numbers change, but the **value the buyer sees does not**.

---

## 5. Currency-code hint beside money inputs (admin) (v2.1)

Because every product money input is in the **base currency**, data-entry staff can easily lose track of which
currency they are typing in. From 2.1, the admin **automatically shows a currency-code hint** right beside each
money input:

- **Product screens** (price, cost, variant surcharge, promotion price…): show the **base currency** code,
  e.g. `(VND)` — reminding you "this field is entered in the base currency".
- **Order screens** (create/edit order): show the code of **the order's own currency** (when editing) or the
  currency you have selected (when creating) — so you never confuse orders in different currencies.
- If for some reason the currency cannot be resolved, the hint falls back to a default label (configurable via
  the multi-language system).

> This hint is purely a **display reminder**; it does not change how numbers are stored. You still enter values
> in the base currency as before.

---

## 6. Where is currency displayed?

- **Storefront:** product prices, cart, checkout — displayed in the currency the customer has selected
  (default or their own choice).
- **Customer's orders:** displayed in the currency **at order time** (stored on the order).
- **Admin — order detail & order list:** each order displays in **its own currency** (not re-converted), with
  the symbol so you can tell them apart when the list mixes currencies.
- **Invoice:** in the order's currency.

---

## 7. A few important notes

- **Changing a rate does not change old orders.** A placed order keeps the currency and rate at purchase time;
  the change only affects how **new** prices display on the storefront.
- **Product prices are always entered in the base currency.** Do not enter pre-converted prices. The
  currency-code hint beside the input (section 5) reminds you which currency you are in.
- **To change the base currency, use the Rebase feature (section 4)** — do not hand-edit the base flag/rate;
  hand edits are blocked by the system to avoid mispricing the whole catalog.
- **Precision is per currency**, not per value. E.g. with VND precision `0`, every VND amount shows no
  decimals.
- **Very small rates are still stored accurately.** The system stores rates to 6 decimal places, so a large
  currency converted from a small unit (e.g. rate `0.00004`) is not rounded to 0.

---

## 8. Conditions & Rules (know before you act)

The Currency screen validates data and enforces a few business rules. Know them upfront so you are not
surprised by an error.

### When adding / editing a currency
- **Name is required**, max 100 characters — so the currency list always has a clear label.
- **Code is required and must be unique**, max 10 characters — because the code is the "key" identifying the
  currency (orders store it by code); a duplicate code causes data confusion.
- **Symbol is required**, max 20 characters — to display prices to customers.
- **Rate is required, numeric and must be greater than 0** — a rate of `0` or negative would convert every
  price to 0 or wrong.
- **Precision is an integer from 0 to 8** — a limit for sensible money display (no real currency needs more
  than 8 decimals).
- **Thousands separator is required**, max 2 characters.
- **Sort is numeric and non-negative.**

### For the base currency (v2.1)
- **You cannot edit or delete the base** — Edit/Delete are locked; the system blocks it even if you try to
  reach it directly by URL. *Why:* the base is the catalog's price measuring stick; hand edits would misprice
  everything.
- **The base rate is always `1` and it is always enabled** — this cannot be changed.
- To change the base: **use the Change base currency feature** (section 4).

### When changing the base currency (rebase) (v2.1)
- **A base must already exist** for a change to be possible.
- **The new base must be enabled and not already the base** — the selector already filters to valid candidates.
- **The new rate for the old base must be a number > 0 and different from `1`** — leaving it `1` would mean the
  two currencies are at par, which makes no sense for a base change.
- **You must tick the "I understand..." confirmation** before the change button works — because this operation
  affects the whole catalog.

### When deleting a currency
The system **blocks deletion** in the following cases and **shows the reason** (no silent delete). The goal is
to avoid breaking the shop or losing old-order data:

- **It is the base currency (v2.1)** — the base is where all prices are stored; deleting it would orphan every
  product price. To remove it, **change the base** to another currency first (section 4).
- **It is the shop's default display currency** — deleting it would leave the shop with no currency to display
  by default. *Change the default to another currency first (Website Info), then delete.*
- **Some order uses this currency** — deleting it would strip old orders of their currency info and display
  wrong amounts. This is a key rule protecting order history.
- **It is the last enabled currency** — the shop always needs at least one usable currency; deleting the last
  one would leave the storefront with no currency to display.

> The delete rules are derived from **live data** (the base flag, the current default currency, actual orders,
> the number of enabled currencies), **not** from any "hard-coded" list — so they hold for any shop, even one
> that has changed currencies over many years.

---

## 9. Processing logic & currency functions (for developers)

> This section is for **developers**. Regular users can skip it.

### Data model
- Table `shop_currency`: `name`, `code` (unique), `symbol`, `exchange_rate` **decimal(16,6)**, `precision`
  (tinyint), `symbol_first` (0/1), `thousands`, `status` (0/1), `sort`, **`is_base` (tinyInteger, default
  `0`)** — the base-currency flag, invariant "exactly one row with `is_base=1`" (v2.1).
- Order currency snapshot: `shop_order.currency` (code) + `shop_order.exchange_rate` **decimal(16,6)**;
  `shop_order_detail.exchange_rate` is also **decimal(16,6)** (all 3 tables unified to keep small rates
  accurate).
- The 4 base-denominated price columns that rebase() rescales: `shop_product.price`, `shop_product.cost`,
  `shop_product_attribute.add_price`, `shop_product_promotion.price_promotion`.

### Resolving the active currency (storefront only)
- `CurrencyMiddleware` (registered only on the **front** route group, **not** admin): reads `session('currency')`
  → if empty uses `gp247_store_info('currency')` (the default display currency) → if not in the enabled list,
  takes the first active currency → calls `ShopCurrency::setCode()` to load static props.
- Storefront currency-switch route: `front.currency` (`currency/{code}`) → `session(['currency' => $code])`.
- **Admin note:** admin does **not** run `CurrencyMiddleware`, so to display correctly it must use the order's
  **snapshot** currency (`onlyRender`), not the active currency.

### Base currency & rebase (v2.1) — `Models/ShopCurrency.php`
- `getBaseCode(): ?string` — the **single source of truth** for the base: `where('is_base', 1)->value('code')`.
  Replaces the old "scan the active currency with rate `1`" approach (error-prone). Returns `null` if an
  upgraded site has not picked a base yet.
- **Boot guards (defense-in-depth):**
  - `deleting`: blocks deletion when `deleteBlockReason() !== null` (applies to ALL delete paths: Livewire,
    seeder, tinker…).
  - `saving`: blocks a save when `is_base=1` but `exchange_rate != 1` or `status != 1` (keeps the base
    invariant). Rebase and seeders use the query builder (which bypasses model events), so this guard never
    fights the legitimate base-swap flow.
- `rebase(string $newBaseCode, float $newRateForOldBase): void` — a **value-preserving** base change, run in
  **one transaction** (NFR-AVAIL-rebase-atomicity). Let `r = the new currency's current exchange_rate`;
  multiply the 4 base price columns × r (bulk UPDATE at the DB layer — never loads the catalog into PHP,
  RISK-TECH-rebase-bulk-price-update); divide every rate ÷ r; pin the new currency `rate=1, is_base=1`, the old
  one `rate=$newRateForOldBase, is_base=0`. Preservation proof: `(price×r)×(oldRate(C)/r) = price×oldRate(C)`
  for every currency C. Throws `InvalidArgumentException` when the base is missing / the target is invalid /
  the rate is invalid. Finally calls `resetStaticCache()`.
- `sqlNumericLiteral(float): string` (private) — `sprintf('%.6F', $r)` builds a safe decimal literal (no
  exponent, dot decimal, capped at scale 6) to interpolate into `col = col * r`. For caller-controlled values
  only.
- `deleteBlockReason(): ?string` — returns `base` / `default` / `in_use` / `last_active` / `null` per the delete
  invariant (section 8). Keyed on `code`; checks `is_base` first (v2.1); uses `gp247_store_info('currency')`
  (not `getCode()`, since admin does not load the middleware); counts `where('status',1)->count()` directly.

### Livewire admin — `Admin/Livewire/CurrencyManager.php`
- Hides/locks Edit & Delete for the base row; `edit()`/`save()` refuse when `is_base=1` (guards direct URL
  access).
- **Change base** modal: `rebaseTarget`, `rebaseOldRate`, `rebaseConfirmed`. `updatedRebaseTarget()`
  auto-suggests `rebaseOldRate = 1 / rate(target)` (`sprintf('%.6F')` then trim). `rebase()` validates
  (`rebaseTarget` must exist, `status=1`, `is_base=0`; `rebaseOldRate` `numeric|gt:0|not_in:1`; `rebaseConfirmed`
  `accepted`) then calls `ShopCurrency::rebase()`, surfaces a domain error via toast, on success flashes +
  redirects.

### Helper functions (`Library/Helpers/currency.php`)
- `gp247_currency_render($money, $currency=null, $rate=null, $space=false, $useSymbol=true)` — **convert**
  (multiply by rate) + format + symbol.
- `gp247_currency_render_symbol($money, $currency=null, $space=false, $includeSymbol=true)` — **format + symbol
  only, NO conversion** (the amount is already in the right currency, e.g. an order's snapshot amount).
- `gp247_currency_value($money, $rate=null)` — convert a value by the rate.
- `gp247_currency_code()` / `gp247_currency_rate()` / `gp247_currency_info()` — the active currency.
- `gp247_currency_format($money)` — format a number by the active currency's precision (no symbol).
- `gp247_currency_all()` — the enabled currencies (sorted); `gp247_currency_all_active()` — a `code → name` map
  of enabled currencies.
- **`gp247_base_currency_code(): ?string` (v2.1)** — wrapper around `ShopCurrency::getBaseCode()`.
- **`gp247_money_hint($code = null): string` (v2.1)** — the hint beside a money input: `null` → uses the base
  currency, returns `"(CODE)"`; if it cannot be resolved, falls back to the i18n label `product.base_unit_hint`.

---

## Q&A

**Q1: Which currency do I enter product prices in?**

→ In the **base currency** — the one with the "Base" badge (rate `1`). Other currencies are converted
automatically on display. From 2.1, each money input has a currency-code hint (e.g. `(VND)`) reminding you
which currency you are entering.

**Q2: How is the "base currency" different from the shop's "default currency"?**

→ The **base** is *where product prices are stored* (the measuring stick, always rate `1`, locked). The
**default** (set in Website Info) is *what customers see first* when they enter the shop. They are often the
same but are two distinct concepts.

**Q3: How do I change the base currency from USD to VND?**

→ Go to Configuration → Currency, click **Change base**, choose the new currency, keep the suggested rate, tick
the confirmation and click change. The system recomputes the numbers so **what customers see does not change** —
you do not re-enter each product's price.

**Q4: Does changing the base misprice products?**

→ No. The base-change feature is **value-preserving**: it multiplies/divides the numbers consistently, so the
displayed price in every currency stays the same. Old orders are also left untouched.

**Q5: Why can't I edit/delete the base currency?**

→ Because the base is the catalog's price measuring stick; hand-editing it would misprice everything. To
replace it, use **Change base currency** instead of editing it directly.

**Q6: What does the `(VND)` hint beside a money input mean?**

→ It reminds you which currency that field is entered in (on product screens it is the **base**; on order
screens it is the order's currency). Display only — it does not change how numbers are stored.

**Q7: Why does my VND show `.00` at the end (e.g. `250,000.00`)?**

→ Because that currency has a non-zero **precision (decimal places)**. Set VND's precision to `0` to drop the
decimals.

**Q8: If I change a rate, are old orders recalculated?**

→ No. Each order stored its currency and rate at order time; changing the rate only affects **new** prices
displayed on the storefront.

**Q9: Why can't I delete a currency?**

→ Because it falls into one of the blocked cases: it is the **base**, it is the shop's **default**, some
**order** uses it, or it is the **last enabled** currency. The system shows the specific reason.

**Q10: Can rates update automatically from the market?**

→ The current version enters rates **manually** on the Currency screen. There is no built-in automatic rate
update.

---

<sub>📅 **Last updated:** 2026-08-22 · ✍️ **Author:** GP247</sub>
