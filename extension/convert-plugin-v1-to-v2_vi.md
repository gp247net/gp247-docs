> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./convert-plugin-v1-to-v2.md)

# Hướng dẫn chuyển đổi Plugin GP247 từ v1 (Core 1.x) lên v2 (Core 2.0)

## Giới thiệu

Tài liệu này hướng dẫn cách **nâng cấp một plugin GP247 viết cho bản cũ (gp247/core 1.x)
sang chuẩn plugin mới (gp247/core 2.0)**. Nó dành cho lập trình viên hoặc người quản trị
đang có sẵn một plugin chạy trên GP247 1.x và muốn nó chạy được trên GP247 2.0. Đọc xong,
bạn sẽ biết **file nào cần sửa, sửa thành gì**, và làm theo được từng bước mà không cần
hiểu sâu toàn bộ hệ thống.

> ⚠️ **Điều quan trọng nhất cần biết trước:** GP247 2.0 đã **thay toàn bộ tầng giao diện**.
> Bản 1.x dùng **AdminLTE (Bootstrap + jQuery + pjax)**, bản 2.0 dùng **TailAdmin
> (Tailwind + Alpine + Livewire)**. Vì vậy một plugin 1.x **không tự chạy được** trên 2.0
> — layout cũ đã bị xóa, jQuery không còn được nạp. Việc chuyển đổi chủ yếu là **sửa lại
> tầng giao diện và một vài file cấu hình**, còn phần logic/model của bạn hầu như giữ nguyên.

---

## 1. Hai định dạng plugin khác nhau chỗ nào?

GP247 giữ một "khuôn mẫu plugin" (plugin format) trong lõi. Đây là bảng so sánh nhanh
khuôn v1 và v2:

| Hạng mục | Plugin v1 (Core 1.x) | Plugin v2 (Core 2.0) |
|---|---|---|
| Framework giao diện admin | AdminLTE (Bootstrap + jQuery) | TailAdmin (Tailwind + Alpine) |
| Điều hướng trang admin | pjax | Không pjax (Livewire re-render) |
| Layout master của view admin | `@extends('gp247-core::layout')` | `@extends('gp247-admin::layouts.admin')` |
| Tương tác động | jQuery + plugin jQuery (select2, daterangepicker…) | Livewire 4 + Alpine |
| Màn admin kiểu mới | *(không có)* | Livewire component `Livewire/AdminLivewire.php` |
| Tích hợp sitemap SEO | *(không có)* | File `Seo.php` + đăng ký trong `Provider.php` |
| Đăng ký page-type LayoutBlock | *(không có)* | Đăng ký `token => lang-key` trong `Provider.php` (nếu plugin có trang public riêng) |
| `gp247.json` → `requireCore` | `["1.2"]` | `["2.1"]` |
| `gp247.json` → khóa phụ thuộc | `requirePackages` / `requireExtensions` | `requireComposerPackages` / `requireGp247Extensions` (đổi tên từ core 2.1) |
| `gp247.json` → `requireUpdateFrom` | *(không có)* | `"1.0"` (điều kiện cập nhật 1-click) |

**Kết luận:** logic nghiệp vụ (Model, xử lý dữ liệu, cài/gỡ) của bạn gần như không đổi.
Cái thay đổi là **giao diện** và **một số khai báo cấu hình**.

---

## 2. Những file bạn sẽ đụng tới

Đây là các file trong plugin cần xem lại khi chuyển đổi. Cột "Bắt buộc?" cho biết việc nào
là tối thiểu để plugin chạy được, việc nào là khuyến nghị để dùng đúng chuẩn mới.

| File | Cần làm gì | Bắt buộc? |
|---|---|---|
| `gp247.json` | Đổi `requireCore` thành `["2.1"]`, thêm `requireUpdateFrom`, đổi `requirePackages`/`requireExtensions` → `requireComposerPackages`/`requireGp247Extensions` | ✅ Bắt buộc |
| `Views/Admin.blade.php` | Đổi layout master sang TailAdmin, bỏ jQuery/AdminLTE | ✅ Bắt buộc |
| `AppConfig.php` | Đổi thông báo lỗi trong `disable()` sang đa ngôn ngữ | ✅ Bắt buộc |
| `Livewire/AdminLivewire.php` | **Tạo mới** — màn admin kiểu Livewire | 🟡 Khuyến nghị |
| `Views/livewire.blade.php` | **Tạo mới** — view cho Livewire component | 🟡 Khuyến nghị |
| `Route.php` | Thêm route Livewire (có bảo vệ `class_exists`) | 🟡 Khuyến nghị |
| `Seo.php` | **Tạo mới** — nếu plugin có trang public cần vào sitemap | ⚪ Tùy chọn |
| `Provider.php` | Thêm khối đăng ký sitemap (nếu dùng `Seo.php`) | ⚪ Tùy chọn |
| `Provider.php` | Thêm khối đăng ký page-type LayoutBlock (nếu plugin có trang public riêng) | ⚪ Tùy chọn |

