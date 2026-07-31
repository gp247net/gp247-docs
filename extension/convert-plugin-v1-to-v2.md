> 🌐 **Language:** [🇻🇳 Tiếng Việt](./convert-plugin-v1-to-v2_vi.md) · 🇬🇧 English (current)

# Guide: Converting a GP247 Plugin from v1 (Core 1.x) to v2 (Core 2.0)

## Introduction

This document explains how to **upgrade a GP247 plugin written for the old version
(gp247/core 1.x) to the new plugin standard (gp247/core 2.0)**. It is meant for developers
or administrators who already have a plugin running on GP247 1.x and want it to run on
GP247 2.0. After reading it, you will know **which files to change and what to change them
into**, and be able to follow each step without needing a deep understanding of the whole system.

> ⚠️ **The most important thing to know first:** GP247 2.0 has **replaced the entire UI layer**.
> Version 1.x used **AdminLTE (Bootstrap + jQuery + pjax)**; version 2.0 uses **TailAdmin
> (Tailwind + Alpine + Livewire)**. As a result, a 1.x plugin **will not run as-is** on 2.0 —
> the old layout has been deleted and jQuery is no longer loaded. The conversion is mostly
> about **reworking the UI layer and a few configuration files**, while your logic/model code
> stays almost untouched.

---

## 1. How do the two plugin formats differ?

GP247 keeps a "plugin format" template inside the core. Here is a quick comparison of the
v1 and v2 templates:

| Item | Plugin v1 (Core 1.x) | Plugin v2 (Core 2.0) |
|---|---|---|
| Admin UI framework | AdminLTE (Bootstrap + jQuery) | TailAdmin (Tailwind + Alpine) |
| Admin page navigation | pjax | No pjax (Livewire re-render) |
| Admin view master layout | `@extends('gp247-core::layout')` | `@extends('gp247-admin::layouts.admin')` |
| Dynamic interaction | jQuery + jQuery plugins (select2, daterangepicker…) | Livewire 4 + Alpine |
| New-style admin screen | *(none)* | Livewire component `Livewire/AdminLivewire.php` |
| SEO sitemap integration | *(none)* | `Seo.php` file + registration in `Provider.php` |
| LayoutBlock page-type registration | *(none)* | Register `token => lang-key` in `Provider.php` (if the plugin has its own public page) |
| `gp247.json` → `requireCore` | `["1.2"]` | `["2.0"]` |
| `gp247.json` → `requireUpdateFrom` | *(none)* | `"1.0"` (1-click update condition) |

**Conclusion:** your business logic (Model, data processing, install/uninstall) barely changes.
What changes is the **UI** and **a few configuration declarations**.

---

## 2. The files you will touch

These are the plugin files to review during the conversion. The "Required?" column indicates
which changes are the minimum for the plugin to run, and which are recommended to fully adopt
the new standard.

| File | What to do | Required? |
|---|---|---|
| `gp247.json` | Change `requireCore` to `["2.0"]`, add `requireUpdateFrom` | ✅ Required |
| `Views/Admin.blade.php` | Switch the master layout to TailAdmin, remove jQuery/AdminLTE | ✅ Required |
| `AppConfig.php` | Change the error message in `disable()` to the multilingual helper | ✅ Required |
| `Livewire/AdminLivewire.php` | **Create new** — Livewire-style admin screen | 🟡 Recommended |
| `Views/livewire.blade.php` | **Create new** — view for the Livewire component | 🟡 Recommended |
| `Route.php` | Add the Livewire route (guarded by `class_exists`) | 🟡 Recommended |
| `Seo.php` | **Create new** — if the plugin has public pages for the sitemap | ⚪ Optional |
| `Provider.php` | Add the sitemap registration block (if you use `Seo.php`) | ⚪ Optional |
| `Provider.php` | Add the LayoutBlock page-type registration block (if the plugin has its own public page) | ⚪ Optional |

> Quick glossary: **Livewire** is a Laravel technology that lets you build dynamic UIs (button
> clicks, partial page reloads) **without writing JavaScript/jQuery**. GP247 2.0 uses Livewire
> instead of the old version's jQuery + pjax.

---

## 3. Conversion steps (do them in order, top to bottom)

### Step 1 — Back up and create a separate working copy

