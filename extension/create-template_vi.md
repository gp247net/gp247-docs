> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./create-template.md)

# Hướng dẫn tạo Template (giao diện) cho GP247

## Giới thiệu

Tài liệu này hướng dẫn cách **tạo một template (giao diện storefront) mới cho GP247**, dành cho lập
trình viên PHP/Laravel muốn tự thiết kế giao diện cho website bán hàng GP247. Đọc xong, bạn sẽ biết
dùng lệnh sinh khung template, hiểu cấu trúc thư mục, xây được giao diện trang chủ/trang tĩnh, và —
quan trọng nhất — hiểu **template làm việc với `gp247/shop` như thế nào** (cơ chế fallback view) để
biết khi nào cần và khi nào không cần tự viết lại các trang bán hàng.

> ⚠️ **Hai điều quan trọng nhất cần nhớ trước khi làm:**
>
> 1. **Template chỉ dùng được khi đã cài `gp247/front`.** Template là giao diện của phần storefront,
>    mà toàn bộ phần storefront do gói `gp247/front` cung cấp. Vì vậy `gp247.json` của template luôn
>    khai báo `requirePackages: ["gp247/front"]`. Không cài `gp247/front` thì template không có tác dụng.
> 2. **Template mẫu KHÔNG chứa view của `gp247/shop`.** Nếu template mới của bạn không có view cho các
>    trang bán hàng (danh sách sản phẩm, giỏ hàng, thanh toán...), `gp247/shop` sẽ **tự động dùng view
>    mặc định trong gói của nó** theo cơ chế fallback (Phần 6). Bạn chỉ cần tự viết các trang này khi
>    muốn tùy chỉnh — và khi đó nên **publish view của `gp247/shop` ra ngoài** để chỉnh cho template mới.

---

## 1. Chuẩn bị

Trước khi bắt đầu, bạn cần:

1. Một website GP247 đã cài `gp247/core` (2.0+) **và** `gp247/front`. Nếu website có bán hàng thì
   thường cũng cài `gp247/shop`.
2. Quyền chạy lệnh `php artisan` trên server hoặc máy phát triển (terminal / command line).
3. (Khuyến nghị) Node.js nếu bạn muốn tự biên dịch CSS Tailwind cho template — xem Phần 5.4.

> Thuật ngữ nhanh:
> - **Template**: bộ giao diện của phần storefront (trang khách xem). Mỗi cửa hàng chọn 1 template đang hoạt động.
> - **`gp247/front`**: gói cung cấp phần storefront (route, controller, cơ chế template). **Bắt buộc có** thì template mới chạy.
> - **`gp247/shop`**: gói bán hàng (sản phẩm, giỏ hàng, thanh toán). Không bắt buộc, nhưng nếu có thì template có thể tùy biến giao diện các trang shop.
> - **`configKey`**: mã định danh duy nhất của template, **trùng đúng tên thư mục** template.
> - **View (Blade)**: file giao diện `.blade.php` của Laravel.

---

## 2. Bước 1 — Sinh khung template bằng lệnh artisan

`gp247/front` có sẵn lệnh tạo khung template chuẩn v2, để bạn khỏi tạo tay từng file.

1. Mở **Terminal** tại thư mục gốc website GP247 (nơi có file `artisan`).
2. Gõ đúng dòng sau rồi nhấn Enter (thay `MyShopSkin` bằng tên template bạn muốn):

   ```bash
   php artisan gp247:make-template --name=MyShopSkin --download=0
   ```

   Ý nghĩa các tham số:
   - `--name=MyShopSkin`: tên template. Nên viết **PascalCase** (viết hoa đầu mỗi từ, không dấu, không khoảng trắng).
   - `--download=0`: tạo template **trực tiếp** vào `app/GP247/Templates/MyShopSkin` (dùng ngay được). Nếu `--download=1`, lệnh tạo một file `.zip` trong `storage/tmp` để đóng gói phân phối.

3. Nếu thành công, terminal trả về một dòng JSON có `"error":0`:

   ```json
   {"error":0,"path":"","msg":"Success"}
   ```

   Thư mục `app/GP247/Templates/MyShopSkin` được tạo với đầy đủ file mẫu.

---

## 3. Cấu trúc thư mục template

