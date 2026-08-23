> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./create-plugin.md)

# Hướng dẫn tạo Plugin cho GP247 (chuẩn v2, an toàn khi cập nhật)

## Giới thiệu

Tài liệu này hướng dẫn cách **tạo một plugin mới cho GP247 theo chuẩn plugin v2 (Core 2.0)**,
dành cho lập trình viên PHP/Laravel muốn tự viết tính năng mở rộng cho website GP247. Đọc xong,
bạn sẽ biết dùng lệnh sinh khung plugin, hiểu từng file trong plugin làm gì, viết được logic
cài/gỡ/bật/tắt, và — quan trọng nhất — **viết plugin đúng cách để nó cập nhật lên phiên bản mới
một cách an toàn** (không mất cấu hình của chủ site, không hỏng dữ liệu).

> ⚠️ **Điều quan trọng nhất của tài liệu này:** GP247 có cơ chế **cập nhật 1-click** cho plugin.
> Khi cập nhật, hệ thống **ghi đè TOÀN BỘ file** của plugin bằng bản mới, nhưng **giữ nguyên
> cấu hình lưu trong database (`admin_config`)**. Nếu bạn viết plugin sai cách (ví dụ để chủ site
> chỉnh sửa trực tiếp file `config.php`), thì **mỗi lần cập nhật sẽ xoá sạch lựa chọn của họ**.
> Phần 6 giải thích chi tiết cách tránh điều này. Hãy đọc kỹ phần đó.

---

## 1. Chuẩn bị

Trước khi bắt đầu, bạn cần:

1. Một website GP247 đã cài đặt và chạy được (đã cài `gp247/core` bản 2.0 trở lên).
2. Quyền chạy lệnh `php artisan` trên server hoặc máy phát triển (terminal / command line).
3. Biết plugin của bạn sẽ làm gì: chỉ hiển thị trong trang admin, hay có cả **trang công khai
   (storefront)** cho khách xem. Điều này quyết định bạn có cần các file `Seo.php` / `FrontController.php` hay không.

> ⭐ **Plugin total-method ở checkout** (mã giảm giá / tích điểm, `configCode: "Total"`) là trường hợp
> đặc biệt: ngoài `getInfo()` thông thường, `AppConfig` của plugin phải implement
> `GP247\Shop\Front\Contracts\CheckoutTotalMethod` (`checkoutApply` / `checkoutRemove` / `checkoutView`)
> để checkout Livewire hiển thị được ô nhập trên **mọi** template. Xem
> [convert-plugin-v1-to-v2.md](convert-plugin-v1-to-v2.md) bước 8c và plugin `ShopDiscount` làm mẫu.

> Thuật ngữ nhanh:
> - **Plugin (extension)**: một gói tính năng cắm thêm vào GP247, có thể bật/tắt/gỡ trong admin.
> - **`configKey`**: mã định danh duy nhất của plugin, **trùng đúng với tên thư mục** plugin. Ví dụ plugin `MyBanner` thì `configKey` là `MyBanner`.
> - **Livewire**: công nghệ của Laravel giúp làm giao diện động (bấm nút, tải lại một phần trang) mà **không cần viết jQuery**. Giao diện admin GP247 2.0 dùng Livewire + Tailwind (bộ TailAdmin).

---

## 2. Bước 1 — Sinh khung plugin bằng lệnh artisan

GP247 có sẵn lệnh tạo khung plugin theo đúng chuẩn v2 (đã kèm sẵn Livewire, SEO, layout mới),
để bạn khỏi phải tạo tay từng file.

1. Mở **Terminal** tại thư mục gốc của website GP247 (nơi có file `artisan`).
2. Gõ đúng dòng sau rồi nhấn Enter (thay `MyBanner` bằng tên plugin bạn muốn):

   ```bash
   php artisan gp247:make-plugin --name=MyBanner --download=0
   ```

   Ý nghĩa các tham số:
   - `--name=MyBanner`: tên plugin. Nên viết kiểu **PascalCase** (viết hoa chữ cái đầu mỗi từ, không dấu, không khoảng trắng), ví dụ `MyBanner`, `ProductFeed`.
   - `--download=0`: tạo plugin **trực tiếp** vào thư mục `app/GP247/Plugins/MyBanner` (dùng ngay được). Nếu đặt `--download=1`, lệnh sẽ tạo một file `.zip` trong `storage/tmp` để bạn tải về đóng gói phân phối.

