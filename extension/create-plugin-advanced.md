> 🌐 **Language:** [🇻🇳 Tiếng Việt](./create-plugin-advanced_vi.md) · 🇬🇧 English (current)

# Advanced plugins — several stores, secret keys, storefront, coupons

## Introduction

This document gathers the advanced topics of writing a GP247 plugin; each section stands on its own: per-store settings,
storing secrets, showing content on the storefront (own page, layout blocks, hooks), coupon / loyalty-point plugins,
changing product prices and adding an admin menu. Read it after [Creating a plugin](./create-plugin.md). Payment and shipping
plugins have their own guides: [payment](./create-payment-plugin.md), [shipping](./create-shipping-plugin.md).

---

## 1. Per-store settings

GP247 has two multi-store shapes: **multi-store** (one domain per store) and **marketplace** (one domain, many sellers). A
"per-store" plugin works in both, and nothing changes on a single-store site.

1. **Declare the scope** in `gp247.json`: `"storeScope": "store"` (on/off and settings differ per store) or `"global"`
   (default, one shared value).
2. **Read settings for the effective store**:

   ```php
   $value = gp247_config('MyPlugin_items_per_page', gp247_plugin_store_id());
   ```

   `gp247_plugin_store_id()` picks the right store (admin session, the store being checked out on a marketplace, the store of
   the storefront domain). With no store-specific value, `gp247_config()` falls back to the shared one. When you already know
   the store (e.g. an order's), pass it directly: `gp247_config('MyPlugin_x', $order->store_id)`.
3. **Turn on the store picker on the settings screen**: in the screen extending `ConfigForm`, make `storeScoped()` return
   `true`. The core builds the store picker, the "inherited / own" badges and the "use shared value" button; only fields a store
   really changes are saved for it.
4. **Let a store's own admins configure it** (optional): in `Provider.php`, inside the `gp247_extension_check_active` block, add
   the plugin's admin path segment:

   ```php
   config(['gp247-config.admin.store_scoped_segments' => array_values(array_unique(array_merge(
       (array) config('gp247-config.admin.store_scoped_segments', []), ['myplugin']
   )))]);
   ```

   Without it, only the root admin sets per-store values — which is what you want for settings that decide where money goes
   (payment accounts).

---

## 2. Secret keys

Every **credential** (API key, token, password, webhook signing secret) must be encrypted, never stored as plain text.

**On the settings screen (the common case):** create the row with `'security' => 1` and declare the field as `password` in
`fieldTypes()`. The core encrypts it on save, **never sends the saved value back to the browser** (the field only shows "a value
is set"; leaving it empty on save keeps it), and at store level it is inherited without being revealed. Read it with
`gp247_config('MyPlugin_api_key', $storeId)` — you get plain text back.

**In the plugin's own table:** use the core's `Secret` cast, a `TEXT` column, and register the column so the key-rotation tool
covers it:

```php
// In the model
protected $casts = ['access_token' => \GP247\Core\Casts\Secret::class];

// In Provider.php (inside gp247_extension_check_active) — table name WITHOUT the prefix
config(['gp247-config.security.encrypted_columns.my_plugin_account' => ['access_token']]);
```

An encrypted column **cannot be searched / filtered** — add a separate hash column if you need lookups.

**Do not:** encrypt by hand with `Crypt` or `base64`; keep secrets in `config.php`, a `.env` shipped with the plugin, a JSON row
or a plain column; write secrets to logs or return them from an API.

---

## 3. Showing content on the storefront

A plugin **never edits or ships template files** (templates belong to other people). There are three ways to plug into the
storefront; `gp247/front` must be installed:

| Way | When to use it | Register in `Provider.php` |
|---|---|---|
| **Own page** | The plugin has pages of its own (list, detail…) | Routes in the `GP247_FRONT_MIDDLEWARE` group + `Controllers/FrontController.php` |
| **Layout block** | The store owner places the block where they want | `gp247-config.front.layout_block_views` |
| **Hook** | Output at a fixed spot of a shop page | `gp247-config.front.plugin_hooks.<hook name>` |

**Own page** — routes follow this pattern (the skeleton generates one in `Route.php`):

```php
Route::group(['middleware' => GP247_FRONT_MIDDLEWARE, 'prefix' => 'plugin/myplugin'], function () {
    Route::get('/', [\App\GP247\Plugins\MyPlugin\Controllers\FrontController::class, 'index'])->name('myplugin.index');
});
```

The page's view uses the running template's layout: `@extends($GP247TemplatePath.'.layout')` and
`@section('block_main_content_center')`. To add the page to `sitemap.xml`, keep `Seo.php` (its `sitemapUrls($storeId)` returns
each URL with `loc` and `alias`) and its registration in `Provider.php`. To let the store owner target layout blocks at this
page, register a page type:

```php
$layoutPage = config('gp247-config.front.layout_page', []);
$layoutPage['myplugin_index'] = 'Plugins/MyPlugin::lang.layout_page_index'; // a language KEY, not a rendered text
config(['gp247-config.front.layout_page' => $layoutPage]);
```

and pass `'layout_page' => 'myplugin_index'` when returning the view.

**Layout block** — the block appears on the **Layout Block** screen for every template:

```php
$blocks = config('gp247-config.front.layout_block_views', []);
$blocks['myplugin_banner'] = 'Plugins/MyPlugin::blocks.myplugin_banner'; // Views/blocks/myplugin_banner.blade.php
config(['gp247-config.front.layout_block_views' => $blocks]);
```

Prefix the block name with the plugin name to avoid clashes; a template with a file of the same name takes precedence.

**Hooks** — available today: `shop_product_detail_bottom` (receives `['product' => …]`) and `shop_order_detail_bottom` (receives
`['order' => …]`). Register an **array callable**, not a closure (a closure makes `php artisan config:cache` fail):

```php
$hooks = config('gp247-config.front.plugin_hooks', []);
$hooks['shop_order_detail_bottom'][] = [
    'key' => 'MyPlugin',
    'callback' => [\App\GP247\Plugins\MyPlugin\Hooks::class, 'orderDetailBottom'],
];
config(['gp247-config.front.plugin_hooks' => $hooks]);
```

```php
namespace App\GP247\Plugins\MyPlugin;

class Hooks
{
    public static function orderDetailBottom(array $data): string
    {
        return view('Plugins/MyPlugin::hooks.order_detail_bottom', $data)->render();
    }
}
```

A failing hook only loses its own output (it is logged); the rest of the page still renders.

---

## 4. Coupon / loyalty-point plugins (total-method)

`configCode: "Promotion"` (the old `"Total"` is still accepted). Besides `getInfo()`, `AppConfig` must implement
`GP247\Shop\Front\Contracts\CheckoutTotalMethod` — otherwise checkout hides the plugin:

```php
public function checkoutApply(array $payload): array; // validate + apply; return ['error' => 0|1, 'msg' => …]
public function checkoutRemove(): void;               // clear what checkoutApply stored
public function checkoutView(): ?string;              // view key of the input fragment, or null
```

The view fragment receives `$pluginKey`, `$plugin`, `$message`, and calls the checkout's `applyTotal` / `removeTotal` actions with
the `totalPayload` field (e.g. `wire:model="totalPayload.{{ $pluginKey }}.code"`). Use only the template's existing UI classes
(`input`, `btn-primary`…). Reference plugin: `ShopDiscount`.

**Money rule:** the store owner enters amounts (discount, minimum order) in the **base** currency; any amount the plugin returns
must be in the **display** currency — convert with `gp247_currency_value($amount)` and compare thresholds with the cart subtotal
(already in the display currency).

---

## 5. Changing product prices

A plugin that changes prices (time-limited sales, member prices) adds a function to `gp247-config.shop.price_resolvers` from
`Provider.php` instead of editing prices in the database. Read the note right above the resolver loop in
`GP247\Shop\Models\ShopProduct` for what the function receives and returns. Register an array callable `[Class::class, 'method']`.

---

## 6. Admin menu

Add an entry under an existing group (found by `key`, e.g. `ADMIN_SHOP_PAYMENT`, `ADMIN_SHOP_SHIPPING`), skip it when the group is
missing, and never add it twice. Call it from `install()` and `update()`; delete it in `uninstall()`:

```php
use GP247\Core\Models\AdminMenu;

private const MENU_URI = 'admin::myplugin'; // admin route prefix after GP247_ADMIN_PREFIX

private function ensureMenu(): void
{
    $parent = AdminMenu::where('key', 'ADMIN_SHOP_PAYMENT')->first();
    if ($parent && !AdminMenu::where('uri', self::MENU_URI)->exists()) {
        AdminMenu::insert([
            'parent_id' => $parent->id, 'sort' => 40, 'title' => 'My plugin',
            'icon' => 'fas fa-puzzle-piece', 'uri' => self::MENU_URI, 'key' => null, 'type' => 0,
        ]);
    }
}

// in uninstall():
AdminMenu::where('uri', self::MENU_URI)->delete();
```

---

## Q&A

**Q1: Does a "per-store" plugin behave differently on a single-store site?**

→ No. On a single-store site, `gp247_plugin_store_id()` points to the root store and every value comes from the shared settings.

**Q2: A sub-store's admin cannot open the plugin's settings screen?**

→ The admin path segment has not been added to `store_scoped_segments` (section 1, step 4).

**Q3: After saving a secret, the field is empty again?**

→ By design: a `password` field never sends the saved value to the browser. Leaving it empty on save keeps the old secret.

**Q4: I want to show something on the product page without editing the template?**

→ Use the `shop_product_detail_bottom` hook (section 3) with an array callable.

**Q5: `php artisan config:cache` reports "not serializable" after installing a plugin?**

→ The plugin registered a closure in the config (hook, resolver). Switch to an array callable `[Class::class, 'method']`.

**Q6: My coupon plugin does not show at checkout?**

→ `AppConfig` does not implement `CheckoutTotalMethod`, or `configCode` is not `"Promotion"`.

**Q7: The discount amount is wrong when the customer switches currency?**

→ The plugin returns the base-currency amount. Convert to the display currency with `gp247_currency_value()` before returning.

**Q8: The customer's template has a block file with the same name as my plugin's?**

→ The template's file takes precedence. Prefix your block name with the plugin name (e.g. `myplugin_banner`) to avoid clashes.

---

<sub>📅 **Last updated:** 2026-10-18 · ✍️ **Author:** GP247</sub>
