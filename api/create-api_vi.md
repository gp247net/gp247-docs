> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./create-api.md) · [🇯🇵 日本語](./create-api_ja.md)

# Hướng dẫn tạo API đúng chuẩn trên GP247

## Giới thiệu

Tài liệu này hướng dẫn **cách tự tạo (viết mới) một API endpoint đúng chuẩn GP247** — dành cho
**lập trình viên** muốn mở rộng site GP247 bằng các API riêng (ví dụ: xuất dữ liệu cho app di động,
cho phần mềm quản lý bên ngoài, hay tích hợp bên thứ ba). Đọc xong, bạn sẽ biết **đặt code ở đâu,
khai báo route thế nào, dùng controller/middleware/xác thực ra sao**, và có một ví dụ hoàn chỉnh
copy vào là chạy được. Nếu bạn chỉ muốn **gọi** các API có sẵn (không tự viết), hãy đọc
[Giới thiệu API GP247](./api-introduction_vi.md) trước.

> ℹ️ **Quy tắc vàng của GP247:** *không sửa code lõi* (`vendor/gp247/*`). Muốn thêm API riêng cho một
> site cụ thể, hãy làm trong **plugin** (thư mục `app/GP247/Plugins/`) hoặc bằng cơ chế **override**
> qua `app/GP247/`. Chỉ khi bạn là người bảo trì chính của gói `core`/`front`/`shop` mới thêm trực
> tiếp vào gói đó.

---

## 1. Nắm kiến trúc API trước khi viết

API của GP247 được ráp lại từ vài mảnh có sẵn. Hiểu chúng giúp bạn cắm code vào đúng chỗ.

### 1.1. Hai nhóm và tiền tố (prefix)

| Nhóm | Hằng số dùng trong route | Giá trị | Dùng cho |
| --- | --- | --- | --- |
| Core (Admin) | `GP247_API_CORE_PREFIX` | `api/core` | Quản trị hệ thống (cần token admin) |
| Front (Storefront) | `GP247_API_FRONT_PREFIX` | `api/front` | Dữ liệu cửa hàng & tài khoản khách |

Hai hằng này khai báo cố định trong `vendor/gp247/core/src/Library/Const.php`. **Luôn dùng hằng số**
trong route (không viết chuỗi `"api/core"` tay) để nếu sau này đổi tiền tố, code của bạn tự theo.

### 1.2. Bật chế độ API

API **chỉ hoạt động khi bật cờ** `GP247_API_MODE`. Kiểm tra trong file `.env` của site:

```dotenv
GP247_API_MODE=true
```

Nếu cờ này tắt, toàn bộ route API sẽ **không được nạp** (xem `vendor/gp247/core/src/routes.php`).

### 1.3. Cách route API được nạp tự động

Khi `GP247_API_MODE=true`, core tự **quét và nạp** mọi file trong các thư mục sau, và bọc tất cả
trong nhóm middleware `GP247_API_MIDDLEWARE`:

- `vendor/gp247/core/src/Routes/Api/*.php`
- `vendor/gp247/shop/src/Routes/Api/*.php`
- `vendor/gp247/front/src/Routes/Api/*.php`

`GP247_API_MIDDLEWARE = ['api', 'api.request_id', 'json.response', 'api.enabled', 'api.extend']`, trong đó `api.extend` là nhóm middleware GP247 định
nghĩa ở `config('gp247-config.api.middleware')`:

| Thứ tự | Middleware | Vai trò |
| --- | --- | --- |
| 1 | `json.response` | Ép mọi phản hồi trả về **JSON** (đặt header `Accept: application/json`). Cũng có trong `GP247_API_MIDDLEWARE` và chạy **trước mọi middleware khác** (kể cả `auth:*`), nên không mất dù config đã publish thiếu dòng này |
| 2 | `api.connection` | Lớp khóa ứng dụng tùy chọn (`apiconnection`/`apikey`) — bật/tắt qua cấu hình |
| 3 | `throttle:gp247-api` | Giới hạn tần suất, mặc định **1000 lần/phút** mỗi IP (`GP247_API_THROTTLE`) |

