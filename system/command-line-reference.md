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
| `gp247:ext-list` | core | List local plugins/templates + status + available updates |
| `gp247:ext-install` | core | Install a plugin/template from a .zip, a directory, or the marketplace |
| `gp247:ext-enable` / `gp247:ext-disable` | core | Enable / disable an installed plugin/template |
| `gp247:ext-uninstall` | core | Uninstall a plugin/template (honors protected + template guards) |
| `gp247:ext-update` | core | Update plugin(s)/template(s) from the marketplace (backup/rollback) |
| `gp247:ext-check-update` | core | Check the marketplace for available updates |
| `gp247:ext-search` | core | Search the marketplace catalog |
| `gp247:ext-license` | core | Set/show/remove the per-plugin license of a paid extension |
| `gp247:install` | core | Install end-to-end (core [+front] [+shop] [+sample]) |
| `gp247:update` | core | Post-`composer update` refresh (core [+shop], safe for live) |
| `gp247:cache-rebuild` | core | Rebuild route/config caches |
| `gp247:doctor` | core | Check the environment (PHP/ext/permissions/DB) |
| `gp247:info` | core | Show status: versions, install marker, extension counts |

---

## Output contract (`--json` & exit codes)

Since core 2.1 every `gp247:*` command shares one output contract, so scripts, CI/CD,
Docker and cron can rely on it:

- **`--json`**: pass it to any command to get a single machine-readable envelope on
  **stdout**; all human/progress/warning lines go to **stderr**, so
  `php artisan gp247:info --json | jq` stays clean. Without `--json` you get the usual
  human-readable text/tables.
- **Envelope shape**:

  ```json
  { "ok": true, "command": "gp247:ext-install", "data": { }, "warnings": [], "error": null }
  ```

  On failure: `"ok": false` and `"error": { "code": "...", "message": "..." }`.
- **Exit codes**: `0` on success, non-zero on failure — for **every** command.

> ⚠️ **Breaking (core 2.1):** `gp247:make-plugin` / `gp247:make-template` now emit the
> envelope above instead of the old `{"error":0,"path":"...","msg":"Success"}`. The zip
> path is at `data.path`. External tooling that parsed the old keys should read the new
> envelope (pass `--json` for the machine format).

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
| `--download` | `0` (default) or `1` | `0`: copy the plugin directly into `app/GP247/Plugins/<Name>` and `public/GP247/Plugins/<Name>`. `1`: do **not** copy into the app, instead package it as a `.zip` in `storage/tmp` (with `--json` the zip path is in `data.path`). |

**Usage:**

```bash
php artisan gp247:make-plugin --name=MyBlog --download=0
```

Build a downloadable zip only (do not install into the app):

```bash
php artisan gp247:make-plugin --name=MyBlog --download=1
```

By default the command prints human-readable text (`Success: <path>` when a zip was built).
Add `--json` for the standardized envelope — e.g.
`{"ok":true,"command":"gp247:make-plugin","data":{"key":"MyBlog","path":"...","msg":"Success"},"warnings":[],"error":null}`
(the zip path is at `data.path`). See "Output contract" above.

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

## Extension lifecycle commands — `gp247:ext-*` (core 2.1)

These bring the full plugin/template lifecycle (previously admin-UI only) to the CLI.
Plugins and templates share one command family; pick which with `--type=plugin|template`
(default `plugin`). All support `--json`.

| Command | Key options | What it does |
| --- | --- | --- |
| `gp247:ext-list` | `--type` | List local extensions with installed/active/version and whether an update is available (cache-only, no API call). |
| `gp247:ext-install` | `--type`, `--file=<zip>`, `--dir=<folder>`, `--key=<key>`, `--paid`, `--license=` | Install from an offline `.zip` (`--file`), an already-extracted folder (`--dir`), or by key (`--key`). For a key: if the extension is **already installed** → refused; if its files are **already on disk but not installed** (e.g. a bundled plugin like `News`) → installed locally (like the admin "Install" button); otherwise → fetched from the marketplace (add `--paid --license=...` for a paid item). |
| `gp247:ext-enable` | `--type`, `--key` | Enable an **installed** extension (refused with a clear error if it is not installed). |
| `gp247:ext-disable` | `--type`, `--key` | Disable an installed extension (refused if not installed, or for a template still in use). |
| `gp247:ext-uninstall` | `--type`, `--key`, `--only-data`, `--purge` | Uninstall (honors `extension_protected` + in-use/default-template guard). **Installed**: removes DB config **and** deletes files; `--only-data` keeps the files. **Not installed but on disk**: refused unless `--purge` (then only the files are deleted). `--only-data` and `--purge` cannot be combined. |
| `gp247:ext-update` | `--type`, `--key`, `--all` | Apply marketplace updates for one extension or every one with an update (backup + rollback). |
| `gp247:ext-check-update` | `--type`, `--force` | Report available updates (cached unless `--force`). |
| `gp247:ext-search` | `--type`, `--keyword=`, `--free`, `--page=` | Browse/search the marketplace catalog. |
| `gp247:ext-license` | `--type`, `--key`, `--license=`, `--delete` | Set / show / remove the per-plugin license of a paid extension (stored in `admin_config`, never in `.env`). |

