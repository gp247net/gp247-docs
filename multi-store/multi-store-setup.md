> 🌐 **Language:** [🇻🇳 Tiếng Việt](./multi-store-setup_vi.md) · 🇬🇧 English (current)

# Multi-Store — Part 1: Setup

## Introduction
This document walks you through installing the **Multi-Store** plugin on a running S-Cart website, creating a second store, pointing a domain at it, adding the **Pro edition** if you bought it, and checking that everything works. It is written for site owners or administrators with basic technical skills; follow the steps and it will run — no programming required. How to operate several stores and what each setting means is covered in [Part 2 — Operations](./multi-store-operations.md).

## Before you start
- A running S-Cart **3.x** website with `gp247/core` **3.0** and `gp247/shop` installed.
- No multi-vendor plugin installed on the same website — the two models are mutually exclusive.
- **One domain per new store**, plus the access needed to change DNS and web-server configuration so it points at this website.
- Permission to run `php artisan` on the server, **or** use the admin-interface install method in Step 1.

No S-Cart website yet? Install the platform first following [Part 1 of the Multi-Vendor documentation](../multi-vendor/multi-vendor-setup.md) (the S-Cart installation steps are the same), then come back here.

## Step 1: Install the Multi-Store plugin (free edition)
Pick **one** of three methods.

**Method 1 — From the GP247 library (recommended)**
1. Log into the admin → **Extensions** → **Plugins**.
2. Open the **Library** tab, find **MultiStore**, click **Install**.

**Method 2 — Import a zip file**
1. Go to **Extensions** → **Plugins** → **Import file** tab.
2. Choose the plugin zip file and upload it.

**Method 3 — Copy manually (when the first two fail)**
1. Unzip and copy the source folder to `app/GP247/Plugins/MultiStore/`.
2. Copy the plugin's `public` folder to `public/GP247/Plugins/MultiStore/`.
3. Go to **Extensions** → **Plugins** → **Stored locally** tab, find **MultiStore**, click **Install**.

If a multi-vendor plugin is present, the installer **stops immediately and writes nothing** — that is deliberate, see Conditions & Rules.

## Step 2: Enable it
1. In the plugin list, click **Enable** on the MultiStore row. A **Multi-store** menu appears in the admin sidebar with:
   - **Store list** — the free edition's main screen;
   - four items labelled *(Pro)*: Unified dashboard, Store administrators, Cross-store product publishing, Product revenue report.
2. Click one of the *(Pro)* items: without the Pro edition it opens an **explanation page** — that is the correct state, not a bug.

Open **Multi-store → Store list**: you will see the existing root store, a quota indicator (for example *1/3 stores*) and the **Strict domain checking (STRICT)** switch.

## Step 3: Create your second store
1. Go to **Multi-store** → **Store list** → **Add new**.
2. Fill in the **required** fields:

   | Field | Rule | Example |
   | --- | --- | --- |
   | Store code | unique, max 20 characters | `brandb` |
   | Domain | unique, max 200 characters, **written without `https://`** | `brand-b.com` |
   | Language | pick from the active languages | `English` |
   | Currency | pick from the active currencies | `USD` |
   | Theme (template) | pick an installed template | `GP247Front` |
   | Title (per language) | required, max 200 characters | `Brand B` |

   The rest are optional: SEO keywords (max 200), description (max 300), logo, phone, email, address, and the **maintenance page content** per language (pre-filled with a default you can edit).

3. Click **Save**. The system creates the store and its default data in a single transaction — it either succeeds completely or creates nothing.

If you have used up your quota (free edition: 3 stores, **including the root store**), adding is blocked with a clear message — that is the boundary between the editions, not a bug.

## Step 4: Point the domain at the system
A new store only opens once its domain **actually points at this website**. Two things, done outside S-Cart:

1. **DNS**: point the domain (an `A` or `CNAME` record) at the server running the website.
2. **Web server**: declare that domain for the same website — the same `public/` document root as the main domain. On Apache that is another `ServerAlias`, on Nginx another entry in `server_name`; on a control-panel host, add it as a "parked domain" / "addon domain" **pointing at the same folder**.
3. Install an SSL certificate for the new domain if the website runs on `https`.

> This is the most commonly skipped step: a store created in the admin whose domain does not point at the system will show the domain registrar's page or a connection error — nothing to do with the plugin.

## Step 5: Turn on strict domain checking (recommended)
On **Store list**, switch on **Strict domain checking (STRICT)**.

With it on, the website **only serves** the domains declared on your stores, plus the root domain in `.env` (`APP_URL`). Any unknown domain pointed at your server gets an **access denied** page instead of your website. This stops other people pointing their domains at your site.

> ⚠️ Turning STRICT on **before** every domain is declared will lock you out of the stores you missed. Create the stores and fill in their domains first.

## Step 6: Verify
1. Open the new store's domain. You must see the website running with **that store's theme, language and currency** — not the root store's.
2. In the admin, create (or edit) a product and **assign it to the new store**; check it appears on the new domain and does **not** appear on the root store unless you assigned it there.
3. Place a test order on the new store, then check in the admin that the order is recorded against the right store.
4. Check system health:

   ```bash
   php artisan gp247:info
   php artisan gp247:doctor
   ```

