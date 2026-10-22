> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./create-plugin-advanced.md) · [🇯🇵 日本語](./create-plugin-advanced_ja.md)

# Plugin nâng cao — nhiều cửa hàng, khoá bí mật, storefront, mã giảm giá

## Giới thiệu

Tài liệu này gom các chủ đề nâng cao khi viết plugin GP247, mỗi mục đọc riêng được: thiết lập theo từng cửa hàng, lưu khoá
bí mật, hiện nội dung trên trang cửa hàng (trang riêng, khối layout, điểm cắm), plugin mã giảm giá / tích điểm, đổi giá sản
phẩm và thêm menu quản trị. Nên đọc sau [Hướng dẫn tạo plugin](./create-plugin_vi.md). Plugin thanh toán và vận chuyển có
hướng dẫn riêng: [thanh toán](./create-payment-plugin_vi.md), [vận chuyển](./create-shipping-plugin_vi.md).

> 💡 **Làm cùng AI agent?** Dùng skill **`gp247-plugin-create`** trong repo [gp247-skills](https://github.com/gp247net/gp247-skills) (chép thư mục skill vào thư mục skill
> của agent, ví dụ `.claude/skills/` với Claude Code): skill tự dò phiên bản site và áp các mục dưới đây (theo cửa hàng, khoá
> bí mật, điểm cắm storefront…). Plugin thanh toán / vận chuyển: `gp247-payment-plugin-create` / `gp247-shipping-plugin-create`.

---

## 1. Thiết lập theo từng cửa hàng

GP247 có hai mô hình nhiều cửa hàng: **multi-store** (mỗi tên miền một cửa hàng) và **marketplace** (một tên miền, nhiều người
bán). Một plugin "theo cửa hàng" chạy được cả hai, và trên site một cửa hàng thì không có gì khác.

1. **Khai phạm vi** trong `gp247.json`: `"storeScope": "store"` (bật/tắt và thiết lập khác nhau theo cửa hàng) hoặc `"global"`
   (mặc định, một giá trị chung).
2. **Đọc thiết lập theo cửa hàng đang hiệu lực**:

   ```php
   $value = gp247_config('MyPlugin_items_per_page', gp247_plugin_store_id());
   ```

   `gp247_plugin_store_id()` tự chọn đúng cửa hàng (phiên quản trị, cửa hàng lúc thanh toán trên sàn, cửa hàng theo tên miền).
   Không có giá trị riêng thì `gp247_config()` lấy giá trị chung. Khi đã biết cửa hàng (ví dụ của một đơn hàng), truyền thẳng:
   `gp247_config('MyPlugin_x', $order->store_id)`.
3. **Bật chọn cửa hàng trên màn cấu hình**: trong màn kế thừa `ConfigForm`, cho `storeScoped()` trả `true`. Lõi tự dựng ô chọn
   cửa hàng, nhãn "kế thừa / riêng", nút "dùng cấu hình chung"; chỉ ô nào cửa hàng thật sự đổi mới được lưu riêng.
4. **Cho quản trị viên của cửa hàng tự cấu hình** (tuỳ chọn): trong `Provider.php`, khối `gp247_extension_check_active`, thêm
   đoạn đường dẫn quản trị của plugin:

   ```php
   config(['gp247-config.admin.store_scoped_segments' => array_values(array_unique(array_merge(
       (array) config('gp247-config.admin.store_scoped_segments', []), ['myplugin']
   )))]);
   ```

   Không thêm thì chỉ quản trị viên gốc đặt được giá trị cho từng cửa hàng — nên **không** thêm với thiết lập quyết định tiền
   đi đâu (tài khoản thanh toán).

---

## 2. Khoá bí mật

Mọi **thông tin đăng nhập** (API key, token, mật khẩu, mã ký webhook) phải được mã hoá, không lưu dạng chữ thường.

**Trên màn cấu hình (trường hợp thường gặp):** tạo dòng với `'security' => 1` và khai ô là `password` trong `fieldTypes()`.
Lõi mã hoá khi lưu, **không bao giờ gửi giá trị đã lưu ra trình duyệt** (ô chỉ báo "đã có giá trị"; để trống khi lưu nghĩa là
giữ nguyên), và ở cấp cửa hàng thì kế thừa mà không lộ. Đọc bằng `gp247_config('MyPlugin_api_key', $storeId)` — nhận lại chữ
thường.

