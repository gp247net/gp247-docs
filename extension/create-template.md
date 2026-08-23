> 🌐 **Language:** [🇻🇳 Tiếng Việt](./create-template_vi.md) · 🇬🇧 English (current)

# Creating a Template (storefront theme) for GP247

## Introduction

This document explains how to **create a new template (storefront theme) for GP247**, aimed at
PHP/Laravel developers who want to design their own look for a GP247 shopping website. By the end you
will know how to scaffold a template, understand its folder structure, build home/static pages, and —
most importantly — understand **how a template works together with `gp247/shop`** (the view fallback
mechanism), so you know when you do and do not need to rewrite the shopping pages yourself.

> ⚠️ **The two most important things to remember before you start:**
>
> 1. **A template only works when `gp247/front` is installed.** A template is the look of the
>    storefront, and the whole storefront is provided by the `gp247/front` package. That is why a
>    template's `gp247.json` always declares `requireComposerPackages: ["gp247/front"]`. Without `gp247/front`,
>    the template has no effect.
> 2. **The sample template does NOT contain any `gp247/shop` views.** If your new template has no views
>    for the shopping pages (product list, cart, checkout...), `gp247/shop` will **automatically use its
>    own default views** through the fallback mechanism (Section 6). You only need to write these pages
>    yourself when you want to customize them — and in that case you should **publish the `gp247/shop`
>    views out** so you can adapt them for your new template.

---

## 1. Prerequisites

Before you begin, you need:

1. A GP247 website with `gp247/core` (2.0+) **and** `gp247/front` installed. If the site sells
   products, it usually also has `gp247/shop`.
2. Access to run `php artisan` commands on the server or your development machine (terminal / command line).
3. (Recommended) Node.js if you want to compile the Tailwind CSS for your template yourself — see Section 5.4.

> Quick glossary:
> - **Template**: the look of the storefront (what customers see). Each store has one active template.
> - **`gp247/front`**: the package that provides the storefront (routes, controllers, template mechanism). **Required** for a template to run.
> - **`gp247/shop`**: the commerce package (products, cart, checkout). Optional, but if present a template can customize the look of the shop pages.
> - **`configKey`**: the unique identifier of the template, **exactly the same as the template's folder name**.
> - **View (Blade)**: a Laravel `.blade.php` interface file.

---

## 2. Step 1 — Scaffold the template with an artisan command

`gp247/front` ships a command that scaffolds a standard v2 template so you don't have to create every
file by hand.

1. Open a **Terminal** at the root of your GP247 website (where the `artisan` file lives).
2. Type exactly the following line and press Enter (replace `MyShopSkin` with the template name you want):

   ```bash
   php artisan gp247:make-template --name=MyShopSkin --download=0
   ```

   What the parameters mean:
   - `--name=MyShopSkin`: the template name. Use **PascalCase** (capitalize each word, no accents, no spaces).
   - `--download=0`: create the template **directly** in `app/GP247/Templates/MyShopSkin` (ready to use right away). With `--download=1`, the command creates a `.zip` file in `storage/tmp` for distribution/packaging.

3. On success, the terminal returns a JSON line with `"error":0`:

   ```json
   {"error":0,"path":"","msg":"Success"}
   ```

   The `app/GP247/Templates/MyShopSkin` folder is created with all the sample files.

---

## 3. Template folder structure