> Thuật ngữ nhanh: **Livewire** là công nghệ của Laravel cho phép làm giao diện động (bấm nút,
> tải lại một phần trang) mà **không cần viết JavaScript/jQuery**. GP247 2.0 dùng Livewire
> thay cho jQuery + pjax của bản cũ.

---

## 3. Các bước chuyển đổi (làm lần lượt từ trên xuống)

### Bước 1 — Sao lưu và tạo bản làm việc riêng

1. Copy toàn bộ thư mục plugin ra một chỗ an toàn (ví dụ nén thành file `.zip`), để nếu sai
   còn có bản gốc quay về.
2. Nếu plugin của bạn dùng Git, tạo một nhánh mới để làm việc:

   ```bash
   git checkout -b nang-cap-len-v2
   ```

   Nếu thành công, terminal hiện dòng `Switched to a new branch 'nang-cap-len-v2'`.

### Bước 2 — Cập nhật `gp247.json`

Mở file `gp247.json` trong thư mục plugin. Sửa `requireCore`, **thêm** `requireUpdateFrom`, và
**đổi tên** 2 khóa phụ thuộc theo chuẩn core 2.1: `requirePackages` → `requireComposerPackages`,
`requireExtensions` → `requireGp247Extensions`.

**Trước (v1):**

```json
{
    "version": "1.0",
    "requireCore": ["1.2"],
    "requirePackages": [],
    "requireExtensions": []
}
```

**Sau (v2 — core 2.1):**

```json
{
    "version": "1.0",
    "requireCore": ["2.1"],
    "requireUpdateFrom": "1.0",
    "requireComposerPackages": [],
    "requireGp247Extensions": [],
    "requireLivewire": false
}
```

Ý nghĩa các dòng:
- `requireCore`: phiên bản lõi GP247 mà plugin này hợp lệ. Đặt `["2.1"]` để báo plugin dành cho Core 2.1.
- `requireLivewire`: plugin có cần Livewire hay không (`true`/`false`). Thêm khi bạn đưa màn Livewire vào ở Bước 5 (`false` là mặc định an toàn — Livewire đi kèm core).
- `requireComposerPackages` / `requireGp247Extensions`: đổi tên từ `requirePackages` / `requireExtensions` cho rõ nguồn phụ thuộc (gói Composer vs extension GP247). Core 2.1 vẫn đọc khóa cũ (tương thích ngược) nhưng đã deprecated — hãy dùng khóa mới.
- `requireUpdateFrom`: phiên bản đang cài **tối thiểu** được phép cập nhật 1-click lên bản này.
  Để `"1.0"` là an toàn (gần như không giới hạn). Chỉ nâng số này lên khi bạn phát hành bản
  lớn mà không thể tự động migrate từ dòng cũ.

### Bước 3 — Sửa layout của view admin (quan trọng nhất)

Đây là thay đổi làm plugin cũ "hỏng" nếu bỏ qua, vì layout `gp247-core::layout` **đã bị
xóa khỏi 2.0**. Mở file `Views/Admin.blade.php`.

1. Tìm dòng đầu tiên `@extends(...)` và đổi tên layout:

   **Trước (v1):**
   ```blade
   @extends('gp247-core::layout')
   ```

   **Sau (v2):**
   ```blade
   @extends('gp247-admin::layouts.admin')
   ```

2. Giữ nguyên cấu trúc `@section('main') ... @endsection`. Nội dung bên trong có thể giữ như cũ
   trước, rồi tinh chỉnh sau ở Bước 4.

Kết quả file tối thiểu trông như sau:

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

### Bước 4 — Gỡ bỏ jQuery và widget AdminLTE

GP247 2.0 **không nạp jQuery**. Mọi đoạn code sau đây trong view/asset của plugin sẽ **không
chạy** và phải được thay thế:

