> 🌐 **Language:** [🇻🇳 Tiếng Việt](./create-api_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./create-api_ja.md)

# How to build an API correctly on GP247

## Introduction

This document explains **how to build (write) a new API endpoint the GP247 way** — for **developers**
who want to extend a GP247 site with their own APIs (e.g. feeding a mobile app, an external management
tool, or a third-party integration). By the end you will know **where to put the code, how to declare
the route, and how controllers/middleware/authentication fit together**, with a complete copy-and-run
example. If you only want to **consume** existing APIs (not write new ones), read
[GP247 API Introduction](./api-introduction.md) first.

> ℹ️ **GP247's golden rule:** *never modify core code* (`vendor/gp247/*`). To add your own API for a
> specific site, do it in a **plugin** (`app/GP247/Plugins/`) or via the **override** mechanism under
> `app/GP247/`. Only add directly to `core`/`front`/`shop` when you are the primary maintainer of that
> package.

---

## 1. Understand the API architecture first

A GP247 API is assembled from a few existing pieces. Understanding them lets you plug your code in at
the right place.

### 1.1. The two groups and their prefixes

Every new endpoint goes under the **v2** prefixes:

| Group | Constant used in routes | Value | Used for |
| --- | --- | --- | --- |
| Core (Admin) | `GP247_API_V2_CORE_PREFIX` | `api/v2/core` | System administration (requires an admin token) |
| Front (Storefront) | `GP247_API_V2_FRONT_PREFIX` | `api/v2/front` | Storefront data & customer accounts |

Both constants are fixed in `vendor/gp247/core/src/Library/Const.php`. **Always use the constants** in
routes (never a hand-typed `"api/v2/core"` string) so that if the prefix ever changes, your code follows
automatically.

> ⚠️ **REST API v1 is frozen.** Since gp247/core, gp247/front, gp247/shop 3.3.0, API v1
> (`GP247_API_CORE_PREFIX` = `api/core`, `GP247_API_FRONT_PREFIX` = `api/front`) gets no new features — security
> fixes only; fresh installs since gp247/core 3.3.1 have v1 switched off. Existing v1 endpoints keep working, but
> **do not add new endpoints there**. See [API v1](./api-v1.md).

### 1.2. Enable API mode

The API **only works when the `GP247_API_MODE` flag is on**. Check the site's `.env` file:

```dotenv
GP247_API_MODE=true
```

If this flag is off, none of the API routes are loaded at all (see `vendor/gp247/core/src/routes.php`).

With the flag on, the site owner can still switch each group on or off on the API management screen (the
`api.enabled` layer, see [API Introduction, section 5](./api-introduction.md)). Routes under `api/v2/front` follow
the **"v2 · Public & customer API (front)"** switch, routes under `api/v2/core` the **"v2 · Admin API (core)"**
switch — routes added by a plugin included. A group that is off answers `404` with `code: api_disabled`.

### 1.3. How API routes are auto-loaded

When `GP247_API_MODE=true`, each package automatically **scans and loads** every file in the following folders
and wraps them all in the `GP247_API_MIDDLEWARE` group:

- `vendor/gp247/core/src/Routes/Api/v2/*.php`
- `vendor/gp247/shop/src/Routes/Api/v2/*.php`
- `vendor/gp247/front/src/Routes/Api/v2/*.php`

(The v1 files live in `Routes/Api/*.php` of the same package — kept only for the existing endpoints.)

`GP247_API_MIDDLEWARE = ['api', 'api.request_id', 'json.response', 'api.enabled', 'api.extend']`, where `api.extend` is the GP247 middleware group defined
in `config('gp247-config.api.middleware')`:

| Order | Middleware | Role |
| --- | --- | --- |
| 1 | `json.response` | Forces every response to be **JSON** (sets header `Accept: application/json`). Also part of `GP247_API_MIDDLEWARE` and runs **before every other middleware** (`auth:*` included), so it stays even when a published config lacks this line |
| 2 | `api.connection` | Optional application-level gate (`apiconnection`/`apikey`) — toggled by config |
| 3 | `throttle:gp247-api` | Rate limit, by default **1000 requests/minute** per IP (`GP247_API_THROTTLE`) |

> ⚠️ **This order is fixed — do not change it.** It is a convention stated explicitly in the config.
>
> ℹ️ **Available since:** gp247/core 3.6.0 — `json.response` runs before `auth:*`: a missing token without
> `Accept: application/json` still gets a JSON `401` (before: `500`).
>
> 👉 **Important consequence for plugins:** routes placed in the `Routes/Api/v2/` folder of core/shop/front
> get these three layers **automatically**. But routes you add from a **plugin** are loaded elsewhere
> (section 4), so they do **not** get them automatically — you must wrap them yourself, or (since 3.3.0)
> register them through `ApiRouteRegistrar` (section 4.1).

> ℹ️ **Available since:** gp247/core, gp247/front, gp247/shop 3.3.0 — each package registers its own API routes
> (including the `Routes/Api/v2/` folder) through `GP247\Core\Api\Support\ApiRouteRegistrar`; a package's routes
> only exist once the package has been installed into the database. Paths and route names are unchanged.

### 1.4. Authentication with Laravel Sanctum

GP247 uses **Sanctum** (Bearer tokens). There are two separate "guards" (login gates):

| Guard | Provider | For | Where the token is issued |
| --- | --- | --- | --- |
| `admin-api` | `admin_provider` | Administrators | `api/v2/core/login` |
| `customer-api` | `customer_provider` | Customers/members | `api/v2/front/login` |

Each token also carries a **scope/ability** that limits what it can do. Scopes come from
`config('gp247-config.api.auth')` (overridable via `.env`):

| Config key | Default value | Meaning |
| --- | --- | --- |
| `api_scope_admin` | `admin` | Regular admin |
| `api_scope_admin_supper` | `admin-supper` | Super admin (`administrator` role) |
| `api_scope_user` | `user` | Activated member |
| `api_scope_user_guest` | `user-guest` | Non-activated member/guest |

To protect a route that requires login, use this pair of middleware:

```php
'middleware' => [
    'auth:admin-api',                                // or 'auth:customer-api' for front APIs
    \GP247\Core\Api\Support\ApiAbilities::admin(),   // or ApiAbilities::customer(): the allowed scopes
]
```

### 1.5. The `gp247_namespace()` helper — the override hook

In GP247 routes, a controller is **not** referenced directly as `Controller::class`; it is wrapped in
`gp247_namespace()`:

```php
$catalog = gp247_namespace(\GP247\Shop\Api\V2\CatalogController::class);
Route::get('product/list', $catalog.'@productList');
```

This helper checks: if an override file exists at `app/GP247/...`, it uses the app's version, otherwise
it uses the vendor version. This is how end users can **override a controller without touching vendor**.
Always use this helper when pointing a route at a controller from a `GP247\...` package.

---

## 2. Naming conventions & response format

Follow these conventions so your new API stays consistent with the rest of the system.

- **Group by resource**: each data group is one `Route::group(['prefix' => 'product'], ...)`.
- **Two standard actions**: `list` (collection) and `detail/{id}` (a single record).
- **Controller method names**: `<resource>List` and `<resource>Detail`
  (e.g. `productList`, `productDetail` — like the v2 controllers of the shop package).
- **Always return JSON** through the `GP247\Core\Api\V2\ApiQuery` helper (it returns a `JsonResponse`), or
  `response()->json(...)` for a response that is not a record.
- **On success**: wrap the data in `data` — `{"data": …}` for one record (`ApiQuery::one()`), and
  `{"data": [...], "meta": {...}, "links": {...}}` for a list (`ApiQuery::page()`).
