> 🌐 **Language:** [🇻🇳 Tiếng Việt](./release_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./release_ja.md)

# GP247 releases

## Introduction
This page lists the **S-Cart** releases from the **3.x** line, newest first: what is new, security fixes and what to do when
updating. Each S-Cart release bundles releases of `gp247/core`, `gp247/front` and `gp247/shop`; S-Cart has its own version
numbers (a *minor* release brings new features, a *patch* release only fixes bugs). It is for **site owners and operators**:
the "At a glance" section tells you what is new and whether your site needs an update.

## At a glance
| | S-Cart | Check on your site: the packages |
| --- | --- | --- |
| **Latest** | **3.3.1** · 2026-10-29 | gp247/core 3.5.0 · gp247/front 3.3.0 · gp247/shop 3.3.1 |
| **Minimum safe** | **3.3.0** | gp247/core ≥ **3.5.0** · gp247/front ≥ **3.2.3** · gp247/shop ≥ **3.3.1** |

**✨ Recently added**
- A contact form with an inbox in the admin — read every message customers send, right in the admin *(S-Cart 3.3)*
- Turn the REST API and each API version on or off from the API management screen — no config file to edit *(S-Cart 3.3)*
- Failed admin login attempts are limited — stops password guessing *(S-Cart 3.3)*

> 💡 Check with `php artisan gp247:info` (or `composer show "gp247/*"`). If the packages reach the "Minimum safe" row, your
> site is safe, even if you have not reinstalled S-Cart.

> ⚠️ **Note:** only the **3.x** line still receives security fixes; sites still on 2.x need to move to 3.x.
> `gp247/shop` 3.3.1 requires `gp247/core` 3.3 or later, and `gp247/front` 3.2.3 requires `gp247/core` 3.2.3 or later — update
> all three together.

## Releases

### S-Cart 3.3.1 — 2026-10-29
**Includes:** gp247/core 3.5.0 · gp247/front 3.3.0 · gp247/shop 3.3.1

- 🐞 **Fixes:** the web server follows the stable nginx channel so it receives security fixes; unused PHP features turned
  off to lower the PHP2Shell risk (running with Docker)
- ⚠️ **To do when updating:** (running with Docker) pull the new images and rebuild, following "Security updates" in
  [DOCKER.md](https://github.com/gp247net/s-cart/blob/master/DOCKER.md)

Details: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.1)

### S-Cart 3.3.0 — 2026-10-28
**A contact form with an inbox, and the REST API switched on and off from the admin.**

**Includes:** gp247/core 3.5.0 · gp247/front 3.3.0 · gp247/shop 3.3.1

- ✨ **New:**
  - A contact form with an inbox in the admin — read every message customers send, right in the admin
  - Turn the REST API and each API version on or off from the API management screen — no config file to edit
    ([docs](../api/api-introduction.md))
  - Failed admin login attempts are limited — stops password guessing
  - For developers: REST API v2 for products, orders, banners and pages (with filters and final price); every API response
    carries a request id for log lookups
- ⚠️ **Security:** 5 fixes — see [Security fixes](#security-fixes). Update now.
- 🐞 **Fixes:** the social sharing image (og:image) used the wrong picture for a page; the admin order API answered 500
- ⚠️ **To do when updating:**
  - Run `php artisan gp247:update`: it hashes API keys and gives old tokens an expiry
  - Rename `GP247_API_LOGIN_THROTTLE` to `GP247_LOGIN_THROTTLE` and `GP247_API_LOGIN_IP_THROTTLE` to
    `GP247_LOGIN_IP_THROTTLE` (the old names are no longer read)
  - Apps calling the API: log out with POST (GET is being retired), and move to API v2 over time

Details: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.0)

### S-Cart 3.2 — 2026-10-23
**Japanese, standard shipping with shipments, and a storefront theme that updates with the package.**

**Includes:** gp247/core 3.2.0 · gp247/front 3.2.0 · gp247/shop 3.2.0

- ✨ **New:**
  - Standard shipping: carriers, shipments, wards — track deliveries right in the order
    ([docs](../s-cart/shipping/shipping.md))
  - Japanese and the Japanese yen (JPY) ready on a fresh install — sell to the Japanese market
    ([docs](../system/language-system.md))
  - The GP247Front theme runs straight from the package — `composer update` brings the new look, publish only the files you
    want to change ([docs](../extension/create-template.md))
  - The checkout shows the payment method's instructions — customers know how to pay by bank transfer
    ([docs](../s-cart/payment/payment.md))
  - Wishlist and compare right on the product page
  - For developers: plugins can add storefront blocks and extension points without owning a template; a "Check before use"
    box tells what a plugin still needs configured
- ⚠️ **Security:** 2 fixes — see [Security fixes](#security-fixes).
- 🐞 **Fixes:** wishlist/compare buttons on product cards were not visible; "too many redirects" when removing from the
  wishlist; page links stacked vertically
- ⚠️ **To do when updating:** sites that published the whole theme can run
  `php artisan gp247:template-prune GP247Front --dry-run` to hand unchanged files back to the package

Details: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.2)

### S-Cart 3.1 — 2026-10-03
**Payment requests outside the cart, and stored passwords and payment keys encrypted.**

**Includes:** gp247/core 3.1.0 · gp247/front 3.0.0 · gp247/shop 3.1.0