Sau khi sinh khung, template có cấu trúc như sau (bảng giải thích vai trò từng phần). Ngoài ra, một
template **đầy đủ đang chạy thật** thường có thêm các thư mục giao diện như `layout/`, `screen/`,
`partials/`, `components/`, `livewire/`:

```
app/GP247/Templates/MyShopSkin/
├── blocks/          # Các khối UI dùng lại (banner, khối nội dung...) cho LayoutBlock
├── Lang/
│   ├── en/lang.php  # Chuỗi ngôn ngữ tiếng Anh
│   └── vi/lang.php  # Chuỗi ngôn ngữ tiếng Việt
├── Plugins/         # (Tùy chọn) plugin đóng gói kèm template
├── public/          # Asset tĩnh (css/js/ảnh); khi cài sẽ copy sang public/GP247/Templates/MyShopSkin
├── AppConfig.php    # File cấu hình chính: install/uninstall/enable/disable/setupStore
├── config.php       # Cấu hình mặc định của template (BỊ GHI ĐÈ khi cập nhật — xem Phần 7)
├── function.php     # Các hàm helper của template
├── gp247.json       # ⭐ Khai báo thông tin template (đặc biệt requirePackages)
├── Provider.php     # Đăng ký service (view, lang, config...)
└── Route.php        # Khai báo route riêng của template (nếu cần)

# Các thư mục giao diện bạn tự thêm (như template thật đang chạy):
├── layout.blade.php # Khung layout tổng (header + nội dung + footer)
├── layout/          # Các mảnh layout (header, footer, menu...)
├── screen/          # Các trang: home, page_detail, 404... (và shop_* nếu muốn override — Phần 6)
├── partials/        # Mảnh giao diện dùng lại
├── components/      # Blade component riêng của template
└── livewire/        # View cho các Livewire component (giỏ hàng, lọc sản phẩm...)
```

> Lưu ý về đường dẫn view: view của template được GP247 đăng ký dưới namespace đặc biệt
> `GP247TemplatePath::<TênTemplate>.<đường-dẫn>`. Ví dụ file `screen/home.blade.php` của template
> `MyShopSkin` được gọi là `GP247TemplatePath::MyShopSkin.screen.home`. Bạn **không** cần tự đăng ký —
> `gp247/front` tự quét toàn bộ `app/GP247/Templates`.

---

## 4. Bước 2 — Khai báo `gp247.json`

Mở `gp247.json`, nội dung mẫu sinh sẵn:

```json
{
    "name": "MyShopSkin module",
    "image": "images/logo.jpg",
    "auth": "GP247",
    "email": "support@gp247.net",
    "link": "https://GP247.net",
    "configGroup": "Templates",
    "configCode": "MyShopSkin",
    "configKey": "MyShopSkin",
    "version": "1.0",
    "requireCore": ["2.0"],
    "requireUpdateFrom": "1.0",
    "requirePackages": ["gp247/front"],
    "requireExtensions": []
}
```

Ý nghĩa các trường quan trọng:

| Trường | Ý nghĩa |
|---|---|
| `configGroup` | Luôn là `"Templates"` với template. |
| `configKey` | **Mã định danh duy nhất, trùng tên thư mục template.** Không đổi sau khi phát hành. |
| `version` | Phiên bản (semver: `1.0`, `1.1`...). Mỗi bản phát hành mới phải **tăng** số này. |
| `requireCore` | Phiên bản `gp247/core` tương thích. Chuẩn v2 để `["2.0"]`. |
| **`requirePackages`** | **Luôn có `"gp247/front"`** — vì template chỉ chạy khi có storefront. Thêm `"gp247/shop"` nếu template chỉ dành cho site có bán hàng. |
| `requireUpdateFrom` | Phiên bản tối thiểu được cập nhật 1-click lên bản này. Để `"1.0"` là an toàn. |

---

## 5. Bước 3 — Xây giao diện template

### 5.1. `AppConfig.php` — vòng đời template

File này kế thừa `ExtensionConfigDefault`, định nghĩa việc xảy ra khi chủ site cài/gỡ/bật/tắt template.
Điểm khác biệt so với plugin: phương thức `setupStore($storeId)` dùng để **gán template cho một cửa
hàng** (cập nhật cột `template` của `AdminStore`). Khung sinh sẵn đã chạy được, bạn thường chỉ cần bổ
sung logic riêng.

### 5.2. Layout tổng và các trang