- **On failure/not found**: use `ApiErrorResponder` so you get a **meaningful HTTP status code** and the
  shared error body `{"error": 1, "msg": "…", "code": "…"}` (code table in
  [API Introduction](./api-introduction.md), section 6). `ApiQuery::one()` returns `404` by itself when there is
  no record:

  ```php
  use GP247\Core\Api\Support\ApiErrorResponder;

  if (!$product) {
      return ApiErrorResponder::notFound();          // 404, code: not_found
  }
  ```

  > ℹ️ **Available since:** gp247/core 3.2.3 — authentication, authorization, validation and rate-limit
  > errors under `api/*` are converted to this shape automatically; you only need to return
  > `notFound()`/`forbidden()` yourself when the controller makes that decision. The old pattern
  > `response()->json([...], 200)` for errors is **no longer used**.
- **Return only the fields you need**: the `$toArray` function you pass to `ApiQuery` declares exactly the fields
  returned, instead of returning the whole model — new columns added to the table will not leak into the API
  automatically. For public data, build the base query with `GP247\Core\Api\Support\ApiVisibility::visible(...)` so
  the API returns exactly the records the website shows.

- **Pagination, filtering, sorting**: use `ApiQuery::page($query, $request, $toArray, $filters, $sorts, $search)`
  instead of `->get()` when returning a list. The client drives it with `page[number]`, `page[size]` (default 20,
  max 100), `filter[<name>]`, `sort=<name>` / `sort=-<name>` and `q` (search). Only the filter/sort names you
  declare are accepted — an unknown name returns `422` (`validation_failed`); client input never becomes a column
  name in the query. Multilingual content: get the language with `ApiQuery::lang($request)` and the description
  fields with `ApiQuery::described(...)`.

---

## 3. Example A — Add an endpoint to a package you maintain (core/front/shop)

Use this when you are the **package maintainer** (editing directly in `vendor/gp247/<package>`), e.g.
adding a public front API `api/v2/front/review/list`. Follow the exact order:

1. **Create the controller** under `vendor/gp247/<package>/src/Api/V2/...`. For front data, extend
   `RootFrontController`; for admin data, extend `RootAdminController`.

   Create `src/Api/V2/ReviewController.php`:

   ```php
   <?php

   namespace GP247\Shop\Api\V2;

   use GP247\Core\Api\V2\ApiQuery;
   use GP247\Front\Controllers\RootFrontController;
   use GP247\Shop\Models\ShopProductReview;
   use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Http\Request;

   class ReviewController extends RootFrontController
   {
       /**
        * Get the public review list.
        *
        * @return \Illuminate\Http\JsonResponse
        */
       public function reviewList(Request $request)
       {
           $query = ShopProductReview::query()->where('status', 1);

           return ApiQuery::page($query, $request, fn ($r) => self::review($r),
               ['product_id' => fn (Builder $q, string $v) => $q->where('product_id', $v)],   // filter[product_id]
               ['created_at' => 'created_at']);                                                // sort=created_at | -created_at
       }

       /**
        * Get one public review (404 when it does not exist).
        *
        * @return \Illuminate\Http\JsonResponse
        */
       public function reviewDetail(Request $request, $id)
       {
           $review = ShopProductReview::query()->where('status', 1)->where('id', $id)->first();

           return ApiQuery::one($review, fn ($r) => self::review($r));
       }

       /**
        * Declared shape of a review: only these fields reach the API.
        */
       private static function review($r): array
       {
           return [
               'id'         => $r->id,
               'product_id' => $r->product_id,
               'point'      => (int) $r->point,
               'comment'    => $r->comment,
               'created_at' => optional($r->created_at)->toIso8601String(),
           ];
       }
   }
   ```