- `$(...)`, `$.pjax(...)`, `$.ajax(...)` — cú pháp jQuery.
- `select2`, `daterangepicker`, `datetimepicker`, `bootstrap modal` — các plugin jQuery cũ.
- Header hoặc script liên quan tới `x-pjax`.

Cách thay thế:
- **Chọn ngày/giờ** → dùng **flatpickr** (đã có sẵn trong bộ TailAdmin), hoặc component
  `<x-gp247::*>` tương ứng nếu có.
- **Bảng/nút/tương tác động** → dùng **Livewire + Alpine** (xem Bước 5).
- **Text hiển thị** → luôn render qua `gp247_language_render('...')`, không hardcode.

> Nếu plugin của bạn chỉ hiển thị dữ liệu tĩnh (không có nút bấm động, không jQuery), thì
> sau Bước 3 là đã chạy được rồi — Bước 4 và 5 chỉ cần khi plugin có tương tác động.

### Bước 5 — (Khuyến nghị) Thêm màn admin kiểu Livewire

Đây là chuẩn giao diện mới của v2. Nếu muốn plugin dùng đúng chuẩn (RBAC 2 lớp, layout
đồng nhất), hãy tạo **2 file mới**.

1. Tạo file `Livewire/AdminLivewire.php` với nội dung sau (thay `Extension_Key` bằng tên
   plugin của bạn — đúng bằng `configKey` trong `gp247.json`):

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

   - Kế thừa `GP247AdminComponent` để plugin **tự động có** RBAC lớp 2 (kiểm tra quyền khi
     mở màn), toast thông báo, và layout admin dùng chung — giống hệt màn của core.
   - `$permission = null` nghĩa là quyền được suy ra tự động theo tên component.

2. Tạo file `Views/livewire.blade.php` cho view của component đó:

   ```blade
   <div class="space-y-5">
       <x-gp247::card :title="trans('Plugins/Extension_Key::lang.title')">
           <p class="text-sm text-gray-600 dark:text-gray-300">
               {{ trans('Plugins/Extension_Key::lang.title') }} — Your content here!
           </p>
       </x-gp247::card>
   </div>
   ```

   - `<x-gp247::card>` là component dùng chung của TailAdmin — bạn nên ưu tiên các component
     `<x-gp247::*>` sẵn có thay vì viết lại HTML thô.
   - Lớp `dark:text-gray-300` giúp giao diện đẹp cả ở chế độ nền tối (dark-mode).

3. Trong `Provider.php`, đăng ký namespace class Livewire của plugin (có bảo vệ để host không có
   Livewire vẫn boot), để component resolve dạng `<livewire:Extension_Key::admin-livewire>`:

   ```php
   if (class_exists(\Livewire\Livewire::class)) {
       \Livewire\Livewire::addNamespace('Extension_Key', classNamespace: 'App\\GP247\\Plugins\\Extension_Key\\Livewire');
   }
   ```

### Bước 6 — Thêm route cho Livewire trong `Route.php`

Mở `Route.php`. Bên trong khối `Route::group([...], function () { ... })` của admin, **thêm**
đoạn đăng ký route Livewire. Đoạn này có bảo vệ `class_exists` nên **an toàn** cả khi plugin
chưa có file Livewire (không gây lỗi).

**Trước (v1):**

```php
function () {
    Route::get('/', 'AdminController@index')
    ->name('admin_ExtensionUrlKey.index');
}
```

**Sau (v2):**

```php
function () {
    Route::get('/', 'AdminController@index')
    ->name('admin_ExtensionUrlKey.index');

    // Route Livewire, đăng ký song song với controller cũ để plugin cũ vẫn chạy
    if (class_exists(\App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire::class)) {
        Route::get('/livewire', \App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire::class)
        ->name('admin_ExtensionUrlKey.livewire');
    }
}
```

> Lưu ý: route controller cũ (`AdminController@index`) **vẫn giữ nguyên**. Nhờ vậy plugin
> tương thích ngược — vừa có màn cũ, vừa có màn Livewire mới.

### Bước 7 — Sửa `AppConfig.php` (thông báo lỗi đa ngôn ngữ)

Trong `AppConfig.php`, tìm phương thức `disable()`. Bản v1 hardcode chuỗi tiếng Anh
`'Error disable'`; bản v2 dùng hàm đa ngôn ngữ.

**Trước (v1):**

```php
if (!$process) {
    $return = ['error' => 1, 'msg' => 'Error disable'];
}
```

