> 🌐 **Language:** [🇻🇳 Tiếng Việt](./product-bundle_vi.md) · 🇬🇧 English (current)

# Product Bundle (Combo) in GP247

## Introduction
This document explains the **product bundle (combo)** feature in GP247 (from version 2) and guides store
owners in creating a combo product made of several child products. It is written for **non-technical
readers**: after reading, you will understand how a bundle differs from a normal product, how to enable
the feature, how to create a combo, and how its price / stock behave.

---

## 1. What is a bundle? The three product kinds in GP247

In GP247, every product is one of **three kinds**:

| Kind | Meaning | Sellable? | Price | Stock |
| --- | --- | --- | --- | --- |
| **Single** | A normal, standalone product | Yes | Its own price | Its own stock |
| **Bundle / Combo (Build)** | One product sold as **a package** containing several **child products** with quantities | Yes (like a normal product) | **The bundle's own price** (you set it) | The bundle has its own stock; **selling it deducts each child product's stock** |
| **Group** | Just **groups products for display**, not sold directly | **No** | None (shows a label, not a price) | Not managed |

> **A bundle is the "Bundle / Combo (Build)" kind.** Example: a "Tet Gift Set" = 1 box of cookies + 2
> bottles of water + 1 paper bag. The customer buys **1 set**, and the system deducts stock automatically:
> 1 box of cookies, 2 bottles of water, 1 paper bag.

**The most important things to remember:**
- The bundle's price is **the price you set for the bundle**; GP247 does **not** auto-sum the child prices.
  You decide the combo price yourself (usually cheaper than buying separately, to attract customers).
- When 1 bundle is sold, the stock of **each child product is deducted** by the quantity declared in the bundle.

---

## 2. Enable the bundle feature

The product-kind feature (Single/Build/Group) is controlled by a switch in the configuration. If the switch
is off, **every product is Single** and you will **not see** the option to create a bundle.

1. Log in to the **admin** area.
2. Open **Shop Configuration**.
3. Find the product structure-type option (the config key is **`product_kind`**, labeled something like
   "Use structure type").
4. **Turn it on**, then save.

   If it succeeds, when you open the add/edit product form you will see a **product kind** selector with 3
   cards: Single, Bundle (Build), Group.

> If you turn this switch off later, products are treated as Single and the kind selector is hidden (old
> bundle data stays in the database — only the selector is not shown).

---

## 3. Steps to create a bundle product

> Preparation: the **child products** (e.g. box of cookies, bottle of water…) should already exist as
> Single products, because you will search for them by name/SKU when adding them to the bundle.