Examples:

```bash
php artisan gp247:ext-list --type=plugin --json
php artisan gp247:ext-install --type=plugin --file=storage/tmp/MyBlog.zip
php artisan gp247:ext-install --type=plugin --key=News
php artisan gp247:ext-enable --type=plugin --key=News
php artisan gp247:ext-update --type=plugin --all
php artisan gp247:ext-uninstall --type=plugin --key=News
```

> **Batch (multiple items).** `ext-install`, `ext-enable`, `ext-disable` and
> `ext-uninstall` accept **multiple keys** — repeat the option (`--key=A --key=B`) or
> comma-separate (`--key=A,B`); `ext-install` likewise takes multiple `--file`/`--dir`.
> Items are processed **one at a time and independently** (there is no atomic transaction
> across different extensions — each is its own files+migrations+config unit), results are
> reported per item, the route/config cache is rebuilt **once** at the end, and the command
> exits non-zero if **any** item failed. **Paid remote extensions must be installed one at
> a time** — `--paid` combined with more than one `--key` is **refused up front**
> (`error.code: paid_multi_not_allowed`), because a single `--license` would be applied to
> the wrong plugins. Run one `--key` per paid install.

> **Re-running / already installed.** "Installed" means it has an `admin_config` row (not
> merely that files exist on disk). `ext-install` on an **already-installed** extension is
> rejected with the "already exists" error (remote is checked **before** downloading — no
> wasted bandwidth), and never creates a duplicate `admin_config` row. To refresh an installed
> extension use `gp247:ext-update`; to reinstall, `gp247:ext-uninstall` first. In a batch, an
> already-installed key is reported under `failed` while the other items proceed.
> Symmetrically, `ext-enable`/`ext-disable`/`ext-uninstall` treat a **not-installed** extension
> as an error (enable/disable refuse; uninstall refuses unless `--purge`), so a bundled
> on-disk plugin is never enabled as a no-op or deleted by surprise.

> The CLI and the admin UI now run the **same** underlying engine
> (`ExtensionInstaller` / `LibraryClient`), so behavior is identical regardless of which
> you use. Protected extensions and the in-use/default template are refused from both.

---

## Orchestration & diagnostics (core 2.1)

| Command | Key options | What it does |
| --- | --- | --- |
| `gp247:install` | `--sample`, `--force=1` | The common install entry point for the whole ecosystem. **Auto-detects** which packages are present and installs them in order: `core-install` → (`front-install`) → (`shop-install`) → (`shop-sample` when `--sample`). A failing step aborts with a non-zero exit. **Requires confirmation by default** (see the safety note below); pass `--force=1` for unattended installs. Available immediately after `composer require` — even before the platform is installed. Package selection is fully automatic — there are **no** `--with-front` / `--with-shop` flags. |
| `gp247:update` | `--overwrite-lang`, `--publish=<tokens>` | Safe post-`composer update` refresh for a live site: `core-update`, then `shop-update` (only if the shop is installed), optional `language-update` (`--overwrite-lang`), an **opt-in** asset/view re-publish (`--publish=`, off by default), then `cache-rebuild`. Never runs a destructive (re)install. See the re-publish note below for the impact of each `--publish` token. |
| `gp247:cache-rebuild` | — | Rebuild route/config caches (after enabling/updating extensions). |
| `gp247:doctor` | `--json` | Check the environment: PHP ≥ 8.2, required extensions, write permissions, DB connectivity, install marker. Exits non-zero if any check fails — usable as a CI/pre-install gate. |
| `gp247:info` | `--json` | Show status: installed package versions (core/front/shop), install marker, plugin/template counts, marketplace API endpoint. Read-only. |

Examples:

```bash
php artisan gp247:install            # interactive: shows the plan + data-loss warnings, then asks to confirm
php artisan gp247:install --force=1  # unattended (CI/Docker): skips confirmation — see the safety note
php artisan gp247:install --sample   # also seed demo shop data (interactive by default)
php artisan gp247:update                       # default: refresh only, publishes NOTHING (safe for live)
php artisan gp247:update --publish=core-public  # also re-publish compiled admin assets (safe)
php artisan gp247:update --publish=front-view   # also re-publish live storefront templates (DESTRUCTIVE — see note)
php artisan gp247:update --publish=all          # re-publish every target (DESTRUCTIVE — back up first)
php artisan gp247:doctor --json
php artisan gp247:info --json
```