> ⚠️ **Thứ tự này cố định — không đổi.** Đây là quy ước đã ghi rõ trong config.
>
> ℹ️ **Có từ:** gp247/core 3.5.2 — `json.response` chạy trước `auth:*`: thiếu token mà không gửi `Accept: application/json`
> vẫn nhận `401` JSON (trước đây `500`).
>
> 👉 **Hệ quả quan trọng cho plugin:** route đặt trong `Routes/Api/` của core/shop/front sẽ **tự
> động** có 3 lớp trên. Nhưng route bạn thêm từ **plugin** lại nạp ở chỗ khác (mục 4) nên **không tự
> có** — bạn phải tự bọc chúng, hoặc (từ 3.3.0) đăng ký qua `ApiRouteRegistrar` (mục 4.1).

> ℹ️ **Có từ:** gp247/core, gp247/front, gp247/shop 3.3.0 — mỗi gói tự đăng ký route API của mình (kể cả
> thư mục `Routes/Api/v2/`) qua `GP247\Core\Api\Support\ApiRouteRegistrar`; route của gói chỉ có khi gói đã
> được cài vào cơ sở dữ liệu. Đường dẫn và tên route không đổi.

### 1.4. Xác thực bằng Laravel Sanctum

GP247 dùng **Sanctum** (token dạng Bearer). Có hai "guard" (cổng đăng nhập) tách biệt:

| Guard | Provider | Dành cho | Cấp token ở đâu |
| --- | --- | --- | --- |
| `admin-api` | `admin_provider` | Quản trị viên | `api/core/login` |
| `customer-api` | `customer_provider` | Khách/thành viên | `api/front/login` |

Mỗi token còn mang **phạm vi quyền (scope/ability)** để giới hạn nó làm được gì. Các scope lấy từ
`config('gp247-config.api.auth')` (đổi được qua `.env`):

| Khóa config | Giá trị mặc định | Ý nghĩa |
| --- | --- | --- |
| `api_scope_admin` | `admin` | Admin thường |
| `api_scope_admin_supper` | `admin-supper` | Admin cấp cao (vai trò `administrator`) |
| `api_scope_user` | `user` | Thành viên đã kích hoạt |
| `api_scope_user_guest` | `user-guest` | Thành viên chưa kích hoạt/khách |

Để bảo vệ một route cần đăng nhập, dùng cặp middleware:

```php
'middleware' => [
    'auth:admin-api',                    // hoặc 'auth:customer-api' cho API front
    'ability:admin,admin-supper',        // các scope được phép
]
```

### 1.5. Helper `gp247_namespace()` — điểm cho phép override

Trong route GP247, controller **không** viết trực tiếp `Controller::class` mà bọc qua
`gp247_namespace()`:

```php
$productController = gp247_namespace(\GP247\Shop\Api\Admin\AdminProductController::class);
Route::get('list', $productController.'@getProductList');
```

Helper này tự kiểm tra: nếu tồn tại file override ở `app/GP247/...` thì dùng bản của app, ngược lại
dùng bản trong vendor. Nhờ đó người dùng cuối có thể **override controller mà không sửa vendor**.
Luôn dùng helper này khi trỏ route tới controller của một gói `GP247\...`.

---

## 2. Quy ước đặt tên & định dạng phản hồi

Giữ đúng các quy ước sau để API mới đồng bộ với phần còn lại của hệ thống.

- **Gom theo tài nguyên**: mỗi nhóm dữ liệu là một `Route::group(['prefix' => 'product'], ...)`.
- **Hai action chuẩn**: `list` (danh sách) và `detail/{id}` (chi tiết một bản ghi).
- **Tên method controller**: `get<Tài nguyên>List` và `get<Tài nguyên>Detail`
  (ví dụ `getProductList`, `getProductDetail`).