3. Nếu thành công, terminal in một dòng text human (ví dụ `Success`); đồng thời thư mục
   `app/GP247/Plugins/MyBanner` được tạo ra với đầy đủ file mẫu. Thêm `--json` nếu cần envelope
   máy-đọc:

   ```json
   {"ok":true,"command":"gp247:make-plugin","data":{"key":"MyBanner","path":"","msg":"Success"},"warnings":[],"error":null}
   ```

   (Với `--download=1`, đường dẫn zip nằm ở `data.path`.)

> Lưu ý: nếu website **chưa cài** `gp247/front` (phần storefront), lệnh sẽ tự động **bỏ qua**
> file `Controllers/FrontController.php` — đây là hành vi bình thường, không phải lỗi.

---

## 3. Cấu trúc thư mục plugin và vai trò từng file

Sau khi sinh khung, plugin của bạn có cấu trúc như sau. Bảng dưới giải thích **file nào để làm gì**
và **file nào bắt buộc phải có**:

```
app/GP247/Plugins/MyBanner/
├── Admin/
│   └── AdminController.php     # Controller cho màn admin kiểu cũ (tương thích ngược)
├── Controllers/
│   └── FrontController.php     # Controller cho trang công khai (chỉ có nếu cài gp247/front)
├── Lang/
│   ├── en/lang.php             # Chuỗi ngôn ngữ tiếng Anh
│   └── vi/lang.php             # Chuỗi ngôn ngữ tiếng Việt
├── Livewire/
│   └── AdminLivewire.php       # Màn admin kiểu MỚI (Livewire + TailAdmin) — chuẩn v2
├── Models/
│   └── ExtensionModel.php      # Nơi xử lý tạo/xoá bảng dữ liệu khi cài/gỡ plugin
├── Views/
│   ├── Admin.blade.php         # View cho AdminController cũ
│   ├── livewire.blade.php      # View cho AdminLivewire (chuẩn v2)
│   └── Front.blade.php         # View cho trang công khai
├── public/
│   └── images/logo.jpg         # Asset tĩnh (css/js/ảnh), sẽ được copy sang public/GP247/Plugins/MyBanner
├── AppConfig.php               # ⭐ File cấu hình chính: install/uninstall/enable/disable/update
├── config.php                  # Giá trị mặc định của plugin (BỊ GHI ĐÈ khi update — xem Phần 6)
├── function.php                # Các hàm helper của plugin
├── gp247.json                  # ⭐ Khai báo thông tin plugin (version, tương thích...)
├── Provider.php                # Đăng ký service (view, lang, sitemap, page-type...)
├── Route.php                   # Khai báo route (đường dẫn) admin và Livewire
└── Seo.php                     # Cung cấp URL cho sitemap.xml (chỉ cần nếu có trang công khai)
```

Hai file bạn **chắc chắn phải chỉnh sửa**: `gp247.json` (khai báo) và `AppConfig.php` (logic cài đặt).

---

## 4. Bước 2 — Khai báo `gp247.json`