1. Copy the entire plugin folder to a safe place (for example, zip it into a `.zip` file), so
   that if something goes wrong you still have the original to fall back on.
2. If your plugin uses Git, create a new branch to work on:

   ```bash
   git checkout -b upgrade-to-v2
   ```

   If successful, the terminal shows a line like `Switched to a new branch 'upgrade-to-v2'`.

### Step 2 — Update `gp247.json`

Open the `gp247.json` file in the plugin folder. Find the `requireCore` line and change it,
and also **add** the `requireUpdateFrom` line.

**Before (v1):**

```json
{
    "version": "1.0",
    "requireCore": ["1.2"],
    "requirePackages": [],
    "requireExtensions": []
}
```

**After (v2):**

```json
{
    "version": "1.0",
    "requireCore": ["2.0"],
    "requireUpdateFrom": "1.0",
    "requirePackages": [],
    "requireExtensions": []
}
```

Meaning of the two lines:
- `requireCore`: the GP247 core version this plugin is valid for. Set it to `["2.0"]` to declare the plugin targets Core 2.0.
- `requireUpdateFrom`: the **minimum** currently-installed version allowed to 1-click update to
  this release. Leaving it at `"1.0"` is safe (practically no restriction). Only raise this
  number when you ship a major release that cannot auto-migrate from older lines.

### Step 3 — Fix the admin view layout (the most important change)

This is the change that makes an old plugin "break" if skipped, because the `gp247-core::layout`
layout has been **removed in 2.0**. Open the `Views/Admin.blade.php` file.

1. Find the first `@extends(...)` line and change the layout name:

   **Before (v1):**
   ```blade
   @extends('gp247-core::layout')
   ```

   **After (v2):**
   ```blade
   @extends('gp247-admin::layouts.admin')
   ```

2. Keep the `@section('main') ... @endsection` structure as is. You can keep the inner content
   as before for now, then refine it later in Step 4.

The minimal resulting file looks like this:

```blade
@extends('gp247-admin::layouts.admin')

@section('main')
<div class="row">
      <div class="col-md-12">
            Your-content!
      </div>
</div>
@endsection

@push('styles')
      {{-- style css --}}
@endpush

@push('scripts')
      {{-- script --}}
@endpush
```

### Step 4 — Remove jQuery and AdminLTE widgets

GP247 2.0 **does not load jQuery**. Every piece of code below in the plugin's views/assets will
**not run** and must be replaced:

- `$(...)`, `$.pjax(...)`, `$.ajax(...)` — jQuery syntax.
- `select2`, `daterangepicker`, `datetimepicker`, `bootstrap modal` — old jQuery plugins.
- Any header or script related to `x-pjax`.

How to replace them:
- **Date/time pickers** → use **flatpickr** (already included in the TailAdmin stack), or the
  matching `<x-gp247::*>` component if one exists.
- **Tables/buttons/dynamic interaction** → use **Livewire + Alpine** (see Step 5).
- **Displayed text** → always render via `gp247_language_render('...')`, never hardcode.

> If your plugin only displays static data (no dynamic buttons, no jQuery), then it already
> works after Step 3 — Steps 4 and 5 are only needed when the plugin has dynamic interaction.

### Step 5 — (Recommended) Add a Livewire-style admin screen

This is the new UI standard for v2. If you want the plugin to follow the standard fully
(2-layer RBAC, consistent layout), create **2 new files**.

1. Create `Livewire/AdminLivewire.php` with the following content (replace `Extension_Key` with
   your plugin name — exactly the `configKey` in `gp247.json`):

   ```php
   <?php
   #App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire.php

   namespace App\GP247\Plugins\Extension_Key\Livewire;

   use GP247\Core\AdminShell\Infrastructure\GP247AdminComponent;

   class AdminLivewire extends GP247AdminComponent
   {
       protected ?string $permission = null;

       public function render()
       {
           return view('Plugins/Extension_Key::livewire')
               ->layout('gp247-admin::layouts.admin', [
                   'title' => trans('Plugins/Extension_Key::lang.title'),
               ]);
       }
   }
   ```

   - Extending `GP247AdminComponent` gives the plugin **automatic** Layer-2 RBAC (permission
     check on screen open), toast notifications, and the shared admin layout — exactly like the
     core's own screens.
   - `$permission = null` means the permission is inferred automatically from the component name.