> **Re-publish (`--publish=<tokens>`) — opt-in, tiered by impact.** `composer update` refreshes the
> vendor code but **not** the published copies under `public/GP247`, `app/GP247` and
> `resources/views/vendor/*`. `--publish=` re-publishes them on demand. **Default (no `--publish`) publishes
> nothing** — existing behavior, safe for live sites. Each token names a publish tag (naming its package),
> comma-separated (e.g. `--publish=core-public,front-view`), or `all` for every token:
>
> | Token | Publishes to | Impact |
> | --- | --- | --- |
> | `core-public` | `public/GP247` (compiled admin CSS/JS) | **Safe** — regenerated artifact, not hand-edited |
> | `core-view` | `resources/views/vendor/gp247-admin` | **Destructive** — overwrites your admin-view overrides |
> | `front-public` | `public/GP247/Templates/GP247Front` | **Destructive** — overwrites the in-place-built storefront CSS |
> | `front-view` | `app/GP247/Templates/GP247Front` | **Destructive** — overwrites your live storefront templates |
> | `shop-view-admin` | `resources/views/vendor/gp247-shop-admin` | **Destructive** — overwrites your shop admin-view overrides |
> | `shop-view-front` | `app/GP247/Templates/GP247Front` | **Destructive** — overwrites your live storefront templates |
>
> **Back up the target folder(s) before publishing any destructive token** — the overwrite is not
> reversible. There is **no** `--force` flag on `gp247:update`: typing a destructive token is itself your
> consent. In a non-interactive / `--json` run the destructive tokens publish directly (with an impact
> warning on stderr); in an **interactive terminal** the command warns, reminds you to back up, and asks
> to confirm (default **no**) — declining skips only the destructive publish while the update and cache
> steps still run. A per-target write failure (e.g. read-only shared host) is warned and skipped, not fatal.
> (The `--force` in the underlying `vendor:publish --tag=… --force` is that command's own overwrite flag,
> not an option of `gp247:update`.)

> **Safety — confirmation is required by default.** `front-install` / `shop-install` **drop and recreate**
> their tables (they call the matching `*-uninstall` first), so re-running the installer on a live site
> **destroys** front/shop data. Because of this, `gp247:install` without `--force`:
> - **refuses** to run in a non-interactive or `--json` context (returns `error.code = "confirmation_required"`,
>   non-zero exit, and runs **no** steps) — this makes accidental unattended data loss impossible; and
> - in an **interactive terminal**, prints the detected plan plus data-loss warnings and asks
>   `Proceed with installation?` (default **no**). Declining exits 0 without running anything.
>
> Pass `--force=1` only when you genuinely intend an unattended (re)install. `sc:install` takes its own
> confirmation and then delegates to `gp247:install --force=1`.

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

→ Run `php artisan gp247:core-update`. If it still doesn't change, re-publish the compiled admin
assets — either `php artisan vendor:publish --tag=gp247:core-public --force`, or do it as part of the
refresh with `php artisan gp247:update --publish=core-public` (safe; publishes only the admin CSS/JS).
Only reach for the destructive view/template tokens (and back up first) if you actually need to reset
customized views — see the re-publish note under *Orchestration & diagnostics*.

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
| 2026-08-24 | gp247/core 2.1 | `gp247:update` gained an **opt-in** `--publish=<tokens>` option to re-publish assets/views after `composer update` (default publishes nothing). Tokens name the publish tag (`core-public`/`core-view`/`front-public`/`front-view`/`shop-view-admin`/`shop-view-front`/`all`) and are **tiered by impact**: only `core-public` is safe; view/template tokens overwrite your customizations. **No `--force` flag** — typing a destructive token is the consent; interactive runs still warn + confirm (default no). |
| 2026-08-24 | gp247/core 2.1 | `gp247:install` and `gp247:doctor` now register in a **bootstrap tier** (available right after `composer require`, before the platform is installed — fixes "only `gp247:core-install` existed pre-install"). `gp247:install` **auto-detects** present packages; the `--with-front`/`--with-shop` flags were **removed** (never shipped in a stable release). **Safety:** `gp247:install` now **requires confirmation by default** — it refuses non-interactive/`--json` runs without `--force=1` and prompts (default no) interactively. `sc:install` delegates to `gp247:install`. |
| 2026-08-24 | gp247/core 2.1 | `ext-install --key` installs a bundled/on-disk plugin locally (or refuses if already installed); `ext-enable`/`ext-disable` refuse a not-installed extension; `ext-uninstall` refuses a not-installed on-disk extension unless `--purge` (`--only-data`/`--purge` mutually exclusive). |
| 2026-08-23 | gp247/core 2.1 | Standardized CLI output contract (`--json` + exit codes, all commands); added the `gp247:ext-*` extension-lifecycle family, and `gp247:install` / `gp247:update` / `gp247:cache-rebuild` / `gp247:doctor` / `gp247:info`. **Breaking:** `make-plugin`/`make-template` now emit the JSON envelope (path at `data.path`). |
| 2026-08-22 | gp247/shop 2.1 | Added `gp247:shop-update` — non-destructive shop upgrade for live sites |

---

<sub>📅 **Last updated:** 2026-08-24 · ✍️ **Author:** GP247</sub>
