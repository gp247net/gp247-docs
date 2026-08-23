> 🌐 **Language:** [🇻🇳 Tiếng Việt](./install-extension_vi.md) · 🇬🇧 English (current)

# Installing Plugins & Templates for GP247

## Introduction

This document explains how to **install a plugin or template (collectively called an extension) into a
GP247 website**, aimed at site owners — including non-technical ones. GP247 supports **4 installation
methods**: install **online** via the official extension library, **import** a ready-made `.zip` file,
install **manually** by copying the folder onto the server, or use the **command line**
(`gp247:ext-*`) for developers/automation. By the end, you will know which method suits you and how to
follow each step.

> 💡 Plugins and templates install **exactly the same way** — the same methods. The only difference is
> that they live under two different admin menus: **Plugin** and **Template**. Wherever this document
> says "extension", it applies to both.

---

## 1. Before you install — what you need to know

1. You must log in to admin with an account that **has permission to manage extensions**.
2. The extension must be **compatible** with your website. On install, GP247 automatically checks the 3
   conditions declared in the extension's `gp247.json`:
   - `requireCore`: the required `gp247/core` version (the current standard is `2.1`).
   - `requireComposerPackages`: mandatory packages (for example, a template always needs `gp247/front`).
   - `requireGp247Extensions`: other extensions that must be installed first.

   > Since gp247/core 2.1, the keys `requireComposerPackages`/`requireGp247Extensions` replace the old `requirePackages`/`requireExtensions`. Core 2.1 still reads the old keys (backward compatible) but they are deprecated.

   If any condition is missing, GP247 **reports an error and refuses to install** — install the missing
   part first.
3. After installing, GP247 **clears the cache automatically** (routes/config), so you don't have to do
   it by hand. For a **template**, after installing you must also **Activate** it for the look to take effect.

> Quick glossary:
> - **Extension**: the collective name for plugins and templates.
> - **`gp247.json`**: the file declaring the extension's info (name, version, compatibility conditions).
> - **`configKey`**: the extension's unique identifier, the same as its folder name.

---

## 2. Method 1 — Install Online (via the extension library)

This is the **easiest** way: browse GP247's official extension store right inside admin and click to
install, with no manual file download.

> ⚠️ This method only appears if your website has the **GP247 library connection enabled** (the
> `api_plugins` config for plugins / `api_templates` for templates is on). If you don't see the
> **Online** menu, use Method 2 or Method 3.

### Step 0 (do it once) — Register an API License to connect to the library

Before you can browse the extension store, your website needs an **API License** to identify itself to
the GP247 library. **This license is free.**

1. In admin, open **API License settings** (Register / Install API license) — usually inside the
   **Online** screen of Plugin/Template.
2. Click **Register / Install**. GP247 contacts the library and fetches a license key for your domain.
3. On success, this key is **automatically saved into the `GP247_API_LICENSE` variable** in the `.env`
   file at the website root. You do **not** need to edit `.env` by hand.

Once `GP247_API_LICENSE` is set, your website is connected to the library — move on to the install steps
below. Step 0 is a one-time task; later installs don't require repeating it.

> ℹ️ Distinguishing the two license types:
> - **API License** (Step 0, **free**): the key for your website to *connect and browse* the library. Stored in `GP247_API_LICENSE`.
> - **Paid extension license**: a separate license for *each paid extension*, needed only when downloading a paid extension (see the end of this section).

### The online install steps

1. Log in to admin. Go to the **Plugin** (or **Template**) menu, then choose the **Online** submenu
   (extension library / store).
2. The list of extensions from the GP247 library appears. You can **search by keyword**, filter
   **free / paid**, and sort. Each extension shows its name, version and price.
3. Choose the extension to install and click **Install**.
4. GP247 automatically: downloads the file → checks compatibility → extracts → installs. On success you
   see an install-successful notice and the extension appears in the installed list.

**About paid extensions:** besides the free API License in Step 0, a paid extension also needs a
**license specific to that extension** (issued for your domain after purchase). If you don't have one,
admin guides you to activate the license before downloading. **Free** extensions download and install
right away, with no separate license (just the Step 0 API License).

---

## 3. Method 2 — Import (upload a `.zip` file)

Use this when you **already have the extension as a `.zip` file** (bought outside the library, received
from a developer, or packaged yourself). You upload this file through admin, and GP247 extracts and
installs it automatically.

Steps:

1. Go to the **Plugin** (or **Template**) menu and choose **Import** (Upload).
2. Select the extension `.zip` file from your computer, then click **Upload / Import**.
3. GP247 checks and installs it automatically. On success, the extension appears in the installed list.

**Requirements for the `.zip` file:**
- Must be a proper **`.zip`** (not `.rar`, `.7z`...).
- Maximum size **50MB** (and not exceeding the server's upload limits — `upload_max_filesize` /
  `post_max_size` in PHP).
