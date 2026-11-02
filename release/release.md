> 🌐 **Language:** [🇻🇳 Tiếng Việt](./release_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./release_ja.md)

# S-Cart releases and security

## Introduction
This page is for S-Cart (3.x line) **site owners and operators**: it tells you what each release brings, which version to run
and how to update. Latest release: **S-Cart 3.4.0** (2026-11-02).

## Recommended versions
Run `php artisan gp247:info` (or `composer show "gp247/*"`) and compare each line with the **Recommended minimum** column:

| Package | Recommended minimum | Latest |
| --- | --- | --- |
| gp247/core | **3.6.0** | 3.8.0 |
| gp247/front | **3.2.3** | 3.5.0 |
| gp247/shop | **3.6.0** | 3.7.0 |

- **Below** the "Recommended minimum" column → [update](#how-to-update) when you can.
- **At or above it** → your site has all the important improvements, even if you have not reinstalled S-Cart.

> ⚠️ **Note:** update **all three packages together**: `gp247/shop` 3.6.0 needs `gp247/core` ≥ 3.7, `gp247/front` 3.2.3
> needs `gp247/core` ≥ 3.2.3. Which lines are still supported: see [Support lifecycle](#support-lifecycle).

## Support lifecycle
| Line | Platform | Status | Security fixes until |
| --- | --- | --- | --- |
| **3.x** | Laravel 13 · PHP 8.3+ | ✅ Supported | March 2028 |
| 2.x and earlier | Laravel 12–13 | ⛔ No longer supported | — |

- **New features** go into the newest line only.
- **When a new line ships:** the previous line gets critical bug fixes for 6 more months and security fixes until the time in
  the table — never past the security support of the Laravel version that line runs on.
- **Sites still on 2.x or earlier:** they keep running but receive no more fixes. Move to 3.x with `composer update` and
  `php artisan gp247:update` — see the [v3.0 notes](../system/release-notes-3.0.md).

## Highlights
- **Sell through your own app** — the store's app keeps the cart, places orders and pays online through the API *(S-Cart 3.4)*
- **Customer accounts in the app** — sign up, password reset, profile, address book *(S-Cart 3.4)*
- **Sync with ERP and warehouse software** — order status, shipments, prices, stock through the admin API *(S-Cart 3.4)*
- **Event log and webhooks** — the ERP knows at once when orders, stock, products or customers change *(S-Cart 3.4)*

## How to update
**Step 1 —** Back up the database and the site folder.

**Step 2 —** Update the code:
```bash
composer update gp247/core gp247/front gp247/shop
```

**Step 3 —** Apply data, configuration and cache updates:
```bash
php artisan gp247:update
```

**Step 4 —** Check the versions again with `php artisan gp247:info`.

A release may need extra steps — see its **📋 To do when updating** below.
Step-by-step details: [Updating GP247](https://gp247.net/en/docs/system/how-to-update-gp247.html).

## Releases
Newest first. An *x.**Y*** release brings new features; an *x.y.**Z*** release only fixes bugs.

### S-Cart 3.4.0 — 2026-11-02
**Sell through your own app and sync with your ERP over the REST API.**

**✨ New**
- **Sell through your own app** — the store's app keeps the cart, places orders and pays online through the API; the site
  owner turns on "Orders via API" ([docs](../api/api-introduction.md))
- **Customer accounts in the app** — sign up, password reset, profile, address book; long sign-ins with a refresh token
- **Sync with ERP and warehouse software** — update order status, tracking numbers, prices and stock through the admin API,
  with the same permissions as the admin screens
- **Event log and webhooks** — the ERP knows at once when orders, stock, products or customers change; off by default
- **Faster apps** — fetch only the fields you need; unchanged data is not downloaded again (`304`)
- **For developers:** an extension point for two-factor authentication (MFA) on API sign-in; `Idempotency-Key` against
  duplicate writes; an API deprecation policy; generate a TypeScript client from `openapi.json`

**🐞 Fixes:** out-of-stock products still showed the buy button; sign-up and password change accepted an empty password; the
API answered 500 without an `Accept` header

**📋 To do when updating**
- Run `php artisan gp247:update` — it creates the API session, event log and webhook tables and the sync indexes
- *(Stock management on without stock entered)* turn off *Stock management* or turn on *Allow selling when out of stock*,
  otherwise the buy button disappears on products with 0 stock
- *(Several apps sharing one API account)* the 1000 requests/minute limit now counts per account — split the accounts or raise
  `GP247_API_THROTTLE`

<sub>Includes core 3.8.0 · front 3.5.0 · shop 3.7.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.4.0)</sub>

### S-Cart 3.3.1 — 2026-10-29
**A contact form with an inbox, and the REST API switched on and off from the admin.**

**✨ New**
- **Contact inbox in the admin** — read every message customers send, right in the admin
- **Turn the REST API on or off**, and each API version, from the API management screen — no config file to edit
  ([docs](../api/api-introduction.md))
- **Failed login limit** — stops admin password guessing
- **For developers:** REST API v2 for products, orders, banners and pages (with filters and final price); every API response
  carries a request id for log lookups

**🐞 Fixes:** the social sharing image (og:image) used the wrong picture for a page; the admin order API answered 500; the web
server follows the stable nginx channel and unused PHP features are turned off *(running with Docker)*

**📋 To do when updating**
- Run `php artisan gp247:update` — it hashes API keys and gives old tokens an expiry
- Rename `GP247_API_LOGIN_THROTTLE` → `GP247_LOGIN_THROTTLE` and `GP247_API_LOGIN_IP_THROTTLE` →
  `GP247_LOGIN_IP_THROTTLE` (the old names are no longer read)
- Apps calling the API: log out with POST (GET is being retired), and move to API v2 over time
- *(Running with Docker)* pull the new images and rebuild, following "Security updates" in
  [DOCKER.md](https://github.com/gp247net/s-cart/blob/master/DOCKER.md)

<sub>Includes core 3.5.0 · front 3.3.0 · shop 3.3.1 · also covers 3.3.0 (2026-10-28) · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.1)</sub>

### S-Cart 3.2 — 2026-10-23
**Japanese, standard shipping with shipments, and a storefront theme that updates with the package.**

**✨ New**
- **Standard shipping** — carriers, shipments, wards; track deliveries right in the order
  ([docs](../s-cart/shipping/shipping.md))
- **Japanese and the Japanese yen (JPY)** ready on a fresh install — sell to the Japanese market
  ([docs](../system/language-system.md))
- **The GP247Front theme runs straight from the package** — `composer update` brings the new look, publish only the files you
  want to change ([docs](../extension/create-template.md))
- **Payment instructions at checkout** — customers know how to pay by bank transfer
  ([docs](../s-cart/payment/payment.md))
- **Wishlist and compare** right on the product page
- **For developers:** plugins can add storefront blocks and extension points without owning a template; a "Check before use"
  box tells what a plugin still needs configured

**🐞 Fixes:** wishlist/compare buttons on product cards were not visible; "too many redirects" when removing from the
wishlist; page links stacked vertically

**📋 To do when updating**
- Sites that published the whole theme can run `php artisan gp247:template-prune GP247Front --dry-run` to hand unchanged
  files back to the package

<sub>Includes core 3.2.0 · front 3.2.0 · shop 3.2.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.2)</sub>

### S-Cart 3.1 — 2026-10-03
**Payment requests outside the cart, and stored passwords and payment keys encrypted.**

**✨ New**
- **Payment requests** — send online payment links, record money in/out by hand, refund, with no order needed
  ([docs](../s-cart/order-processing/payment-request.md))
- **SMTP passwords and payment gateway keys encrypted** in the database, with a key rotation command
  ([docs](../system/data-encryption.md))
- **Plugins per store** — configure and enable them separately *(multi-store sites)*
  ([docs](../multi-store/multi-store.md))
- **Admin action log** — see who changed what
- **For developers:** `gp247:doctor` reports PHP files with a BOM; error reports say which site they came from

**🐞 Fixes:** Email/SMTP settings did not always save; customers without an email were reported as duplicates; order-total
promotion codes were not applied

**📋 To do when updating**
- Run `php artisan gp247:update`
- *(Running with Docker)* the production stack refuses to start while `.env` still holds the sample passwords

<sub>Includes core 3.1.0 · front 3.0.0 · shop 3.1.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.1)</sub>