- **Luôn trả JSON** bằng `response()->json(...)`.
- **Thành công**: trả thẳng dữ liệu (object hoặc danh sách phân trang).
- **Thất bại/không tìm thấy**: dùng `ApiErrorResponder` để có **mã HTTP đúng nghĩa** và thân lỗi chung
  `{"error": 1, "msg": "…", "code": "…"}` (bảng mã ở [Giới thiệu API](./api-introduction_vi.md), mục 7):

  ```php
  use GP247\Core\Api\Support\ApiErrorResponder;

  if (!$product) {
      return ApiErrorResponder::notFound();          // 404, code: not_found
  }
  ```

  > ℹ️ **Có từ:** gp247/core 3.2.3 — lỗi xác thực, phân quyền, validate và giới hạn tần suất dưới
  > `api/*` được tự đổi sang dạng này; bạn chỉ cần tự trả `notFound()`/`forbidden()` khi controller
  > tự quyết định. Mẫu cũ `response()->json([...], 200)` cho lỗi **không còn dùng**.
- **Chỉ trả trường cần thiết**: với dữ liệu công khai, lọc trường bằng
  `GP247\Core\Api\Support\ApiFieldAllowlist::one()` / `::page()` thay vì trả nguyên model — cột mới
  thêm vào bảng sẽ không tự lộ ra API.

- **Phân trang**: dùng macro `->jsonPaginate()` (gói `spatie/laravel-json-api-paginate` đã cài sẵn)
  thay cho `->get()` khi trả danh sách — nó trả về cấu trúc phân trang chuẩn (`data`, `current_page`,
  `total`…), kích thước trang do request điều khiển.

---

## 3. Ví dụ A — Thêm endpoint vào một gói bạn bảo trì (core/front/shop)

Áp dụng khi bạn là **maintainer của gói** (sửa trực tiếp trong `vendor/gp247/<gói>`), ví dụ thêm API
front công khai `api/front/review/list`. Làm theo đúng thứ tự:

1. **Tạo controller** tại `vendor/gp247/<gói>/src/Api/...`. Với dữ liệu front, kế thừa
   `RootFrontController`; với dữ liệu admin, kế thừa `RootAdminController`.

   Tạo file `src/Api/Front/ReviewController.php`:

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

2. **Tạo file route** tại `src/Routes/Api/review.php` (chỉ cần đặt vào thư mục `Routes/Api/` là core
   tự nạp — không phải khai báo ở đâu khác):

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

3. **Kiểm tra route đã nạp** bằng lệnh Artisan (chạy ở thư mục gốc site):

   ```bash
   php artisan route:list --path=api/front/review
   ```

   Nếu thành công, bạn thấy dòng `GET | api/front/review/list`. Nếu **không thấy**, kiểm tra lại
   `GP247_API_MODE=true` trong `.env` và xóa cache route: `php artisan route:clear`.

4. **Gọi thử** (endpoint công khai nên không cần token):

   ```bash
   curl -X GET "https://shop.example.com/api/front/review/list" -H "Accept: application/json"
   ```

> Với endpoint **admin** (cần đăng nhập), đặt route trong `src/Routes/Api/` của gói và bọc thêm nhóm
> middleware `auth:admin-api` + `ability:...` với `prefix => GP247_API_CORE_PREFIX` (xem mẫu ở
> `vendor/gp247/shop/src/Routes/Api/admin.php`).

---

## 4. Ví dụ B — Thêm API từ một plugin (khuyến nghị cho site riêng)

Đây là cách **đúng chuẩn** khi bạn thêm API cho một site cụ thể mà **không được sửa vendor**. Plugin
đặt tại `app/GP247/Plugins/<TênPlugin>/`, và file `Route.php` của nó được core tự nạp.

> ⚠️ **Khác biệt then chốt:** route của plugin nạp **ngoài** nhóm middleware API (mục 1.3), nên bạn
> **phải tự bọc** `GP247_API_MIDDLEWARE` và tự đặt `prefix`. Nếu quên, API của bạn sẽ **không** trả
> JSON ép buộc, **không** qua lớp `api.connection`, và **không** có giới hạn tần suất.

1. **Tạo controller** trong plugin, ví dụ
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