1. Go to **Product** → **Add new**.
2. Under **Product kind**, choose the **Bundle (Build)** card.
3. Fill in the basics like a normal product: **Name**, **SKU**, **Price** (the bundle's selling price),
   the bundle's **Stock**, description…
4. Open the **Composition** tab (this tab appears only when the kind is Bundle or Group).
5. In the search box, type the child product's **name or SKU**, pick it from the suggestions, then click add.
6. For each child product added, enter the **Quantity** contained in the bundle (e.g. `2` bottles of
   water). Repeat for all child products.
7. Click **Save**.

   If it succeeds, the bundle product appears in the product list; reopening the Composition tab shows all
   child products with their quantities.

> Tip: you can edit the bundle anytime — add/remove children or change quantities, then Save. The old child
> list is overwritten by the new one.

---

## 4. Bundle price and stock

- **Price:** it is **the price you enter for the bundle** (you can set a promotion price like a normal
  product). GP247 does **not** auto-sum the children — you fully control the combo price.
- **Stock when selling:** when a customer buys **N bundles**, the system deducts:
  - The **bundle's** stock by N, and
  - Each **child product's** stock by `N × (child quantity in the bundle)`.
  - Example: a bundle contains 2 bottles of water, the customer buys 3 bundles → 6 bottles are deducted.
- **Promotion:** supported for bundle products (same as Single).
- **Attributes/variants (size, color…):** **not** supported for bundle products — variants are for Single
  products only.

> Stock note: since selling one bundle draws down the children's stock, make sure the children have enough
> quantity. If a child runs out, review the bundle's stock accordingly.

---

## 5. How does a bundle appear to customers?

- On the **product detail page** (storefront), a bundle shows a **"Bundle" block** listing the child
  products: image, **quantity** of each, child name, and a **`+`** sign between them to convey "the bundle
  contains A + B + C".
- Customers **add to cart and buy it like a normal product** (no need to pick each child).
- The displayed price is **the bundle's price**.

> Unlike the **Group** kind: a Group has no buy button — it just shows member products for the customer to
> click through — it is not a sellable combo.

---

## 6. Technical details (for developers)

> This section is for **developers**. Regular users can skip it.

**Product-kind constants** — `vendor/gp247/shop/src/Library/Const.php`:
```php
define('GP247_PRODUCT_SINGLE', 0);
define('GP247_PRODUCT_BUILD', 1);   // bundle/combo
define('GP247_PRODUCT_GROUP', 2);
```
Stored in the `shop_product.kind` column.

**Database tables**
- `shop_product_build` — bundle→child relation: `build_id` (parent bundle id), `product_id` (child id),
  `quantity` (child quantity, `decimal(15,2)`), composite primary key `(build_id, product_id)`.
- `shop_product_group` — group→member relation: `group_id`, `product_id` (composite primary key). **No
  `quantity` column** (unlike Build).

**Models & relations** (`ShopProduct`)
- `builds()` → `hasMany(ShopProductBuild, 'build_id', 'id')`; each item has `->product` (child) and `->quantity`.
- `groups()` → `hasMany(ShopProductGroup, 'group_id', 'id')`.
- `ShopProductBuild::product()` / `ShopProductGroup::product()` → `belongsTo(ShopProduct, 'product_id')`.

**Price & stock** (`ShopProduct`)
- `getFinalPrice()` — returns the **parent product's own price** (or promotion price); it does **not** sum
  the children.
- `updateStock($productId, $qtyChange)` — deducts the parent's stock; if `kind == GP247_PRODUCT_BUILD` it
  loops `builds` and deducts each child by `qtyChange × build->quantity`.
- `allowSale()` — excludes **Group** from selling (`kind != GP247_PRODUCT_GROUP`).

**Admin** (Livewire `ProductManager` + trait `HasProductComposition`)
- The kind selector is gated by `gp247_config('product_kind')`: when `= 0` it hides the selector and forces
  `kind = SINGLE`.
- `persistComposition()` — **delete-then-recreate** the rows via `builds()->create(['product_id','quantity'])`
  (or `groups()->create([...])`).
- The "composition" tab is enabled only for Build/Group; the "variants" tab only for Single.

**Storefront** — `shop_product_detail.blade.php`: the Bundle block loops `$product->builds`, and the
quantity badge uses `gp247_qty_format($build->quantity)`.

---

## Q&A

**Q1: I don't see the "Bundle (Build)" kind option when creating a product?**

→ You must enable `product_kind` in Shop Configuration first (see Section 2). If it is off, every product
defaults to Single and the kind selector is hidden.

**Q2: Is the bundle price auto-summed from the child prices?**

→ No. You **set the price** for the bundle yourself. GP247 does not auto-sum — you fully control the combo price.

**Q3: When a customer buys 1 bundle, how is stock deducted?**

→ The bundle's stock drops by 1, and each **child product's** stock drops by its declared quantity (e.g. a
bundle with 2 bottles → 2 bottles deducted).

**Q4: How does a Bundle (Build) differ from a Group?**

→ Build is a **sellable combo** with a price and child-stock deduction. Group only **groups products for
display** — not sold, no price, cannot be added to cart.

**Q5: Can I assign attributes (size, color) to a bundle product?**

→ No. Attributes/variants are for Single products only. Bundles do not use variants.

**Q6: Do child products need to exist first?**

→ They should, because you search for children by name/SKU when adding them to the bundle. Create the child
(Single) products first.

**Q7: Can I set a promotion for a bundle?**

→ Yes. Bundle products use promotions the same way as Single products.

**Q8: Can I edit a bundle (add/remove children, change quantities)?**

→ Yes. Open the bundle product, go to the Composition tab, adjust, then Save. The old child list is replaced
by the new one.

**Q9: If a child product is out of stock, can the bundle still be sold?**

→ The system still deducts child stock on sale; you should keep child stocks and the bundle stock in sync
with reality to avoid overselling.

**Q10: What if I turn off `product_kind` after creating bundles?**

→ Products are treated as Single and the kind selector is hidden. Old bundle data stays in the database —
only the kind option is no longer shown on the form.

---

<sub>📅 **Last updated:** 2026-08-04 · ✍️ **Author:** GP247</sub>
