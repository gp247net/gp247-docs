> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./create-api.md)

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

`GP247_API_MIDDLEWARE = ['api', 'api.extend']`, trong đó `api.extend` là nhóm middleware GP247 định
nghĩa ở `config('gp247-config.api.middleware')`:

| Thứ tự | Middleware | Vai trò |
| --- | --- | --- |
| 1 | `json.response` | Ép mọi phản hồi trả về **JSON** (đặt header `Accept: application/json`) |
| 2 | `api.connection` | Lớp khóa ứng dụng tùy chọn (`apiconnection`/`apikey`) — bật/tắt qua cấu hình |
| 3 | `throttle: 1000` | Giới hạn **1000 lần gọi**/chu kỳ (chống lạm dụng) |

> ⚠️ **Thứ tự này cố định — không đổi.** Đây là quy ước đã ghi rõ trong config.
>
> 👉 **Hệ quả quan trọng cho plugin:** route đặt trong `Routes/Api/` của core/shop/front sẽ **tự
> động** có 3 lớp trên. Nhưng route bạn thêm từ **plugin** lại nạp ở chỗ khác (mục 4) nên **không tự
> có** — bạn phải tự bọc chúng.

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
- **Thất bại/không tìm thấy**: trả object lỗi theo mẫu chung:

  ```php
  return response()->json([
      'error' => 1,
      'msg'   => 'Not found',
      'detail'=> 'Product not found or no permission!',
  ], 200);
  ```

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

---

## 6. Danh sách kiểm tra trước khi coi là "xong"

- [ ] Dùng **hằng số** `GP247_API_CORE_PREFIX` / `GP247_API_FRONT_PREFIX`, không hardcode chuỗi.
- [ ] Controller trỏ qua `gp247_namespace(...)` để cho phép override.
- [ ] Route trong plugin **tự bọc** `GP247_API_MIDDLEWARE` + `prefix` (mục 4).
- [ ] Có `$request->validate([...])` cho mọi input; **không** ghép chuỗi SQL (dùng Eloquent).
- [ ] Danh sách dùng `->jsonPaginate()`; mọi phản hồi qua `response()->json(...)`.
- [ ] Endpoint riêng tư có `auth:admin-api`/`auth:customer-api` + `ability:...` đúng scope.
- [ ] **Không sửa** `vendor/gp247/*` (trừ khi bạn là maintainer của gói đó).
- [ ] Mỗi method có PHPDoc tiếng Anh kèm tag `@aidlc-unit` / `@aidlc-story` (theo coding-style).
- [ ] Đã `php artisan route:clear` và xác nhận route hiện ra trong `php artisan route:list`.

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

→ Dùng object `{'error': 1, 'msg': '...', 'detail': '...'}` với `response()->json(...)`. Với lỗi xác
thực, để Sanctum tự trả `401`. Không trả HTML hay chuỗi trần.

**Câu 6: Tôi cần scope/quyền riêng cho API của mình?**

→ Có thể tái dùng các scope sẵn có (`admin`, `admin-supper`, `user`, `user-guest`). Nếu cần scope mới,
cấp thêm khi tạo token và kiểm tra bằng middleware `ability:<scope-của-bạn>`. Tránh tự phát minh cơ
chế quyền song song với Sanctum.

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

<sub>📅 **Cập nhật lần cuối:** 2026-07-30 · ✍️ **Tác giả (Author):** GP247</sub>
