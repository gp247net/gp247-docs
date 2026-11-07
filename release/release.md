> 🌐 **Language:** [🇻🇳 Tiếng Việt](./release_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./release_ja.md)

# S-Cart releases and security

## Introduction
This page is for S-Cart (3.x line) **site owners and operators**: it tells you what each release brings, which version to run
and how to update. Latest release: **S-Cart 3.5.2** (2026-11-07).

## Recommended versions
Run `php artisan gp247:info` (or `composer show "gp247/*"`) and compare each line with the **Recommended minimum** column:

| Package | Recommended minimum | Latest |
| --- | --- | --- |
| gp247/core | **3.10.3** | 3.10.3 |
| gp247/front | **3.2.3** | 3.5.2 |
| gp247/shop | **3.8.1** | 3.8.1 |
| gp247/mcp | **2.0.1** | 2.0.1 |

- **Below** the "Recommended minimum" column → [update](#how-to-update) when you can.
- **At or above it** → your site has all the important improvements, even if you have not reinstalled S-Cart.
- **Third-party libraries** → also run `composer audit`; see [Third-party libraries](#third-party-libraries).

> ⚠️ **Note:** update **all the packages together**: `gp247/shop` 3.8.1 needs `gp247/core` ≥ 3.10.3, `gp247/mcp` 2.0.1 needs `gp247/core` ≥ 3.9, `gp247/shop` 3.6.0 needs `gp247/core` ≥ 3.7,
> `gp247/front` 3.2.3 needs `gp247/core` ≥ 3.2.3. Which lines are still supported: see [Support lifecycle](#support-lifecycle).

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
- **System alerts** — the admin flags unsafe settings by itself *(S-Cart 3.5.1)*
- **MCP server built into S-Cart** — ask and manage your shop with the AI agent you already use *(S-Cart 3.5)*
- **The agent looks things up for you** — sales, low stock, orders, customers, with the right permissions *(S-Cart 3.5)*
- **Write actions are always confirmed** — preview before changing an order status or stock *(S-Cart 3.5)*

## How to update
**Step 1 —** Back up the database and the site folder.

**Step 2 —** Update the code:
```bash
composer update gp247/core gp247/front gp247/shop gp247/mcp
```
A site installed before S-Cart 3.5.0 has no MCP server yet: run `composer require gp247/mcp -W` once instead of the command above.

**Step 3 —** Apply data, configuration and cache updates:
```bash
php artisan gp247:update
```

**Step 4 —** Check the versions again with `php artisan gp247:info`.

A release may need extra steps — see its **📋 To do when updating** below.
Step-by-step details: [Updating GP247](https://gp247.net/en/docs/system/how-to-update-gp247.html).

## Releases
Newest first. An *x.**Y*** release brings new features; an *x.y.**Z*** release only fixes bugs.

### S-Cart 3.5.2 — 2026-11-07
An improvement release worth installing now, especially for sites on Docker or behind a proxy / CDN.

**✨ New**
- **`TRUSTED_PROXIES` alert** — the System alerts page and `gp247:doctor` warn when the proxy setting trusts every visitor
  ([docs](../security/security-production-config.md))

**🔧 Improvements:** password-reset and e-mail verification links always point at the store's configured domain; the visitor
IP and domain are only taken from a declared proxy; the Docker stack trusts no proxy by default *(Docker)*

**📋 To do when updating**
- Run `composer update`, then `php artisan gp247:update`
- Check that `APP_URL` in `.env` is your real domain (`https://…`) — e-mail links use it
- *(Behind a proxy / CDN)* the proxy must keep the `Host` header; GP247 no longer reads `X-Forwarded-Host` / `X-Forwarded-Prefix`
- *(Docker behind nginx / Caddy / Traefik on the same server)* `composer update` does not change your site files: take the new
  `docker-compose*.yml` and `docker/nginx/default.conf`, set `TRUSTED_PROXIES=172.16.0.0/12` and
  `SC_DOCKER_APP_PORT=127.0.0.1:8000` in `.env`, then run `docker compose up -d`
- *(`TRUSTED_PROXIES` contains `REMOTE_ADDR` or `*`)* replace it with your real proxy's IP ranges, or leave it empty
- *(Sites using the CheckIP plugin)* update CheckIP to 2.1.7 or later

<sub>Includes core 3.10.3 · front 3.5.2 · shop 3.8.1 · mcp 2.0.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.5.2)</sub>

### S-Cart 3.5.1 — 2026-11-05
**The admin now flags unsafe settings; the web installer is simpler.**

**✨ New**
- **System alerts** — the dashboard summarises unsafe settings (debug, HTTPS, default admin password, MFA…) with a detail
  page and fix guides; Administrators only ([docs](../security/security-built-in.md))
- **Web install finishes in one run** — choose the admin username; the account entered in the installer replaces the default one
- **Tidier two-column forms** — "+ Create new" in the form header while editing; the list keeps its page and keyword *(core
  admin screens)*
- **For developers:** `gp247:doctor` runs the same security checks (WARN/PASS, exit code unchanged); other packages can add
  checks

**🛡️ Third-party libraries:** Livewire 4.4.8 and symfony/mime 7.4.22 fix 2 published flaws — [see details](#third-party-libraries).
**Update now.**

**🔧 Improvements:** `gp247:update` keeps a single "System alerts" block; the Remove button now clears leftover extension
folders; searchable selects stay closed while you edit another row

**📋 To do when updating**
- Run `composer update` (pulls Livewire ≥ 4.4.8, symfony/mime ≥ 7.4.22), then `php artisan gp247:update`
- *(Sites whose `SESSION_DRIVER` is not `file`)* every user signs in again once after the update

<sub>Includes core 3.10.2 · front 3.5.2 · shop 3.7.0 · mcp 2.0.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.5.1)</sub>

### S-Cart 3.5.0 — 2026-11-03
**Built-in MCP server: ask and manage your shop with the AI agent you already use.**

**✨ New**
- **MCP server built into S-Cart** — connect Claude, Cursor or VS Code to your shop and ask in plain language; off by default
  ([docs](../mcp/mcp-introduction.md))
- **The agent looks things up for you** — sales, low stock, orders, customers, recent changes; with the account's own permissions
- **Write actions are always confirmed** — order status, shipping status, stock in/out: preview first, then confirm; off by default
- **The extension library shows "Core X.Y+"** — see at once which core an extension needs
- **For developers:** `gp247:mcp-install` / `gp247:mcp-update` / `gp247:mcp-uninstall`; `gp247:info` lists `gp247/mcp`;
  the `minUpdateFrom` manifest key replaces `requireUpdateFrom` (the old key is still read)

**📋 To do when updating**
- *(Site installed before S-Cart 3.5.0)* run `composer require gp247/mcp -W` once, then `php artisan gp247:update`
- *(Using the McpServer plugin)* `gp247:update` moves tokens, settings and permissions; then remove the plugin folder with
  `php artisan gp247:ext-uninstall --type=plugin --key=McpServer --purge`; the MCP screens are now under **System config → MCP Server**

<sub>Includes core 3.9.0 · front 3.5.2 · shop 3.7.0 · mcp 2.0.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.5.0)</sub>

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

**🔧 Improvements:** out-of-stock products still showed the buy button; sign-up and password change accepted an empty password; the
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

**🔧 Improvements:** the social sharing image (og:image) used the wrong picture for a page; the admin order API answered 500; the web
server follows the stable nginx channel and unused PHP features are turned off *(running with Docker)*

**📋 To do when updating**
- Run `php artisan gp247:update` — it hashes API keys and gives old tokens an expiry
- Rename `GP247_API_LOGIN_THROTTLE` → `GP247_LOGIN_THROTTLE` and `GP247_API_LOGIN_IP_THROTTLE` →
  `GP247_LOGIN_IP_THROTTLE` (the old names are no longer read)
- Apps calling the API: log out with POST (GET is being retired), and move to API v2 over time
- *(Running with Docker)* pull the new images and rebuild, following "Security updates" in
  [DOCKER.md](https://github.com/gp247net/s-cart/blob/master/DOCKER.md)

<sub>Includes core 3.5.0 · front 3.3.0 · shop 3.3.1 · also covers 3.3.0 (2026-10-28) · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.1)</sub>

### Older releases
| Release | Date | Summary |
| --- | --- | --- |
| [S-Cart 3.2](https://github.com/gp247net/s-cart/releases/tag/3.2) | 2026-10-23 | Japanese, standard shipping with shipments, storefront theme that updates with the package |
| [S-Cart 3.1](https://github.com/gp247net/s-cart/releases/tag/3.1) | 2026-10-03 | Payment requests outside the cart; stored passwords and payment keys encrypted |
| [S-Cart 3.0.1](https://github.com/gp247net/s-cart/releases/tag/3.0.1) | 2026-09-04 | Old `sc:*` commands removed, use the `gp247:*` commands — [command list](../system/command-line-reference.md) |
| [S-Cart 3.0](https://github.com/gp247net/s-cart/releases/tag/3.0) | 2026-08-30 | Start of the 3.x line: a payment ledger per order, discounts taken before tax — [v3.0 notes](../system/release-notes-3.0.md) |

## Security

Keeping a site secure is more than updating — configuration, encryption keys, backups, accounts and incident response are covered in [GP247 system security](../security/security-introduction.md).

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

## Third-party libraries
Flaws in libraries GP247 uses (Livewire, Symfony…) are fixed by their own maintainers; GP247 raises its minimum when needed.
GP247's own code does not use the affected features of the two flaws below, but third-party plugins may — update anyway.

| Issue | Severity | Fixed in |
| --- | --- | --- |
| **Livewire** — authorization bypass (GHSA-mhf7-xp6r-6qrc) | Medium | livewire/livewire 4.4.8 (required by core 3.10.2) |
| **symfony/mime** — email header injection (CVE-2026-45070) | Medium | symfony/mime 7.4.22 |

**What to do**
- **Update:** `composer update`, then check with `composer show livewire/livewire symfony/mime`.
- **Check every library at once:** `composer audit` lists the libraries on your site that have a published flaw.

## Reporting a vulnerability
If you find a vulnerability, please email **support@gp247.net** — **do not** open a public GitHub issue, so the problem is not
exposed before a fix is released.

---

<sub>📅 **Last updated:** 2026-11-07 · ✍️ **Author:** GP247</sub>