2. **Khai báo route** trong `app/GP247/Plugins/MyApi/Route.php`, **tự bọc** nhóm middleware API:

   ```php
   <?php

   use App\GP247\Plugins\MyApi\Api\Front\StatusController;
   use Illuminate\Support\Facades\Route;

   // Only register when the API mode is on, matching core behaviour.
   if (config('gp247-config.env.GP247_API_MODE')) {
       Route::group([
           'middleware' => GP247_API_MIDDLEWARE,   // ['api','api.extend'] — bắt buộc phải tự thêm
           'prefix'     => GP247_API_FRONT_PREFIX,  // => api/front
       ], function () {
           $statusController = gp247_namespace(StatusController::class);

           Route::group(['prefix' => 'myapi'], function () use ($statusController) {
               Route::get('status', $statusController.'@getStatus');
           });
       });
   }
   ```

3. **Xóa cache và kiểm tra route**:

   ```bash
   php artisan route:clear
   php artisan route:list --path=api/front/myapi
   ```

   Nếu thành công, bạn thấy `GET | api/front/myapi/status`.

4. **Gọi thử**:

   ```bash
   curl -X GET "https://shop.example.com/api/front/myapi/status" -H "Accept: application/json"
   ```

   Kết quả mong đợi:

   ```json
   { "error": 0, "status": "ok", "time": "2026-07-30 10:00:00" }
   ```

---

### 4.1. Cách gọn hơn: đăng ký qua `ApiRouteRegistrar`

> ℹ️ **Có từ:** gp247/core 3.3.0

Thay vì tự bọc nhóm middleware trong `Route.php`, đặt route API vào một file riêng của plugin và đăng ký
nó từ `Provider.php` của plugin:

```php
// app/GP247/Plugins/MyApi/Provider.php
\GP247\Core\Api\Support\ApiRouteRegistrar::register(__DIR__ . '/Routes/api.php');
```

```php
// app/GP247/Plugins/MyApi/Routes/api.php — chỉ khai báo prefix và route
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => GP247_API_FRONT_PREFIX], function () {
    $statusController = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Front\StatusController::class);
    Route::get('myapi/status', $statusController.'@getStatus');
});
```

`ApiRouteRegistrar` tự bọc `GP247_API_MIDDLEWARE` (JSON, API Connection, giới hạn tần suất), không đăng ký gì
khi `GP247_API_MODE` tắt, và không đăng ký lại khi site đã `route:cache`. Hợp đồng lỗi `{error, msg, code}` áp
dụng cho mọi đường dẫn dưới `api/core`, `api/front`, `api/v2/core`, `api/v2/front`.

---

## 5. Tạo endpoint cần đăng nhập (bảo vệ bằng token)

Nếu API của bạn trả dữ liệu riêng tư (đơn hàng của khách, dữ liệu admin…), phải bọc bằng guard
Sanctum tương ứng. Trong controller, lấy người dùng đang đăng nhập qua `$request->user()`.

Ví dụ route admin cần đăng nhập:

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

Client phải gửi token đã lấy từ `login` trong header:

```
Authorization: Bearer <access_token>
```

> Việc **cấp token** (`createToken`) và **thu hồi token** (`logout`) đã có sẵn trong các controller
> đăng nhập của core/shop. Bạn chỉ cần **tái dùng** endpoint `api/core/login` / `api/front/login` để
> lấy token, không cần tự viết lại phần đăng nhập trừ khi có nhu cầu đặc biệt.

### 5.1. Endpoint quản trị: khai báo màn admin và giới hạn theo cửa hàng

> ℹ️ **Có từ:** gp247/core 3.2.3

Token admin **không** tự cho phép xem mọi dữ liệu. Mỗi route dưới `GP247_API_CORE_PREFIX` phải khai
báo **màn hình admin** tương ứng bằng middleware `api.admin:<tên route admin>`; GP247 chỉ cho gọi
khi tài khoản có quyền mở màn hình đó (cùng phân quyền với trang quản trị), áp rào chặn của plugin
đa cửa hàng và xác định cửa hàng của tài khoản:

```php
Route::group([
    'prefix'     => GP247_API_CORE_PREFIX,
    'middleware' => [
        'auth:admin-api',
        \GP247\Core\Api\Support\ApiAbilities::admin(),   // từ 3.3.0; bản cũ hơn: 'ability:admin,admin-supper'
    ],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Admin\ReportController::class);
    Route::get('report/sales', $ctrl.'@getSalesReport')
        ->middleware('api.admin:admin_report.sales');   // tên route của màn admin tương ứng
});
```

