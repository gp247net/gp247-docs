> 🌐 **Language:** [🇻🇳 Tiếng Việt](./create-api_vi.md) · 🇬🇧 English (current)

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

| Group | Constant used in routes | Value | Used for |
| --- | --- | --- | --- |
| Core (Admin) | `GP247_API_CORE_PREFIX` | `api/core` | System administration (requires an admin token) |
| Front (Storefront) | `GP247_API_FRONT_PREFIX` | `api/front` | Storefront data & customer accounts |

Both constants are fixed in `vendor/gp247/core/src/Library/Const.php`. **Always use the constants** in
routes (never a hand-typed `"api/core"` string) so that if the prefix ever changes, your code follows
automatically.

### 1.2. Enable API mode

The API **only works when the `GP247_API_MODE` flag is on**. Check the site's `.env` file:

```dotenv
GP247_API_MODE=true
```

If this flag is off, none of the API routes are loaded at all (see `vendor/gp247/core/src/routes.php`).

### 1.3. How API routes are auto-loaded

When `GP247_API_MODE=true`, core automatically **scans and loads** every file in the following folders
and wraps them all in the `GP247_API_MIDDLEWARE` group:

- `vendor/gp247/core/src/Routes/Api/*.php`
- `vendor/gp247/shop/src/Routes/Api/*.php`
- `vendor/gp247/front/src/Routes/Api/*.php`

`GP247_API_MIDDLEWARE = ['api', 'api.extend']`, where `api.extend` is the GP247 middleware group defined
in `config('gp247-config.api.middleware')`:

| Order | Middleware | Role |
| --- | --- | --- |
| 1 | `json.response` | Forces every response to be **JSON** (sets header `Accept: application/json`) |
| 2 | `api.connection` | Optional application-level gate (`apiconnection`/`apikey`) — toggled by config |
| 3 | `throttle: 1000` | Rate limit of **1000 calls**/window (abuse protection) |

> ⚠️ **This order is fixed — do not change it.** It is a convention stated explicitly in the config.
>
> 👉 **Important consequence for plugins:** routes placed in the `Routes/Api/` folder of core/shop/front
> get these three layers **automatically**. But routes you add from a **plugin** are loaded elsewhere
> (section 4), so they do **not** get them automatically — you must wrap them yourself.

### 1.4. Authentication with Laravel Sanctum

GP247 uses **Sanctum** (Bearer tokens). There are two separate "guards" (login gates):