**Sau (v2):**

```php
if (!$process) {
    $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Disable'])];
}
```

Ngoài ra, bạn có thể đổi dòng comment ở đầu file từ `Plugin format 1.0` thành
`Plugin format 2.0` cho rõ ràng. Các phương thức còn lại (`install`, `uninstall`, `enable`,
`getInfo`…) giữ nguyên.

### Bước 8 — (Tùy chọn) Thêm tích hợp sitemap SEO

Chỉ làm bước này nếu plugin của bạn có **trang công khai (public)** muốn xuất hiện trong
`sitemap.xml` của website.

1. Tạo file `Seo.php` trong thư mục plugin:

   ```php
   <?php

   namespace App\GP247\Plugins\Extension_Key;

   class Seo
   {
       public static function sitemapUrls($storeId): array
       {
           // Trả về danh sách URL công khai của plugin cho sitemap.
           // Khi chưa có, trả về mảng rỗng là an toàn.
           return [];
       }
   }
   ```

2. Mở `Provider.php`, **thêm** khối sau vào bên trong đoạn `if (gp247_extension_check_active(...))`:

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

   Khối này có `class_exists` bảo vệ, nên nếu website **không** cài `gp247/front` thì plugin
   vẫn cài đặt bình thường (không lỗi).

### Bước 8b — (Tùy chọn) Đăng ký page-type cho LayoutBlock

Chỉ làm bước này nếu plugin của bạn có **trang storefront (public) riêng** (ví dụ trang danh
sách/chi tiết của plugin) và bạn muốn admin **cắm được khối LayoutBlock** (banner, HTML, view…)
hiển thị đúng trên các trang đó.

Bối cảnh: mỗi trang storefront phát một "page-type" qua biến `$layout_page` khi render (controller
của bạn truyền `'layout_page' => 'myplugin_index'` vào `view()`). Màn admin "Layout block" chỉ liệt
kê được các page-type **đã đăng ký** vào registry `config('gp247-config.front.layout_page')`. Vì vậy
plugin có trang riêng phải tự đăng ký page-type của mình — cùng cách plugin đăng ký sitemap ở Bước 8.

1. Đảm bảo controller của plugin truyền `layout_page` khi render trang public, ví dụ:

   ```php
   return view($view, [
       // ... dữ liệu khác ...
       'layout_page' => 'myplugin_index',   // token page-type của trang này
   ]);
   ```

2. Mở `Provider.php`, **thêm** khối sau vào bên trong đoạn `if (gp247_extension_check_active(...))`:

   ```php
   if (class_exists('GP247\Front\Controllers\RootFrontController')) {
       $layoutPage = config('gp247-config.front.layout_page', []);
       // Lưu KEY đa ngôn ngữ (KHÔNG render sẵn) — admin sẽ tự render theo ngôn ngữ đang xem.
       $layoutPage['myplugin_index'] = $extensionPath.'::lang.layout_block_page.myplugin_index';
       config(['gp247-config.front.layout_page' => $layoutPage]);
   }
   ```

   - `token` (`myplugin_index`) **phải trùng** giá trị `$layout_page` controller của bạn phát ra, nếu
     không admin chọn xong khối vẫn không hiển thị.
   - Giá trị là **key đa ngôn ngữ** (trỏ tới file `Lang` của plugin), không phải chuỗi đã dịch sẵn —
     để dropdown admin hiển thị đúng theo locale đang xem.
   - Khối có `class_exists` bảo vệ, nên website không cài `gp247/front` thì bỏ qua, plugin vẫn cài
     bình thường.

3. Thêm dòng ngôn ngữ tương ứng vào file `Lang/vi/lang.php` và `Lang/en/lang.php` của plugin
   (mảng `layout_block_page`), ví dụ `'myplugin_index' => 'Trang danh sách plugin'`.

> Plugin `News` (`app/GP247/Plugins/News/Provider.php`) là ví dụ tham chiếu: nó đăng ký
> `news_index`/`news_category`/`news_detail` theo đúng cách này.

> Lưu ý phân biệt với **template**: nếu bạn làm *template* (giao diện) chứ không phải plugin, bạn
> **không** đăng ký page-type — template chỉ hiển thị theo `$layout_page` mà controller đã phát,
> không định nghĩa page-type mới.

---

### Bước 8c — (Tùy chọn) Plugin total-method (coupon/point) hiển thị ở checkout

