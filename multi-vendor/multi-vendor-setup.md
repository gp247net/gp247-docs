> 🌐 **Language:** [🇻🇳 Tiếng Việt](./multi-vendor-setup_vi.md) · 🇬🇧 English (current)

# Multi-Vendor — Part 1: Setup

## Introduction
This document walks you through installing S-Cart 3.x, installing and enabling the **Multi-Vendor** plugin (free edition), adding the **Pro edition** if you bought it, creating your first shop and checking that the marketplace works. It is written for site owners or administrators with basic technical skills; follow the steps and it will run — no programming required. How the marketplace operates and what each setting means is covered in [Part 2 — Operations](./multi-vendor-operations.md).

## Before you start
- PHP **8.3** or newer, Composer, MySQL/MariaDB (SQLite only for a quick trial).
- S-Cart **3.x** with `gp247/core` **3.0** and `gp247/shop` installed (the plugin builds on shop products, carts and orders).
- Multi-Store **not installed** on the same website — the two models are mutually exclusive.
- Permission to run `php artisan` on the server, **or** use the admin-interface install method in Step 2.

## Step 1: Install S-Cart 3.x
Skip this step if your S-Cart 3.x site is already running.

1. Open a **Terminal** ("Command Prompt" on Windows) in the folder where the website should live, type these two lines exactly and press Enter:

   ```bash
   composer create-project gp247/s-cart website-folder
   cd website-folder
   ```

   If it succeeds, a `website-folder` directory appears with a ready-made `.env` file.

2. Open `.env` in a text editor and fix the database connection for your machine:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=s-cart
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. Generate the application key:

   ```bash
   php artisan key:generate
   ```

4. Initialise S-Cart (tables, default data, admin account):

   ```bash
   php artisan gp247:install
   ```

   Answer the on-screen questions. If it succeeds, the command prints the admin URL and your login — save them.

5. (Optional) Load sample store data so you have products to test with:

   ```bash
   php artisan gp247:shop-sample
   ```