- The `.zip` must contain a **`gp247.json`** file (otherwise GP247 reports a "wrong configuration" error).
- It must **not duplicate** an already-installed extension: if the `configKey` already exists on the
  site, GP247 refuses it to avoid accidental overwrites. To upgrade to a newer version, use the
  **update** feature, not import-over.

> ℹ️ Small note: after importing a **template** successfully, the screen may return to the **Plugin
> list** instead of the Template list (this is a current characteristic of the system). Don't worry —
> the template files are installed correctly into the template folder; just go to the **Template** menu
> to see and **Activate** it.

---

## 4. Method 3 — Manual install (copy the folder onto the server)

Use this when you have **file access to the server** (FTP, SSH, or your hosting's File Manager) and want
to place the extension directly — usually for developers, or when the two methods above aren't available.

Steps:

1. Copy the **whole extension folder** (the folder containing `AppConfig.php` and `gp247.json`) to the
   right location on the server, depending on the type:
   - Plugin → `app/GP247/Plugins/<ExtensionName>`
   - Template → `app/GP247/Templates/<ExtensionName>`

   Here `<ExtensionName>` must **exactly match** the `configKey` declared in `gp247.json`.

   For example, a plugin whose `configKey` is `MyBanner` goes into:

   ```
   app/GP247/Plugins/MyBanner/
   ├── AppConfig.php
   ├── gp247.json
   └── ...(the remaining files)
   ```

2. If the extension has a `public/` folder (css/js/images), copy its contents to:
   - Plugin → `public/GP247/Plugins/<ExtensionName>`
   - Template → `public/GP247/Templates/<ExtensionName>`

3. In admin, open the **Plugin** (or **Template**) menu. The extension you just copied will **appear
   automatically** in the list (GP247 scans the folder to detect it). It is in the **not-installed** state.
4. Click **Install** next to that extension. GP247 checks compatibility, then installs. On success, the
   button switches to the installed state.

> ⚠️ The extension folder **must** contain `AppConfig.php` — GP247 relies on this file for detection. If
> you copy without this file, the extension will **not appear** in the admin list.

---

## 5. Method 4 — Command line (`gp247:ext-*`)

For developers, CI/CD, Docker or shared hosts with terminal access, the whole extension lifecycle is
available from the command line — the **same engine** the admin UI uses (so compatibility checks,
`GP247_PROTECTED_*` and the in-use/default-template guard all apply identically). Plugins and templates
share one command family; choose with `--type=plugin|template`.

```bash
# List local extensions + status + available updates
php artisan gp247:ext-list --type=plugin

# Install from a local .zip, an extracted folder, or the marketplace (by key)
php artisan gp247:ext-install --type=plugin --file=storage/tmp/MyBanner.zip
php artisan gp247:ext-install --type=plugin --key=News

# Enable / disable / uninstall (accept multiple keys)
php artisan gp247:ext-enable  --type=plugin --key=News
php artisan gp247:ext-uninstall --type=plugin --key=News

# Update from the marketplace (one, or all with an available update)
php artisan gp247:ext-update --type=plugin --all

# Search the marketplace; manage a paid extension's license
php artisan gp247:ext-search --type=plugin --keyword=blog
php artisan gp247:ext-license --type=plugin --key=ProPlugin --license=XXItXX
```

Notes:
- Add `--json` to any command for a machine-readable envelope (`{ok,command,data,warnings,error}`) with
  a standard exit code (0 success / non-zero failure) — ideal for scripts and CI.
- **Batch**: `ext-install/enable/disable/uninstall` accept multiple keys (`--key=A --key=B` or
  `--key=A,B`); items run one at a time and independently, the cache is rebuilt once at the end, and the
  command exits non-zero if any item failed. Install a **paid** extension one key at a time.
- **Re-running is safe**: `ext-install` refuses an already-installed extension (use `ext-update` to
  update, `ext-uninstall` to reinstall).
- Full reference: [command-line-reference.md](../system/command-line-reference.md).

---

## 6. After installing — activate and verify

1. **For a plugin:** it is usually ready to use after installing. Some plugins have **Enable/Disable**
   and **Config** buttons — adjust them if needed.
2. **For a template:** after installing, you must click **Activate** to make it the store's active look.
   Only one template is active at a time.
3. GP247 clears the cache after installing. If for some reason the look/feature isn't updated, run the
   following command at the website root to clear the cache manually:

   ```bash
   php artisan optimize:clear
   ```

4. Open the website (or the related admin screen) to check the extension works correctly.

---

## 7. Which method should I use? (quick comparison)

| Method | When to use | Advantage | Requires |
|---|---|---|---|
| **Online** | Want to browse & install quickly from the official store | Easiest, fully automatic | Site with library connection on; a license for paid ones |
| **Import** | You already have the `.zip` file | No server access needed | A valid `.zip`, ≤ 50MB, containing `gp247.json` |
| **Manual** | You have server file access / the two methods above aren't available | Full control, no upload/API dependency | FTP/SSH/File Manager access; copy to the right folder |
| **CLI** (`gp247:ext-*`) | Developers, CI/CD, Docker, scripted/batch installs | Scriptable (`--json` + exit codes), batch multiple items, same engine as UI | Terminal access at the project root |

---

## 8. Common troubleshooting

- **Compatibility error on install:** a `requireCore` / `requireComposerPackages` / `requireGp247Extensions`
  condition is missing. Install the missing part (e.g. install `gp247/front` before a template) and retry.
- **Import reports "wrong configuration":** the `.zip` doesn't contain `gp247.json` at the right level,
  or you zipped it wrong (an extra parent folder layer). Check the archive structure.
- **Import reports a duplicate:** the extension is already installed. To go to a newer version, use the
  **update** feature, don't import over it.
- **Manual install but admin doesn't show it:** check that you copied to the **right folder**
  (`app/GP247/Plugins` vs `Templates`) and that the folder has `AppConfig.php`; then run
  `php artisan optimize:clear` and reload admin.
- **The `.zip` file is too big to upload:** over 50MB or over the server's upload limit. Use Method 3
  (manual) instead.

---

## 9. Q&A

**Q1: Is installing a plugin different from installing a template?**

→ The installation is identical (the same methods). Only the place differs: plugins under the **Plugin**
menu, templates under the **Template** menu. A template additionally must be **Activated** after
installing for it to take effect.

**Q2: I don't see the "Online" menu in admin?**

→ Your website hasn't enabled the GP247 library connection (`api_plugins`/`api_templates`). Use Method 2
(Import) or Method 3 (manual).

**Q3: What's the first thing to do to use the online library?**

→ Register the **API License** (free) once in admin — see Step 0 in Method 1. This key is auto-saved into
the `GP247_API_LICENSE` variable in `.env`, letting your website connect and browse the extension store.

**Q4: How do I install a paid extension?**

→ Install it online as usual, but besides the free API License, a paid extension also needs a **separate
license** for your domain (after purchase). Free extensions don't need one.

**Q5: What structure must the `.zip` have to be importable?**

→ The `.zip` must contain a `gp247.json` file (along with `AppConfig.php` and the extension's files).
Don't add an extra parent folder layer that pushes `gp247.json` too deep. The file must also be ≤ 50MB;
larger than that, install manually.

**Q6: I installed manually but admin doesn't show the extension?**

→ Check: did you copy to the right folder `app/GP247/Plugins/<Key>` or `app/GP247/Templates/<Key>`, does
the folder have `AppConfig.php`, and does `<Key>` match the `configKey` in `gp247.json`? Then run
`php artisan optimize:clear` and reload the admin page.

**Q7: Why does importing a template jump back to the Plugin list?**

→ This is a current characteristic of the system — the template files are still installed in the right
place. Just go to the **Template** menu to see and activate it.

**Q8: Do I have to clear the cache manually after installing?**

→ Usually not — GP247 clears the cache after installing. If it isn't updated, run `php artisan optimize:clear`.

**Q9: Can I install a new version over the old one by importing?**

→ You shouldn't. Import refuses if the `configKey` already exists. To go to a newer version, use the
**update** feature — it preserves the settings stored in the database.

**Q10: How do I uninstall/delete an extension, and how do I avoid accidentally deleting the source?**

→ The extension list has 2 deletion levels: **"Delete data"** (removes only the data/config in the
database, **keeps the source files**) and **"Remove files"** (removes both the data and all source files
on the server). Note: you can't delete a template that's currently active (switch to another template first).

To **guard against accidental deletion** of important extensions, GP247 has a **protection** mechanism:
declare their `configKey` in the `GP247_PROTECTED_PLUGINS` (for plugins) and `GP247_PROTECTED_TEMPLATES`
(for templates) variables in `.env`, comma-separated. For example:

```
GP247_PROTECTED_PLUGINS="Payment,ShippingVN"
GP247_PROTECTED_TEMPLATES="GP247Front"
```

For a protected extension, admin **hides both the "Delete data" and "Remove files" buttons** — meaning
it cannot be uninstalled or deleted from the interface, preventing source/data loss from a mistaken
action. To actually remove it, take its name out of the corresponding `.env` variable and try again.

> On the command line, the two deletion levels map to `gp247:ext-uninstall --type=... --key=...`
> (removes data **and** files) and `gp247:ext-uninstall ... --only-data` (removes data only, keeps
> files). The CLI honors `GP247_PROTECTED_*` and the in-use/default-template guard just like the UI —
> a protected or in-use extension is refused with a clear error.

---

<sub>📅 **Last updated:** 2026-08-23 · ✍️ **Author:** GP247</sub>