## Step 7 (optional): Add the Pro edition
Only do this if you bought the Pro edition. Pro installs **on top of** the free edition; it does not replace it.

1. Check that the **MultiStore** row in the plugin list is installed and **enabled**. The Pro plugin declares the free edition as a required extension and refuses to install without it.
2. Install **MultiStorePro** using any of the three methods in Step 1 (usually **Import file** with the zip GP247 delivered to you).
3. Click **Enable**. **Installing without enabling means Pro is not active yet** — this is the most common cause of "I installed it but still see the explanation page".
4. Run this command (or clear the cache in the admin) so Pro's routes are registered:

   ```bash
   php artisan gp247:cache-rebuild
   ```

5. Reload the admin and check three signs that it unlocked:
   - The four *(Pro)* items in the **Multi-store** menu open the **real screens**, not explanation pages.
   - **Store list**: the quota indicator switches to **unlimited**, and the **Lock** column becomes clickable (it was disabled with a Pro note before).
   - Under **User permissions → Roles**, two preset roles appear — **[M]Store Admin** and **[M]Store Member** — ready to hand to store administrators.

Nothing is reinstalled and no data is migrated: stores, products, orders and settings stay as they are.

**If you disable or uninstall Pro**: Pro screens revert to explanation pages, the quota returns to 3 stores and the Lock column is disabled again. **No data is deleted** — stores beyond the quota remain and keep running, cross-store product links and store-administrator assignments are kept; reinstall Pro and you carry on.

## Conditions & Rules (know before you act)

**At install time**
- **The plugin refuses to install if a multi-vendor plugin is present** — the two use the same store data under different models; the installer stops before writing anything. Uninstall the other plugin first.
- **`gp247/shop` must be installed** — the plugin builds on shop products and orders.
- **Pro requires the free edition enabled** — Pro is an extension, not a replacement.
- **Installing without enabling does nothing** — true for both editions.

**When creating a store**
- **The store code is unique, max 20 characters**.
- **The domain is unique, max 200 characters** — two stores cannot share a domain, because the domain is how the system identifies a store.
- **Write the domain without `http://`/`https://` and without a trailing `/`** — the system normalises it to lower case anyway, but entering it as `brand-b.com` avoids confusion.
- **Language, currency, theme and title are required** — without them the store cannot open correctly.
- **The free edition allows 3 stores including the root store** — checked on the server, not just hidden in the UI.

**When changing a live store's domain**
- **The old domain will no longer open that store** — if the site is live and indexed by Google, prepare 301 redirects before changing it.
- **Turning STRICT on with a domain undeclared** means that very domain is refused — declare them all first.

## Troubleshooting
| Symptom | What to do |
| --- | --- |
| No **Multi-store** menu | Check the plugin is **enabled** (not just installed); run `php artisan gp247:cache-rebuild` |
| A *(Pro)* item still opens the explanation page | Check MultiStorePro is **enabled**, then run `php artisan gp247:cache-rebuild` |
| Install reports a conflict | A multi-vendor plugin is installed — the two models are exclusive, uninstall it first |
| Installing Pro reports a missing extension | Install and enable the free **MultiStore** plugin first |
| The new domain shows the registrar's page or a connection error | DNS/web server does not point the domain at this website — see Step 4 |
| The new domain shows an **access denied** page | STRICT is on and that domain is not declared on any store; re-check the Domain field in the store's configuration |
| The new domain shows the root store's content | The domain in the store configuration does not match the domain actually being typed (a `www` difference, a typo); make them match |
| Cannot add another store | You reached the free edition's quota (3 stores including the root) — move to Pro |
| Screens show raw keys instead of text | Open **Store list** once, or disable and re-enable the plugin |
| You want the detailed error | Open `storage/logs/laravel.log` |

## Q&A
**Q1: Do I need extra hosting for a second store?**

→ No. Every store runs on the same system and the same database. You only need another **domain** pointing at this website.

**Q2: What is the root store?**

→ The store that exists from the moment S-Cart is installed, using the domain in `.env`. It is the foundation of the whole system, so it can neither be deleted nor locked.

**Q3: Can I use a subdomain (`b.brand-a.com`) instead of a separate domain?**

→ Yes. The system simply matches the request's domain against the domains you declared, so a subdomain behaves exactly like a separate domain — as long as it points at the same website.

**Q4: Is STRICT mandatory?**

→ No, but turn it on once every domain is declared: it stops other people pointing unknown domains at your website.

**Q5: I have 5 stores on Pro — if I remove Pro, do stores 4 and 5 disappear?**

→ No. Their data remains and they keep running; you simply cannot create new stores until Pro is reinstalled.

**Q6: Does installing Pro require reconfiguring the menu?**

→ No. The *(Pro)* items already point at an intermediate address; with Pro installed, that same address forwards to the real screen.

**Q7: Does deleting a store delete its products?**

→ Products, categories, banners and content pages are **not** deleted — they only lose their link to that store. But a store that still has orders, customers or suppliers **cannot be deleted**; details in [Part 2 — Operations](./multi-store-operations.md).

---

⬅️ [Index](./multi-store.md) · [Part 2 — Operations](./multi-store-operations.md) ➡️

---

<sub>📅 **Last updated:** 2026-09-21 · ✍️ **Author:** GP247</sub>