Chỉ làm khi plugin là **total-method** (`configCode: "Total"` — vd coupon hoặc plugin tích điểm)
cần hiện ô nhập ở trang thanh toán. Ở v1, template checkout include thẳng `Views/render.blade.php`
+ `Views/script.blade.php` (jQuery) của plugin. GP247 2.0 thay bằng một **contract** để mọi template
(mặc định hay tùy biến) làm việc được với mọi plugin total-method, không phụ thuộc lẫn nhau. Thay cho
render/script jQuery cũ, hãy làm:

1. **Implement contract trên `AppConfig.php`.** Thêm `implements CheckoutTotalMethod`
   (`use GP247\Shop\Front\Contracts\CheckoutTotalMethod;`) và 3 method — tái dùng logic
   validate/session sẵn có:

   ```php
   public function checkoutApply(array $payload): array   // validate + set session('totalMethod')[key]; trả ['error'=>0|1,'msg'=>...]
   public function checkoutRemove(): void                 // unset session('totalMethod')[key]
   public function checkoutView(): ?string                // vd return $this->appPath.'::checkout'
   ```

2. **Tạo fragment view** theo tên `checkoutView()` (vd `Views/checkout.blade.php`). Nó render **bên
   trong** component Livewire của checkout, nên dùng `wire:` — không jQuery, không fetch:

   ```blade
   <input type="text" class="input" wire:model="totalPayload.{{ $pluginKey }}.code">
   <button type="button" wire:click="applyTotal('{{ $pluginKey }}')">{{ gp247_language_render('cart.apply') }}</button>
   ```

   Chỉ dùng **UI token template đã có sẵn** (`input`, `btn-primary`, `btn-ghost`, `card`,
   `text-ink-*`…). Một class Tailwind hoàn toàn mới sẽ không có trong CSS đã build của template và
   **lặng lẽ không có style**.

3. Layer dữ liệu giữ nguyên: `session('totalMethod')` → `getInfo()` → `ShopOrderTotal` →
   `addOrder()`. Giữ nguyên `getInfo()`.

Checkout **tự động phát hiện** plugin (`code='total'` + implements interface) và render fragment vào
vùng total-method. Plugin total **không** implements interface sẽ bị ẩn khỏi checkout 2.0 (có ghi
`Log::warning`) tới khi bạn nâng cấp. Contract mới **thay thế hoàn toàn** đường AJAX jQuery cũ
(`render.blade.php` + `script.blade.php` + endpoint `discount.process`/`discount.remove` + swap DOM
`#gp247_showTotal`) — plugin tham chiếu `ShopDiscount` đã **xoá sạch** vì không nơi nào trong UI 2.0
gọi tới. Chỉ giữ endpoint HTTP như vậy nếu bạn thực sự cần một entry headless/API của riêng mình; nó
không thuộc UI checkout.

> Người viết template: để hỗ trợ plugin total-method, view checkout tùy biến chỉ cần 2 include ở bước
> xác nhận: `@include('gp247-shop-front::partials.checkout_total_methods')` và
> `@include('gp247-shop-front::partials.order_totals')`.

---

### Bước 9 — Kiểm tra lại

1. Xóa cache của Laravel để nạp lại route/view/config mới:

   ```bash
   php artisan optimize:clear
   ```

   Nếu thành công, terminal hiện các dòng như `cache ... cleared successfully`.

2. Vào trang admin, mở màn của plugin. Nếu bạn đã thêm Livewire (Bước 5–6), thử mở đường dẫn
   có đuôi `/livewire`. Màn hình hiển thị đúng, không báo lỗi "view not found" hay "class not
   found" là đạt.
3. Thử bật/tắt (enable/disable) plugin trong admin để chắc chắn `AppConfig.php` vẫn chạy.

---

## 4. Danh sách kiểm tra trước khi coi là "xong"