See also: [S-Cart overview](https://gp247.net/en/s-cart/s-cart-overview.html).

## Step 2: Install the Multi-Vendor plugin (free edition)
Pick **one** of three methods.

**Method 1 — From the GP247 library (recommended)**
1. Log into the admin → **Extensions** → **Plugins**.
2. Open the **Library** tab, find **MultiVendor**, click **Install**.

**Method 2 — Import a zip file**
1. Go to **Extensions** → **Plugins** → **Import file** tab.
2. Choose the plugin zip file and upload it.

**Method 3 — Copy manually (when the first two fail)**
1. Unzip and copy the source folder to `app/GP247/Plugins/MultiVendor/`.
2. Copy the plugin's `public` folder to `public/GP247/Plugins/MultiVendor/`.
3. Go to **Extensions** → **Plugins** → **Stored locally** tab, find **MultiVendor**, click **Install**.

If it succeeds, the plugin appears in the list with an **Enable** button. Details: [Extension installation guide](https://gp247.net/en/user-guide-extension/guide-to-installing-the-extension.html).

## Step 3: Enable it and configure the marketplace
1. In the plugin list, click **Enable** on the MultiVendor row. A **Marketplace** menu appears in the admin sidebar.
2. Go to **Marketplace** → **Quick config** and set the basics:
   - **Commission rate (%)** the marketplace keeps.
   - **Allow vendor registration** — turn on if sellers should sign up themselves.
   - **Auto-approve vendors** / **Auto-approve products** — turn off if you want to moderate manually.
3. Click **Save**. Every setting is explained in [Part 2 — Operations](./multi-vendor-operations.md).

On the free edition, Pro-only switches appear **locked** with a link to an explanation page — that is the correct state, not a bug.

## Step 4: Create your first shop

**Option A — The admin creates it**
1. **Marketplace** → **Vendor stores** → **Add new**: enter the shop code (letters and digits only, max 20 characters, e.g. `vendor01`), name, description → **Save**.
2. **Marketplace** → **Vendor users** → **Add new**: enter email, password, pick the shop you just created, status **Active** → **Save**.
3. Open `https://your-domain/vendor_admin` and sign in with that email/password. If it succeeds, you land on the shop dashboard.

**Option B — The seller registers** (once "Allow vendor registration" is on)
1. The seller opens `https://your-domain/vendor_admin/register`, fills in their details and a shop code.
2. If "Auto-approve vendors" is off, the shop stays pending; the admin opens **Vendor stores** and switches it on so the seller can sign in.

**Sample data for a quick trial** (test sites only):

```bash
php artisan gp247:vendor-sample
```

The command creates three sample shops, `vendor01`–`vendor03`. Each comes with a login account, a supplier, 3 own categories and 9 products spread 3 per category, so both the shop page and the category filter have something to show:

| Shop page | Seller account | Password |
| --- | --- | --- |
| `/shop/vendor01` | `vendor01@gp247.local` | `123456` |
| `/shop/vendor02` | `vendor02@gp247.local` | `123456` |
| `/shop/vendor03` | `vendor03@gp247.local` | `123456` |

Three shops is exactly the free edition's limit, so the sample data works on both editions. Running the command again replaces those sample shops rather than adding more, and never touches shops you created yourself. **Change or delete these accounts before the site goes live.**

## Step 5: Check that the marketplace works
1. Open `https://your-domain/shop/vendor01` — the shop page shows that seller's categories and products (empty if nothing is listed yet).
2. Sign in at `/vendor_admin` and create a product; if auto-approval is off, approve it in the S-Cart admin.
3. **The most important test**: buy products from two different shops in one cart and check out — the result must be **two orders**, one per shop.
4. Check system health:

   ```bash
   php artisan gp247:info
   php artisan gp247:doctor
   ```

## Step 6 (optional): Add the Pro edition
Only do this if you bought the Pro edition. Pro installs **on top of** the free edition; it does not replace it.

1. Check that the **MultiVendor** row in the plugin list is installed and **enabled**. The Pro plugin declares the free edition as a required extension and refuses to install without it.
2. Install **MultiVendorPro** using any of the three methods from Step 2 (usually **Import file** with the zip GP247 delivered to you).
3. Click **Enable**. **Installing without enabling means Pro is not active yet** — this is the most common cause of "I installed it but still see the explanation page".
4. Run this command (or clear the cache in the admin) so Pro's routes are registered:

   ```bash
   php artisan gp247:cache-rebuild
   ```

5. Reload the admin and check three signs that it unlocked:
   - **Marketplace** menu → click any item labelled *(Pro)*: it opens the **real screen**, not the explanation page.
   - **Quick config**: the previously locked switches (quick order, seller order scope, identity verification, disputes, the extra emails) are now **editable**.
   - Sign into a seller account at `/vendor_admin`: the sidebar now has the Pro items (Identity verification, My plan, Create order, Price groups, Reviews, Shop plugins).

Nothing is reinstalled and no data is migrated: shops, seller accounts, orders and the payout ledger stay as they are. Per-shop commission rates you set while on the free edition take effect immediately.

**Turn on what you need** — Pro enables nothing that could disturb your sellers:

| Setting | Turn on when | Default |
| --- | --- | --- |
| Quick order | You sell wholesale and customers order many lines at once | Off |
| Seller order scope | You want sellers to confirm or complete orders themselves | Confirm + ship |
| Require identity verification (KYC) | The marketplace must know who a seller is before they sell or get paid | Off |
| Dispute window / seller response days | You are opening a dispute desk for customers | 14 days / 3 days |
| Extra emails | You want the system to notify sellers and the marketplace automatically | On |
| Seller-configurable plugins | You want sellers to set their own shipping fees or promotions | No plugin opened |

**If you disable or uninstall Pro**: Pro screens revert to explanation pages and Pro switches return to the locked state, but **data created by Pro is never deleted** — verification files, disputes, plans and subscriptions, customer price groups and payout batch logs all remain; reinstall and they are all there. The free edition keeps running in both cases: the marketplace does not stop and no orders are lost.

## Changing URLs (optional)
Add these to `.env` to change the default paths, then run `php artisan gp247:cache-rebuild`:

```env
MULTIVENDOR_FRONT_PATH=shop
MULTIVENDOR_ADMIN_PATH=vendor_admin
PREFIX_QUICK_ORDER_VENDOR=quick-order
PREFIX_CATEGORY_VENDOR=category-vendor
```

| Variable | Which URL it changes | Default |
| --- | --- | --- |
| `MULTIVENDOR_FRONT_PATH` | Shop directory and customer-facing shop pages: `/shop`, `/shop/{code}` | `shop` |
| `MULTIVENDOR_ADMIN_PATH` | The seller admin area: `/vendor_admin`, including its login and registration pages | `vendor_admin` |
| `PREFIX_QUICK_ORDER_VENDOR` | Last segment of the quick-order page: `/shop/{code}/quick-order` | `quick-order` |
| `PREFIX_CATEGORY_VENDOR` | A shop's own category pages | `category-vendor` |

Four things to note before changing them:

- **Do not clash** with an existing path: the admin prefix (`gp247_admin` by default), the other variables in this table, or a storefront page/product slug. A clash makes one of the two pages unreachable.
- **Never use a name that is a real folder in `public/`** — currently `vendor`, `storage` and `GP247`. Real folders are served by the web server, so the request never reaches S-Cart and the page returns **403 Forbidden** (or blank) no matter how it is configured. This is exactly why the default shop path is `shop` and **not** `vendor`. The symptom is easy to misread: individual shop pages `/{path}/{code}` still work and only the directory `/{path}` dies — and with the SEO language prefix on, the error is hidden entirely.
- **Old URLs will return 404.** If the site is live and indexed by Google, set up 301 redirects from the old paths before changing them.
- Write them **without a leading or trailing `/`**. On hosting without command-line access, delete `bootstrap/cache/config.php` (if present) instead of running `gp247:cache-rebuild`.

To change on-screen text, email content, shop page design or staff permissions, see [Part 3 — Customization](./multi-vendor-customize.md).

## Conditions & Rules (know before you act)

**At install time**
- **The plugin refuses to install if Multi-Store is present** — the two plugins share the store mechanism under different models; the installer stops immediately without writing anything. Uninstall Multi-Store first.
- **`gp247/shop` must be installed** — the plugin builds on shop products, carts and orders.
- **Pro requires the free edition enabled** — Pro is an extension, not a replacement; uninstall the free edition and Pro loses its foundation.
- **Installing without enabling does nothing** — true for both editions.

**When creating shops and seller accounts**
- **Shop codes are unique, max 20 characters** — the code becomes the `/shop/{code}` URL, so duplicates would be ambiguous.
- **Each seller account belongs to exactly one shop** — permissions and data (orders, products) follow that shop.
- **A locked account or a closed shop cannot access the seller area** — the system redirects to an "account not active" page; an admin must re-open it.
- **The free edition is limited to 3 shops** — add the Pro edition for more.

**When uninstalling**
- **Uninstalling the free edition deletes seller accounts, shop categories and the payout ledger** (S-Cart products and orders are untouched) — export anything you need first.
- **Uninstalling Pro loses no data** — data created by Pro is never deleted; reinstall and it is all there.

## Troubleshooting
| Symptom | What to do |
| --- | --- |
| No **Marketplace** menu | Check the plugin is **enabled** (not just installed); run `php artisan gp247:cache-rebuild` |
| A *(Pro)* item still opens the explanation page | Check MultiVendorPro is **enabled**, then run `php artisan gp247:cache-rebuild` |
| Install reports a missing extension | Install and enable the free **MultiVendor** plugin first |
| Install reports a conflict | Multi-Store is installed — the two models are exclusive, uninstall it first |
| `/vendor_admin` says "account not active" | The admin must open both the seller account status **and** the shop status |
| Screens show raw keys instead of text | Open **Quick config** once (the plugin reseeds its language strings), or disable and re-enable the plugin |
| Sellers do not see Pro items in their area | Check the account and shop are open, and that the feature is enabled in Quick config |
| Write permission errors | Grant write access to `storage/` and `bootstrap/cache/` |
| Database connection errors | Re-check the `DB_*` lines in `.env` |
| You want the detailed error | Open `storage/logs/laravel.log` |

## Q&A
**Q1: My site runs S-Cart 2.x or still uses the old `sc:*` commands — can I install this?**

→ The plugin requires `gp247/core` 3.0. Upgrade S-Cart to 3.x first; the `gp247:*` commands replace the old `sc:*` ones.

**Q2: What if I cannot run commands on my hosting?**

→ Use Method 1 or Method 2 in Step 2 (install through the admin interface). The plugin needs neither cron nor a queue to run.

**Q3: Where do sellers sign in, and where does the marketplace owner sign in?**

→ Sellers: `/vendor_admin`. The owner: the normal S-Cart admin. The two areas are separate and accounts are not interchangeable.

**Q4: Can I change `/shop` to another path?**

→ Yes, set `MULTIVENDOR_FRONT_PATH` in `.env` and run `php artisan gp247:cache-rebuild`. Pick a name that is **not** a folder in `public/` (avoid `vendor`, `storage`, `GP247`) — see the four notes above.

**Q5: Must I uninstall the free edition before installing Pro?**

→ No. Pro installs **on top of** the free edition and requires it to stay enabled.

**Q6: Is data created on the free edition kept when I move to Pro?**

→ All of it: shops, seller accounts, products, orders, the payout ledger, and per-shop commission rates.

**Q7: Can the sample data be removed?**

→ Delete the `vendor01`–`vendor03` shops and their accounts by hand in **Marketplace**. Never run the sample command on a live site.

---

⬅️ [Index](./multi-vendor.md) · [Part 2 — Operations](./multi-vendor-operations.md) ➡️

---

<sub>📅 **Last updated:** 2026-09-21 · ✍️ **Author:** GP247</sub>