Trong controller, luôn lọc truy vấn theo cửa hàng của tài khoản:

```php
use GP247\Core\Api\Support\ApiStoreScope;

$rows = ApiStoreScope::apply(ShopOrder::query())->jsonPaginate();
```

Gọi `ApiStoreScope` trên route thiếu `api.admin` sẽ báo lỗi (an toàn mặc định) — đây là cách GP247
nhắc bạn đã quên khai báo quyền.

---

## 6. Danh sách kiểm tra trước khi coi là "xong"

- [ ] Dùng **hằng số** `GP247_API_CORE_PREFIX` / `GP247_API_FRONT_PREFIX`, không hardcode chuỗi.
- [ ] Controller trỏ qua `gp247_namespace(...)` để cho phép override.
- [ ] Route trong plugin **tự bọc** `GP247_API_MIDDLEWARE` + `prefix` (mục 4).
- [ ] Có `$request->validate([...])` cho mọi input; **không** ghép chuỗi SQL (dùng Eloquent).
- [ ] Danh sách dùng `->jsonPaginate()`; mọi phản hồi qua `response()->json(...)`.
- [ ] Endpoint riêng tư có `auth:admin-api`/`auth:customer-api` + `ability:...` đúng scope.
- [ ] Endpoint quản trị có `api.admin:<tên route admin>` và lọc dữ liệu bằng `ApiStoreScope::apply()` (mục 5.1).
- [ ] Lỗi trả qua `ApiErrorResponder` (mã HTTP đúng nghĩa); dữ liệu công khai lọc trường bằng `ApiFieldAllowlist`.
- [ ] **Không sửa** `vendor/gp247/*` (trừ khi bạn là maintainer của gói đó).
- [ ] Mỗi method có PHPDoc tiếng Anh kèm tag `@aidlc-unit` / `@aidlc-story` (theo coding-style).
- [ ] Đã `php artisan route:clear` và xác nhận route hiện ra trong `php artisan route:list`.
- [ ] Plugin thêm bước xác thực đăng nhập: cài `LoginChallenge`, không trả bí mật trong `describe()` (mục 7).

---

## 7. Thêm bước xác thực đăng nhập (MFA) từ plugin

> ℹ️ **Có từ:** gp247/core 3.6.0

Core không tự làm OTP; plugin của bạn gắn bước xác thực thứ hai vào đăng nhập API (v2 khách và quản trị) bằng interface
`GP247\Core\Api\Contracts\LoginChallenge`:

| Method | Khi nào được gọi | Bạn trả về |
| --- | --- | --- |
| `required($owner, $request)` | Ngay sau khi mật khẩu đúng (cả ở v1) | `true` nếu tài khoản này phải qua bước xác thực — **không** gửi mã ở đây |
| `describe($owner)` | Một lần, khi cấp vé (v2) | Gửi mã SMS/email ở đây nếu cần; trả gợi ý **công khai**: `method`, `meta` — không chứa bí mật |
| `verify($owner, $request)` | Khi ứng dụng gọi `POST login/challenge` | `true` nếu câu trả lời (trường do bạn đặt, ví dụ `code`) đúng |

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

Core lo phần còn lại: vé dùng một lần (5 phút, lưu dạng băm trong cache mặc định — chạy được trên shared host), giới hạn số lần
thử (dùng chung bộ đếm đăng nhập; mỗi vé bị huỷ sau 5 lần sai, từ bất kỳ IP nào), cấp token/phiên khi `verify()` trả `true`. Plugin ném lỗi hoặc khai báo sai class → đăng nhập
bị **từ chối** (không bao giờ bỏ qua bước xác thực). Đăng ký cho quản trị viên: khoá `gp247-config.api.login_challenge.admin`.
Phía ứng dụng gọi thế nào: [Giới thiệu API — mục 10.4](./api-introduction_vi.md).

## 8. Plugin xử lý đơn hàng: đơn từ ứng dụng đi qua cùng điểm cắm

> ℹ️ **Có từ:** gp247/shop 3.5.0