2. **Create the route file** at `src/Routes/Api/v2/review.php` (just placing it in the `Routes/Api/v2/` folder
   is enough — the package loads it automatically, no registration needed anywhere else):

   ```php
   <?php

   use GP247\Shop\Api\V2\ReviewController;
   use Illuminate\Support\Facades\Route;

   Route::group([
       'prefix' => GP247_API_V2_FRONT_PREFIX,   // => api/v2/front
   ], function () {
       $reviewController = gp247_namespace(ReviewController::class);

       Route::group(['prefix' => 'review'], function () use ($reviewController) {
           Route::get('list', $reviewController.'@reviewList');
           Route::get('detail/{id}', $reviewController.'@reviewDetail');
       });
   });
   ```

3. **Verify the route is loaded** with an Artisan command (run from the site root):

   ```bash
   php artisan route:list --path=api/v2/front/review
   ```

   On success you will see a `GET | api/v2/front/review/list` line. If you **don't**, re-check
   `GP247_API_MODE=true` in `.env` and clear the route cache: `php artisan route:clear`.

4. **Try calling it** (a public endpoint, so no token needed; `-g` keeps curl from interpreting `[ ]`):

   ```bash
   curl -g -X GET "https://shop.example.com/api/v2/front/review/list?page[size]=10&sort=-created_at" -H "Accept: application/json"
   ```

   Expected result:

   ```json
   {
     "data": [ { "id": 12, "product_id": 5, "point": 5, "comment": "…", "created_at": "2026-10-30T10:00:00+00:00" } ],
     "meta": { "current_page": 1, "per_page": 10, "total": 1, "last_page": 1 },
     "links": { "first": "…", "last": "…", "prev": null, "next": null }
   }
   ```

> For an **admin** endpoint (login required), put the route in the package's `src/Routes/Api/v2/` and wrap
> it in the `auth:admin-api` + `ApiAbilities::admin()` middleware group with `prefix => GP247_API_V2_CORE_PREFIX`,
> each route declaring `api.admin:<admin route name>` (section 5.1; see the template at
> `vendor/gp247/shop/src/Routes/Api/v2/admin.php`).

---

## 4. Example B — Add an API from a plugin (recommended for site-specific APIs)

This is the **correct way** to add an API for a specific site when you must **not touch vendor**. A
plugin lives at `app/GP247/Plugins/<PluginName>/`, and its `Route.php` file is loaded by core
automatically.

> ⚠️ **Key difference:** plugin routes are loaded **outside** the API middleware group (section 1.3),
> so you **must wrap them yourself** with `GP247_API_MIDDLEWARE` and set the `prefix` yourself. If you
> forget, your API will **not** force JSON, **not** pass through the `api.connection` layer, **not** follow the
> API on/off switches, and **not** have any rate limit.

1. **Create the controller** in the plugin, e.g.
   `app/GP247/Plugins/MyApi/Api/Front/StatusController.php`:

   ```php
   <?php

   namespace App\GP247\Plugins\MyApi\Api\Front;

   use GP247\Front\Controllers\RootFrontController;
   use Illuminate\Http\Request;

   class StatusController extends RootFrontController
   {
       /**
        * Return a simple health-check payload for external monitors.
        *
        * @return \Illuminate\Http\JsonResponse
        */
       public function status(Request $request)
       {
           return response()->json(['data' => [
               'status' => 'ok',
               'time'   => gp247_time_now(),
           ]]);
       }
   }
   ```

2. **Declare the route** in `app/GP247/Plugins/MyApi/Route.php`, **wrapping** the API middleware group
   yourself:

   ```php
   <?php

   use App\GP247\Plugins\MyApi\Api\Front\StatusController;
   use Illuminate\Support\Facades\Route;

   // Only register when the API mode is on, matching core behaviour.
   if (config('gp247-config.env.GP247_API_MODE')) {
       Route::group([
           'middleware' => GP247_API_MIDDLEWARE,       // required: plugin routes load outside the API group
           'prefix'     => GP247_API_V2_FRONT_PREFIX,  // => api/v2/front
       ], function () {
           $statusController = gp247_namespace(StatusController::class);

           Route::group(['prefix' => 'myapi'], function () use ($statusController) {
               Route::get('status', $statusController.'@status');
           });
       });
   }
   ```