Đây là "chứng minh thư" của plugin. Mở file `gp247.json`, nội dung mẫu sinh sẵn như sau:

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
    "requireCore": ["2.1"],
    "requireUpdateFrom": "1.0",
    "requireComposerPackages": [],
    "requireGp247Extensions": [],
    "requireLivewire": false
}
```

Ý nghĩa các trường (các trường in đậm ảnh hưởng trực tiếp tới cập nhật phiên bản — xem Phần 6):

| Trường | Ý nghĩa |
|---|---|
| `name` | Tên hiển thị của plugin. |
| `image` | Đường dẫn logo (tương đối trong thư mục `public/` của plugin). |
| `auth`, `email`, `link` | Thông tin tác giả. |
| `configGroup` | Luôn là `"Plugins"` với plugin. |
| `configCode` | Mã cấu hình, thường trùng `configKey`. |
| `configKey` | **Mã định danh duy nhất, phải trùng tên thư mục plugin.** Không đổi giá trị này sau khi phát hành. |
| **`version`** | **Phiên bản plugin (dạng semver: `1.0`, `1.1`, `2.0`...). Mỗi lần phát hành bản mới PHẢI tăng số này lên** thì cập nhật 1-click mới nhận. |
| **`requireCore`** | Danh sách phiên bản `gp247/core` mà plugin hợp lệ. Với chuẩn v2 để `["2.1"]`. |
| **`requireUpdateFrom`** | Phiên bản đang cài **tối thiểu** được phép cập nhật 1-click lên bản này. Để `"1.0"` là an toàn (gần như không chặn). |
| `requireComposerPackages` | Các gói composer bắt buộc (từ packagist.org). |
| `requireGp247Extensions` | Các extension GP247 khác bắt buộc phải có (ví dụ `Shop`, `Front`, `News`). |
| `requireLivewire` | Plugin có cần Livewire hay không (`true`/`false`). Scaffold đã ship sẵn màn admin Livewire + đăng ký trong `Provider.php`, nên `false` là mặc định an toàn (Livewire đã đi kèm core). |

> **Đổi tên khóa từ gp247/core 2.1:** `requirePackages` → `requireComposerPackages`, `requireExtensions` → `requireGp247Extensions` (tên nói rõ nguồn phụ thuộc). Core 2.1 **vẫn đọc** khóa cũ (tương thích ngược) nhưng đã **deprecated** và sẽ bị gỡ ở bản sau — plugin mới hãy dùng khóa mới.

---

## 5. Bước 3 — Viết logic plugin

### 5.1. `AppConfig.php` — vòng đời cài đặt

File này kế thừa `ExtensionConfigDefault` và định nghĩa những việc xảy ra khi chủ site
**cài / gỡ / bật / tắt / cập nhật** plugin. Các phương thức chính:

| Phương thức | Chạy khi nào | Bạn nên làm gì trong đó |
|---|---|---|
| `install()` | Chủ site bấm "Cài đặt" | Ghi bản ghi cấu hình vào `admin_config`, gọi `ExtensionModel::installExtension()` để tạo bảng dữ liệu. |
| `uninstall()` | Chủ site bấm "Gỡ" | Xoá các bản ghi cấu hình đã tạo, gọi `ExtensionModel::uninstallExtension()`. |
| `enable()` / `disable()` | Bật / tắt tạm thời | Đổi cờ trạng thái, **không** xoá dữ liệu. |
| `update($fromVersion)` | Cập nhật lên bản mới | Di trú (migrate) dữ liệu nếu cần — xem Phần 6.4. |
| `getInfo()` | Admin hiển thị thông tin plugin | Trả về mảng thông tin (đã có sẵn, thường không cần sửa). |

Khung sinh sẵn đã chạy được ngay. Bạn chỉ cần bổ sung logic riêng của plugin.

### 5.2. `Models/ExtensionModel.php` — tạo/xoá bảng dữ liệu

Nếu plugin cần bảng riêng trong database, hãy viết lệnh tạo bảng trong `installExtension()`
và lệnh xoá bảng trong `uninstallExtension()`. Ví dụ:

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

### 5.3. Màn admin — dùng Livewire (chuẩn v2)

Chuẩn giao diện mới của GP247 2.0 là **Livewire + TailAdmin**. Khung sinh sẵn 2 file:
`Livewire/AdminLivewire.php` (logic) và `Views/livewire.blade.php` (giao diện).

- `AdminLivewire` kế thừa `GP247AdminComponent`, nên **tự động có** kiểm tra quyền (RBAC),
  toast thông báo và layout admin dùng chung — giống hệt màn của core.
- Trong `Views/livewire.blade.php`, ưu tiên dùng các component dùng chung `<x-gp247::*>`
  (ví dụ `<x-gp247::card>`) thay vì viết HTML thô.
- **Tuyệt đối không** dùng jQuery / AdminLTE / select2... trên màn mới — GP247 2.0 không nạp jQuery.
- Mọi chữ hiển thị phải render qua `trans('Plugins/MyBanner::lang.title')` hoặc
  `gp247_language_render(...)`, **không hardcode** để hỗ trợ đa ngôn ngữ.

### 5.4. Route, đăng ký Livewire và ngôn ngữ

- `Provider.php`: khung đã đăng ký sẵn namespace class Livewire của plugin để component admin
  resolve dạng `<livewire:MyBanner::admin-livewire>` (và qua route full-page). Có bảo vệ để host
  không có Livewire vẫn boot sạch:

  ```php
  if (class_exists(\Livewire\Livewire::class)) {
      \Livewire\Livewire::addNamespace('MyBanner', classNamespace: 'App\\GP247\\Plugins\\MyBanner\\Livewire');
  }
  ```

- `Route.php`: khung đã đăng ký sẵn route admin cũ **và** route Livewire (có bảo vệ `class_exists`
  nên an toàn). Bạn chỉ thêm route riêng khi cần.
- `Lang/vi/lang.php` và `Lang/en/lang.php`: thêm các chuỗi hiển thị của plugin vào đây.

---

## 6. ⭐ TUÂN THỦ ĐỂ HỖ TRỢ CẬP NHẬT PHIÊN BẢN (đọc kỹ)

Đây là phần quan trọng nhất. GP247 có cơ chế **cập nhật 1-click**: từ trang admin, chủ site
bấm một nút là plugin tự tải bản mới về và thay thế. Để plugin của bạn cập nhật được **an toàn**,
bạn phải hiểu cơ chế và tuân thủ 5 quy tắc dưới đây.

### 6.1. Cơ chế cập nhật hoạt động thế nào

Khi chủ site bấm "Cập nhật", hệ thống làm tuần tự (nếu bước nào lỗi sẽ **tự động khôi phục** — rollback):

1. **Sao lưu (backup)** toàn bộ thư mục plugin hiện tại (cả `app/` và `public/`) vào `storage/backups`.
2. **Tải về** file `.zip` bản mới từ marketplace GP247 (có kiểm tra checksum sha256 chống hỏng/giả mạo).
3. **Kiểm tra hợp lệ** trước khi động vào file thật: `configKey` phải trùng, `version` mới phải **lớn hơn** bản đang cài, thoả `requireUpdateFrom` và `requireCore`.
4. **Ghi đè file**: xoá thư mục plugin cũ (cả `app/GP247/Plugins/...` và `public/GP247/Plugins/...`) rồi copy toàn bộ file mới vào.
5. **Chạy hook `AppConfig::update($oldVersion)`** để plugin tự di trú dữ liệu nếu cần.
6. Nếu bất kỳ bước 4–5 nào lỗi → **khôi phục lại từ bản backup** ở bước 1.

**Điểm mấu chốt rút ra:** bước 4 **ghi đè sạch mọi file** của plugin, nhưng **database
(`admin_config`, bảng dữ liệu của bạn) được giữ nguyên**. Đây là nền tảng của mọi quy tắc dưới đây.

### 6.2. Quy tắc 1 — `version` phải tăng, `configKey` không được đổi

- Mỗi lần phát hành bản mới, **tăng `version`** trong `gp247.json` (ví dụ `1.0` → `1.1`). Nếu số
  version không lớn hơn bản đang cài, hệ thống sẽ từ chối cập nhật ("not newer").
- **Không bao giờ đổi `configKey`** sau khi đã phát hành — nó là mã định danh dùng để đối chiếu
  bản cũ và bản mới. Đổi nó = hệ thống coi như plugin khác.

### 6.3. Quy tắc 2 (quan trọng nhất) — Cấu hình của chủ site phải nằm trong DATABASE, không nằm trong file

Vì bước ghi đè xoá sạch file plugin, **mọi thứ chủ site chỉnh sửa trực tiếp trong file sẽ mất khi cập nhật.**
Cụ thể:

- **`config.php` chỉ chứa GIÁ TRỊ MẶC ĐỊNH** (do bạn — tác giả — quyết định). File này **bị ghi đè**
  mỗi lần cập nhật, nên đừng bao giờ mong chủ site sửa nó.
- **Mọi giá trị chủ site được phép thay đổi** (bật/tắt tính năng, số sản phẩm mỗi trang, API key
  của họ...) phải lưu trong bảng `admin_config` (database) — nơi cập nhật **giữ nguyên**.
- Cách làm chuẩn: lưu một bản ghi override trong `admin_config` với `code` = `<configKey>_config`,
  rồi lúc chạy **phủ (overlay)** override đó lên mặc định: `giá trị chạy thật = mặc định (config.php) ⊕ override (DB)`.

Khung sinh sẵn đã có 2 hàm helper mẫu (đang comment) trong `function.php` — bỏ comment và sửa tên
cho đúng plugin của bạn:

```php
// Lấy cấu hình đang có hiệu lực = mặc định ⊕ override trong DB
function MyBanner_effective_config()
{
    $defaults = (array) config('Plugins/MyBanner.settings', []);

    $row = \GP247\Core\Models\AdminConfig::where('group', 'Plugins')
        ->where('key', 'MyBanner_config')
        ->first();
    $overrides = $row ? json_decode((string) $row->value, true) : null;

    return is_array($overrides) ? array_merge($defaults, $overrides) : $defaults;
}