- ✨ **New:**
  - Payment requests: send online payment links, record money in/out by hand, refund — no order needed
    ([docs](../s-cart/order-processing/payment-request.md))
  - SMTP passwords and payment gateway keys encrypted in the database, with a key rotation command
    ([docs](../system/data-encryption.md))
  - Configure and enable plugins per store (multi-store sites) ([docs](../multi-store/multi-store.md))
  - A log of every action in the admin — see who changed what
  - For developers: `gp247:doctor` reports PHP files with a BOM; error reports say which site they came from
- ⚠️ **Security:** 3 fixes — see [Security fixes](#security-fixes).
- 🐞 **Fixes:** Email/SMTP settings did not always save; customers without an email were reported as duplicates; order-total
  promotion codes were not applied
- ⚠️ **To do when updating:** run `php artisan gp247:update`; (running with Docker) the production stack refuses to start
  while `.env` still holds the sample passwords

Details: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.1)

### S-Cart 3.0.1 — 2026-09-04
**Includes:** gp247/core 3.0.0 · gp247/front 3.0.0 · gp247/shop 3.0.0

- ⚠️ **To do when updating:** the old `sc:*` commands are removed; use the matching `gp247:*` commands
  ([docs](../system/command-line-reference.md))

Details: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.0.1)

### Older releases
| Date | S-Cart | Includes | Summary |
| --- | --- | --- | --- |
| 2026-08-30 | [3.0](https://github.com/gp247net/s-cart/releases/tag/3.0) | core 3.0.0 · front 3.0.0 · shop 3.0.0 | Start of the 3.x line: a payment ledger per order, discounts taken before tax — see [v3.0 release notes](../system/release-notes-3.0.md) |

## Security fixes
One row per S-Cart release, stating only the kind of issue, who is affected and the fixed release — never how to exploit it.
Items marked "(… sites)" only affect sites using that setup.

| Date | S-Cart | Fixes |
| --- | --- | --- |
| 2026-10-28 | **3.3.0** | **core 3.5.0 · shop 3.3.1**<br>• **Brute force, CSRF** — no limit on admin login attempts; the file manager could change files from another site<br>• **Price tampering** — a negative quantity in the cart brought the order total down to 0<br>• **Secret exposure** — API connection keys stored in plain text in the database (sites with the API enabled)<br>**core · front · shop 3.2.3** *(released 2026-10-27)*<br>• **Access control, data leak, brute force** — the REST API ignored role and store permissions and exposed cost price and stock; no login limit, tokens never expired (sites with the API enabled)<br>• **Authentication** — locked customers could still sign in |
| 2026-10-23 | **3.2** | **core 3.1.2** *(released 2026-10-21)*<br>• **Path traversal, SSRF** — installing a plugin/template from an untrusted package<br>**front 3.0.1** *(released 2026-09-02)*<br>• **Data leak** — pages and link groups of other stores shown to a sub-store admin (multi-store sites) |
| 2026-10-03 | **3.1** | **core 3.0.14** *(released 2026-09-29)*<br>• **Secret exposure** — saved passwords and keys on plugin settings screens were sent to the browser<br>**shop 3.0.18** *(released 2026-09-28)*<br>• **Data leak** — the admin home showed revenue and orders of every store to a sub-store admin (multi-store sites)<br>**shop 3.0.2** *(released 2026-09-02)*<br>• **Data leak** — categories and products of other stores shown to a sub-store admin when creating an order (multi-store sites) |

## Server software (PHP, web server)
GP247 runs on PHP and a web server that you or your hosting provider install. Flaws in them are fixed by their own
vendors, not by a GP247 release — keep them up to date as well.

| Date | Software | Issue | Fixed in (official PHP releases) |
| --- | --- | --- | --- |
| 2026-10-21 | PHP | **PHP2Shell** — remote code execution, critical (CVE-2026-109618, CVE-2026-109619) | 8.3.36 · 8.4.27 · 8.5.12 |

**What to do**
1. Check the PHP version you run: `php -v`, or **Admin → Server info**.
2. PHP built from the official releases (php.net, the official Docker images, most control panels that let you pick a version):
   move to the fixed version of your branch, or later.
3. PHP installed by your Linux distribution (Ubuntu, Debian, RHEL…) or managed by your hosting provider: these vendors often patch
   an older version number, so **the number alone does not tell you whether you are safe**. Install the latest operating system
   updates, or ask your provider whether CVE-2026-109618 and CVE-2026-109619 are patched.
4. Cannot update yet: set `session.upload_progress.enabled = Off` in `php.ini` (GP247 does not use this feature) and restart PHP.
   It lowers the risk; it does not replace the update.
5. Running S-Cart with Docker: follow **Security updates** in [DOCKER.md](https://github.com/gp247net/s-cart/blob/master/DOCKER.md).

## How to update
1. Back up the database and the site folder.
2. Update the code:
   ```bash
   composer update gp247/core gp247/front gp247/shop
   ```
3. Apply data, configuration and cache updates:
   ```bash
   php artisan gp247:update
   ```
4. Check the versions again with `php artisan gp247:info`.

Step-by-step details: [Updating GP247](https://gp247.net/en/docs/system/how-to-update-gp247.html).

## Reporting a vulnerability
If you find a vulnerability, please email **support@gp247.net** — **do not** open a public GitHub
issue, so the problem is not exposed before a fix is released.

---

<sub>📅 **Last updated:** 2026-10-30 · ✍️ **Author:** GP247</sub>