3. **Clear the cache and verify the route**:

   ```bash
   php artisan route:clear
   php artisan route:list --path=api/v2/front/myapi
   ```

   On success you will see `GET | api/v2/front/myapi/status`.

4. **Try calling it**:

   ```bash
   curl -X GET "https://shop.example.com/api/v2/front/myapi/status" -H "Accept: application/json"
   ```

   Expected result:

   ```json
   { "data": { "status": "ok", "time": "2026-07-30 10:00:00" } }
   ```

---

### 4.1. A simpler way: register through `ApiRouteRegistrar`

> ℹ️ **Available since:** gp247/core 3.3.0

Instead of wrapping the middleware group yourself in `Route.php`, put the API routes in a separate file of the
plugin and register it from the plugin's `Provider.php`:

```php
// app/GP247/Plugins/MyApi/Provider.php
\GP247\Core\Api\Support\ApiRouteRegistrar::register(__DIR__ . '/Routes/api.php');
```

```php
// app/GP247/Plugins/MyApi/Routes/api.php — declare only the prefix and the routes
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => GP247_API_V2_FRONT_PREFIX], function () {
    $statusController = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Front\StatusController::class);
    Route::get('myapi/status', $statusController.'@status');
});
```

`ApiRouteRegistrar` wraps `GP247_API_MIDDLEWARE` for you (JSON, API Connection, rate limit), registers nothing
when `GP247_API_MODE` is off, and does not register the routes again when the site has run `route:cache`. The
`{error, msg, code}` error contract applies to every path under `api/core`, `api/front`, `api/v2/core` and
`api/v2/front`; a route under a prefix of its own (outside these four) keeps Laravel's default error bodies and
follows only the master switch of the API screen.

---

## 5. Create an endpoint that requires login (protected by a token)

