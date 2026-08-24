> 🌐 **Language:** [🇻🇳 Tiếng Việt](./update-gp247_vi.md) · 🇬🇧 English (current)

# How to update GP247

## Introduction
This document explains how to update a GP247 (S-Cart) website to a newer version
**safely on a live site** — without losing data or your customizations. It is
written for site administrators, including non-technical ones: just follow each
step and copy the exact commands. The key idea: **prefer GP247's standardized
commands** (`gp247:update`, `gp247:info`, `gp247:doctor`) — you no longer need to
remember a separate command per package (core/front/shop).

> ℹ️ **Two kinds of commands to distinguish:**
> - **Standardized command (recommended):** `gp247:update` — a single command that
>   updates *every* GP247 package present on the site.
> - **Per-package commands (advanced):** `gp247:core-update`, `gp247:shop-update`…
>   — run for one package only. Normally you **don't** call these directly, because
>   `gp247:update` runs them for you in the correct order.

---

## An update has 2 parts
Updating GP247 always involves **2 distinct parts**, and you must do **both**:

1. **Update the source code** — download the packages' new code onto the machine,
   using **Composer** (`composer update`). This only changes files under `vendor/`.
2. **Apply the changes to the site** — update the data structure (database),
   rebuild the caches… using the standardized artisan command **`gp247:update`**.

> ⚠️ Running `composer update` alone is **not enough**. If you skip part 2, the
> site may break because the code is new but the data structure / cache is still
> the old version.

---

## Before you update

1. **Back up first.** Back up the **database** and the source folder. This is the
   most important step — if anything goes wrong, you can always roll back.

2. **Check the current version.** Open a **Terminal** (on Windows, "Command
   Prompt"), change into the website root folder, then run:

   ```bash
   php artisan gp247:info
   ```

   If successful, it lists the installed package versions (core / front / shop).
   Note these numbers so you can compare after updating.

3. **(Recommended) Check the environment.** Run the diagnostic command to make
   sure the server meets the requirements (PHP version, required extensions, write
   permissions, DB connection):

   ```bash
   php artisan gp247:doctor
   ```

   If every item is **OK**, you can update with confidence. If any item reports
   **fail**, resolve that item first, then update.

---

## Update steps (the standard way — recommended)

1. Go to the website root folder in the Terminal.

2. **Download the new source code** with Composer. Run the packages you actually
   use — if unsure, just run all three; any package not present is skipped:

   ```bash
   composer update gp247/core
   composer update gp247/front
   composer update gp247/shop
   ```

   If successful, Composer prints the list of packages that were upgraded.

3. **Apply the changes to the site** with a single standardized command:

   ```bash
   php artisan gp247:update
   ```

   This command runs in sequence, automatically (safe, non-destructive):
   - Updates **core**;
   - Upgrades the **shop** data structure — *only if* the site has the shop module
     installed;
   - Rebuilds the **caches** (route/config) so the site picks up the new code.

   If successful, it prints the steps it ran (e.g. `gp247:core-update`,
   `gp247:cache-rebuild`).

4. **Verify.** Run `php artisan gp247:info` again and compare the version numbers
   with the ones you noted in the preparation step — if they went up to the new
   version, you're done. Open the site (home page + admin) to confirm it displays
   normally.

> ✅ For most websites, **the 4 steps above are enough**. The sections below are
> only for special needs (refreshing translations, refreshing published assets).

---

## Advanced options

### A. Refresh language files (`--overwrite-lang`)
By default `gp247:update` **keeps** every translation you edited in Admin. If you
want to pull the package's latest default translations (which **overwrites** the
language strings you changed), add the option:

```bash
php artisan gp247:update --overwrite-lang
```

> ⚠️ Use this only when you truly want to reset translations to the defaults. If
> you edited a lot, back up the language table first. See the
> [Language system](./language-system.md) document for details.

### B. Refresh published views / assets (`--publish=<tokens>`)
`composer update` only refreshes the code under `vendor/`. The **published** UI
files (admin CSS/JS in `public/GP247`, views in `resources/views/vendor/…`,
storefront templates in `app/GP247/…`) are **not** overwritten automatically. To
refresh them, add the `--publish` option with a list of "tokens" (comma-separated):