// Lưu lựa chọn của chủ site vào DB (an toàn khi cập nhật)
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

Và trong `config.php`, chỉ để **mặc định**:

```php
return [
    'settings' => [
        'enabled' => 0,
        'items_per_page' => 20,
    ],
];
```

> Nếu plugin của bạn **không có** cấu hình cho chủ site chỉnh (chỉ chạy cố định), thì xoá luôn 2 hàm
> helper này cho gọn — không cần bận tâm.

### 6.4. Quy tắc 3 — Di trú dữ liệu bằng hook `update()`

Nếu bản mới **đổi cấu trúc database** (thêm cột, thêm bảng, đổi định dạng cấu hình...), bạn phải
di trú dữ liệu trong hook `AppConfig::update($fromVersion)`. Mặc định hook này chỉ trả về "thành công"
mà không làm gì (đủ cho trường hợp chỉ thay code, không đổi DB). Khi cần, hãy ghi đè nó:

```php
/**
 * Migrate plugin data after files were replaced by a newer version.
 *
 * @param string|null $fromVersion Version installed before the update.
 * @return array ['error' => 0|1, 'msg' => string]
 */
public function update(?string $fromVersion = null)
{
    // Ví dụ: bản >= 1.1 cần thêm cột "sort" vào bảng my_banner
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

- Luôn kiểm tra `$fromVersion` để **chỉ chạy đúng bước di trú cần thiết** (idempotent — chạy lại
  nhiều lần không gây lỗi).
- Nếu hook trả về `['error' => 1, ...]` hoặc ném exception, hệ thống sẽ **khôi phục bản cũ** — nên
  hãy để nó thất bại rõ ràng khi di trú không an toàn, thay vì để dữ liệu dở dang.

### 6.5. Quy tắc 4 — Không lưu dữ liệu người dùng trong thư mục plugin

Cả `app/GP247/Plugins/<key>` lẫn `public/GP247/Plugins/<key>` đều **bị xoá và thay mới** khi cập nhật.
Vì vậy:

- **Không** để file do người dùng tải lên (ảnh sản phẩm, file upload...) bên trong thư mục plugin.
  Hãy lưu chúng ở `public/GP247/...` khu vực dữ liệu chung, hoặc `storage/`.
- `public/` trong plugin chỉ nên chứa **asset tĩnh của chính plugin** (css/js/ảnh giao diện) — những
  thứ đi kèm bản phát hành và có thể bị ghi đè an toàn.

### 6.6. Quy tắc 5 — Đặt `requireUpdateFrom` / `requireCore` đúng

- `requireCore`: đảm bảo plugin chỉ cập nhật trên lõi tương thích. Với chuẩn v2 để `["2.1"]`.
- `requireUpdateFrom`: chặn cập nhật 1-click từ một bản **quá cũ** mà hook `update()` của bạn không
  thể di trú. Để `"1.0"` khi bạn tự tin migrate được từ mọi bản; nâng lên (ví dụ `"2.0"` cho bản 2.9)
  khi bạn muốn buộc người ở dòng 1.x phải cài lại thủ công thay vì cập nhật thẳng.

---

## 7. Bước 4 — Cài đặt và kiểm tra plugin

1. Xoá cache Laravel để nạp route/view/config mới:

   ```bash
   php artisan optimize:clear
   ```

   Nếu thành công, terminal hiện các dòng như `... cleared successfully`.

2. Đăng nhập trang admin, vào mục **Plugins** (Tiện ích mở rộng), tìm plugin của bạn và bấm **Cài đặt**.
3. Sau khi cài, thử **bật/tắt** và mở màn admin của plugin. Nếu hiện đúng, không báo lỗi
   "view not found" hay "class not found", là đạt.
4. Kiểm tra giao diện ở cả **nền sáng và nền tối (dark-mode)**.

---

## 8. Danh sách kiểm tra trước khi phát hành

- [ ] `gp247.json`: `configKey` trùng tên thư mục; `requireCore` = `["2.1"]`.
- [ ] `version` đã **tăng** so với bản trước (nếu là bản cập nhật).
- [ ] Màn admin dùng Livewire + `<x-gp247::*>`; **không** còn jQuery/AdminLTE.
- [ ] Mọi chữ render qua `trans(...)` / `gp247_language_render(...)`, không hardcode; có cả `vi` và `en`.
- [ ] Cấu hình chủ-site-chỉnh nằm trong `admin_config` (không nằm trong `config.php`).
- [ ] `config.php` chỉ chứa **mặc định**; đã dùng cặp helper `*_effective_config()` / `*_save_config()` nếu có settings.
- [ ] Nếu bản mới đổi DB: đã viết hook `update($fromVersion)` di trú an toàn, idempotent.
- [ ] Không lưu file người dùng tải lên bên trong thư mục plugin.
- [ ] `install()` / `uninstall()` tạo và dọn dữ liệu cân xứng (gỡ xong không để rác).
- [ ] Đã `php artisan optimize:clear` và cài/gỡ thử thành công.

---

## 9. Hỏi & Đáp (Q&A)

**Câu 1: Tôi phải viết tay từng file plugin à?**

→ Không. Chạy `php artisan gp247:make-plugin --name=TenPlugin --download=0` để sinh sẵn toàn bộ khung
chuẩn v2, rồi chỉ bổ sung logic riêng của bạn.

**Câu 2: `configKey` là gì và có bắt buộc trùng tên thư mục không?**

→ `configKey` là mã định danh duy nhất của plugin và **bắt buộc trùng đúng tên thư mục** plugin. Nó
cũng là mã dùng để đối chiếu khi cập nhật, nên đừng đổi sau khi đã phát hành.

**Câu 3: Vì sao chủ site sửa `config.php` xong, cập nhật một cái là mất hết?**

→ Vì cập nhật 1-click **ghi đè toàn bộ file** plugin, gồm cả `config.php`. Mọi giá trị chủ site chỉnh
phải lưu ở database (`admin_config`), không lưu trong file. Xem Phần 6.3.

**Câu 4: Làm sao lưu cấu hình của chủ site cho an toàn khi cập nhật?**

→ Lưu vào bảng `admin_config` với `code` = `<configKey>_config`, và lúc chạy thì phủ nó lên mặc định
trong `config.php`. Dùng cặp hàm `*_effective_config()` / `*_save_config()` mẫu trong `function.php`.

**Câu 5: Bản mới của tôi có thêm cột database. Tôi xử lý ở đâu?**

→ Trong hook `AppConfig::update($fromVersion)`. Kiểm tra `$fromVersion` để chỉ chạy đúng bước di trú
cần thiết, và viết sao cho chạy lại nhiều lần không lỗi. Xem Phần 6.4.

**Câu 6: Nếu bước cập nhật bị lỗi giữa chừng thì sao?**

→ Hệ thống tự **khôi phục (rollback)** plugin về bản cũ từ backup đã tạo trước khi động vào file thật,
nên site không bị kẹt ở trạng thái dở dang.

**Câu 7: Plugin của tôi chỉ dùng trong admin, có cần `Seo.php` và `FrontController.php` không?**

→ Không. `Seo.php` chỉ cần khi plugin có **trang công khai** muốn góp URL vào `sitemap.xml`;
`FrontController.php` chỉ cần khi plugin có trang storefront. Plugin admin-only có thể bỏ qua.

**Câu 8: Tôi có được dùng jQuery hay select2 trên màn admin của plugin không?**

→ Không. GP247 2.0 dùng TailAdmin (Tailwind + Alpine + Livewire) và **không nạp jQuery**. Hãy dùng
Livewire/Alpine và các component `<x-gp247::*>` sẵn có.

**Câu 9: `version` để dạng nào? `1.0.0` hay `1.0`?**

→ Dùng semver dạng số, ví dụ `1.0`, `1.1`, `2.0`. Miễn là mỗi bản phát hành mới có số **lớn hơn** bản
trước (so sánh theo `version_compare`), cập nhật 1-click sẽ nhận.

**Câu 10: Cập nhật xong mà admin vẫn hiện bản cũ, phải làm sao?**

→ Chạy `php artisan optimize:clear` để xoá cache route/view/config rồi tải lại trang. Đây là lỗi hay
gặp nhất do Laravel còn giữ cache cũ.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-23 · ✍️ **Tác giả (Author):** GP247</sub>