- [ ] `gp247.json`: `requireCore` = `["2.1"]`, đã thêm `requireUpdateFrom`, đã đổi sang `requireComposerPackages`/`requireGp247Extensions`.
- [ ] `Views/Admin.blade.php`: layout đã đổi sang `gp247-admin::layouts.admin`.
- [ ] Không còn `$(...)`, pjax, hay widget jQuery (select2/daterangepicker…) nào trong view/asset.
- [ ] Text hiển thị render qua `gp247_language_render(...)`, không hardcode.
- [ ] `AppConfig.php`: `disable()` dùng `gp247_language_render(...)` thay cho `'Error disable'`.
- [ ] (Nếu dùng Livewire) Có `Livewire/AdminLivewire.php` + `Views/livewire.blade.php`, route đã thêm.
- [ ] (Nếu có trang public) Có `Seo.php` + khối đăng ký trong `Provider.php`.
- [ ] (Nếu có trang public riêng cần cắm LayoutBlock) Đã đăng ký page-type (`token => lang-key`) vào `config('gp247-config.front.layout_page')` trong `Provider.php`, token trùng `$layout_page` controller phát.
- [ ] (Nếu là plugin total-method — coupon/point) `AppConfig` implements `CheckoutTotalMethod` và có fragment `checkoutView()`, chỉ dùng UI token template đã có sẵn.
- [ ] Đã chạy `php artisan optimize:clear` và mở thử màn admin thành công.
- [ ] Giao diện hiển thị đúng ở cả nền sáng và nền tối (dark-mode).

---

## 5. Hỏi & Đáp (Q&A)

**Câu 1: Plugin 1.x của tôi có tự chạy trên 2.0 mà không cần sửa gì không?**

→ Không, trừ khi nó cực kỳ đơn giản. Vì layout `gp247-core::layout` đã bị xóa và jQuery không
còn được nạp, plugin dùng giao diện admin cũ sẽ hỏng. Tối thiểu bạn phải làm Bước 2, 3, 7.

**Câu 2: Tôi bắt buộc phải chuyển sang Livewire không?**

→ Không bắt buộc. Nếu màn admin của plugin chỉ hiển thị nội dung tĩnh, bạn chỉ cần đổi layout
(Bước 3) là chạy. Livewire (Bước 5–6) chỉ cần khi plugin có tương tác động (trước đây làm bằng jQuery).

**Câu 3: Logic và Model của tôi có phải viết lại không?**

→ Không. Schema database và tầng logic của GP247 2.0 giữ nguyên như 1.x. Việc chuyển đổi chỉ
đụng tới tầng giao diện và vài file khai báo cấu hình.

**Câu 4: Tôi mở màn admin thì báo "View [gp247-core::layout] not found" — vì sao?**

→ Vì bạn chưa làm Bước 3. Layout đó đã bị xóa ở 2.0. Hãy đổi `@extends('gp247-core::layout')`
thành `@extends('gp247-admin::layouts.admin')`.

**Câu 5: Sửa xong route/view mà admin vẫn hiện bản cũ, phải làm sao?**

→ Chạy `php artisan optimize:clear` để xóa cache route/view/config, rồi tải lại trang. Đây là
lỗi hay gặp nhất do Laravel còn giữ cache cũ.

**Câu 6: `requireUpdateFrom` nên để giá trị nào?**

→ Để `"1.0"` là an toàn nhất (gần như không chặn cập nhật). Chỉ nâng lên (ví dụ `"2.0"`) khi
bạn phát hành một bản lớn mà hàm `update()` không thể migrate từ dòng cũ.

**Câu 7: Tôi có nên tạo file `Seo.php` cho mọi plugin không?**

→ Không. Chỉ tạo khi plugin có trang công khai (public) muốn góp URL vào `sitemap.xml`. Plugin
chỉ dùng trong admin thì bỏ qua Bước 8.

**Câu 8: Khối đăng ký sitemap trong `Provider.php` có gây lỗi nếu website không có gp247/front không?**

→ Không. Khối đó được bọc trong `class_exists('GP247\Front\Controllers\RootFrontController')`,
nên nếu không cài `gp247/front` thì nó tự bỏ qua, plugin vẫn cài bình thường.

**Câu 9: Vì sao route controller cũ vẫn được giữ lại bên cạnh route Livewire?**

→ Để tương thích ngược. Nhờ giữ cả hai, plugin đang chạy màn cũ không bị gãy đột ngột, còn
bạn có thể chuyển dần sang màn Livewire mới.

**Câu 10: Cách nhanh nhất để tạo một plugin v2 mới hoàn toàn (thay vì sửa từ v1) là gì?**

→ Chạy lệnh artisan tạo plugin theo khuôn v2 rồi chép logic cũ của bạn sang:

```bash
php artisan gp247:make-plugin --name=YourPluginName --download=0
```

Lệnh này sinh sẵn đầy đủ cấu trúc chuẩn v2 (đã có Livewire, Seo, layout mới) để bạn khỏi tạo tay từng file.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-23 · ✍️ **Tác giả (Author):** GP247</sub>
