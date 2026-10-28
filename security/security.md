> 🌐 **Language:** [🇻🇳 Tiếng Việt](./security_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./security_ja.md)

# GP247 security releases

## Introduction
This page lists the releases of `gp247/core`, `gp247/front` and `gp247/shop` that **fix security
issues** from the **3.x** line, newest first. It is for **site owners and operators**: compare the versions you run with
the "At a glance" table to know whether your site is safe or needs an update.

## At a glance — minimum safe versions
| Repo | Minimum safe version | Latest security fix |
| --- | --- | --- |
| gp247/core | **3.4.0** | 2026-10-28 |
| gp247/front | **3.0.1** | 2026-09-02 |
| gp247/shop | **3.2.3** | 2026-10-27 |

> ⚠️ **Note:** only the **3.x** line still receives security fixes. Sites still on 2.x need to move to 3.x.
> `gp247/shop` 3.2.3 requires `gp247/core` 3.2.3 or later — update both together.

> 💡 **Check the versions you run:** `php artisan gp247:info`, or `composer show "gp247/*"`.

## Timeline
| Date | Release | Repo | Security issue |
| --- | --- | --- | --- |
| 2026-10-28 | 3.4.0 | gp247/core | **Secret exposure** — sites upgraded from before 3.2.3 (sites with the API enabled): API tokens issued before 3.2.3 still never expired and old API keys were still stored in plain text in the database; `gp247:update` now gives old tokens an expiry and removes the plain-text keys |
| 2026-10-27 | 3.2.3<br>3.2.3 | gp247/core<br>gp247/shop | **Authorization / Data exposure** — REST API (sites with the API enabled): admin API did not apply admin permissions or store scope; API login had no attempt limit; tokens never expired; API keys stored in plain text; public API exposed internal fields (cost, supplier, stock) and unpublished products. Also: locked customers could still sign in |
| 2026-10-21 | 3.1.2 | gp247/core | **Path traversal / SSRF** — extension install and update trusted untrusted packages and download URLs; the license could be sent to another host through a redirect |
| 2026-09-29 | 3.0.14 | gp247/core | **Secret exposure** — settings screens sent decrypted secrets (SMTP password, secret keys) to the browser |
| 2026-09-04 | 3.0.3 | gp247/core | **Secret exposure** — configuration secrets (SMTP password, client secrets, license) stored in plain text in the database; now encrypted |
| 2026-09-02 | 3.0.1<br>3.0.2 | gp247/front<br>gp247/shop | **Data exposure** — some admin screens (link groups, categories, the order-create product picker) showed other stores' data (multi-store sites) |

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

Step-by-step details: [Updating GP247](../system/update-gp247.md).

## Reporting a vulnerability
If you find a vulnerability, please email **gp247.net@gmail.com** — **do not** open a public GitHub
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

<sub>📅 **Last updated:** 2026-10-28 · ✍️ **Author:** GP247</sub>