**Trong bảng riêng của plugin:** dùng cast `Secret` của lõi, cột kiểu `TEXT`, và đăng ký cột để công cụ đổi khoá mã hoá xử lý:

```php
// In the model
protected $casts = ['access_token' => \GP247\Core\Casts\Secret::class];

// In Provider.php (inside gp247_extension_check_active) — table name WITHOUT the prefix
config(['gp247-config.security.encrypted_columns.my_plugin_account' => ['access_token']]);
```

Cột đã mã hoá **không tìm kiếm / lọc được** — cần tra cứu thì thêm một cột băm riêng.

**Không làm:** tự mã hoá bằng `Crypt` hay `base64`; để khoá trong `config.php`, file `.env` đi kèm plugin, dòng JSON hay cột thường;
ghi khoá ra log hoặc trả về trong API.

---

## 3. Hiện nội dung trên trang cửa hàng

Plugin **không bao giờ sửa hay kèm file của template** (template là của người khác). Có ba cách cắm vào trang cửa hàng, cần
`gp247/front` đã cài:

| Cách | Khi nào dùng | Đăng ký ở `Provider.php` |
|---|---|---|
| **Trang riêng** | Plugin có trang của nó (danh sách, chi tiết…) | Route trong nhóm `GP247_FRONT_MIDDLEWARE` + `Controllers/FrontController.php` |
| **Khối layout** | Chủ cửa hàng tự đặt khối ở vị trí muốn | `gp247-config.front.layout_block_views` |
| **Điểm cắm** | Hiện ở một vị trí cố định của trang shop | `gp247-config.front.plugin_hooks.<tên điểm>` |

**Trang riêng** — route theo mẫu (khung sinh sẵn trong `Route.php`):

```php
Route::group(['middleware' => GP247_FRONT_MIDDLEWARE, 'prefix' => 'plugin/myplugin'], function () {
    Route::get('/', [\App\GP247\Plugins\MyPlugin\Controllers\FrontController::class, 'index'])->name('myplugin.index');
});
```

View của trang dùng bố cục của template đang chạy: `@extends($GP247TemplatePath.'.layout')` và
`@section('block_main_content_center')`. Muốn trang vào `sitemap.xml`, giữ `Seo.php` (hàm `sitemapUrls($storeId)` trả mỗi URL có
`loc` và `alias`) cùng đoạn đăng ký trong `Provider.php`. Muốn chủ cửa hàng nhắm khối layout vào trang này, đăng ký loại trang:

```php
$layoutPage = config('gp247-config.front.layout_page', []);
$layoutPage['myplugin_index'] = 'Plugins/MyPlugin::lang.layout_page_index'; // a language KEY, not a rendered text
config(['gp247-config.front.layout_page' => $layoutPage]);
```

và truyền `'layout_page' => 'myplugin_index'` khi trả view.

**Khối layout** — khối hiện trong màn **Layout Block** cho mọi template:

```php
$blocks = config('gp247-config.front.layout_block_views', []);
$blocks['myplugin_banner'] = 'Plugins/MyPlugin::blocks.myplugin_banner'; // Views/blocks/myplugin_banner.blade.php
config(['gp247-config.front.layout_block_views' => $blocks]);
```

Đặt tên khối có tiền tố plugin để không trùng; template có file cùng tên sẽ được ưu tiên.

**Điểm cắm** — hiện có `shop_product_detail_bottom` (nhận `['product' => …]`) và `shop_order_detail_bottom` (nhận
`['order' => …]`). Đăng ký **hàm dạng mảng**, không dùng closure (closure làm `php artisan config:cache` lỗi):

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

Một hàm cắm bị lỗi chỉ mất phần của nó (được ghi nhật ký), không làm hỏng cả trang.

---

## 4. Plugin mã giảm giá / tích điểm (total-method)