If your API returns private data (a customer's orders, admin data…), you must wrap it in the matching
Sanctum guard. In the controller, get the logged-in user via `$request->user()`.

Example of a customer route that requires login (`api.customer.active`, from the shop package, refuses the token of
an account locked after the token was issued):

```php
use GP247\Core\Api\Support\ApiAbilities;

Route::group([
    'prefix'     => GP247_API_V2_FRONT_PREFIX,   // => api/v2/front
    'middleware' => ['auth:customer-api', ApiAbilities::customer(), 'api.customer.active'],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Front\WishlistController::class);
    Route::get('member/wishlist/list', $ctrl.'@wishlistList');
});
```

For admin routes, see section 5.1.

The client must send the token obtained from `login` in the header:

```
Authorization: Bearer <access_token>
```

> **Issuing tokens** (`createToken`) and **revoking tokens** (`logout`) are already implemented in the
> core/shop login controllers. You just need to **reuse** the `api/v2/core/login` / `api/v2/front/login`
> endpoints to obtain a token (v2 also offers `with_refresh` to get a refresh token, `login/challenge` for the
> sign-in verification step, and `refresh` — see [API Introduction, section 2](./api-introduction.md)) — no need to
> rewrite the login part unless you have a special requirement.

### 5.1. Admin endpoints: declare the admin screen and scope by store

> ℹ️ **Available since:** gp247/core 3.2.3

An admin token does **not** automatically allow viewing all data. Every route under
`GP247_API_V2_CORE_PREFIX` must declare its matching **admin screen** with the
`api.admin:<admin route name>` middleware; GP247 only allows the call when the account has permission to
open that screen (the same permissions as the admin panel), applies the guards of the multi-store
plugins, and resolves the account's store:

```php
Route::group([
    'prefix'     => GP247_API_V2_CORE_PREFIX,   // => api/v2/core
    'middleware' => [
        'auth:admin-api',
        \GP247\Core\Api\Support\ApiAbilities::admin(),   // since 3.3.0
    ],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Admin\ReportController::class);
    Route::get('report/sales', $ctrl.'@salesList')
        ->middleware('api.admin:admin_report.sales');   // route name of the matching admin screen
});
```

In the controller, always filter the query by the account's store:

```php
use GP247\Core\Api\Support\ApiStoreScope;
use GP247\Core\Api\V2\ApiQuery;

return ApiQuery::page(ApiStoreScope::apply(ShopOrder::query()), $request,
    fn ($o) => ['id' => $o->id, 'total' => $o->total, 'status' => (int) $o->status]);
```

Calling `ApiStoreScope` on a route without `api.admin` raises an error (safe by default) — this is how
GP247 reminds you that you forgot to declare the permission.

---

## 6. Checklist before calling it "done"

- [ ] The new endpoint is under **v2** — use the **constants** `GP247_API_V2_CORE_PREFIX` / `GP247_API_V2_FRONT_PREFIX`, no hardcoded strings, nothing added to v1.
- [ ] Reference controllers through `gp247_namespace(...)` to allow overrides.
- [ ] Plugin routes **wrap** `GP247_API_MIDDLEWARE` + `prefix` themselves (section 4) or register through `ApiRouteRegistrar` (section 4.1).
- [ ] Every input has `$request->validate([...])`; **no** string-concatenated SQL (use Eloquent).
- [ ] Lists use `ApiQuery::page()` (declaring only the `filter`/`sort` names you need); one record uses `ApiQuery::one()`.
- [ ] Private endpoints have `auth:admin-api` + `ApiAbilities::admin()`, or `auth:customer-api` + `ApiAbilities::customer()` + `api.customer.active`.
- [ ] Admin endpoints have `api.admin:<admin route name>` and filter data with `ApiStoreScope::apply()` (section 5.1).
- [ ] Errors go through `ApiErrorResponder` (meaningful HTTP status codes); the `$toArray` function returns only the declared fields.
- [ ] **Do not modify** `vendor/gp247/*` (unless you are the maintainer of that package).
- [ ] Every method has an English PHPDoc.
- [ ] You ran `php artisan route:clear` and confirmed the route appears in `php artisan route:list`.
- [ ] A plugin adding a sign-in step implements `LoginChallenge` and returns no secret from `describe()` (section 7).

---

## 7. Adding a sign-in verification step (MFA) from a plugin

> ℹ️ **Available since:** gp247/core 3.6.0

Core does not do OTP itself; your plugin adds a second step to API sign-in (v2 customer and admin) through the interface
`GP247\Core\Api\Contracts\LoginChallenge`:

| Method | When it is called | You return |
| --- | --- | --- |
| `required($owner, $request)` | Right after the password is correct (on v1 too) | `true` when this account must pass the step — do **not** send the code here |
| `describe($owner)` | Once, when the ticket is issued (v2) | Send the SMS/e-mail code here if needed; return **public** hints: `method`, `meta` — no secrets |
| `verify($owner, $request)` | When the app calls `POST login/challenge` | `true` when the answer (fields you define, e.g. `code`) is correct |

```php
<?php

namespace App\GP247\Plugins\MyOtp;

use GP247\Core\Api\Contracts\LoginChallenge;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class OtpChallenge implements LoginChallenge
{
    public function required(Authenticatable $owner, Request $request): bool
    {
        return MyOtpSettings::enabledFor($owner);   // e.g. the user turned 2FA on
    }

    public function describe(Authenticatable $owner): array
    {
        return ['method' => 'totp', 'meta' => ['digits' => 6]];   // public hints only
    }

    public function verify(Authenticatable $owner, Request $request): bool
    {
        return MyOtp::check($owner, (string) $request->input('code'));
    }
}
```

```php
// In the plugin's service provider (boot)
config(['gp247-config.api.login_challenge.customer' => array_merge(
    config('gp247-config.api.login_challenge.customer', []), [OtpChallenge::class]
)]);
```

Core does the rest: a single-use ticket (5 minutes, stored hashed in the default cache — works on shared hosting), the attempt
limit (shared with the login counter; a ticket is destroyed after 5 wrong answers, from any IP), and the token/session once `verify()` returns `true`. A plugin that throws, or a wrong
class registration, **denies** the sign-in (the step is never skipped). For administrators register under
`gp247-config.api.login_challenge.admin`. How an app calls it: [API introduction — section 9.4](./api-introduction.md).

## 8. Order-handling plugins: app orders go through the same hooks

> ℹ️ **Available since:** gp247/shop 3.5.0

An order placed through the API (`POST api/v2/front/checkout/place`) runs **the website's own checkout code**. A plugin that already handles
website orders — splitting orders by vendor, booking commissions, using up promotion quotas, sending notifications… — receives app orders
**without writing anything for the API**:

| Hook | When it runs | Notes |
| --- | --- | --- |
| `ShopOrder::created` model event | When the order is written | The order lines are written after it, in the same transaction — read them in `DB::afterCommit()` |
| `gp247_listen_order_created($order)` | Right after the order is created | Helper function; a site can define its own |
| `endApp()` in the `AppConfig` of the payment / shipping / total plugin | When the order is completed | The order id is read from `session('orderID')`, as on the website |
| `gp247_listen_order_success($order)` | When the order is completed | API orders: runs **as soon as the order is placed**, even when it is paid through a gateway (no money yet) |
| `gp247_order_process_after_success($orderId)` | After the success event | Sends the order confirmation e-mail |

To replace a helper function in the table, add its name to `config/gp247_functions_except.php` and define the function yourself (same
signature).

```php
// In the plugin's service provider (boot)
\GP247\Shop\Models\ShopOrder::created(function ($order) {
    \Illuminate\Support\Facades\DB::afterCommit(function () use ($order) {
        // The order lines exist here, whether the order came from the website or the app.
        MyCommission::book($order->fresh('details'));
    });
});
```

Three things to keep in mind when a plugin runs inside an API request:

- **Order source**: the order's `channel` column is `api` for app orders and `web` for website orders (orders created in the admin and older
  orders leave it empty). Use it only when you really need to tell them apart — most plugins do not.