- `layout.blade.php` (hoặc `layout/`): khung tổng gồm `<head>`, header, vùng nội dung, footer.
- `screen/`: từng trang cụ thể — ví dụ `home.blade.php` (trang chủ), `page_detail.blade.php`,
  `404.blade.php`, `front_search.blade.php`.
- `partials/`, `components/`: các mảnh giao diện dùng lại.

### 5.3. Giao diện động — dùng Livewire/Alpine, không jQuery

GP247 2.0 dùng **Tailwind + Alpine + Livewire**. Tương tác động (giỏ hàng, lọc sản phẩm...) làm bằng
Livewire component; view của chúng đặt trong `livewire/`. **Không** dùng jQuery/Bootstrap widget cũ.
Mọi chữ hiển thị phải render qua `gp247_language_render(...)` / `trans(...)`, không hardcode.

### 5.4. Asset và CSS Tailwind

- Asset tĩnh (css/js/ảnh) đặt trong `public/` của template; khi cài sẽ được copy sang
  `public/GP247/Templates/MyShopSkin`.
- GP247 dùng Tailwind **biên dịch sẵn** (không JIT runtime). Nếu template của bạn thêm class Tailwind
  mới, bạn phải tự biên dịch lại file CSS bằng Node.js (`npx tailwindcss ...`) và commit bản output.
  Nếu chỉ tái dùng class đã có sẵn thì không cần build lại.

---

## 6. ⭐ TEMPLATE VÀ `gp247/shop` — CƠ CHẾ FALLBACK VIEW (đọc kỹ)

Đây là phần quan trọng nhất của tài liệu. Nó trả lời câu hỏi: *"Template mới của tôi không có trang
sản phẩm/giỏ hàng, vậy website bán hàng có hỏng không?"* — **Không hỏng.**

### 6.1. Cơ chế hoạt động

Khi khách mở một trang do `gp247/shop` xử lý (ví dụ danh sách sản phẩm), controller của shop **không**
gọi thẳng một file view cố định. Nó gọi qua hàm chọn view `gp247_shop_process_view()` hoạt động như sau:

1. **Thử tìm view trong template đang hoạt động trước.** Ví dụ template `MyShopSkin` → thử
   `GP247TemplatePath::MyShopSkin.screen.shop_product_list` (tức file
   `app/GP247/Templates/MyShopSkin/screen/shop_product_list.blade.php`).
2. **Nếu template có file đó → dùng nó** (template đã "override" giao diện shop).
3. **Nếu template KHÔNG có → rơi về (fallback) view mặc định của gói shop:**
   `gp247-shop-front::screen.shop_product_list` (tức file
   `vendor/gp247/shop/src/Views/front/screen/shop_product_list.blade.php`).

**Hệ quả:** một template mới **không cần** chứa bất kỳ trang shop nào vẫn chạy website bán hàng bình
thường — mọi trang shop tự động dùng bản mặc định trong gói `gp247/shop`. Đây chính là lý do template
mẫu chỉ có các trang front (home, page_detail, 404...) mà không có `shop_*`.

### 6.2. Khi nào cần tự viết trang shop trong template?

Chỉ khi bạn muốn **giao diện shop khác với mặc định** (bố cục danh sách sản phẩm riêng, trang giỏ hàng
theo phong cách của bạn...). Lúc đó bạn tạo view shop **ngay trong template của mình**, đặt đúng
đường dẫn con mà shop mong đợi. Danh sách các trang shop có thể override (đặt trong `screen/` của template):

| File đặt trong template | Trang tương ứng |
|---|---|
| `screen/shop_product_list.blade.php` | Danh sách sản phẩm |
| `screen/shop_product_detail.blade.php` | Chi tiết sản phẩm |
| `screen/shop_cart.blade.php` | Giỏ hàng |
| `screen/shop_checkout.blade.php` | Thanh toán |
| `screen/shop_wishlist.blade.php` | Sản phẩm yêu thích |
| `screen/shop_compare.blade.php` | So sánh sản phẩm |
| `screen/shop_search.blade.php` | Tìm kiếm |
| `screen/shop_order_success.blade.php` | Đặt hàng thành công |

Ngoài `screen/`, gói shop còn có các thư mục view khác có thể override tương tự (`account/`, `auth/`,
`blocks/`, `common/`, `livewire/`) — đặt trong template theo đúng đường dẫn con giống trong gói shop.