`configCode: "Promotion"` (giá trị cũ `"Total"` vẫn nhận). Ngoài `getInfo()`, `AppConfig` phải implement
`GP247\Shop\Front\Contracts\CheckoutTotalMethod` — không có thì trang thanh toán ẩn plugin:

```php
public function checkoutApply(array $payload): array; // validate + apply; return ['error' => 0|1, 'msg' => …]
public function checkoutRemove(): void;               // clear what checkoutApply stored
public function checkoutView(): ?string;              // view key of the input fragment, or null
```

Đoạn view nhận `$pluginKey`, `$plugin`, `$message`, và gọi hành động `applyTotal` / `removeTotal` của trang thanh toán với trường
`totalPayload` (ví dụ `wire:model="totalPayload.{{ $pluginKey }}.code"`). Chỉ dùng class giao diện có sẵn của template (`input`,
`btn-primary`…). Plugin mẫu: `ShopDiscount`.

**Quy ước tiền:** chủ cửa hàng nhập số tiền (mức giảm, đơn tối thiểu) theo **tiền gốc**; số tiền plugin trả về phải theo **tiền
hiển thị** — đổi bằng `gp247_currency_value($amount)` và so ngưỡng với tạm tính của giỏ (đã là tiền hiển thị).

---

## 5. Đổi giá sản phẩm

Plugin đổi giá (giảm giá theo thời gian, giá thành viên) thì thêm một hàm vào `gp247-config.shop.price_resolvers` từ
`Provider.php`, **không** sửa giá trong database. Đọc ghi chú ngay trên vòng lặp resolver trong `GP247\Shop\Models\ShopProduct`
để biết hàm nhận và trả gì. Đăng ký hàm dạng mảng `[Lớp::class, 'tênHàm']`.

---

## 6. Menu quản trị

Thêm mục dưới một nhóm có sẵn (tìm theo `key`, ví dụ `ADMIN_SHOP_PAYMENT`, `ADMIN_SHOP_SHIPPING`), bỏ qua nếu nhóm không có, và
không thêm hai lần. Gọi từ `install()` và `update()`; xoá trong `uninstall()`:

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

## Hỏi & Đáp (Q&A)

**Câu 1: Plugin "theo cửa hàng" có làm site một cửa hàng chạy khác đi không?**

→ Không. Trên site một cửa hàng, `gp247_plugin_store_id()` trỏ về cửa hàng gốc và mọi giá trị lấy từ thiết lập chung.

**Câu 2: Quản trị viên của một cửa hàng con không mở được màn cấu hình plugin?**

→ Chưa thêm đoạn đường dẫn quản trị vào `store_scoped_segments` (mục 1, bước 4).

**Câu 3: Lưu khoá bí mật xong ô lại trống?**

→ Đúng thiết kế: ô `password` không gửi giá trị đã lưu ra trình duyệt. Để trống khi lưu là giữ khoá cũ.

**Câu 4: Tôi muốn hiện thứ gì đó trên trang sản phẩm mà không sửa template?**

→ Dùng điểm cắm `shop_product_detail_bottom` (mục 3) với một hàm dạng mảng.

**Câu 5: `php artisan config:cache` báo lỗi "not serializable" sau khi cài plugin?**

→ Plugin đăng ký một closure vào config (điểm cắm, resolver). Đổi sang hàm dạng mảng `[Lớp::class, 'tênHàm']`.

**Câu 6: Plugin mã giảm giá không hiện ở trang thanh toán?**

→ `AppConfig` chưa implement `CheckoutTotalMethod`, hoặc `configCode` không phải `"Promotion"`.

**Câu 7: Số tiền giảm hiện sai khi khách đổi tiền tệ?**

→ Plugin trả số theo tiền gốc. Đổi sang tiền hiển thị bằng `gp247_currency_value()` trước khi trả.

**Câu 8: Template của khách có file khối cùng tên với khối của plugin?**

→ File của template được ưu tiên. Đặt tên khối có tiền tố plugin (ví dụ `myplugin_banner`) để tránh trùng.

---

<sub>📅 **Cập nhật lần cuối:** 2026-10-22 · ✍️ **Tác giả (Author):** GP247</sub>