Đơn đặt qua API (`POST api/v2/front/checkout/place`) chạy **đúng mã checkout của website**. Plugin đang xử lý đơn web — tách đơn theo
nhà bán, tính hoa hồng, trừ suất khuyến mãi, gửi thông báo… — nhận đơn từ ứng dụng **mà không cần viết thêm gì cho API**:

| Điểm cắm | Khi nào chạy | Ghi chú |
| --- | --- | --- |
| Sự kiện model `ShopOrder::created` | Khi ghi đơn | Dòng sản phẩm được ghi sau, trong cùng giao dịch — đọc chúng trong `DB::afterCommit()` |
| `gp247_listen_order_created($order)` | Ngay sau khi tạo đơn | Hàm helper, site tự định nghĩa lại được |
| `endApp()` trong `AppConfig` của plugin thanh toán / vận chuyển / tổng tiền | Khi hoàn tất đơn | Mã đơn đọc từ `session('orderID')` như trên website |
| `gp247_listen_order_success($order)` | Khi hoàn tất đơn | Đơn qua API: chạy **ngay khi đặt**, kể cả đơn trả qua cổng (chưa có tiền) |
| `gp247_order_process_after_success($orderId)` | Sau sự kiện thành công | Gửi email xác nhận đơn |

Muốn thay một hàm helper ở bảng trên, thêm tên hàm vào `config/gp247_functions_except.php` rồi tự định nghĩa hàm đó (cùng chữ ký).

```php
// In the plugin's service provider (boot)
\GP247\Shop\Models\ShopOrder::created(function ($order) {
    \Illuminate\Support\Facades\DB::afterCommit(function () use ($order) {
        // The order lines exist here, whether the order came from the website or the app.
        MyCommission::book($order->fresh('details'));
    });
});
```

Ba điều cần nhớ khi plugin chạy trong request API:

- **Nguồn đơn**: cột `channel` của đơn là `api` cho đơn từ ứng dụng, `web` cho đơn từ website (đơn tạo trong trang quản trị và đơn cũ
  để trống). Chỉ dùng khi thật sự cần phân biệt — đa số plugin không cần.
- **Không có phiên trình duyệt**: trong request API, `session()` là phiên riêng của request đó (không cookie). Đọc / ghi khoá checkout
  như trên website vẫn đúng, nhưng đừng redirect, flash message hay đọc cookie — ứng dụng không thấy chúng.
- **"Đơn thành công" không có nghĩa "đã trả tiền"**: với đơn qua API, sự kiện chạy ngay khi đặt. Việc chỉ làm *sau khi đã trả tiền*
  (giao hàng số, mở khoá dịch vụ…) hãy dựa vào trạng thái thanh toán của đơn.

Phía ứng dụng gọi thế nào: [Giới thiệu API — mục 11](./api-introduction_vi.md).

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi nên thêm API vào đâu — vendor hay plugin?**

→ Gần như luôn là **plugin** (`app/GP247/Plugins/`) hoặc override qua `app/GP247/`. Chỉ sửa trực tiếp
`vendor/gp247/*` khi bạn là người bảo trì chính của gói đó, vì mọi thay đổi trong `vendor` sẽ mất khi
`composer update`.

**Câu 2: Tại sao route mới của tôi không xuất hiện?**

→ Ba nguyên nhân phổ biến: (1) chưa bật `GP247_API_MODE=true` trong `.env`; (2) chưa xóa cache —
chạy `php artisan route:clear`; (3) với plugin, file phải tên đúng `Route.php` và plugin đang được
kích hoạt.

**Câu 3: API từ plugin của tôi không trả JSON / không bị giới hạn tần suất?**

→ Vì route plugin nạp ngoài nhóm middleware API. Bạn phải **tự bọc** `'middleware' => GP247_API_MIDDLEWARE`
như ở mục 4. Đây là lỗi hay gặp nhất khi thêm API từ plugin.

**Câu 4: `jsonPaginate()` khác gì `paginate()` của Laravel?**

→ `jsonPaginate()` (từ gói `spatie/laravel-json-api-paginate`) trả về cấu trúc phân trang thân thiện
với chuẩn JSON:API và cho client điều khiển kích thước trang qua tham số. Hãy dùng nó cho các endpoint
danh sách để đồng bộ với các API sẵn có của GP247.