2. Create `Views/livewire.blade.php` for that component's view:

   ```blade
   <div class="space-y-5">
       <x-gp247::card :title="trans('Plugins/Extension_Key::lang.title')">
           <p class="text-sm text-gray-600 dark:text-gray-300">
               {{ trans('Plugins/Extension_Key::lang.title') }} — Your content here!
           </p>
       </x-gp247::card>
   </div>
   ```

   - `<x-gp247::card>` is a shared TailAdmin component — you should prefer the existing
     `<x-gp247::*>` components over writing raw HTML.
   - The `dark:text-gray-300` class keeps the UI looking good in dark mode as well.

### Step 6 — Add the Livewire route in `Route.php`

Open `Route.php`. Inside the admin `Route::group([...], function () { ... })` block, **add** the
Livewire route registration. This block is guarded by `class_exists`, so it is **safe** even when
the plugin does not yet have the Livewire file (it causes no error).

**Before (v1):**

```php
function () {
    Route::get('/', 'AdminController@index')
    ->name('admin_ExtensionUrlKey.index');
}
```

**After (v2):**

```php
function () {
    Route::get('/', 'AdminController@index')
    ->name('admin_ExtensionUrlKey.index');

    // Livewire route, registered alongside the legacy controller so old plugins keep working
    if (class_exists(\App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire::class)) {
        Route::get('/livewire', \App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire::class)
        ->name('admin_ExtensionUrlKey.livewire');
    }
}
```

> Note: the old controller route (`AdminController@index`) is **kept unchanged**. This keeps the
> plugin backward-compatible — it has both the old screen and the new Livewire screen.

### Step 7 — Fix `AppConfig.php` (multilingual error message)

In `AppConfig.php`, find the `disable()` method. The v1 version hardcodes the English string
`'Error disable'`; the v2 version uses the multilingual helper.

**Before (v1):**

```php
if (!$process) {
    $return = ['error' => 1, 'msg' => 'Error disable'];
}
```

**After (v2):**

```php
if (!$process) {
    $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Disable'])];
}
```

In addition, you may change the comment at the top of the file from `Plugin format 1.0` to
`Plugin format 2.0` for clarity. The other methods (`install`, `uninstall`, `enable`, `getInfo`…)
stay unchanged.

### Step 8 — (Optional) Add SEO sitemap integration

Only do this step if your plugin has a **public page** that you want to appear in the website's
`sitemap.xml`.

1. Create a `Seo.php` file in the plugin folder:

   ```php
   <?php

   namespace App\GP247\Plugins\Extension_Key;

   class Seo
   {
       public static function sitemapUrls($storeId): array
       {
           // Return the list of the plugin's public URLs for the sitemap.
           // When there are none, returning an empty array is safe.
           return [];
       }
   }
   ```

2. Open `Provider.php` and **add** the following block inside the
   `if (gp247_extension_check_active(...))` section:

   ```php
   if (class_exists('GP247\Front\Controllers\RootFrontController')) {
       $sitemapProviders = config('gp247-config.front.seo_sitemap_providers', []);
       $sitemapProviders[] = [
           'key' => $config['configKey'],
           'label' => $config['name'],
           'callback' => [\App\GP247\Plugins\Extension_Key\Seo::class, 'sitemapUrls'],
       ];
       config(['gp247-config.front.seo_sitemap_providers' => $sitemapProviders]);
   }
   ```

   This block is guarded by `class_exists`, so if the website does **not** have `gp247/front`
   installed, the plugin still installs normally (no error).

### Step 8b — (Optional) Register a page-type for LayoutBlock

