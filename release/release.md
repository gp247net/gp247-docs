> 🌐 **Language:** [🇻🇳 Tiếng Việt](./release_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./release_ja.md)

# S-Cart releases and security

## Introduction
This page is for S-Cart (3.x line) **site owners and operators**: it tells you whether your site is safe, what each release
brings and how to update. Latest release: **S-Cart 3.3.1** (2026-10-29).

## Is your site safe?
Run `php artisan gp247:info` (or `composer show "gp247/*"`) and compare each line with the **Minimum safe** column:

| Package | Minimum safe | Latest |
| --- | --- | --- |
| gp247/core | **3.5.0** | 3.5.0 |
| gp247/front | **3.2.3** | 3.3.0 |
| gp247/shop | **3.3.1** | 3.3.1 |

- **Below** the "Minimum safe" column → [update now](#how-to-update).
- **At or above it** → your site is safe, even if you have not reinstalled S-Cart.

> ⚠️ **Note:** only the **3.x** line still receives security fixes — sites still on 2.x need to move to 3.x. Update **all
> three packages together**: `gp247/shop` 3.3.1 needs `gp247/core` ≥ 3.3, `gp247/front` 3.2.3 needs `gp247/core` ≥ 3.2.3.

## Highlights
- **Contact inbox in the admin** — read every message customers send, right in the admin *(S-Cart 3.3)*
- **Turn the REST API on or off from the API screen** — no config file to edit *(S-Cart 3.3)*
- **Failed login limit** — stops admin password guessing *(S-Cart 3.3)*
- **Standard shipping with shipments** — track deliveries right in the order *(S-Cart 3.2)*
- **Japanese and the Japanese yen** — ready on a fresh install *(S-Cart 3.2)*

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

### S-Cart 3.3.1 — 2026-10-29
A bug-fix release for sites running with Docker.

**🐞 Fixes:** the web server follows the stable nginx channel so it receives security fixes; unused PHP features turned off
to lower the PHP2Shell risk *(running with Docker)*

**📋 To do when updating**
- *(Running with Docker)* pull the new images and rebuild, following "Security updates" in
  [DOCKER.md](https://github.com/gp247net/s-cart/blob/master/DOCKER.md)

<sub>Includes core 3.5.0 · front 3.3.0 · shop 3.3.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.1)</sub>

### S-Cart 3.3.0 — 2026-10-28
**A contact form with an inbox, and the REST API switched on and off from the admin.**

**✨ New**
- **Contact inbox in the admin** — read every message customers send, right in the admin
- **Turn the REST API on or off**, and each API version, from the API management screen — no config file to edit
  ([docs](../api/api-introduction.md))
- **Failed login limit** — stops admin password guessing
- **For developers:** REST API v2 for products, orders, banners and pages (with filters and final price); every API response
  carries a request id for log lookups

**🛡️ Security:** 5 fixes — [see details](#security-fixes). **Update now.**

**🐞 Fixes:** the social sharing image (og:image) used the wrong picture for a page; the admin order API answered 500

**📋 To do when updating**
- Run `php artisan gp247:update` — it hashes API keys and gives old tokens an expiry
- Rename `GP247_API_LOGIN_THROTTLE` → `GP247_LOGIN_THROTTLE` and `GP247_API_LOGIN_IP_THROTTLE` →
  `GP247_LOGIN_IP_THROTTLE` (the old names are no longer read)
- Apps calling the API: log out with POST (GET is being retired), and move to API v2 over time

<sub>Includes core 3.5.0 · front 3.3.0 · shop 3.3.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.0)</sub>

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

**🛡️ Security:** 2 fixes — [see details](#security-fixes).

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

**🛡️ Security:** 3 fixes — [see details](#security-fixes).

**🐞 Fixes:** Email/SMTP settings did not always save; customers without an email were reported as duplicates; order-total
promotion codes were not applied

**📋 To do when updating**
- Run `php artisan gp247:update`
- *(Running with Docker)* the production stack refuses to start while `.env` still holds the sample passwords

<sub>Includes core 3.1.0 · front 3.0.0 · shop 3.1.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.1)</sub>

### S-Cart 3.0.1 — 2026-09-04
**📋 To do when updating**
- The old `sc:*` commands are removed; use the matching `gp247:*` commands ([docs](../system/command-line-reference.md))

<sub>Includes core 3.0.0 · front 3.0.0 · shop 3.0.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.0.1)</sub>

### Older releases
| Release | Date | Summary |
| --- | --- | --- |
| [S-Cart 3.0](https://github.com/gp247net/s-cart/releases/tag/3.0) | 2026-08-30 | Start of the 3.x line: a payment ledger per order, discounts taken before tax — [v3.0 notes](../system/release-notes-3.0.md) |

## Security fixes
Only the kind of issue, who is affected and the fixed release — never how to exploit it. Rows marked *(… sites)* only affect
sites using that setup. A date in brackets is the package release date — sites that ran `composer update` were safe from
that day.

### S-Cart 3.3.0 — 5 fixes
| Issue | Who is affected | Fixed in |
| --- | --- | --- |
| **Brute force, CSRF** | No limit on admin login attempts; the file manager could change files from another site | core 3.5.0 · shop 3.3.1 |
| **Price tampering** | A negative quantity in the cart brought the order total down to 0 | core 3.5.0 · shop 3.3.1 |
| **Secret exposure** | API connection keys stored in plain text in the database *(sites with the API enabled)* | core 3.5.0 · shop 3.3.1 |
| **Access control, data leak, brute force** | The REST API ignored role and store permissions and exposed cost price and stock; no login limit, tokens never expired *(sites with the API enabled)* | core · front · shop 3.2.3 *(2026-10-27)* |
| **Authentication** | Locked customers could still sign in | core · front · shop 3.2.3 *(2026-10-27)* |

### S-Cart 3.2 — 2 fixes
| Issue | Who is affected | Fixed in |
| --- | --- | --- |
| **Path traversal, SSRF** | Installing a plugin/template from an untrusted package | core 3.1.2 *(2026-10-21)* |
| **Data leak** | Pages and link groups of other stores shown to a sub-store admin *(multi-store sites)* | front 3.0.1 *(2026-09-02)* |

### S-Cart 3.1 — 3 fixes
| Issue | Who is affected | Fixed in |
| --- | --- | --- |
| **Secret exposure** | Saved passwords and keys on plugin settings screens were sent to the browser | core 3.0.14 *(2026-09-29)* |
| **Data leak** | The admin home showed revenue and orders of every store to a sub-store admin *(multi-store sites)* | shop 3.0.18 *(2026-09-28)* |
| **Data leak** | Categories and products of other stores shown to a sub-store admin when creating an order *(multi-store sites)* | shop 3.0.2 *(2026-09-02)* |

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

<sub>📅 **Last updated:** 2026-10-31 · ✍️ **Author:** GP247</sub>
