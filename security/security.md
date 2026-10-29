> 🌐 **Language:** [🇻🇳 Tiếng Việt](./security_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./security_ja.md)

# GP247 security releases

## Introduction
This page lists the releases of `gp247/core`, `gp247/front` and `gp247/shop` that **fix security
issues** from the **3.x** line, newest first. It is for **site owners and operators**: compare the versions you run with
the "At a glance" table to know whether your site is safe or needs an update.

## At a glance — minimum safe versions
| Repo | Minimum safe version | Latest security fix |
| --- | --- | --- |
| gp247/core | **3.5.0** | 2026-10-28 |
| gp247/front | **3.0.1** | 2026-09-02 |
| gp247/shop | **3.3.1** | 2026-10-28 |

> ⚠️ **Note:** only the **3.x** line still receives security fixes. Sites still on 2.x need to move to 3.x.
> `gp247/shop` 3.3.1 requires `gp247/core` 3.3 or later — update both together.

> 💡 **Check the versions you run:** `php artisan gp247:info`, or `composer show "gp247/*"`.

## Timeline
*Updating.*

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

## Q&A
**Q1: Is my site affected?**

→ Compare the versions you run with the "At a glance" table. Anything below the minimum in any repo needs an update.

**Q2: I do not use multi-store / the API. Do I still need to update?**

→ Rows marked "(multi-store sites)" or "(sites with the API enabled)" do not affect you, but the other rows do. Moving to the minimum versions is always recommended.

**Q3: What if I am still on 2.x?**

→ The 2.x line no longer receives security fixes and is not listed on this page. Move to 3.x (the minimum in the "At a glance" table) following the update guide.

**Q4: Why does this page not explain how the issues can be exploited?**

→ To avoid guiding attackers to sites that have not updated yet. The page only states the kind of issue, who is affected and the fixed release.

---

<sub>📅 **Last updated:** 2026-10-29 · ✍️ **Author:** GP247</sub>