Only do this step if your plugin has its **own public (storefront) page(s)** (for example the
plugin's list/detail page) and you want an admin to be able to **attach LayoutBlock blocks**
(banner, HTML, view…) that display on those pages.

Background: each storefront page emits a "page-type" through the `$layout_page` variable at render
(your controller passes `'layout_page' => 'myplugin_index'` to `view()`). The admin "Layout block"
screen can only list page-types that are **registered** into the
`config('gp247-config.front.layout_page')` registry. So a plugin with its own page must register
its page-type — the same way a plugin registers its sitemap in Step 8.

1. Make sure your plugin's controller passes `layout_page` when rendering the public page, e.g.:

   ```php
   return view($view, [
       // ... other data ...
       'layout_page' => 'myplugin_index',   // this page's page-type token
   ]);
   ```

2. Open `Provider.php` and **add** the following block inside the
   `if (gp247_extension_check_active(...))` section:

   ```php
   if (class_exists('GP247\Front\Controllers\RootFrontController')) {
       $layoutPage = config('gp247-config.front.layout_page', []);
       // Store the i18n KEY (NOT a pre-rendered string) — the admin renders it in its current locale.
       $layoutPage['myplugin_index'] = $extensionPath.'::lang.layout_block_page.myplugin_index';
       config(['gp247-config.front.layout_page' => $layoutPage]);
   }
   ```

   - The `token` (`myplugin_index`) **must match** the `$layout_page` value your controller emits,
     otherwise a block selected for it will never display.
   - The value is a **language key** (pointing to the plugin's `Lang` files), not a pre-translated
     string — so the admin dropdown renders it in the viewer's current locale.
   - The block is guarded by `class_exists`, so a website without `gp247/front` simply skips it and
     the plugin still installs normally.

3. Add the matching language lines to the plugin's `Lang/en/lang.php` and `Lang/vi/lang.php`
   (the `layout_block_page` array), e.g. `'myplugin_index' => 'Plugin listing page'`.

> The `News` plugin (`app/GP247/Plugins/News/Provider.php`) is a reference example: it registers
> `news_index`/`news_category`/`news_detail` exactly this way.

> Note the difference from a **template**: if you are building a *template* (theme) rather than a
> plugin, you do **not** register page-types — a template only renders based on the `$layout_page`
> the controller already emits; it does not define new page-types.

### Step 8c — (Optional) Total-method plugin (coupon/point) shown at checkout

Do this only when the plugin is a **total-method** (`configCode: "Total"` — e.g. a coupon or a
loyalty-point plugin) that must show an input at checkout. In v1 the checkout template included the
plugin's `Views/render.blade.php` + `Views/script.blade.php` (jQuery) directly. GP247 2.0 replaced
that with a **contract** so any template (default or custom) works with any total-method plugin
without template↔plugin coupling. Instead of the old jQuery render/script, do:

1. **Implement the contract on `AppConfig.php`.** Add `implements CheckoutTotalMethod`
   (`use GP247\Shop\Front\Contracts\CheckoutTotalMethod;`) and three methods — reuse your existing
   validation/session logic:

   ```php
   public function checkoutApply(array $payload): array   // validate + set session('totalMethod')[key]; return ['error'=>0|1,'msg'=>...]
   public function checkoutRemove(): void                 // unset session('totalMethod')[key]
   public function checkoutView(): ?string                // e.g. return $this->appPath.'::checkout'
   ```

2. **Add the fragment view** named by `checkoutView()` (e.g. `Views/checkout.blade.php`). It renders
   inside the checkout Livewire component, so bind with `wire:` — no jQuery, no fetch:

   ```blade
   <input type="text" class="input" wire:model="totalPayload.{{ $pluginKey }}.code">
   <button type="button" wire:click="applyTotal('{{ $pluginKey }}')">{{ gp247_language_render('cart.apply') }}</button>
   ```

   Use only **storefront UI tokens the template already ships** (`input`, `btn-primary`, `btn-ghost`,
   `card`, `text-ink-*`…). A brand-new Tailwind class won't exist in the template's pre-built CSS and
   will silently have no style.

3. The data layer is unchanged: `session('totalMethod')` → `getInfo()` → `ShopOrderTotal` →
   `addOrder()`. Keep your `getInfo()` as-is.

The checkout **discovers** the plugin automatically (`code='total'` + implements the interface) and
renders your fragment in the total-method zone. A total plugin that does **not** implement the
interface is hidden from the 2.0 checkout (with a logged warning) until you upgrade it. Your old
`discount.process`/`discount.remove` HTTP endpoints may stay for headless/API use.

> Template authors: to support total-method plugins, a custom checkout view only needs two includes
> at the confirm step: `@include('gp247-shop-front::partials.checkout_total_methods')` and
> `@include('gp247-shop-front::partials.order_totals')`.

---

### Step 9 — Verify

1. Clear Laravel's cache so the new routes/views/config are reloaded:

   ```bash
   php artisan optimize:clear
   ```

   If successful, the terminal shows lines like `cache ... cleared successfully`.

2. Go to the admin area and open the plugin's screen. If you added Livewire (Steps 5–6), try
   opening the path ending in `/livewire`. It works if the screen displays correctly with no
   "view not found" or "class not found" error.
3. Try enabling/disabling the plugin in the admin to make sure `AppConfig.php` still runs.

---

## 4. Checklist before calling it "done"

- [ ] `gp247.json`: `requireCore` = `["2.0"]`, `requireUpdateFrom` added.
- [ ] `Views/Admin.blade.php`: layout changed to `gp247-admin::layouts.admin`.
- [ ] No `$(...)`, pjax, or jQuery widgets (select2/daterangepicker…) left in views/assets.
- [ ] Displayed text rendered via `gp247_language_render(...)`, not hardcoded.
- [ ] `AppConfig.php`: `disable()` uses `gp247_language_render(...)` instead of `'Error disable'`.
- [ ] (If using Livewire) `Livewire/AdminLivewire.php` + `Views/livewire.blade.php` exist, route added.
- [ ] (If it has public pages) `Seo.php` + registration block in `Provider.php` exist.
- [ ] (If it has its own public page needing LayoutBlock) Registered the page-type (`token => lang-key`) into `config('gp247-config.front.layout_page')` in `Provider.php`, with the token matching the `$layout_page` the controller emits.
- [ ] (If it is a total-method plugin — coupon/point) `AppConfig` implements `CheckoutTotalMethod` and a `checkoutView()` fragment exists, using only the template's existing UI tokens.
- [ ] Ran `php artisan optimize:clear` and opened the admin screen successfully.
- [ ] The UI displays correctly in both light mode and dark mode.

---

## 5. Q&A

**Q1: Will my 1.x plugin run on 2.0 with no changes?**
No, unless it is extremely simple. Because the `gp247-core::layout` layout was removed and
jQuery is no longer loaded, a plugin using the old admin UI will break. At minimum you must do
Steps 2, 3, and 7.

**Q2: Do I have to switch to Livewire?**
No, it is not mandatory. If the plugin's admin screen only displays static content, you only need
to change the layout (Step 3) and it will run. Livewire (Steps 5–6) is only needed when the
plugin has dynamic interaction (previously done with jQuery).

**Q3: Do I have to rewrite my logic and Models?**
No. GP247 2.0's database schema and logic layer stay the same as 1.x. The conversion only touches
the UI layer and a few configuration declaration files.

**Q4: I open the admin screen and get "View [gp247-core::layout] not found" — why?**
Because you have not done Step 3. That layout was removed in 2.0. Change
`@extends('gp247-core::layout')` to `@extends('gp247-admin::layouts.admin')`.

**Q5: I finished editing the route/view but the admin still shows the old version — what do I do?**
Run `php artisan optimize:clear` to clear the route/view/config cache, then reload the page. This
is the most common issue, caused by Laravel still holding the old cache.

**Q6: What value should `requireUpdateFrom` be?**
Leaving it at `"1.0"` is safest (it barely blocks any updates). Only raise it (for example
`"2.0"`) when you ship a major release whose `update()` hook cannot migrate from older lines.

**Q7: Should I create a `Seo.php` file for every plugin?**
No. Create it only when the plugin has a public page that should contribute URLs to
`sitemap.xml`. If the plugin is admin-only, skip Step 8.

**Q8: Will the sitemap registration block in `Provider.php` cause an error if the website does not have gp247/front?**
No. That block is wrapped in `class_exists('GP247\Front\Controllers\RootFrontController')`, so if
`gp247/front` is not installed it is simply skipped, and the plugin installs normally.

**Q9: Why is the old controller route kept alongside the Livewire route?**
For backward compatibility. Keeping both means a plugin still running the old screen does not
break abruptly, while you can gradually migrate to the new Livewire screen.

**Q10: What is the fastest way to create a brand-new v2 plugin (instead of editing from v1)?**
Run the artisan command to scaffold a plugin using the v2 template, then copy your old logic in:

```bash
php artisan gp247:make-plugin --name=YourPluginName --download=0
```

This command generates the full v2 standard structure (already including Livewire, Seo, and the
new layout) so you do not have to create each file by hand.

---

<sub>📅 **Last updated:** 2026-07-29 · ✍️ **Author:** GP247</sub>