- **No browser session**: inside an API request, `session()` is a session of that request only (no cookie). Reading / writing the checkout
  keys works as on the website, but do not redirect, flash messages or read cookies — the app never sees them.
- **"Order success" does not mean "paid"**: for API orders the event runs as soon as the order is placed. Work that must wait for the money
  (digital delivery, unlocking a service…) should rely on the order's payment status.

How the app calls it: [API Introduction — section 10](./api-introduction.md).

---

## Q&A

**Q1: Where should I add my API — vendor or a plugin?**

→ Almost always a **plugin** (`app/GP247/Plugins/`) or an override under `app/GP247/`. Only edit
`vendor/gp247/*` directly if you are the primary maintainer of that package, because any change inside
`vendor` is lost on `composer update`.

**Q2: Why doesn't my new route show up?**

→ Three common causes: (1) `GP247_API_MODE=true` is not set in `.env`; (2) the cache wasn't cleared — run
`php artisan route:clear`; (3) for a plugin, the file must be named exactly `Route.php` and the plugin
must be active.

**Q3: My plugin's API doesn't return JSON / isn't rate-limited?**

→ Because plugin routes are loaded outside the API middleware group. You must **wrap them yourself** with
`'middleware' => GP247_API_MIDDLEWARE` as in section 4. This is the single most common mistake when
adding an API from a plugin.

**Q4: How is `ApiQuery::page()` different from Laravel's `paginate()`?**