After scaffolding, the template has the structure below (the table explains each part's role). In
addition, a **full, live** template usually has extra interface folders such as `layout/`, `screen/`,
`partials/`, `components/`, `livewire/`:

```
app/GP247/Templates/MyShopSkin/
├── blocks/          # Reusable UI blocks (banners, content blocks...) for LayoutBlock
├── Lang/
│   ├── en/lang.php  # English language strings
│   └── vi/lang.php  # Vietnamese language strings
├── Plugins/         # (Optional) plugins bundled with the template
├── public/          # Static assets (css/js/images); copied to public/GP247/Templates/MyShopSkin on install
├── AppConfig.php    # Main config file: install/uninstall/enable/disable/setupStore
├── config.php       # Template default config (OVERWRITTEN on update — see Section 7)
├── function.php     # The template's helper functions
├── gp247.json       # ⭐ Declares template info (especially requireComposerPackages)
├── Provider.php     # Registers services (views, lang, config...)
└── Route.php        # The template's own routes (if any)

# Interface folders you add yourself (like a real running template):
├── layout.blade.php # Overall layout shell (header + content + footer)
├── layout/          # Layout fragments (header, footer, menu...)
├── screen/          # Pages: home, page_detail, 404... (and shop_* to override — Section 6)
├── partials/        # Reusable interface fragments
├── components/      # The template's own Blade components
└── livewire/        # Views for Livewire components (cart, product filter...)
```

> Note on view paths: template views are registered by GP247 under the special namespace
> `GP247TemplatePath::<TemplateName>.<path>`. For example, the `screen/home.blade.php` file of the
> `MyShopSkin` template is referenced as `GP247TemplatePath::MyShopSkin.screen.home`. You do **not**
> need to register anything yourself — `gp247/front` scans the whole `app/GP247/Templates` folder.

---

## 4. Step 2 — Declare `gp247.json`

Open `gp247.json`; the generated sample content is:

```json
{
    "name": "MyShopSkin module",
    "image": "images/logo.jpg",
    "auth": "GP247",
    "email": "support@gp247.net",
    "link": "https://GP247.net",
    "configGroup": "Templates",
    "configCode": "MyShopSkin",
    "configKey": "MyShopSkin",
    "version": "1.0",
    "requireCore": ["2.1"],
    "requireUpdateFrom": "1.0",
    "requireComposerPackages": ["gp247/front"],
    "requireGp247Extensions": []
}
```

What the important fields mean:

| Field | Meaning |
|---|---|
| `configGroup` | Always `"Templates"` for a template. |
| `configKey` | **Unique identifier, same as the template folder name.** Never change it after release. |
| `version` | Version (semver: `1.0`, `1.1`...). Every new release must **increase** this number. |
| `requireCore` | Compatible `gp247/core` versions. For the v2 standard, use `["2.1"]`. |
| **`requireComposerPackages`** | **Always includes `"gp247/front"`** — because a template only runs with the storefront present. Add `"gp247/shop"` if the template is meant only for shopping sites. |
| `requireUpdateFrom` | The minimum version allowed to 1-click update to this one. `"1.0"` is a safe default. |

> **Keys renamed in gp247/core 2.1:** `requirePackages` → `requireComposerPackages`, `requireExtensions` → `requireGp247Extensions`. Core 2.1 **still reads** the old keys (backward compatible) but they are **deprecated** — new templates should use the new keys (`requireComposerPackages` always includes `"gp247/front"`).

---

## 5. Step 3 — Build the template interface

### 5.1. `AppConfig.php` — the template lifecycle

This file extends `ExtensionConfigDefault` and defines what happens when the site owner
installs/uninstalls/enables/disables the template. The difference from a plugin: the `setupStore($storeId)`
method is used to **assign the template to a store** (it updates the `template` column of `AdminStore`).
The scaffolded stub already works; you usually only add your own logic.

### 5.2. Overall layout and pages

- `layout.blade.php` (or `layout/`): the overall shell with `<head>`, header, content area, footer.
- `screen/`: individual pages — e.g. `home.blade.php` (home), `page_detail.blade.php`,
  `404.blade.php`, `front_search.blade.php`.
- `partials/`, `components/`: reusable interface fragments.

### 5.3. Dynamic interface — use Livewire/Alpine, no jQuery

GP247 2.0 uses **Tailwind + Alpine + Livewire**. Dynamic interactions (cart, product filter...) are
built as Livewire components; their views live in `livewire/`. Do **not** use old jQuery/Bootstrap
widgets. Every displayed string must be rendered through `gp247_language_render(...)` / `trans(...)`,
never hardcoded.

### 5.4. Assets and Tailwind CSS

- Static assets (css/js/images) go in the template's `public/` folder; on install they are copied to
  `public/GP247/Templates/MyShopSkin`.
- GP247 uses **precompiled** Tailwind (not runtime JIT). If your template adds new Tailwind classes,
  you must recompile the CSS file yourself with Node.js (`npx tailwindcss ...`) and commit the output.
  If you only reuse existing classes, no rebuild is needed.

---

## 6. ⭐ TEMPLATES AND `gp247/shop` — THE VIEW FALLBACK MECHANISM (read carefully)

This is the most important part of the document. It answers the question: *"My new template has no
product/cart page — will the shopping site break?"* — **It will not.**

### 6.1. How it works

When a customer opens a page handled by `gp247/shop` (for example the product list), the shop's
controller does **not** call a fixed view file directly. It resolves the view through the
`gp247_shop_process_view()` helper, which works like this:

1. **It tries the active template's view first.** For the `MyShopSkin` template it tries
   `GP247TemplatePath::MyShopSkin.screen.shop_product_list` (i.e. the file
   `app/GP247/Templates/MyShopSkin/screen/shop_product_list.blade.php`).
2. **If the template has that file → it uses it** (the template has "overridden" the shop view).
3. **If the template does NOT have it → it falls back to the shop package's default view:**
   `gp247-shop-front::screen.shop_product_list` (i.e. the file
   `vendor/gp247/shop/src/Views/front/screen/shop_product_list.blade.php`).

**Consequence:** a new template that contains **no** shop page at all still runs the shopping website
normally — every shop page automatically uses the default in the `gp247/shop` package. This is exactly
why the sample template only has front pages (home, page_detail, 404...) and no `shop_*` pages.

### 6.2. When do you need to write shop pages inside the template?

Only when you want a **shop look different from the default** (a custom product-list layout, a cart page
in your own style...). In that case you create the shop view **inside your own template**, placed at the
exact sub-path the shop expects. List of shop pages you can override (placed in the template's `screen/`):

| File placed in the template | Corresponding page |
|---|---|
| `screen/shop_product_list.blade.php` | Product list |
| `screen/shop_product_detail.blade.php` | Product detail |
| `screen/shop_cart.blade.php` | Cart |
| `screen/shop_checkout.blade.php` | Checkout |
| `screen/shop_wishlist.blade.php` | Wishlist |
| `screen/shop_compare.blade.php` | Product compare |
| `screen/shop_search.blade.php` | Search |
| `screen/shop_order_success.blade.php` | Order success |

Besides `screen/`, the shop package has other view folders you can override the same way (`account/`,
`auth/`, `blocks/`, `common/`, `livewire/`) — place them in the template at the same sub-path as in the
shop package.

> ⭐ Total-method plugins (coupon/point) work **automatically** when you do **not** override the checkout
> view — the `gp247/shop` default already renders the total-method zone (recommended path). **Only if you
> override the checkout wizard view** (`livewire/shop_checkout-wizard.blade.php`) for custom styling, keep
> the two total-method includes at the confirm step:
> `@include('gp247-shop-front::partials.checkout_total_methods')` and
> `@include('gp247-shop-front::partials.order_totals')`. They render every coupon/point plugin
> (the `CheckoutTotalMethod` contract) generically. Removing them silently hides the coupon input for
> **all** total-method plugins. Any fragment they render must use only UI classes your template's
> precompiled CSS actually ships (a brand-new Tailwind class won't exist in the built CSS).

### 6.3. How to reference a full template (with shop): publish the `gp247/shop` views

So you don't have to write shop pages from scratch, **publish the default `gp247/shop` views out** as a
reference, then copy them into your template to customize:

1. Run the command to publish the shop's storefront views:

   ```bash
   php artisan vendor:publish --tag=gp247:shop-view-front
   ```

   On success, the terminal lists the copied files. All of the shop's default front views are copied
   into the default template folder `app/GP247/Templates/GP247Front` (this is the command's fixed destination).

2. Open the just-published folder (`app/GP247/Templates/GP247Front`) to see the full structure of a
   template that includes the shop: `screen/shop_*.blade.php`, `account/`, `auth/`, `blocks/`,
   `common/`, `livewire/`...

3. **Copy the shop pages you want to customize into your own template**, keeping the same sub-path.
   For example, to customize the product-list page for `MyShopSkin`:

   ```
   Copy:  app/GP247/Templates/GP247Front/screen/shop_product_list.blade.php
   To:    app/GP247/Templates/MyShopSkin/screen/shop_product_list.blade.php
   ```

   Then edit the file inside `MyShopSkin` as you wish. Thanks to the mechanism in 6.1, `gp247/shop` will
   **prefer the version in your template**, while any shop pages you did **not** copy still use the default.

> Tip: only copy the pages you truly want to change. Copying the whole set and leaving it untouched
> creates needless maintenance burden (and when the shop package updates, the copy kept in your template
> will not update along with it).

> Distinguishing the tags: `gp247:shop-view-front` is the **storefront** views (for templates).
> `gp247:shop-view-admin` is the shop's **admin** views — **unrelated** to the storefront template,
> don't confuse them.

---

## 7. Step 4 — Version updates (safe to update)

A template uses the **same 1-click update mechanism** as a plugin: on update, the system overwrites
**all files** of the template but **keeps the database (`admin_config`) intact**. Therefore the same
safety principles as for plugins apply to templates:

- `version` must **increase** each release; `configKey` **never changes**.
- `config.php` contains only **default values** (overwritten on update); every site-owner choice must be
  stored in `admin_config`.
- If the data structure changes between versions, migrate it in the `AppConfig::update($fromVersion)` hook.
- Do not store user-uploaded files inside the template folder.

For full details on the safe-update mechanism, see the
[Creating a Plugin](./create-plugin.md) guide (Section 6) — the principles are identical.

---

## 8. Step 5 — Install and test

1. Clear the Laravel cache to load the new routes/views/config:

   ```bash
   php artisan optimize:clear
   ```

   On success, the terminal shows lines like `... cleared successfully`.

2. Go to admin → **Templates**, find your template, click **Install** then **Activate** (set it as the
   store's active template).
3. Open the website's home page to see the new template. If `gp247/shop` is present, try the product-list
   and cart pages: if you haven't overridden them, they display with the shop's default look (exactly as expected).
4. Check the display on a **phone** (responsive) and in **dark mode** if the template supports it.

---

## 9. Checklist before release

- [ ] `gp247.json`: `configGroup` = `"Templates"`, `configKey` matches the folder name, `requireComposerPackages` includes `"gp247/front"`.
- [ ] `version` has **increased** from the previous release (if this is an update).
- [ ] The interface uses Tailwind + Livewire/Alpine; **no** more old jQuery/Bootstrap widgets.
- [ ] Every string is rendered through `gp247_language_render(...)` / `trans(...)`, not hardcoded; both `vi` and `en` present.
- [ ] If you added new Tailwind classes: you recompiled the CSS and included the output in `public/`.
- [ ] You have clearly decided which shop pages to override (copied into the template's `screen/`) and which to leave on the default fallback.
- [ ] `config.php` contains only defaults; site-owner choices live in `admin_config` (safe on update).
- [ ] You ran `php artisan optimize:clear`, installed + activated successfully; home page and shop pages display correctly.
- [ ] Checked responsive (phone) and dark mode (if supported).

---

## Q&A

**Q1: I installed my template but nothing seems to change?**

→ Most likely the website **does not have `gp247/front` installed**. A template is the storefront look and
only works when `gp247/front` is present. Install `gp247/front` first.

**Q2: My new template has no product/cart page — will the shopping site break?**

→ No. `gp247/shop` automatically uses its own default views (fallback) for pages the template doesn't have.
The site still sells normally. See Section 6.1.

**Q3: How do I make the cart page use my own custom look?**

→ Create the file `screen/shop_cart.blade.php` **inside your template**. `gp247/shop` will prefer this file
over the default. See the path table in Section 6.2.

**Q4: I want to look at a full template that already has all the shop pages for reference — how?**

→ Run `php artisan vendor:publish --tag=gp247:shop-view-front`. This command exports all of the shop's
default storefront views into `app/GP247/Templates/GP247Front` for you to view/copy. See Section 6.3.

**Q5: After publishing, do I have to copy everything into my template?**

→ You shouldn't. Only copy the pages you actually want to restyle. Pages you don't copy will use the
default — which is both leaner and keeps you receiving updates from the shop package.

**Q6: What if I put a shop file in the wrong place — will it be ignored?**

→ Yes. The fallback mechanism looks by exact sub-path (e.g. `screen/shop_product_list.blade.php`). A wrong
name or wrong folder means the system treats the template as not having it and uses the default.

**Q7: What's the difference between `gp247:shop-view-front` and `gp247:shop-view-admin`?**

→ `gp247:shop-view-front` is the **storefront** views (for templates — what you need).
`gp247:shop-view-admin` is the shop's **admin screens**, unrelated to the customer-facing template.

**Q8: Does updating a template lose the site owner's settings?**

→ No, if you do it right: an update overwrites files but keeps the database. Every site-owner choice must
be stored in `admin_config`, not in `config.php` (which gets overwritten). See Section 7.

**Q9: I added a new Tailwind class but the interface didn't change?**

→ GP247 uses precompiled Tailwind, not JIT. A new class that hasn't been built has no effect. You must
recompile the CSS file (`npx tailwindcss ...`) before it takes effect.

**Q10: I edited a view but the page still shows the old version?**

→ Run `php artisan optimize:clear` to clear the view/route/config cache, then reload the page. This is the
most common issue, caused by Laravel still holding the old cache.

---

<sub>📅 **Last updated:** 2026-07-30 · ✍️ **Author:** GP247</sub>