| Guard | Provider | For | Where the token is issued |
| --- | --- | --- | --- |
| `admin-api` | `admin_provider` | Administrators | `api/core/login` |
| `customer-api` | `customer_provider` | Customers/members | `api/front/login` |

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
    'auth:admin-api',                    // or 'auth:customer-api' for front APIs
    'ability:admin,admin-supper',        // the allowed scopes
]
```

### 1.5. The `gp247_namespace()` helper — the override hook

In GP247 routes, a controller is **not** referenced directly as `Controller::class`; it is wrapped in
`gp247_namespace()`:

```php
$productController = gp247_namespace(\GP247\Shop\Api\Admin\AdminProductController::class);
Route::get('list', $productController.'@getProductList');
```

This helper checks: if an override file exists at `app/GP247/...`, it uses the app's version, otherwise
it uses the vendor version. This is how end users can **override a controller without touching vendor**.
Always use this helper when pointing a route at a controller from a `GP247\...` package.

---

## 2. Naming conventions & response format

Follow these conventions so your new API stays consistent with the rest of the system.

- **Group by resource**: each data group is one `Route::group(['prefix' => 'product'], ...)`.
- **Two standard actions**: `list` (collection) and `detail/{id}` (a single record).
- **Controller method names**: `get<Resource>List` and `get<Resource>Detail`
  (e.g. `getProductList`, `getProductDetail`).
- **Always return JSON** via `response()->json(...)`.
- **On success**: return the data directly (an object or a paginated list).
- **On failure/not found**: return an error object following the shared shape:

  ```php
  return response()->json([
      'error' => 1,
      'msg'   => 'Not found',
      'detail'=> 'Product not found or no permission!',
  ], 200);
  ```

- **Pagination**: use the `->jsonPaginate()` macro (from the pre-installed
  `spatie/laravel-json-api-paginate` package) instead of `->get()` when returning a list — it returns a
  standard pagination structure (`data`, `current_page`, `total`…) with the page size controlled by the
  request.

---

## 3. Example A — Add an endpoint to a package you maintain (core/front/shop)

Use this when you are the **package maintainer** (editing directly in `vendor/gp247/<package>`), e.g.
adding a public front API `api/front/review/list`. Follow the exact order:

1. **Create the controller** under `vendor/gp247/<package>/src/Api/...`. For front data, extend
   `RootFrontController`; for admin data, extend `RootAdminController`.

   Create `src/Api/Front/ReviewController.php`:

   ```php
   <?php

   namespace GP247\Shop\Api\Front;

   use GP247\Front\Controllers\RootFrontController;
   use Illuminate\Http\Request;
   use GP247\Shop\Models\ShopProductReview;

   class ReviewController extends RootFrontController
   {
       /**
        * Get the public review list.
        *
        * @return \Illuminate\Http\JsonResponse
        *
        * @aidlc-unit shop-api
        * @aidlc-story US-shop-api-review-list
        */
       public function getReviewList(Request $request)
       {
           $reviews = (new ShopProductReview)
               ->where('status', 1)
               ->jsonPaginate();

           return response()->json($reviews, 200);
       }
   }
   ```

2. **Create the route file** at `src/Routes/Api/review.php` (just placing it in the `Routes/Api/` folder
   is enough — core loads it automatically, no registration needed anywhere else):

   ```php
   <?php

   use GP247\Shop\Api\Front\ReviewController;
   use Illuminate\Support\Facades\Route;

   Route::group([
       'prefix' => GP247_API_FRONT_PREFIX,   // => api/front
   ], function () {
       $reviewController = gp247_namespace(ReviewController::class);

       Route::group(['prefix' => 'review'], function () use ($reviewController) {
           Route::get('list', $reviewController.'@getReviewList');
       });
   });
   ```

3. **Verify the route is loaded** with an Artisan command (run from the site root):

   ```bash
   php artisan route:list --path=api/front/review
   ```

   On success you will see a `GET | api/front/review/list` line. If you **don't**, re-check
   `GP247_API_MODE=true` in `.env` and clear the route cache: `php artisan route:clear`.

4. **Try calling it** (a public endpoint, so no token needed):

   ```bash
   curl -X GET "https://shop.example.com/api/front/review/list" -H "Accept: application/json"
   ```

> For an **admin** endpoint (login required), put the route in the package's `src/Routes/Api/` and wrap
> it in the `auth:admin-api` + `ability:...` middleware group with `prefix => GP247_API_CORE_PREFIX`
> (see the template at `vendor/gp247/shop/src/Routes/Api/admin.php`).

---

## 4. Example B — Add an API from a plugin (recommended for site-specific APIs)

This is the **correct way** to add an API for a specific site when you must **not touch vendor**. A
plugin lives at `app/GP247/Plugins/<PluginName>/`, and its `Route.php` file is loaded by core
automatically.

> ⚠️ **Key difference:** plugin routes are loaded **outside** the API middleware group (section 1.3),
> so you **must wrap them yourself** with `GP247_API_MIDDLEWARE` and set the `prefix` yourself. If you
> forget, your API will **not** force JSON, **not** pass through the `api.connection` layer, and **not**
> have any rate limit.

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
        *
        * @aidlc-unit myapi-plugin
        * @aidlc-story US-myapi-plugin-status
        */
       public function getStatus(Request $request)
       {
           return response()->json([
               'error'  => 0,
               'status' => 'ok',
               'time'   => gp247_time_now(),
           ], 200);
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
           'middleware' => GP247_API_MIDDLEWARE,   // ['api','api.extend'] — you MUST add this yourself
           'prefix'     => GP247_API_FRONT_PREFIX,  // => api/front
       ], function () {
           $statusController = gp247_namespace(StatusController::class);

           Route::group(['prefix' => 'myapi'], function () use ($statusController) {
               Route::get('status', $statusController.'@getStatus');
           });
       });
   }
   ```

3. **Clear the cache and verify the route**:

   ```bash
   php artisan route:clear
   php artisan route:list --path=api/front/myapi
   ```

   On success you will see `GET | api/front/myapi/status`.

