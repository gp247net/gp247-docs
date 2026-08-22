> 🌐 **Language:** [🇻🇳 Tiếng Việt](./command-line-reference_vi.md) · 🇬🇧 English (current)

# GP247 System Command-Line (CLI) Reference

## Introduction

This document lists **every Artisan command (`php artisan ...`) provided by the GP247 system**,
across three packages: `gp247/core`, `gp247/front` and `gp247/shop`. Each command includes a
feature description, the meaning of every option, use cases (including how to combine it with other
commands), and the bundled package version. It is aimed at GP247 developers and site
administrators — after reading it you'll know **which command to run, when, and with which options**,
and you can copy-paste and run them right away.

> ℹ️ **How to run:** every command runs in a **Terminal** (Command Prompt on Windows), from the
> **project root** (the folder that contains the `artisan` file). General syntax:
> `php artisan <command-name> [options]`. To quickly list every available GP247 command, run:
> `php artisan list gp247`.

**Versions this document applies to** (read from the project's `composer.lock`):

| Package | Version | Release date |
| --- | --- | --- |
| `gp247/core` | 2.0.4 | 2026-07-08 |
| `gp247/front` | 2.0.1 | 2026-07-07 |
| `gp247/shop` | 2.0.3 | 2026-07-08 |

> Your package versions may differ. Check with `php artisan gp247:core-info` or open
> `composer.lock`. The command list below is stable across the entire 2.0.x branch.

---

## Quick reference

| Command | Package | Short description |
| --- | --- | --- |
| `gp247:core-install` | core | Install GP247 (migrate, seed, publish assets) |
| `gp247:core-update` | core | Update GP247 after `composer update` |
| `gp247:core-info` | core | Show system information & version |
| `gp247:make-plugin` | core | Scaffold a new plugin |
| `gp247:language-update` | core | Overwrite language strings with package defaults (upsert) |
| `gp247:front-install` | front | Install the storefront UI module |
| `gp247:front-uninstall` | front | Uninstall the front module (drop front tables) |
| `gp247:make-template` | front | Scaffold a new storefront template |
| `gp247:template-setup` | front | Set up the default template for the root store |
| `gp247:shop-install` | shop | Install the shop (ecommerce) module |
| `gp247:shop-update` | shop | Non-destructive shop schema/data upgrade (live site) |
| `gp247:shop-uninstall` | shop | Uninstall the shop module (drop shop tables) |
| `gp247:shop-sample` | shop | Create sample data (⚠️ wipes existing shop data) |
| `gp247:shop-clear-cart` | shop | Remove expired cart / wishlist / compare entries |

---

## CORE commands — `gp247/core` (2.0.4)

### 1. `gp247:core-install`

**Feature:** First-time installation of the whole GP247 platform. It runs the database migrations,
seeds the default data (admin, store, languages), publishes core and laravel-filemanager assets,
creates the `storage` symbolic link, and writes the installed marker
(`storage/app/gp247-installed.txt`).

**Options:**

| Option | Value | Meaning |
| --- | --- | --- |
| `--force` | `0` (default) or `1` | `0`: ask for confirmation and **block** if already installed (the `gp247-installed.txt` file exists). `1`: **skip** both the already-installed check and the confirmation prompt, install immediately (for automated installs / scripts / Docker). |

**Usage:**

```bash
php artisan gp247:core-install
```

Unattended install (automation):

```bash
php artisan gp247:core-install --force=1
```

On success the terminal prints the GP247 logo with a `Welcome to GP247` line, the admin path, and
the default account `admin/admin`.

**Use cases & combinations:**
- It is the **first step** after downloading the source, creating the `.env` file, and configuring
  the database connection.
- If `APP_KEY` is missing, the command calls `key:generate` for you.
- To **reinstall**: delete `storage/app/gp247-installed.txt` then run it again (or use `--force=1`).
- It is usually followed by `gp247:front-install` and/or `gp247:shop-install` (see "Full install
  scenario" at the end).

---

### 2. `gp247:core-update`

**Feature:** Update GP247 after you bump package versions with `composer update`. It re-seeds the
default data and the language data in **safe** mode (only inserts missing rows, **never** overwrites
data you have edited), refreshes static files, then prints the current core version.

**Options:** none.

**Usage:**

```bash
composer update
php artisan gp247:core-update
```

**Use cases & combinations:**
- Run it **after every `composer update`** to sync base data and assets with the new source.
- If the update adds language strings and you want to **overwrite** them back to the package's
  latest values, additionally run `gp247:language-update` (see command #5 — safe vs. overwrite).
- Technical note: internally the command calls `gp247:customize static` to refresh customized
  static files. That helper command is provided by the installation's customization layer; if your
  environment does not have it, that step reports its own error and is logged, without affecting the
  data seeding that already ran before it.

---

### 3. `gp247:core-info`

**Feature:** Print GP247 system information: name, author, **core version** and sub-version,
homepage, GitHub, Facebook, and the library API endpoint.

**Options:** none.

**Usage:**

```bash
php artisan gp247:core-info
```

**Use cases:**
- Quickly check **which core version is running** before reporting a bug or before/after an update.
- The fastest way to get the exact sub-version (same as the last line `gp247:core-update` prints).

---

### 4. `gp247:make-plugin`

**Feature:** Scaffold a **new plugin** following the GP247 2.0 standard (ready-made directory
structure, admin Controller, Model, sample Livewire component, Seo/sitemap, vi/en language files,
Provider, Route, `gp247.json`, `AppConfig`, etc.). If the `gp247/front` package is installed, the
generated plugin also includes a FrontController and front route; if not, those front parts are
automatically omitted.

**Options:**

| Option | Value | Meaning |
| --- | --- | --- |
| `--name` | string (**required**) | Plugin name, e.g. `MyBlog`. GP247 normalizes it into the class name and URL key. Leaving it empty → `Command error`. |
| `--download` | `0` (default) or `1` | `0`: copy the plugin directly into `app/GP247/Plugins/<Name>` and `public/GP247/Plugins/<Name>`. `1`: do **not** copy into the app, instead package it as a `.zip` in `storage/tmp` (the JSON result returns the zip path). |

**Usage:**

```bash
php artisan gp247:make-plugin --name=MyBlog --download=0
```

Build a downloadable zip only (do not install into the app):

```bash
php artisan gp247:make-plugin --name=MyBlog --download=1
```

The command returns a JSON string like `{"error":0,"path":"...","msg":"Success"}`.

**Use cases & combinations:**
- When starting a new plugin — use the generated scaffold instead of hand-creating each file.
- Run it **after** `gp247:core-install`. If you want the plugin to have a storefront part, install
  `gp247:front-install` first so the command scaffolds the FrontController + front route as well.
- To upgrade an old (1.x) plugin to 2.0, see `extension/convert-plugin-v1-to-v2.md`.

---

### 5. `gp247:language-update`

**Feature:** Refresh the language rows for core (and front/shop if installed) using **upsert**
mode — i.e. it **overwrites** each string's text/position back to the package default. This is the
key difference from `core-install`/`core-update` (those only **insert missing rows** and never touch
strings you have edited).

**Options:** none. It runs core → front → shop in order; any package that is not installed is
skipped with a `skipped (package not installed)` message.

**Usage:**

```bash
php artisan gp247:language-update
```

**Use cases & combinations:**
- When an update **changes/adds translations** and you want to restore the package's exact standard
  version.
- ⚠️ **Caution:** this command **overwrites** language strings you previously edited in the admin.
  If you have customized many strings, back them up first. If you only want to add missing strings
  while keeping your edits, use `gp247:core-update` instead.
- Typically run **after** `composer update` + `gp247:core-update`.

---

## FRONT commands — `gp247/front` (2.0.1)

> This group is only available when the `gp247/front` package (the storefront UI module) is
> installed.

### 6. `gp247:front-install`

**Feature:** Install the front module. The command **uninstalls the old front first** (calls
`gp247:front-uninstall`), recreates the front tables, seeds the default front configuration,
publishes the default template's assets + views, then sets up the template for the root store (calls
`gp247:template-setup`).

**Options:** none.

**Usage:**

```bash
php artisan gp247:front-install
```

**Use cases & combinations:**
- Run it **after `gp247:core-install`** when you need a website with a user-facing UI (storefront).
- It is also the way to **cleanly reinstall** the front module (it always uninstalls first, then
  installs).
- If the template publish step fails (network/write permission), copy the template folder manually
  and re-run `gp247:template-setup`.

---

### 7. `gp247:front-uninstall`

**Feature:** Uninstall the front module: drop the front tables (runs the `down()` part of the front
migration) and delete the corresponding migration record.

**Options:** none.

**Usage:**

```bash
php artisan gp247:front-uninstall
```

**Use cases & combinations:**
- When you want to **fully remove** the storefront UI from the system.
- You usually don't need to call it manually when reinstalling, because `gp247:front-install` calls
  it at the start.
- ⚠️ Dropping tables means **losing** front configuration data — consider backing up first.

---

### 8. `gp247:make-template`

**Feature:** Scaffold a **new storefront template** following the GP247 2.0 standard (template
directory, `AppConfig`, vi/en language files, Provider, `gp247.json`, etc.).

**Options:**

| Option | Value | Meaning |
| --- | --- | --- |
| `--name` | string (**required**) | Template name, e.g. `MyShopTheme`. Leaving it empty → `Command error`. |
| `--download` | `0` (default) or `1` | `0`: copy the template into `app/GP247/Templates/<Name>` and `public/GP247/Templates/<Name>`. `1`: package it as a `.zip` in `storage/tmp` instead of copying into the app. |

**Usage:**

```bash
php artisan gp247:make-template --name=MyShopTheme --download=0
```

**Use cases & combinations:**
- When you want to build your own storefront theme instead of the default `GP247Front`.
- Run it after `gp247/front` is installed. After creating and selecting your template as the
  default, use `gp247:template-setup` to apply the template configuration to the root store.

---

### 9. `gp247:template-setup`

**Feature:** Set up the default template for the **root store**: load the `AppConfig` class of the
default template (`GP247_TEMPLATE_FRONT_DEFAULT`), and call `install()` and `setupStore()` to write
the display configuration for the root store.

**Options:** none.

**Usage:**

```bash
php artisan gp247:template-setup
```

**Use cases & combinations:**
- It is called automatically at the end of `gp247:front-install`; you only need to run it manually
  when the earlier template publish step failed and you copied the template by hand.
- If the terminal prints `Class template Default not found`, the default template has not been
  copied into `app/GP247/Templates` — publish/copy the template then re-run.

---

## SHOP commands — `gp247/shop` (2.0.3)

> This group is only available when the `gp247/shop` package (the shop module) is installed.
> `gp247/shop` requires both `gp247/core` and `gp247/front`.

### 10. `gp247:shop-install`

**Feature:** Install the shop (ecommerce) module. The command **uninstalls the old shop first**
(calls `gp247:shop-uninstall`), recreates the shop tables, seeds the initialization data + the
default data for the root store, then publishes the shop's front views.

**Options:** none.

**Usage:**

```bash
php artisan gp247:shop-install
```

**Use cases & combinations:**
- Run it **after `gp247:core-install` and `gp247:front-install`** to turn the site into an online
  store.
- Also used to **cleanly reinstall** the shop module (it always uninstalls first).
- After installing, if you want demo data to preview, run `gp247:shop-sample` (see the warning
  below).

---

### 11. `gp247:shop-update`

> ℹ️ **Available since:** gp247/shop 2.1

**Feature:** **Non-destructive** upgrade of the shop module for an already-installed site. Unlike
`gp247:shop-install` (which drops and recreates every shop table → **data loss**), this command only
runs the **incremental, idempotent upgrade migrations** in
`vendor/gp247/shop/src/Admin/Database/Migrations/upgrade` (it deliberately **never** touches the
create-tables migration), so a live site **keeps** its categories, products, orders, etc. After the
migrations finish, the command reminds you to run `gp247:language-update` to refresh new/renamed
language labels (e.g. the city/district and address1/2/3 address labels).

**Options:** none.

**Usage:**

```bash
composer update
php artisan gp247:shop-update
```

**Use cases & combinations:**
- Run it **after `composer update`** when you bump `gp247/shop` on a **live site that has real
  data** — it is the safe replacement for `gp247:shop-install` (which is only for fresh / clean
  reinstalls and wipes data).
- It is **idempotent**: running it repeatedly is harmless; already-applied migrations are skipped.
- If the upgrade **adds/renames language strings**, run `gp247:language-update` next as the command
  suggests (see CORE command #5 — mind the overwrite of customized strings).
- If a migration fails, the terminal prints `Shop upgrade failed: ...` and returns a failure code;
  the error is logged via `gp247_report`. Fix the cause then run it again.

---

### 12. `gp247:shop-uninstall`

**Feature:** Uninstall the shop module: drop the shop tables (runs the shop migration's `down()`)
and delete the corresponding migration record.

**Options:** none.

**Usage:**

```bash
php artisan gp247:shop-uninstall
```

**Use cases & combinations:**
- When you want to **fully remove** the shopping functionality.
- You usually don't need to call it manually when reinstalling, because `gp247:shop-install` calls
  it at the start.
- ⚠️ Dropping the shop tables = **losing all** categories, products, orders, etc. — back up before
  running.

---

### 13. `gp247:shop-sample`

**Feature:** Create **sample data** for the store: multi-level categories, brands, suppliers, single
products, bundle products, group products, and sample promotions.

**Options:** none.

**Usage:**

```bash
php artisan gp247:shop-sample
```

**Use cases & combinations:**
- For **demo / dev environments**, to have ready-made products for previewing the UI and testing
  features.
- ⚠️ **Very important:** this command **truncates the existing shop tables** before creating samples
  (categories, products, brands, suppliers, attributes, etc.). **Never run it on a live site that
  has real data.**
- It is usually run right after `gp247:shop-install` on a dev machine.

---

### 14. `gp247:shop-clear-cart`

**Feature:** Clean up **expired** carts: the shopping cart (`default`), the wishlist (`wishlist`),
and compare (`compare`), based on the number of days configured in
`config('gp247-config.shop.cart_expire.*')`.

**Options:** none.

**Usage:**

```bash
php artisan gp247:shop-clear-cart
```

**Use cases & combinations:**
- This is a **periodic maintenance** command — put it in the scheduler/cron to run daily so the cart
  table doesn't grow indefinitely.
- Example cron running at 2 AM every day:

  ```bash
  0 2 * * * cd /path-to-project && php artisan gp247:shop-clear-cart >> /dev/null 2>&1
  ```

- Adjust the expiry days in the `gp247-config.shop.cart_expire` configuration (`cart`, `wishlist`,
  `compare`).

---

## Frequently paired `vendor:publish` commands

These are standard Laravel commands (not GP247-specific), but they commonly accompany installation
or UI **customization**. They use the `--tag` flag to select the group of files to publish.

| Command | What it publishes |
| --- | --- |
| `php artisan vendor:publish --tag=gp247:core-public --force` | Core admin assets (CSS/JS) to `public/GP247` |
| `php artisan vendor:publish --tag=gp247:front-public --force` | Front module assets |
| `php artisan vendor:publish --tag=gp247:front-view --force` | Default front template views |
| `php artisan vendor:publish --tag=gp247:shop-view-admin` | Shop admin views (to override) → `resources/views/vendor/gp247-shop-admin` |
| `php artisan vendor:publish --tag=gp247:shop-view-front` | Shop front views → `app/GP247/Templates/GP247Front` |

> After **updating packages** with `composer update`, if the admin UI doesn't change accordingly,
> re-run `vendor:publish --tag=gp247:core-public --force` to refresh the published assets.

---

## Full install scenario (recommended order)

Run the following steps in order for a complete GP247 ecommerce site:

1. Prepare `.env` (database connection) and install the source via Composer.
2. Install the core platform:

   ```bash
   php artisan gp247:core-install
   ```

3. Install the storefront UI module:

   ```bash
   php artisan gp247:front-install
   ```

4. Install the shop module:

   ```bash
   php artisan gp247:shop-install
   ```

5. (Dev/demo machines only) Create sample data:

   ```bash
   php artisan gp247:shop-sample
   ```

After going live, for each upgrade:

```bash
composer update
php artisan gp247:core-update
php artisan gp247:shop-update   # if you use the shop module (safe, non-destructive upgrade)
```

And set up a cron to clear expired carts: `php artisan gp247:shop-clear-cart` (run daily).

---

## Q&A

**Q1: How do I quickly see every GP247 command available on my machine?**

→ Run `php artisan list gp247`. The terminal lists every command prefixed with `gp247:` and a short
description.

**Q2: `gp247:core-install` prints "GP247 has been installed" — what now?**

→ It means it's already installed (the `storage/app/gp247-installed.txt` file exists). To reinstall,
delete that file then run again, or use `php artisan gp247:core-install --force=1`.

**Q3: What's the difference between `gp247:core-update` and `gp247:language-update`?**

→ `core-update` seeds **safely** (only adds missing rows, keeps your edits). `language-update`
**overwrites** language strings back to the package defaults. Use `core-update` to keep customized
text; use `language-update` to restore the latest standard version.

**Q4: Is `gp247:shop-sample` safe to run on a live site?**

→ **No.** This command **wipes** the existing shop data before creating samples. Only run it on a
demo/dev environment.

**Q5: `gp247:shop-install` fails with missing tables / missing classes — what's wrong?**

→ `gp247/shop` requires `gp247/core` and `gp247/front` to be installed first. Run in the correct
order: `core-install` → `front-install` → `shop-install`.

**Q6: I don't need shopping, only an informational website — what do I install?**

→ Just `gp247:core-install` then `gp247:front-install`. Skip the shop command group.

**Q7: What does `--download=1` do differently from `--download=0` in `make-plugin`/`make-template`?**

→ `--download=0` copies the plugin/template straight into `app/GP247/...` for immediate use.
`--download=1` does **not** copy into the app, instead it packages a `.zip` in `storage/tmp` for you
to download / carry elsewhere.

**Q8: After `composer update`, the admin UI doesn't change?**

→ Run `php artisan gp247:core-update`. If it still doesn't change, re-publish the assets:
`php artisan vendor:publish --tag=gp247:core-public --force`.

**Q9: What is `gp247:customize static` that `core-update` calls — do I need to install it?**

→ It's an internal command that refreshes customized static files, provided by the installation's
customization layer. You don't call it directly. If your environment lacks it, that step only
reports its own error and is logged, without breaking the data update.

**Q10: How do I know the exact core/front/shop versions running?**

→ Run `php artisan gp247:core-info` (shows the core version), or open `composer.lock` and look for
`gp247/core`, `gp247/front`, `gp247/shop`.

---

## Change history
<!-- Only when logic/behavior changed. Newest row on top. -->

| Date | GP247 version | Change |
| --- | --- | --- |
| 2026-08-22 | gp247/shop 2.1 | Added `gp247:shop-update` — non-destructive shop upgrade for live sites |

---

<sub>📅 **Last updated:** 2026-08-22 · ✍️ **Author:** GP247</sub>