### 6.3. Cách tham khảo template đầy đủ (gồm shop): publish view của `gp247/shop`

Để không phải viết trang shop từ con số 0, hãy **publish (xuất) view mặc định của `gp247/shop` ra ngoài**
làm bản tham khảo, rồi chép sang template của bạn để chỉnh:

1. Chạy lệnh publish view storefront của shop:

   ```bash
   php artisan vendor:publish --tag=gp247:shop-view-front
   ```

   Nếu thành công, terminal liệt kê các file được copy. Toàn bộ view front mặc định của shop được đổ
   vào thư mục template mặc định `app/GP247/Templates/GP247Front` (đây là đích cố định của lệnh này).

2. Mở thư mục vừa publish (`app/GP247/Templates/GP247Front`) để xem cấu trúc đầy đủ của một template có
   cả shop: `screen/shop_*.blade.php`, `account/`, `auth/`, `blocks/`, `common/`, `livewire/`...

3. **Chép các file trang shop bạn muốn tùy chỉnh sang template của bạn**, giữ nguyên đường dẫn con.
   Ví dụ muốn tùy chỉnh trang danh sách sản phẩm cho `MyShopSkin`:

   ```
   Chép:  app/GP247/Templates/GP247Front/screen/shop_product_list.blade.php
   Đến:   app/GP247/Templates/MyShopSkin/screen/shop_product_list.blade.php
   ```

   Sau đó sửa nội dung file trong `MyShopSkin` theo ý bạn. Nhờ cơ chế ở 6.1, `gp247/shop` sẽ **ưu tiên
   dùng bản trong template của bạn**, còn những trang shop bạn **không** chép thì vẫn tự dùng bản mặc định.

> Mẹo: chỉ chép những trang bạn thực sự muốn đổi. Chép cả bộ rồi để nguyên không sửa sẽ tạo gánh nặng
> bảo trì vô ích (và khi gói shop cập nhật, bản bạn giữ trong template sẽ không tự cập nhật theo).

> Phân biệt tag: `gp247:shop-view-front` là view **storefront** (dành cho template). Còn
> `gp247:shop-view-admin` là view **admin** của shop — **không** liên quan tới template storefront,
> đừng nhầm.

---

## 7. Bước 4 — Cập nhật phiên bản (an toàn khi update)

Template dùng **chung cơ chế cập nhật 1-click** với plugin: khi cập nhật, hệ thống ghi đè **toàn bộ
file** template nhưng **giữ nguyên database (`admin_config`)**. Vì vậy các nguyên tắc an toàn giống hệt
plugin cũng áp dụng cho template:

- `version` phải **tăng** mỗi bản; `configKey` **không đổi**.
- `config.php` chỉ chứa **giá trị mặc định** (bị ghi đè khi update); mọi lựa chọn của chủ site phải lưu
  trong `admin_config`.
- Đổi cấu trúc dữ liệu giữa các bản thì di trú trong hook `AppConfig::update($fromVersion)`.
- Không lưu file người dùng tải lên bên trong thư mục template.

Chi tiết đầy đủ về cơ chế update-an-toàn xem tài liệu
[Hướng dẫn tạo Plugin](./create-plugin_vi.md) (Phần 6) — nguyên tắc y hệt.

---

## 8. Bước 5 — Cài đặt và kiểm tra

1. Xoá cache Laravel để nạp route/view/config mới:

   ```bash
   php artisan optimize:clear
   ```

   Nếu thành công, terminal hiện các dòng như `... cleared successfully`.

2. Vào admin → mục **Templates**, tìm template của bạn, bấm **Cài đặt** rồi **Kích hoạt** (gán làm
   template đang dùng của cửa hàng).
3. Mở trang chủ website để xem giao diện template mới. Nếu có `gp247/shop`, mở thử trang danh sách sản
   phẩm và giỏ hàng: nếu bạn chưa override, chúng hiển thị bằng giao diện mặc định của shop (đúng như mong đợi).
4. Kiểm tra hiển thị trên **điện thoại** (responsive) và ở **nền tối (dark-mode)** nếu template có hỗ trợ.

---

## 9. Danh sách kiểm tra trước khi phát hành