→ `ApiQuery::page()` uses `paginate()` underneath but adds the API v2 standard: it accepts only the `filter[...]` /
`sort` names you declare (an unknown name returns `422`), caps `page[size]` at 100, always adds an order by the
primary key so pages neither repeat nor skip records, and returns exactly the `{data, meta, links}` shape. Use it for
list endpoints to stay consistent with GP247's existing v2 APIs.

**Q5: How do I return errors the standard way?**

→ Use `ApiErrorResponder` (`notFound()`, `forbidden()`, `respond($status, $code, $msg)`) — an
`{error, msg, code}` error body with a meaningful HTTP status code. GP247 converts authentication,
validation and rate-limit errors to this shape automatically. Never return raw HTML or a plain string.

**Q6: Do I need a custom scope/ability for my API?**

→ Scopes only distinguish account types (`admin`, `admin-supper`, `user`). Permission to view data on an
admin endpoint comes from the admin screen permissions via `api.admin:<admin route name>` (section 5.1) —
administrators grant a permission once for both the admin panel and the API. Avoid inventing a parallel
permission mechanism.

**Q7: Is the `apiconnection`/`apikey` layer mandatory for my new API?**

→ It's not something you decide in code — it is toggled globally via the `api_connection_required`
configuration in admin. As long as your route is inside the `GP247_API_MIDDLEWARE` group, this layer
applies automatically when the admin turns it on.

**Q8: Do I have to write my own login/token-issuing endpoint?**

→ No. Reuse `api/v2/core/login` (admin) or `api/v2/front/login` (customer) to obtain a token, then protect your
new endpoint with `auth:admin-api`/`auth:customer-api`. Only write your own login for a special
authentication flow.

**Q9: Which class should my API controller extend?**

→ For **admin** data → extend `GP247\Core\Controllers\RootAdminController`; for **front/storefront** data
→ extend `GP247\Front\Controllers\RootFrontController`. They set up the right context for each side.

**Q10: Do I need to write tests for the new API?**

→ You should — write a feature test (PHPUnit) for the endpoint, in the test folder of your project or plugin. At minimum check:
the status code, the JSON structure, and that access is blocked when a private endpoint has no token.

---

## Change history
<!-- Only when logic/behavior changed. Newest row on top. One row per day: merge same-day changes into the existing row, never add a new row for the same date. -->

| Date | GP247 version | Change |
| --- | --- | --- |
| 2026-10-31 | gp247/core 3.6.0, gp247/shop 3.5.0; API v2: core/front/shop 3.3.0 | New endpoints go under the v2 prefixes (`GP247_API_V2_CORE_PREFIX`, `GP247_API_V2_FRONT_PREFIX`) and use `ApiQuery` for `{data, meta, links}` responses; v1 is frozen (sections 1–6); `json.response` belongs to `GP247_API_MIDDLEWARE` and runs before `auth:*` — a missing `Accept` no longer gives `500` (section 1); orders placed through API v2 go through the same hooks as website orders (`ShopOrder::created`, `endApp()`, `gp247_listen_order_success`, confirmation e-mail); order `channel` column (`api` / `web`) (section 8) |
| 2026-10-28 | gp247/core 3.6.0 | Plugins add an API sign-in verification step (MFA) through `LoginChallenge` (section 7) |
| 2026-10-26 | gp247/core 3.2.3; route registration seam: 3.3.0 | Every package/plugin registers its API routes through `ApiRouteRegistrar`, added the `Routes/Api/v2/` folder, `ApiAbilities::admin()` (3.3.0); admin endpoints must declare their admin screen (`api.admin`) and filter by store (`ApiStoreScope`); errors are returned through `ApiErrorResponder` with meaningful HTTP status codes; public data fields are filtered with `ApiFieldAllowlist` |

---

<sub>📅 **Last updated:** 2026-10-31 · ✍️ **Author:** GP247</sub>
