> 🌐 **Language:** [🇻🇳 Tiếng Việt](./create-plugin_vi.md) · 🇬🇧 English (current)

# Creating a Plugin for GP247 (v2 standard, update-safe)

## Introduction

This document explains how to **create a new GP247 plugin following the v2 plugin standard (Core 2.0)**.
It is for PHP/Laravel developers who want to build their own extension for a GP247 website. After reading
it you will know how to scaffold a plugin, understand what each file does, write the install/uninstall/
enable/disable logic, and — most importantly — **write the plugin the right way so it can be upgraded to
a newer version safely** (without wiping the site owner's settings or corrupting data).

> ⚠️ **The single most important thing in this document:** GP247 has a **1-click update** mechanism for
> plugins. On update, the system **overwrites EVERY file** of the plugin with the new release, but **keeps
> the configuration stored in the database (`admin_config`)**. If you write the plugin the wrong way (for
> example, letting the site owner edit `config.php` directly), then **every update wipes their choices**.
> Section 6 explains how to avoid this in detail. Read it carefully.

---

## 1. Prerequisites

Before you start you need:

1. A working GP247 website (with `gp247/core` 2.0 or later installed).
2. The ability to run `php artisan` commands on the server or dev machine (terminal / command line).
3. To know what your plugin will do: admin-only, or also have a **public (storefront) page** for visitors.
   This determines whether you need the `Seo.php` / `FrontController.php` files.

> ⭐ **Checkout total-method plugins** (coupon / loyalty point, `configCode: "Total"`) are a special
> case: besides the usual `getInfo()`, the plugin's `AppConfig` must implement
> `GP247\Shop\Front\Contracts\CheckoutTotalMethod` (`checkoutApply` / `checkoutRemove` / `checkoutView`)
> so the Livewire checkout can show its input on **any** template. See
> [convert-plugin-v1-to-v2.md](convert-plugin-v1-to-v2.md) step 8c and the `ShopDiscount` plugin for the
> reference implementation.

> Quick glossary:
> - **Plugin (extension)**: a feature package plugged into GP247 that can be enabled/disabled/removed in admin.
> - **`configKey`**: the plugin's unique identifier, which **must match the plugin folder name**. For a plugin `MyBanner`, `configKey` is `MyBanner`.
> - **Livewire**: a Laravel technology for building dynamic UI (button clicks, partial page reloads) **without writing jQuery**. The GP247 2.0 admin UI uses Livewire + Tailwind (the TailAdmin stack).

---

## 2. Step 1 — Scaffold the plugin with an artisan command

GP247 ships a command that scaffolds a plugin following the v2 standard (already including Livewire, SEO,
and the new layout), so you don't have to hand-create every file.

1. Open a **Terminal** at the root of the GP247 website (where the `artisan` file lives).
2. Type exactly the following line and press Enter (replace `MyBanner` with your plugin name):

   ```bash
   php artisan gp247:make-plugin --name=MyBanner --download=0
   ```

   What the parameters mean:
   - `--name=MyBanner`: the plugin name. Prefer **PascalCase** (capitalize the first letter of each word, no accents, no spaces), e.g. `MyBanner`, `ProductFeed`.
   - `--download=0`: create the plugin **directly** in `app/GP247/Plugins/MyBanner` (usable immediately). If you set `--download=1`, the command creates a `.zip` in `storage/tmp` for you to package and distribute.

3. On success, the terminal returns a JSON line with `"error":0`, e.g.:

   ```json
   {"error":0,"path":"","msg":"Success"}
   ```

   The folder `app/GP247/Plugins/MyBanner` is created with all the sample files.

> Note: if the website does **not** have `gp247/front` (the storefront) installed, the command
> automatically **skips** `Controllers/FrontController.php` — this is normal, not an error.

---

## 3. Plugin folder structure and the role of each file

After scaffolding, your plugin has the structure below. The table explains **what each file is for**
and **which files are mandatory**:

```
app/GP247/Plugins/MyBanner/
├── Admin/
│   └── AdminController.php     # Controller for the legacy admin screen (backward compatibility)
├── Controllers/
│   └── FrontController.php     # Controller for the public page (only if gp247/front is installed)
├── Lang/
│   ├── en/lang.php             # English language strings
│   └── vi/lang.php             # Vietnamese language strings
├── Livewire/
│   └── AdminLivewire.php       # NEW-style admin screen (Livewire + TailAdmin) — the v2 standard
├── Models/
│   └── ExtensionModel.php      # Where you create/drop data tables on install/uninstall
├── Views/
│   ├── Admin.blade.php         # View for the legacy AdminController
│   ├── livewire.blade.php      # View for AdminLivewire (v2 standard)
│   └── Front.blade.php         # View for the public page
├── public/
│   └── images/logo.jpg         # Static assets (css/js/images), copied to public/GP247/Plugins/MyBanner
├── AppConfig.php               # ⭐ Main config file: install/uninstall/enable/disable/update
├── config.php                  # Plugin DEFAULT values (OVERWRITTEN on update — see Section 6)
├── function.php                # The plugin's helper functions
├── gp247.json                  # ⭐ Plugin manifest (version, compatibility...)
├── Provider.php                # Registers services (views, lang, sitemap, page-type...)
├── Route.php                   # Route declarations (admin and Livewire)
└── Seo.php                     # Provides URLs for sitemap.xml (only needed if there is a public page)
```

The two files you will **definitely edit**: `gp247.json` (the manifest) and `AppConfig.php` (install logic).

---

## 4. Step 2 — Fill in `gp247.json`

This is the plugin's "ID card". Open `gp247.json`; the generated sample looks like this:

```json
{
    "name": "MyBanner module",
    "image": "images/logo.jpg",
    "auth": "GP247",
    "email": "support@gp247.net",
    "link": "https://GP247.net",
    "configGroup": "Plugins",
    "configCode": "MyBanner",
    "configKey": "MyBanner",
    "version": "1.0",
    "requireCore": ["2.0"],
    "requireUpdateFrom": "1.0",
    "requirePackages": [],
    "requireExtensions": []
}
```

What the fields mean (the bold ones directly affect version updates — see Section 6):

| Field | Meaning |
|---|---|
| `name` | The plugin's display name. |
| `image` | Logo path (relative to the plugin's `public/` folder). |
| `auth`, `email`, `link` | Author information. |
| `configGroup` | Always `"Plugins"` for a plugin. |
| `configCode` | Config code, usually the same as `configKey`. |
| `configKey` | **Unique identifier; must match the plugin folder name.** Do not change it after release. |
| **`version`** | **The plugin version (semver: `1.0`, `1.1`, `2.0`...). Every new release MUST increase this number** for 1-click update to accept it. |
| **`requireCore`** | List of `gp247/core` versions the plugin is valid for. For the v2 standard use `["2.0"]`. |
| **`requireUpdateFrom`** | The **minimum** currently-installed version allowed to 1-click update to this release. `"1.0"` is safe (practically no restriction). |
| `requirePackages` | Required composer packages (from packagist.org). |
| `requireExtensions` | Other GP247 extensions that must be present (e.g. `Shop`, `Front`, `News`). |

---

## 5. Step 3 — Write the plugin logic

### 5.1. `AppConfig.php` — the install lifecycle

This file extends `ExtensionConfigDefault` and defines what happens when the site owner
**installs / uninstalls / enables / disables / updates** the plugin. The main methods:

| Method | Runs when | What you should do in it |
|---|---|---|
| `install()` | Site owner clicks "Install" | Write a config row into `admin_config`, call `ExtensionModel::installExtension()` to create data tables. |
| `uninstall()` | Site owner clicks "Uninstall" | Delete the config rows you created, call `ExtensionModel::uninstallExtension()`. |
| `enable()` / `disable()` | Temporary on / off | Flip the status flag; do **not** delete data. |
| `update($fromVersion)` | Update to a newer version | Migrate data if needed — see Section 6.4. |
| `getInfo()` | Admin shows plugin info | Return the info array (already provided, usually no change needed). |

The generated scaffold works out of the box. You only need to add your own plugin logic.

### 5.2. `Models/ExtensionModel.php` — create/drop data tables

If your plugin needs its own table, put the create-table statement in `installExtension()`
and the drop-table statement in `uninstallExtension()`. Example:

```php
public function installExtension()
{
    if (!\Illuminate\Support\Facades\Schema::hasTable('my_banner')) {
        \Illuminate\Support\Facades\Schema::create('my_banner', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }
}
```

### 5.3. Admin screen — use Livewire (the v2 standard)

The new UI standard for GP247 2.0 is **Livewire + TailAdmin**. The scaffold generates two files:
`Livewire/AdminLivewire.php` (logic) and `Views/livewire.blade.php` (UI).

- `AdminLivewire` extends `GP247AdminComponent`, so it **automatically has** permission checks (RBAC),
  toast notifications and the shared admin layout — exactly like a core screen.
- In `Views/livewire.blade.php`, prefer the shared `<x-gp247::*>` components
  (e.g. `<x-gp247::card>`) over raw HTML.
- **Absolutely no** jQuery / AdminLTE / select2... on the new screen — GP247 2.0 does not load jQuery.
- Every displayed string must be rendered via `trans('Plugins/MyBanner::lang.title')` or
  `gp247_language_render(...)`; **do not hardcode** text, to support multiple languages.

### 5.4. Routes and languages

- `Route.php`: the scaffold already registers the legacy admin route **and** the Livewire route
  (guarded with `class_exists`, so it is safe). Only add your own routes when needed.
- `Lang/vi/lang.php` and `Lang/en/lang.php`: add your plugin's display strings here.

---

## 6. ⭐ COMPLIANCE FOR VERSION UPDATES (read carefully)

This is the most important section. GP247 has a **1-click update** mechanism: from the admin panel,
the site owner clicks one button and the plugin downloads and replaces itself with the new release.
For your plugin to update **safely**, you must understand the mechanism and follow the 5 rules below.

### 6.1. How the update mechanism works

When the site owner clicks "Update", the system runs the following in order (if any step fails, it
**automatically restores** — rolls back):

1. **Backup** the entire current plugin folder (both `app/` and `public/`) into `storage/backups`.
2. **Download** the new `.zip` from the GP247 marketplace (with a sha256 checksum to prevent corruption/tampering).
3. **Validate** before touching live files: `configKey` must match, the new `version` must be **greater** than the installed one, and it must satisfy `requireUpdateFrom` and `requireCore`.
4. **Overwrite files**: delete the old plugin folders (both `app/GP247/Plugins/...` and `public/GP247/Plugins/...`) then copy all the new files in.
5. **Run the `AppConfig::update($oldVersion)` hook** so the plugin can migrate its data if needed.
6. If any of steps 4–5 fails → **restore from the backup** made in step 1.

**The key takeaway:** step 4 **wipes and replaces every file** of the plugin, but the **database
(`admin_config`, your data tables) is kept intact**. This is the foundation of every rule below.

### 6.2. Rule 1 — `version` must increase, `configKey` must not change

- On every new release, **increase `version`** in `gp247.json` (e.g. `1.0` → `1.1`). If the version
  is not greater than the installed one, the system refuses the update ("not newer").
- **Never change `configKey`** after release — it is the identifier used to match the old and new
  releases. Changing it means the system treats it as a different plugin.

### 6.3. Rule 2 (the most important) — the site owner's settings must live in the DATABASE, not in files

Because the overwrite step wipes all plugin files, **anything the site owner edits directly in a file is lost on update.**
Specifically:

- **`config.php` holds DEFAULT values only** (decided by you, the author). This file is **overwritten**
  on every update, so never expect the site owner to edit it.
- **Any value the site owner is allowed to change** (feature toggles, items-per-page, their own API
  key...) must be stored in the `admin_config` table (database), which the update **preserves**.
- The standard approach: store an override row in `admin_config` with `code` = `<configKey>_config`,
  then at runtime **overlay** that override on the defaults: `effective value = defaults (config.php) ⊕ override (DB)`.

The scaffold already ships two sample helpers (commented out) in `function.php` — uncomment them and
rename them for your plugin:

```php
// Effective config = defaults ⊕ overrides in DB
function MyBanner_effective_config()
{
    $defaults = (array) config('Plugins/MyBanner.settings', []);

    $row = \GP247\Core\Models\AdminConfig::where('group', 'Plugins')
        ->where('key', 'MyBanner_config')
        ->first();
    $overrides = $row ? json_decode((string) $row->value, true) : null;

    return is_array($overrides) ? array_merge($defaults, $overrides) : $defaults;
}

// Save the site owner's choices into the DB (update-safe)
function MyBanner_save_config(array $settings)
{
    \GP247\Core\Models\AdminConfig::updateOrCreate(
        ['group' => 'Plugins', 'key' => 'MyBanner_config'],
        [
            'code' => 'MyBanner_config',
            'store_id' => GP247_STORE_ID_GLOBAL,
            'value' => json_encode($settings),
        ]
    );
}
```

And in `config.php`, keep only the **defaults**:

```php
return [
    'settings' => [
        'enabled' => 0,
        'items_per_page' => 20,
    ],
];
```

> If your plugin has **no** site-owner-editable configuration (it runs with fixed behaviour), just
> delete these two helpers to keep things tidy — you don't need to worry about this.

### 6.4. Rule 3 — Migrate data with the `update()` hook

If a new release **changes the database structure** (adds a column, adds a table, changes the config
format...), you must migrate data in the `AppConfig::update($fromVersion)` hook. By default this hook
just returns "success" without doing anything (enough for a code-only change with no DB change). When
needed, override it:

```php
/**
 * Migrate plugin data after files were replaced by a newer version.
 *
 * @param string|null $fromVersion Version installed before the update.
 * @return array ['error' => 0|1, 'msg' => string]
 */
public function update(?string $fromVersion = null)
{
    // Example: version >= 1.1 needs a "sort" column on the my_banner table
    if ($fromVersion !== null && version_compare($fromVersion, '1.1', '<')) {
        if (\Illuminate\Support\Facades\Schema::hasTable('my_banner')
            && !\Illuminate\Support\Facades\Schema::hasColumn('my_banner', 'sort')) {
            \Illuminate\Support\Facades\Schema::table('my_banner', function ($table) {
                $table->integer('sort')->default(0);
            });
        }
    }

    return ['error' => 0, 'msg' => ''];
}
```

- Always check `$fromVersion` to **run only the migration steps that are needed** (idempotent — running
  it again multiple times causes no error).
- If the hook returns `['error' => 1, ...]` or throws an exception, the system will **restore the old
  version** — so let it fail loudly when a migration is unsafe, rather than leaving data half-migrated.

### 6.5. Rule 4 — Do not store user data inside the plugin folder

Both `app/GP247/Plugins/<key>` and `public/GP247/Plugins/<key>` are **deleted and replaced** on update.
Therefore:

- **Do not** put user-uploaded files (product images, uploads...) inside the plugin folder. Store them
  under a shared data area in `public/GP247/...`, or in `storage/`.
- The plugin's `public/` should contain only **the plugin's own static assets** (UI css/js/images) —
  things shipped with the release that are safe to overwrite.

### 6.6. Rule 5 — Set `requireUpdateFrom` / `requireCore` correctly

- `requireCore`: ensures the plugin only updates on a compatible core. For the v2 standard use `["2.0"]`.
- `requireUpdateFrom`: blocks a 1-click update from a version that is **too old** for your `update()`
  hook to migrate. Use `"1.0"` when you are confident you can migrate from any version; raise it (e.g.
  `"2.0"` for a 2.9 release) when you want to force users on the 1.x line to reinstall manually instead
  of updating straight through.

---

## 7. Step 4 — Install and test the plugin

1. Clear the Laravel cache to load the new routes/views/config:

   ```bash
   php artisan optimize:clear
   ```

   On success, the terminal shows lines like `... cleared successfully`.

2. Log in to the admin panel, go to **Plugins**, find your plugin and click **Install**.
3. After installing, try **enable/disable** and open the plugin's admin screen. If it renders correctly
   with no "view not found" or "class not found" error, you're good.
4. Check the UI in both **light and dark mode**.

---

## 8. Pre-release checklist

- [ ] `gp247.json`: `configKey` matches the folder name; `requireCore` = `["2.0"]`.
- [ ] `version` has been **increased** compared to the previous release (if this is an update).
- [ ] The admin screen uses Livewire + `<x-gp247::*>`; **no** jQuery/AdminLTE left.
- [ ] Every string rendered via `trans(...)` / `gp247_language_render(...)`, not hardcoded; both `vi` and `en` present.
- [ ] Site-owner-editable config lives in `admin_config` (not in `config.php`).
- [ ] `config.php` holds **defaults** only; the `*_effective_config()` / `*_save_config()` helper pair is used if there are settings.
- [ ] If the new release changes the DB: an `update($fromVersion)` hook is written, migrating safely and idempotently.
- [ ] No user-uploaded files are stored inside the plugin folder.
- [ ] `install()` / `uninstall()` create and clean up data symmetrically (no leftovers after uninstall).
- [ ] Ran `php artisan optimize:clear` and successfully tested install/uninstall.

---

## 9. Q&A

**Q1: Do I have to hand-write every plugin file?**
No. Run `php artisan gp247:make-plugin --name=YourPlugin --download=0` to scaffold the full v2-standard
skeleton, then just add your own logic.

**Q2: What is `configKey` and must it match the folder name?**
`configKey` is the plugin's unique identifier and **must exactly match the plugin folder name**. It is
also the key used to match releases on update, so don't change it after release.

**Q3: Why does the site owner lose everything when they edit `config.php` and then update?**
Because 1-click update **overwrites every file** of the plugin, including `config.php`. Any value the
site owner edits must be stored in the database (`admin_config`), not in a file. See Section 6.3.

**Q4: How do I store the site owner's config so it survives an update?**
Store it in the `admin_config` table with `code` = `<configKey>_config`, and at runtime overlay it on
the defaults from `config.php`. Use the sample `*_effective_config()` / `*_save_config()` functions in `function.php`.

**Q5: My new release adds a database column. Where do I handle it?**
In the `AppConfig::update($fromVersion)` hook. Check `$fromVersion` to run only the needed migration
step, and write it so running it multiple times causes no error. See Section 6.4.

**Q6: What happens if the update fails midway?**
The system automatically **rolls back** the plugin to the old version from the backup made before it
touched live files, so the site is never stuck in a half-updated state.

**Q7: My plugin is admin-only — do I need `Seo.php` and `FrontController.php`?**
No. `Seo.php` is only needed when the plugin has a **public page** that should contribute URLs to
`sitemap.xml`; `FrontController.php` is only needed for a storefront page. An admin-only plugin can skip both.

**Q8: Can I use jQuery or select2 on my plugin's admin screen?**
No. GP247 2.0 uses TailAdmin (Tailwind + Alpine + Livewire) and **does not load jQuery**. Use
Livewire/Alpine and the existing `<x-gp247::*>` components.

**Q9: What format should `version` be — `1.0.0` or `1.0`?**
Use a numeric semver like `1.0`, `1.1`, `2.0`. As long as each new release has a number **greater** than
the previous one (compared with `version_compare`), 1-click update will accept it.

**Q10: After updating, the admin still shows the old version — what do I do?**
Run `php artisan optimize:clear` to clear the route/view/config cache, then reload the page. This is the
most common issue, caused by Laravel keeping the old cache.

---

<sub>📅 **Last updated:** 2026-07-30 · ✍️ **Author:** GP247</sub>