- [ ] `gp247.json`: `configGroup` = `"Templates"`, `configKey` trùng tên thư mục, `requirePackages` có `"gp247/front"`.
- [ ] `version` đã **tăng** so với bản trước (nếu là bản cập nhật).
- [ ] Giao diện dùng Tailwind + Livewire/Alpine; **không** còn jQuery/Bootstrap widget cũ.
- [ ] Mọi chữ render qua `gp247_language_render(...)` / `trans(...)`, không hardcode; có cả `vi` và `en`.
- [ ] Nếu thêm class Tailwind mới: đã biên dịch lại CSS và kèm bản output trong `public/`.
- [ ] Đã quyết định rõ trang shop nào override (chép vào `screen/` template), trang nào để fallback mặc định.
- [ ] `config.php` chỉ chứa mặc định; lựa chọn chủ-site nằm trong `admin_config` (an toàn khi update).
- [ ] Đã `php artisan optimize:clear`, cài + kích hoạt thử thành công; trang chủ và trang shop hiển thị đúng.
- [ ] Kiểm tra responsive (điện thoại) và dark-mode (nếu hỗ trợ).

---

## 10. Hỏi & Đáp (Q&A)

**Câu 1: Template của tôi cài mà không thấy tác dụng gì?**
Nhiều khả năng website **chưa cài `gp247/front`**. Template là giao diện storefront, chỉ hoạt động khi
có `gp247/front`. Hãy cài `gp247/front` trước.

**Câu 2: Template mới của tôi không có trang sản phẩm/giỏ hàng thì website bán hàng có hỏng không?**
Không. `gp247/shop` tự động dùng view mặc định trong gói của nó (fallback) cho những trang mà template
không có. Website vẫn bán hàng bình thường. Xem Phần 6.1.

**Câu 3: Làm sao để trang giỏ hàng hiển thị theo giao diện riêng của tôi?**
Tạo file `screen/shop_cart.blade.php` **trong template của bạn**. `gp247/shop` sẽ ưu tiên dùng file này
thay cho bản mặc định. Xem bảng đường dẫn ở Phần 6.2.

**Câu 4: Tôi muốn xem một template đầy đủ có sẵn cả trang shop để tham khảo, làm sao?**
Chạy `php artisan vendor:publish --tag=gp247:shop-view-front`. Lệnh này xuất toàn bộ view storefront mặc
định của shop vào `app/GP247/Templates/GP247Front` để bạn xem/chép. Xem Phần 6.3.

**Câu 5: Publish xong rồi, tôi có phải chép hết sang template của tôi không?**
Không nên. Chỉ chép những trang bạn thực sự muốn đổi giao diện. Trang không chép sẽ tự dùng bản mặc định
— vừa gọn vừa dễ hưởng cập nhật từ gói shop.

**Câu 6: Đặt file shop sai chỗ thì sao — nó có bị bỏ qua không?**
Có. Cơ chế fallback tìm theo đúng đường dẫn con (ví dụ `screen/shop_product_list.blade.php`). Đặt sai tên
hoặc sai thư mục thì hệ thống coi như template không có, và dùng bản mặc định.

**Câu 7: `gp247:shop-view-front` và `gp247:shop-view-admin` khác nhau thế nào?**
`gp247:shop-view-front` là view **storefront** (dành cho template — cái bạn cần). `gp247:shop-view-admin`
là view **màn admin** của shop, không liên quan tới template giao diện khách hàng.

**Câu 8: Cập nhật template có làm mất cấu hình của chủ site không?**
Không, nếu bạn làm đúng: cập nhật ghi đè file nhưng giữ database. Mọi lựa chọn chủ site phải lưu trong
`admin_config`, không lưu trong `config.php` (file sẽ bị ghi đè). Xem Phần 7.

**Câu 9: Tôi thêm class Tailwind mới mà giao diện không đổi?**
GP247 dùng Tailwind biên dịch sẵn, không JIT. Class mới chưa được build sẽ không có hiệu lực. Bạn phải
biên dịch lại file CSS (`npx tailwindcss ...`) rồi mới thấy tác dụng.

**Câu 10: Sửa view xong mà trang vẫn hiện bản cũ?**
Chạy `php artisan optimize:clear` để xoá cache view/route/config rồi tải lại trang. Đây là lỗi hay gặp
nhất do Laravel còn giữ cache cũ.

---

<sub>📅 **Cập nhật lần cuối:** 2026-07-30 · ✍️ **Tác giả (Author):** GP247</sub>