```bash
php artisan gp247:update --publish=core-public                 # safe: refresh compiled admin CSS/JS only
php artisan gp247:update --publish=core-public,front-view      # also overwrite storefront templates (DESTRUCTIVE)
php artisan gp247:update --publish=all                         # refresh EVERYTHING (DESTRUCTIVE — back up first)
```

Valid tokens: `core-public`, `core-view`, `front-public`, `front-view`,
`shop-view-admin`, `shop-view-front`, `all`.

> 📖 To understand which folder each token affects, see the "`gp247:update`"
> section of the [Command-line reference](./command-line-reference.md).

---

## Conditions & Rules (know before you act)

**When running a normal update (`gp247:update`)**

- **Safe on a live site** — the command only *upgrades*; it never runs a
  destructive re-install step. So you can use it directly on the production site.
- **Works on restricted hosting too** — it requires no cron, queue worker, or
  websocket. As long as you can run `php artisan`, the site updates fully.
- **The shop step only runs when the shop is installed** — if the site has no shop
  module, the command skips the shop upgrade without erroring.

**When using `--overwrite-lang`**

- **It overwrites the translations you edited** back to the package defaults —
  which is why the option is OFF by default. Enable it only when you deliberately
  want a reset.

**When using `--publish=<tokens>`**

- **Only `core-public` is safe** — those are precompiled admin CSS/JS; overwriting
  them loses none of your customizations.
- **Every other token is DESTRUCTIVE** — they overwrite views/templates, i.e. they
  **erase the UI customizations** you edited in place. Back up the target folder
  first.
- **There is no `--force` flag** — the fact that *you type* a destructive token is
  itself the consent. In an interactive Terminal, the command still **warns and
  asks to confirm** (defaulting to "no"), to prevent slips.
- **A wrong token → stops immediately, nothing done** — if you mistype an invalid
  token, the command errors and exits *before* updating, so the site is untouched.

---

## Q&A

**Q1: Can I just run `composer update`, or do I need `gp247:update` too?**

→ You must run both. `composer update` only downloads new code into `vendor/`;
`gp247:update` is what actually applies the changes (DB update, cache rebuild) to
the site.

**Q2: Do I have to remember `gp247:core-update`, `gp247:shop-update`… per package?**

→ No. Just use the standardized `gp247:update` — it calls the per-package commands
for you, in the right order, and only runs the shop part when the shop is installed.

**Q3: Does running `gp247:update` lose data or customizations?**

→ No. By default the command only upgrades safely: it keeps your data, keeps the
translations you edited, and does not touch published views/templates. Overwriting
only happens when you *deliberately* add `--overwrite-lang` or `--publish` (with a
token other than `core-public`).

**Q4: My site is on shared hosting without cron/queue — can I still update?**

→ Yes. `gp247:update` needs no cron, queue worker, or websocket — being able to run
`php artisan` is enough.

**Q5: After updating, the admin UI has broken CSS/JS — what do I do?**

→ Refresh the compiled admin assets (safe, no loss of customizations):
`php artisan gp247:update --publish=core-public`.

**Q6: How is `--publish=all` different from `--publish=core-public`?**

→ `core-public` refreshes only the admin CSS/JS (safe). `all` refreshes
**everything**, including views/templates — it will **overwrite your UI
customizations**. Back up before using `all`.

**Q7: How do I know the update succeeded?**

→ Run `php artisan gp247:info` and compare the version numbers with those from
before the update; if they went up to the new version and the site runs normally,
you're done.

**Q8: What if the update errors out midway?**

→ `gp247:update` runs step by step and stops if a step fails, so the site usually
doesn't end up half-updated. Read the error message to fix it, or restore from your
backup and try again.

**Q9: Do I need to run `gp247:doctor` on every update?**

→ Not required, but it's good to run before updating (especially right after
changing the server / PHP version) to catch environment issues early.

**Q10: Where do I see every GP247 command and all its options?**

→ See the [Command-line reference](./command-line-reference.md) — it lists all of
GP247's artisan commands with their options and examples.

---

<sub>📅 **Last updated:** 2026-08-24 · ✍️ **Author:** GP247</sub>