**Câu 5: Làm sao trả về lỗi cho đúng chuẩn?**

→ Dùng `ApiErrorResponder` (`notFound()`, `forbidden()`, `respond($status, $code, $msg)`) — thân lỗi
`{error, msg, code}` với mã HTTP đúng nghĩa. Lỗi xác thực, validate và giới hạn tần suất GP247 tự đổi
sang dạng này. Không trả HTML hay chuỗi trần.

**Câu 6: Tôi cần scope/quyền riêng cho API của mình?**

→ Scope chỉ phân biệt loại tài khoản (`admin`, `admin-supper`, `user`). Quyền xem dữ liệu của endpoint
quản trị lấy từ phân quyền màn admin qua `api.admin:<tên route admin>` (mục 5.1) — người quản trị cấp
quyền một lần cho cả trang quản trị lẫn API. Tránh tự phát minh cơ chế quyền song song.

**Câu 7: Lớp `apiconnection`/`apikey` có bắt buộc cho API mới của tôi không?**

→ Không do bạn quyết định trong code — nó bật/tắt toàn cục qua cấu hình `api_connection_required` trong
quản trị. Miễn là route của bạn nằm trong nhóm `GP247_API_MIDDLEWARE`, lớp này áp dụng tự động khi
admin bật.

**Câu 8: Tôi có phải tự viết endpoint đăng nhập/cấp token không?**

→ Không. Hãy tái dùng `api/core/login` (admin) hoặc `api/front/login` (khách) để lấy token, rồi bảo vệ
endpoint mới bằng `auth:admin-api`/`auth:customer-api`. Chỉ tự viết login khi có luồng xác thực đặc thù.

**Câu 9: Controller API nên kế thừa class nào?**

→ Dữ liệu **admin** → kế thừa `GP247\Core\Controllers\RootAdminController`; dữ liệu **front/cửa hàng** →
kế thừa `GP247\Front\Controllers\RootFrontController`. Chúng đã thiết lập sẵn ngữ cảnh phù hợp cho
từng phía.

**Câu 10: Có cần viết test cho API mới không?**

→ Có — theo `testing.md`, viết feature test cho endpoint (đặt ở repo `tests/`). Ít nhất kiểm tra:
mã trạng thái, cấu trúc JSON, và chặn truy cập khi thiếu token với endpoint riêng tư.

---

## Lịch sử thay đổi
<!-- Chỉ ghi khi có thay đổi về logic/hành vi. Dòng mới nhất ở trên cùng. Mỗi ngày một dòng: cùng ngày thì gộp vào dòng có sẵn, không tách dòng mới. -->

| Ngày | Phiên bản GP247 | Thay đổi |
| --- | --- | --- |
| 2026-10-31 | gp247/core 3.5.2, gp247/shop 3.5.0 | `json.response` thuộc `GP247_API_MIDDLEWARE` và chạy trước `auth:*` — thiếu `Accept` không còn ra `500` (mục 1); đơn đặt qua API v2 đi qua cùng điểm cắm với đơn web (`ShopOrder::created`, `endApp()`, `gp247_listen_order_success`, email xác nhận); cột `channel` của đơn (`api` / `web`) (mục 8) |
| 2026-10-28 | gp247/core 3.6.0 | Plugin thêm bước xác thực đăng nhập API (MFA) qua `LoginChallenge` (mục 7) |
| 2026-10-26 | gp247/core 3.2.3; seam đăng ký route: 3.3.0 | Mỗi gói/plugin đăng ký route API qua `ApiRouteRegistrar`, thêm thư mục `Routes/Api/v2/`, `ApiAbilities::admin()` (3.3.0); endpoint quản trị phải khai báo màn admin (`api.admin`) và lọc theo cửa hàng (`ApiStoreScope`); lỗi trả qua `ApiErrorResponder` với mã HTTP đúng nghĩa; lọc trường dữ liệu công khai bằng `ApiFieldAllowlist` |

---

<sub>📅 **Cập nhật lần cuối:** 2026-10-31 · ✍️ **Tác giả (Author):** GP247</sub>