4. **Try calling it**:

   ```bash
   curl -X GET "https://shop.example.com/api/front/myapi/status" -H "Accept: application/json"
   ```

   Expected result:

   ```json
   { "error": 0, "status": "ok", "time": "2026-07-30 10:00:00" }
   ```

---

## 5. Create an endpoint that requires login (protected by a token)

If your API returns private data (a customer's orders, admin data…), you must wrap it in the matching
Sanctum guard. In the controller, get the logged-in user via `$request->user()`.

Example of an admin route that requires login:

```php
Route::group([
    'prefix'     => GP247_API_CORE_PREFIX,   // => api/core
    'middleware' => [
        'auth:admin-api',
        'ability:'.config('gp247-config.api.auth.api_scope_admin')
                 .','.config('gp247-config.api.auth.api_scope_admin_supper'),
    ],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Admin\ReportController::class);
    Route::get('report/sales', $ctrl.'@getSalesReport');
});
```

The client must send the token obtained from `login` in the header:

```
Authorization: Bearer <access_token>
```

> **Issuing tokens** (`createToken`) and **revoking tokens** (`logout`) are already implemented in the
> core/shop login controllers. You just need to **reuse** the `api/core/login` / `api/front/login`
> endpoints to obtain a token — no need to rewrite the login part unless you have a special requirement.

---

## 6. Checklist before calling it "done"

- [ ] Use the **constants** `GP247_API_CORE_PREFIX` / `GP247_API_FRONT_PREFIX`, not hardcoded strings.
- [ ] Reference controllers through `gp247_namespace(...)` to allow overrides.
- [ ] Plugin routes **wrap** `GP247_API_MIDDLEWARE` + `prefix` themselves (section 4).
- [ ] Every input has `$request->validate([...])`; **no** string-concatenated SQL (use Eloquent).
- [ ] Lists use `->jsonPaginate()`; every response goes through `response()->json(...)`.
- [ ] Private endpoints have `auth:admin-api`/`auth:customer-api` + the correct `ability:...` scope.
- [ ] **Do not modify** `vendor/gp247/*` (unless you are the maintainer of that package).
- [ ] Every method has an English PHPDoc with the `@aidlc-unit` / `@aidlc-story` tags (per coding-style).
- [ ] You ran `php artisan route:clear` and confirmed the route appears in `php artisan route:list`.

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

**Q4: How is `jsonPaginate()` different from Laravel's `paginate()`?**

→ `jsonPaginate()` (from `spatie/laravel-json-api-paginate`) returns a JSON:API-friendly pagination
structure and lets the client control the page size via a parameter. Use it for list endpoints to stay
consistent with GP247's existing APIs.

**Q5: How do I return errors the standard way?**

→ Use the object `{'error': 1, 'msg': '...', 'detail': '...'}` with `response()->json(...)`. For auth
errors, let Sanctum return `401` on its own. Never return raw HTML or a plain string.

**Q6: Do I need a custom scope/ability for my API?**

→ You can reuse the existing scopes (`admin`, `admin-supper`, `user`, `user-guest`). If you need a new
scope, grant it when creating the token and check it with the `ability:<your-scope>` middleware. Avoid
inventing a permission mechanism that runs parallel to Sanctum.

**Q7: Is the `apiconnection`/`apikey` layer mandatory for my new API?**

→ It's not something you decide in code — it is toggled globally via the `api_connection_required`
configuration in admin. As long as your route is inside the `GP247_API_MIDDLEWARE` group, this layer
applies automatically when the admin turns it on.

**Q8: Do I have to write my own login/token-issuing endpoint?**

→ No. Reuse `api/core/login` (admin) or `api/front/login` (customer) to obtain a token, then protect your
new endpoint with `auth:admin-api`/`auth:customer-api`. Only write your own login for a special
authentication flow.

**Q9: Which class should my API controller extend?**

→ For **admin** data → extend `GP247\Core\Controllers\RootAdminController`; for **front/storefront** data
→ extend `GP247\Front\Controllers\RootFrontController`. They set up the right context for each side.

**Q10: Do I need to write tests for the new API?**

→ Yes — per `testing.md`, write a feature test for the endpoint (in the `tests/` repo). At minimum check:
the status code, the JSON structure, and that access is blocked when a private endpoint has no token.

---

<sub>📅 **Last updated:** 2026-07-30 · ✍️ **Author:** GP247</sub>