### Older releases
| Release | Date | Summary |
| --- | --- | --- |
| [S-Cart 3.0.1](https://github.com/gp247net/s-cart/releases/tag/3.0.1) | 2026-09-04 | Old `sc:*` commands removed, use the `gp247:*` commands — [command list](../system/command-line-reference.md) |
| [S-Cart 3.0](https://github.com/gp247net/s-cart/releases/tag/3.0) | 2026-08-30 | Start of the 3.x line: a payment ledger per order, discounts taken before tax — [v3.0 notes](../system/release-notes-3.0.md) |

## Security
GP247 reviews security regularly, and improvements ship in every release. The simplest way to keep your site safe is to run
at least the versions in [Recommended versions](#recommended-versions) and update when a new release comes out.

## Server software (PHP, web server)
Flaws in PHP and the web server are fixed by their own vendors, not by a GP247 release — keep them up to date as well.

| Issue | Severity | Fixed in PHP |
| --- | --- | --- |
| **PHP2Shell** — remote code execution (CVE-2026-109618, CVE-2026-109619) · 2026-10-21 | Critical | 8.3.36 · 8.4.27 · 8.5.12 |

**What to do**
- **Check your PHP version:** `php -v`, or **Admin → Server info**.
- **PHP from the official releases** (php.net, the official Docker images, most control panels that let you pick a version):
  move to the fixed version of your branch, or later.
- **PHP managed by your Linux distribution (Ubuntu, Debian, RHEL…) or hosting provider:** these vendors often patch an older
  version number, so **the number alone does not tell you whether you are safe** — install the latest operating system
  updates, or ask your provider whether CVE-2026-109618 and CVE-2026-109619 are patched.
- **Cannot update yet:** set `session.upload_progress.enabled = Off` in `php.ini` (GP247 does not use this feature) and restart
  PHP. It lowers the risk; it does not replace the update.
- **Running with Docker:** follow "Security updates" in
  [DOCKER.md](https://github.com/gp247net/s-cart/blob/master/DOCKER.md).

## Reporting a vulnerability
If you find a vulnerability, please email **support@gp247.net** — **do not** open a public GitHub issue, so the problem is not
exposed before a fix is released.

---

<sub>📅 **Last updated:** 2026-11-02 · ✍️ **Author:** GP247</sub>
