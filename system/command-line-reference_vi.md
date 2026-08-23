> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./command-line-reference.md)

# Danh sách lệnh dòng lệnh (CLI) của hệ thống GP247

## Giới thiệu

Tài liệu này liệt kê **toàn bộ lệnh Artisan (`php artisan ...`) do hệ thống GP247 cung cấp**,
gồm ba gói: `gp247/core`, `gp247/front` và `gp247/shop`. Mỗi lệnh có mô tả tính năng, ý nghĩa
từng tham số, trường hợp sử dụng (kể cả cách kết hợp với lệnh khác), và phiên bản gói kèm theo.
Tài liệu dành cho lập trình viên và người quản trị site GP247 — đọc xong bạn biết **gõ lệnh nào,
khi nào, với tham số gì**, và copy chạy được ngay.

> ℹ️ **Cách gõ lệnh:** mọi lệnh đều chạy trong **Terminal** (Command Prompt trên Windows), tại
> **thư mục gốc dự án** (nơi có file `artisan`). Cú pháp chung: `php artisan <tên-lệnh> [tham-số]`.
> Muốn xem nhanh mọi lệnh GP247 đang có, gõ: `php artisan list gp247`.

**Phiên bản áp dụng cho tài liệu này** (đọc từ `composer.lock` của dự án):

| Gói | Phiên bản | Ngày phát hành |
| --- | --- | --- |
| `gp247/core` | 2.0.4 | 2026-07-08 |
| `gp247/front` | 2.0.1 | 2026-07-07 |
| `gp247/shop` | 2.0.3 | 2026-07-08 |

> Phiên bản gói của bạn có thể khác. Kiểm tra bằng `php artisan gp247:core-info` hoặc mở
> `composer.lock`. Danh sách lệnh dưới đây ổn định trong toàn bộ nhánh 2.0.x.

---

## Bảng tra nhanh

| Lệnh | Gói | Chức năng ngắn gọn |
| --- | --- | --- |
| `gp247:core-install` | core | Cài đặt GP247 (migrate, seed, publish asset) |
| `gp247:core-update` | core | Cập nhật GP247 sau khi `composer update` |
| `gp247:core-info` | core | Xem thông tin & phiên bản hệ thống |
| `gp247:make-plugin` | core | Sinh khung một plugin mới |
| `gp247:language-update` | core | Ghi đè lại chuỗi ngôn ngữ theo mặc định gói (upsert) |
| `gp247:front-install` | front | Cài đặt module giao diện storefront |
| `gp247:front-uninstall` | front | Gỡ module front (xóa bảng front) |
| `gp247:make-template` | front | Sinh khung một template giao diện mới |
| `gp247:template-setup` | front | Thiết lập template mặc định cho store gốc |
| `gp247:shop-install` | shop | Cài đặt module bán hàng (ecommerce) |
| `gp247:shop-update` | shop | Nâng cấp schema/dữ liệu shop không phá dữ liệu (site đang chạy) |
| `gp247:shop-uninstall` | shop | Gỡ module shop (xóa bảng shop) |
| `gp247:shop-sample` | shop | Tạo dữ liệu mẫu (⚠️ xóa dữ liệu shop hiện có) |
| `gp247:shop-clear-cart` | shop | Xóa giỏ hàng / wishlist / so sánh đã hết hạn |
| `gp247:ext-list` | core | Liệt kê plugin/template local + trạng thái + bản cập nhật |
| `gp247:ext-install` | core | Cài plugin/template từ file `.zip`, thư mục, hoặc marketplace |
| `gp247:ext-enable` / `gp247:ext-disable` | core | Bật / tắt plugin/template đã cài |
| `gp247:ext-uninstall` | core | Gỡ plugin/template (tôn trọng protected + guard template) |
| `gp247:ext-update` | core | Cập nhật plugin/template từ marketplace (backup/rollback) |
| `gp247:ext-check-update` | core | Kiểm tra bản cập nhật trên marketplace |
| `gp247:ext-search` | core | Tìm kiếm catalog marketplace |
| `gp247:ext-license` | core | Đặt/xem/xóa license theo plugin của extension trả phí |
| `gp247:install` | core | Cài trọn bộ (core [+front] [+shop] [+sample]) |
| `gp247:update` | core | Làm mới sau `composer update` (core [+shop], an toàn cho site chạy) |
| `gp247:cache-rebuild` | core | Rebuild cache route/config |
| `gp247:doctor` | core | Kiểm tra môi trường (PHP/extension/quyền ghi/DB) |
| `gp247:info` | core | Xem trạng thái: version, marker cài đặt, số lượng extension |

---

## Hợp đồng output (`--json` & mã thoát)

Từ core 2.1, mọi lệnh `gp247:*` dùng chung một hợp đồng output để script, CI/CD,
Docker và cron tin cậy được:

- **`--json`**: thêm cờ này vào bất kỳ lệnh nào để nhận **một** envelope máy-đọc ra
  **stdout**; mọi dòng human/tiến trình/cảnh báo ra **stderr**, nên
  `php artisan gp247:info --json | jq` luôn sạch. Không có `--json` thì vẫn là
  text/bảng human như thường.
- **Cấu trúc envelope**:

  ```json
  { "ok": true, "command": "gp247:ext-install", "data": { }, "warnings": [], "error": null }
  ```

  Khi lỗi: `"ok": false` và `"error": { "code": "...", "message": "..." }`.
- **Mã thoát**: `0` khi thành công, khác `0` khi thất bại — cho **mọi** lệnh.

> ⚠️ **Breaking (core 2.1):** `gp247:make-plugin` / `gp247:make-template` nay xuất
> envelope trên thay cho khóa cũ `{"error":0,"path":"...","msg":"Success"}`. Đường dẫn
> zip nằm ở `data.path`. Tooling ngoài đang parse khóa cũ cần đọc envelope mới (dùng
> `--json` để lấy định dạng máy).

---

## Nhóm lệnh CORE — `gp247/core` (2.0.4)

### 1. `gp247:core-install`

**Chức năng:** Cài đặt toàn bộ nền tảng GP247 lần đầu. Lệnh sẽ chạy migrate cơ sở dữ liệu, seed
dữ liệu mặc định (admin, store, ngôn ngữ), publish asset của core và của laravel-filemanager, tạo
symbolic link `storage`, và ghi dấu đã cài (`storage/app/gp247-installed.txt`).

**Tham số:**

| Tham số | Giá trị | Ý nghĩa |
| --- | --- | --- |
| `--force` | `0` (mặc định) hoặc `1` | `0`: hỏi xác nhận và **chặn** nếu đã cài (có file `gp247-installed.txt`). `1`: **bỏ qua** cả kiểm tra đã-cài lẫn câu hỏi xác nhận, cài luôn (dùng cho cài tự động / script / Docker). |

**Cách dùng:**

```bash
php artisan gp247:core-install
```

Cài không cần hỏi (tự động hoá):

```bash
php artisan gp247:core-install --force=1
```

Nếu thành công, terminal in ra logo GP247 kèm dòng `Welcome to GP247`, đường dẫn trang admin và
tài khoản mặc định `admin/admin`.

**Trường hợp sử dụng & kết hợp:**
- Là **bước đầu tiên** sau khi tải mã nguồn, tạo file `.env` và cấu hình kết nối database.
- Nếu chưa có `APP_KEY`, lệnh tự gọi `key:generate` giúp bạn.
- Muốn **cài lại**: xóa file `storage/app/gp247-installed.txt` rồi chạy lại (hoặc dùng `--force=1`).
- Sau lệnh này thường chạy tiếp `gp247:front-install` và/hoặc `gp247:shop-install` (xem "Kịch bản
  cài đặt đầy đủ" ở cuối).

---

### 2. `gp247:core-update`

**Chức năng:** Cập nhật GP247 sau khi bạn nâng phiên bản gói bằng `composer update`. Lệnh seed lại
dữ liệu mặc định và dữ liệu ngôn ngữ ở chế độ **an toàn** (chỉ thêm bản ghi còn thiếu, **không**
ghi đè dữ liệu bạn đã chỉnh), cập nhật file tĩnh, rồi in ra phiên bản core hiện tại.

**Tham số:** không có.

**Cách dùng:**

```bash
composer update
php artisan gp247:core-update
```

**Trường hợp sử dụng & kết hợp:**
- Chạy **sau mỗi lần `composer update`** để đồng bộ dữ liệu nền và asset với mã nguồn mới.
- Nếu bản cập nhật có bổ sung chuỗi ngôn ngữ và bạn muốn **ghi đè** về đúng bản mới nhất của gói,
  chạy thêm `gp247:language-update` (xem lệnh số 5 — khác biệt an-toàn vs ghi-đè).
- Lưu ý kỹ thuật: bên trong, lệnh có gọi `gp247:customize static` để làm mới file tĩnh tùy biến.
  Lệnh phụ này được cung cấp bởi lớp tùy biến của bản cài; nếu môi trường của bạn không có nó,
  bước này sẽ báo lỗi riêng và được ghi log, không ảnh hưởng phần seed dữ liệu đã chạy trước đó.

---

### 3. `gp247:core-info`

**Chức năng:** In ra thông tin hệ thống GP247: tên, tác giả, **phiên bản core** và sub-version,
trang chủ, GitHub, Facebook, và endpoint API thư viện.

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:core-info
```

**Trường hợp sử dụng:**
- Kiểm tra nhanh **đang chạy phiên bản core nào** trước khi báo lỗi hoặc trước/sau khi cập nhật.
- Là cách nhanh nhất để lấy sub-version chính xác (giống dòng cuối mà `gp247:core-update` in ra).

---

### 4. `gp247:make-plugin`

**Chức năng:** Sinh sẵn **khung một plugin mới** theo chuẩn GP247 2.0 (đã có cấu trúc thư mục,
Controller admin, Model, Livewire mẫu, Seo/sitemap, file ngôn ngữ vi/en, Provider, Route,
`gp247.json`, `AppConfig`...). Nếu gói `gp247/front` đã được cài, plugin sinh ra có sẵn cả
FrontController và route front; nếu chưa, các phần front đó tự động bị bỏ.

**Tham số:**

| Tham số | Giá trị | Ý nghĩa |
| --- | --- | --- |
| `--name` | chuỗi (**bắt buộc**) | Tên plugin, ví dụ `MyBlog`. GP247 tự chuẩn hóa thành tên class và url key. Bỏ trống → báo lỗi `Command error`. |
| `--download` | `0` (mặc định) hoặc `1` | `0`: chép thẳng plugin vào `app/GP247/Plugins/<Tên>` và `public/GP247/Plugins/<Tên>`. `1`: **không** chép vào app mà đóng gói thành file `.zip` trong `storage/tmp` (với `--json`, đường dẫn zip nằm ở `data.path`). |

**Cách dùng:**

```bash
php artisan gp247:make-plugin --name=MyBlog --download=0
```

Chỉ tạo gói zip để tải về (không cài vào app):

```bash
php artisan gp247:make-plugin --name=MyBlog --download=1
```

Mặc định lệnh in text human (`Success: <path>` khi có tạo zip). Thêm `--json` để lấy envelope
chuẩn — ví dụ
`{"ok":true,"command":"gp247:make-plugin","data":{"key":"MyBlog","path":"...","msg":"Success"},"warnings":[],"error":null}`
(đường dẫn zip ở `data.path`). Xem mục "Hợp đồng output" phía trên.

**Trường hợp sử dụng & kết hợp:**
- Khi bắt đầu viết một plugin mới — dùng khung sinh sẵn để khỏi tạo tay từng file.
- Chạy **sau khi** đã `gp247:core-install`. Muốn plugin có phần giao diện storefront thì cài
  `gp247:front-install` trước để lệnh sinh kèm FrontController + route front.
- Nếu bạn nâng cấp một plugin cũ (1.x) lên 2.0, xem tài liệu `extension/convert-plugin-v1-to-v2_vi.md`.

---

### 5. `gp247:language-update`

**Chức năng:** Làm mới các dòng ngôn ngữ của core (và front/shop nếu đã cài) bằng chế độ
**upsert** — tức **ghi đè** text/vị trí của từng chuỗi về đúng giá trị mặc định của gói. Đây là
điểm khác biệt quan trọng so với `core-install`/`core-update` (hai lệnh đó chỉ **thêm bản ghi còn
thiếu**, không đụng tới chuỗi bạn đã sửa).

**Tham số:** không có. Lệnh tự chạy lần lượt core → front → shop; gói nào chưa cài thì bỏ qua và
báo `skipped (package not installed)`.

**Cách dùng:**

```bash
php artisan gp247:language-update
```

**Trường hợp sử dụng & kết hợp:**
- Khi bản cập nhật có **sửa/bổ sung chuỗi dịch** và bạn muốn lấy lại đúng bản chuẩn của gói.
- ⚠️ **Cẩn trọng:** lệnh này **ghi đè** các chuỗi ngôn ngữ bạn từng chỉnh trong admin. Nếu đã tùy
  biến nhiều text, hãy sao lưu trước. Nếu chỉ muốn thêm phần còn thiếu mà giữ nguyên chỉnh sửa,
  dùng `gp247:core-update` thay vì lệnh này.
- Thường chạy **sau** `composer update` + `gp247:core-update`.

---

## Nhóm lệnh FRONT — `gp247/front` (2.0.1)

> Nhóm này chỉ có khi dự án đã cài gói `gp247/front` (module giao diện storefront).

### 6. `gp247:front-install`

**Chức năng:** Cài đặt module front. Lệnh **tự gỡ front cũ trước** (gọi `gp247:front-uninstall`),
tạo lại bảng dữ liệu front, seed cấu hình front mặc định, publish asset + view của template mặc
định, rồi thiết lập template cho store gốc (gọi `gp247:template-setup`).

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:front-install
```

**Trường hợp sử dụng & kết hợp:**
- Chạy **sau `gp247:core-install`**, khi bạn cần một website có giao diện người dùng (storefront).
- Đây cũng là cách **cài lại sạch** module front (vì luôn uninstall trước rồi mới install).
- Nếu bước publish template lỗi (mạng/quyền ghi), copy thủ công thư mục template rồi chạy lại
  `gp247:template-setup`.

---

### 7. `gp247:front-uninstall`

**Chức năng:** Gỡ module front: xóa các bảng dữ liệu front (chạy phần `down()` của migration front)
và xóa bản ghi migration tương ứng.

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:front-uninstall
```

**Trường hợp sử dụng & kết hợp:**
- Khi muốn **gỡ hẳn** phần giao diện storefront khỏi hệ thống.
- Thường không cần gọi tay khi cài lại, vì `gp247:front-install` đã tự gọi lệnh này ở đầu.
- ⚠️ Xóa bảng đồng nghĩa **mất dữ liệu** cấu hình front — cân nhắc sao lưu trước.

---

### 8. `gp247:make-template`

**Chức năng:** Sinh sẵn **khung một template giao diện (storefront) mới** theo chuẩn GP247 2.0
(thư mục template, `AppConfig`, file ngôn ngữ vi/en, Provider, `gp247.json`...).

**Tham số:**

| Tham số | Giá trị | Ý nghĩa |
| --- | --- | --- |
| `--name` | chuỗi (**bắt buộc**) | Tên template, ví dụ `MyShopTheme`. Bỏ trống → báo lỗi `Command error`. |
| `--download` | `0` (mặc định) hoặc `1` | `0`: chép template vào `app/GP247/Templates/<Tên>` và `public/GP247/Templates/<Tên>`. `1`: đóng gói `.zip` trong `storage/tmp` thay vì chép vào app. |

**Cách dùng:**

```bash
php artisan gp247:make-template --name=MyShopTheme --download=0
```

**Trường hợp sử dụng & kết hợp:**
- Khi muốn tự làm một giao diện storefront riêng thay vì dùng `GP247Front` mặc định.
- Chạy sau khi đã cài `gp247/front`. Sau khi tạo và chọn template làm mặc định, dùng
  `gp247:template-setup` để áp cấu hình template cho store gốc.

---

### 9. `gp247:template-setup`

**Chức năng:** Thiết lập template mặc định cho **store gốc (root)**: nạp class `AppConfig` của
template mặc định (`GP247_TEMPLATE_FRONT_DEFAULT`), gọi `install()` và `setupStore()` để ghi cấu
hình hiển thị cho store gốc.

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:template-setup
```

**Trường hợp sử dụng & kết hợp:**
- Được `gp247:front-install` gọi tự động ở cuối; bạn chỉ cần chạy tay khi bước publish template
  trước đó bị lỗi và bạn đã copy template thủ công.
- Nếu terminal in `Class template Default not found`, nghĩa là template mặc định chưa được copy
  vào `app/GP247/Templates` — hãy publish/copy template rồi chạy lại.

---

## Nhóm lệnh SHOP — `gp247/shop` (2.0.3)

> Nhóm này chỉ có khi dự án đã cài gói `gp247/shop` (module bán hàng). `gp247/shop` yêu cầu sẵn
> `gp247/core` và `gp247/front`.

### 10. `gp247:shop-install`

**Chức năng:** Cài đặt module bán hàng (ecommerce). Lệnh **tự gỡ shop cũ trước** (gọi
`gp247:shop-uninstall`), tạo lại bảng dữ liệu shop, seed dữ liệu khởi tạo + dữ liệu mặc định cho
store gốc, rồi publish view front của shop.

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:shop-install
```

**Trường hợp sử dụng & kết hợp:**
- Chạy **sau `gp247:core-install` và `gp247:front-install`** để biến site thành cửa hàng trực tuyến.
- Cũng dùng để **cài lại sạch** module shop (luôn uninstall trước).
- Sau khi cài, nếu muốn có dữ liệu demo để xem thử, chạy `gp247:shop-sample` (xem cảnh báo bên dưới).

---

### 11. `gp247:shop-update`

> ℹ️ **Có từ:** gp247/shop 2.1

**Chức năng:** Nâng cấp module shop **không phá dữ liệu** cho một site đã cài sẵn. Khác với
`gp247:shop-install` (xóa và tạo lại toàn bộ bảng shop → **mất dữ liệu**), lệnh này chỉ chạy các
**migration nâng cấp tăng dần, idempotent** trong thư mục
`vendor/gp247/shop/src/Admin/Database/Migrations/upgrade` (cố tình **không** đụng tới migration tạo
bảng gốc), nên site đang chạy **giữ nguyên** danh mục, sản phẩm, đơn hàng... Sau khi migrate xong,
lệnh nhắc bạn chạy `gp247:language-update` để làm mới các nhãn ngôn ngữ mới/đổi tên (ví dụ nhãn địa
chỉ city/district và address1/2/3).

**Tham số:** không có.

**Cách dùng:**

```bash
composer update
php artisan gp247:shop-update
```

**Trường hợp sử dụng & kết hợp:**
- Chạy **sau `composer update`** khi bạn nâng phiên bản `gp247/shop` trên **site thật đang có dữ
  liệu** — đây là lệnh thay thế an toàn cho `gp247:shop-install` (vốn chỉ dành cho cài mới / cài lại
  sạch, sẽ xóa dữ liệu).
- Lệnh **idempotent**: chạy lại nhiều lần không gây hại; migration nào đã áp dụng trước đó sẽ được
  bỏ qua.
- Nếu bản nâng cấp có **bổ sung/đổi tên chuỗi ngôn ngữ**, chạy tiếp `gp247:language-update` như lệnh
  nhắc (xem lệnh CORE số 5 — lưu ý cơ chế ghi đè chuỗi đã tùy biến).
- Nếu migrate lỗi, terminal in `Shop upgrade failed: ...` và trả về mã lỗi; lỗi được ghi log qua
  `gp247_report`. Xử lý nguyên nhân rồi chạy lại.

---

### 12. `gp247:shop-uninstall`

**Chức năng:** Gỡ module shop: xóa các bảng dữ liệu shop (chạy `down()` của migration shop) và xóa
bản ghi migration tương ứng.

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:shop-uninstall
```

**Trường hợp sử dụng & kết hợp:**
- Khi muốn **gỡ hẳn** chức năng bán hàng.
- Thường không cần gọi tay khi cài lại, vì `gp247:shop-install` đã tự gọi ở đầu.
- ⚠️ Xóa bảng shop = **mất toàn bộ** danh mục, sản phẩm, đơn hàng... — sao lưu trước khi chạy.

---

### 13. `gp247:shop-sample`

**Chức năng:** Tạo **dữ liệu mẫu** cho cửa hàng: danh mục nhiều cấp, thương hiệu, nhà cung cấp,
sản phẩm đơn, sản phẩm bộ (bundle), sản phẩm nhóm (group), và khuyến mãi mẫu.

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:shop-sample
```

**Trường hợp sử dụng & kết hợp:**
- Dùng cho **môi trường demo / dev** để có sẵn sản phẩm mà xem giao diện, thử tính năng.
- ⚠️ **RẤT quan trọng:** lệnh này **xóa sạch (truncate) các bảng shop hiện có** trước khi tạo mẫu
  (danh mục, sản phẩm, thương hiệu, nhà cung cấp, thuộc tính...). **Tuyệt đối không chạy trên site
  thật đang có dữ liệu.**
- Thường chạy ngay sau `gp247:shop-install` trên máy dev.

---

### 14. `gp247:shop-clear-cart`

**Chức năng:** Dọn các giỏ hàng đã **hết hạn**: giỏ hàng (`default`), danh sách yêu thích
(`wishlist`) và so sánh (`compare`), dựa trên số ngày cấu hình trong
`config('gp247-config.shop.cart_expire.*')`.

**Tham số:** không có.

**Cách dùng:**

```bash
php artisan gp247:shop-clear-cart
```

**Trường hợp sử dụng & kết hợp:**
- Đây là lệnh **bảo trì định kỳ** — nên đưa vào bộ lập lịch (scheduler/cron) để chạy hằng ngày,
  giúp bảng giỏ hàng không phình to.
- Ví dụ cron chạy 2 giờ sáng mỗi ngày:

  ```bash
  0 2 * * * cd /đường-dẫn-dự-án && php artisan gp247:shop-clear-cart >> /dev/null 2>&1
  ```

- Điều chỉnh số ngày hết hạn trong cấu hình `gp247-config.shop.cart_expire` (`cart`, `wishlist`,
  `compare`).

---

## Lệnh vòng đời extension — `gp247:ext-*` (core 2.1)

Đưa toàn bộ vòng đời plugin/template (trước chỉ có ở admin UI) lên CLI. Plugin và template
dùng chung một họ lệnh; chọn bằng `--type=plugin|template` (mặc định `plugin`). Tất cả đều
hỗ trợ `--json`.

| Lệnh | Option chính | Chức năng |
| --- | --- | --- |
| `gp247:ext-list` | `--type` | Liệt kê extension local kèm installed/active/version và có bản mới hay không (cache-only, không gọi API). |
| `gp247:ext-install` | `--type`, `--file=<zip>`, `--dir=<thư-mục>`, `--key=<key>`, `--paid`, `--license=` | Cài từ file `.zip` (`--file`), thư mục đã giải nén (`--dir`), hoặc theo key (`--key`). Với key: nếu extension **đã cài** → từ chối; nếu file **đã có trên đĩa nhưng chưa cài** (vd plugin bundled như `News`) → cài tại chỗ (giống nút "Install" của admin); còn lại → tải từ marketplace (thêm `--paid --license=...` cho item trả phí). |
| `gp247:ext-enable` | `--type`, `--key` | Bật extension **đã cài** (từ chối kèm lỗi rõ nếu chưa cài). |
| `gp247:ext-disable` | `--type`, `--key` | Tắt extension đã cài (từ chối nếu chưa cài, hoặc template đang được dùng). |
| `gp247:ext-uninstall` | `--type`, `--key`, `--only-data`, `--purge` | Gỡ (tôn trọng `extension_protected` + guard template đang-dùng/mặc-định). **Đã cài**: xóa cấu hình DB **và** xóa file; `--only-data` thì giữ file. **Chưa cài nhưng có trên đĩa**: từ chối trừ khi có `--purge` (khi đó chỉ xóa file). `--only-data` và `--purge` không dùng chung. |
| `gp247:ext-update` | `--type`, `--key`, `--all` | Cập nhật 1 extension hoặc mọi extension có bản mới (backup + rollback). |
| `gp247:ext-check-update` | `--type`, `--force` | Báo các bản cập nhật (dùng cache trừ khi `--force`). |
| `gp247:ext-search` | `--type`, `--keyword=`, `--free`, `--page=` | Duyệt/tìm catalog marketplace. |
| `gp247:ext-license` | `--type`, `--key`, `--license=`, `--delete` | Đặt / xem / xóa license theo plugin của extension trả phí (lưu ở `admin_config`, không đụng `.env`). |

Ví dụ:

```bash
php artisan gp247:ext-list --type=plugin --json
php artisan gp247:ext-install --type=plugin --file=storage/tmp/MyBlog.zip
php artisan gp247:ext-install --type=plugin --key=News
php artisan gp247:ext-enable --type=plugin --key=News
php artisan gp247:ext-update --type=plugin --all
php artisan gp247:ext-uninstall --type=plugin --key=News
```

> **Nhiều item (batch).** `ext-install`, `ext-enable`, `ext-disable`, `ext-uninstall` nhận
> **nhiều key** — lặp option (`--key=A --key=B`) hoặc phân tách bằng dấu phẩy (`--key=A,B`);
> `ext-install` cũng nhận nhiều `--file`/`--dir`. Các item được xử lý **từng cái một, độc
> lập** (không có transaction nguyên tử xuyên nhiều extension — mỗi cái là một đơn vị
> file+migration+config riêng), báo cáo kết quả **theo từng item**, rebuild cache route/config
> **một lần** ở cuối, và lệnh thoát khác 0 nếu **bất kỳ** item nào fail. **Extension trả phí
> phải cài từng cái một** — `--paid` đi kèm nhiều `--key` sẽ **bị từ chối ngay từ đầu**
> (`error.code: paid_multi_not_allowed`), vì một `--license` sẽ bị áp nhầm cho các plugin
> khác. Mỗi lần cài trả phí dùng đúng một `--key`.

> **Chạy lại / đã cài rồi.** "Đã cài" = có dòng `admin_config` (không phải chỉ có file trên
> đĩa). `ext-install` một extension **đã cài** → từ chối với lỗi "đã tồn tại" (remote kiểm tra
> **trước khi** tải — không tốn băng thông), và **không** tạo bản ghi `admin_config` trùng. Muốn
> làm mới extension đã cài dùng `gp247:ext-update`; muốn cài lại thì `gp247:ext-uninstall` trước.
> Trong batch, key đã-cài chỉ bị ghi vào `failed`, các item khác vẫn chạy tiếp. Đối xứng lại:
> `ext-enable`/`ext-disable`/`ext-uninstall` coi extension **chưa cài** là lỗi (enable/disable từ
> chối; uninstall từ chối trừ khi `--purge`), nên plugin bundled trên đĩa không bị "bật" thành
> no-op hay bị xóa bất ngờ.

> CLI và admin UI nay chạy **cùng một** engine bên dưới (`ExtensionInstaller` /
> `LibraryClient`) nên hành vi giống hệt nhau dù dùng đường nào. Extension được bảo vệ và
> template đang-dùng/mặc-định bị từ chối gỡ ở cả hai.

---

## Điều phối & chẩn đoán (core 2.1)

| Lệnh | Option chính | Chức năng |
| --- | --- | --- |
| `gp247:install` | `--with-front`, `--with-shop`, `--sample`, `--force=1` | Chạy cài đặt trọn bộ theo thứ tự: `core-install` → (`front-install`) → (`shop-install`) → (`shop-sample`). Một bước lỗi thì dừng với mã thoát khác 0. `--with-shop` kéo theo `--with-front`. |
| `gp247:update` | `--overwrite-lang` | Làm mới an toàn sau `composer update` cho site đang chạy: `core-update`, rồi `shop-update` (chỉ khi shop đã cài), tùy chọn `language-update` (`--overwrite-lang`), rồi `cache-rebuild`. Không bao giờ chạy bước (re)install phá dữ liệu. |
| `gp247:cache-rebuild` | — | Rebuild cache route/config (sau khi bật/cập nhật extension). |
| `gp247:doctor` | `--json` | Kiểm tra môi trường: PHP ≥ 8.2, extension bắt buộc, quyền ghi, kết nối DB, marker cài đặt. Thoát khác 0 nếu có mục fail — dùng làm cổng CI/tiền-cài-đặt. |
| `gp247:info` | `--json` | Xem trạng thái: version package đã cài (core/front/shop), marker cài đặt, số lượng plugin/template, endpoint API marketplace. Chỉ đọc. |

Ví dụ:

```bash
php artisan gp247:install --with-front --with-shop --force=1
php artisan gp247:update
php artisan gp247:doctor --json
php artisan gp247:info --json
```

---

## Lệnh `vendor:publish` hay dùng kèm

Đây là các lệnh chuẩn của Laravel (không phải lệnh riêng của GP247) nhưng thường đi cùng khi cài
đặt hoặc khi muốn **tùy biến giao diện**. Chúng dùng cờ `--tag` để chọn nhóm file cần xuất bản.

| Lệnh | Xuất bản gì |
| --- | --- |
| `php artisan vendor:publish --tag=gp247:core-public --force` | Asset (CSS/JS) admin của core ra `public/GP247` |
| `php artisan vendor:publish --tag=gp247:front-public --force` | Asset của module front |
| `php artisan vendor:publish --tag=gp247:front-view --force` | View template front mặc định |
| `php artisan vendor:publish --tag=gp247:shop-view-admin` | View admin của shop (để override) → `resources/views/vendor/gp247-shop-admin` |
| `php artisan vendor:publish --tag=gp247:shop-view-front` | View front của shop → `app/GP247/Templates/GP247Front` |

> Sau khi **cập nhật gói** bằng `composer update`, nếu giao diện admin không đổi theo, hãy chạy
> lại `vendor:publish --tag=gp247:core-public --force` để làm mới asset đã publish.

---

## Kịch bản cài đặt đầy đủ (thứ tự khuyến nghị)

Chạy lần lượt các bước sau cho một site GP247 ecommerce hoàn chỉnh:

1. Chuẩn bị `.env` (kết nối database) và cài mã nguồn qua Composer.
2. Cài nền tảng core:

   ```bash
   php artisan gp247:core-install
   ```

3. Cài module giao diện storefront:

   ```bash
   php artisan gp247:front-install
   ```

4. Cài module bán hàng:

   ```bash
   php artisan gp247:shop-install
   ```

5. (Chỉ trên máy dev/demo) Tạo dữ liệu mẫu:

   ```bash
   php artisan gp247:shop-sample
   ```

Sau khi vận hành, mỗi lần nâng cấp:

```bash
composer update
php artisan gp247:core-update
php artisan gp247:shop-update   # nếu có dùng module shop (nâng cấp an toàn, không mất dữ liệu)
```

Và đặt cron dọn giỏ hàng hết hạn: `php artisan gp247:shop-clear-cart` (chạy hằng ngày).

---

## Hỏi & Đáp (Q&A)

**Câu 1: Làm sao xem nhanh toàn bộ lệnh GP247 đang có trên máy tôi?**

→ Gõ `php artisan list gp247`. Terminal sẽ liệt kê mọi lệnh có tiền tố `gp247:` kèm mô tả ngắn.

**Câu 2: Chạy `gp247:core-install` báo "GP247 has been installed" thì sao?**

→ Nghĩa là đã cài rồi (có file `storage/app/gp247-installed.txt`). Nếu muốn cài lại, xóa file đó rồi
chạy lại, hoặc dùng `php artisan gp247:core-install --force=1`.

**Câu 3: Khác nhau giữa `gp247:core-update` và `gp247:language-update`?**

→ `core-update` seed **an toàn** (chỉ thêm bản ghi còn thiếu, giữ nguyên chỉnh sửa của bạn).
`language-update` **ghi đè** chuỗi ngôn ngữ về đúng bản mặc định của gói. Muốn giữ text đã tùy biến
thì dùng `core-update`; muốn lấy lại bản chuẩn mới nhất thì dùng `language-update`.

**Câu 4: `gp247:shop-sample` có an toàn để chạy trên site thật không?**

→ **Không.** Lệnh này **xóa sạch** dữ liệu shop hiện có trước khi tạo mẫu. Chỉ chạy trên môi trường
demo/dev.

**Câu 5: Cài `gp247:shop-install` báo lỗi thiếu bảng / thiếu lớp thì sao?**

→ `gp247/shop` cần `gp247/core` và `gp247/front` đã cài trước. Hãy chạy đúng thứ tự:
`core-install` → `front-install` → `shop-install`.

**Câu 6: Tôi không dùng bán hàng, chỉ cần website giới thiệu thì cài gì?**

→ Chỉ cần `gp247:core-install` rồi `gp247:front-install`. Bỏ qua nhóm lệnh shop.

**Câu 7: `--download=1` trong `make-plugin`/`make-template` khác gì `--download=0`?**

→ `--download=0` chép thẳng plugin/template vào thư mục `app/GP247/...` để dùng ngay.
`--download=1` **không** chép vào app mà đóng gói thành file `.zip` trong `storage/tmp` để bạn tải
về / mang đi nơi khác.

**Câu 8: Sau khi `composer update`, giao diện admin không thay đổi?**

→ Chạy `php artisan gp247:core-update`. Nếu vẫn chưa đổi, publish lại asset:
`php artisan vendor:publish --tag=gp247:core-public --force`.

**Câu 9: Lệnh `gp247:customize static` mà `core-update` gọi là gì, tôi có cần cài không?**

→ Đó là lệnh nội bộ làm mới file tĩnh tùy biến, được cung cấp bởi lớp tùy biến của bản cài. Bạn không
gọi trực tiếp. Nếu môi trường không có nó, bước đó chỉ báo lỗi riêng và ghi log, không làm hỏng
phần cập nhật dữ liệu.

**Câu 10: Làm sao biết chính xác phiên bản core/front/shop đang chạy?**

→ Chạy `php artisan gp247:core-info` (xem phiên bản core), hoặc mở file `composer.lock` và tìm
`gp247/core`, `gp247/front`, `gp247/shop`.

---

## Lịch sử thay đổi
<!-- Chỉ ghi khi có thay đổi về logic/hành vi. Dòng mới nhất ở trên cùng. -->

| Ngày | Phiên bản GP247 | Thay đổi |
| --- | --- | --- |
| 2026-08-24 | gp247/core 2.1 | `ext-install --key` cài plugin bundled/có-sẵn-trên-đĩa tại chỗ (hoặc từ chối nếu đã cài); `ext-enable`/`ext-disable` từ chối extension chưa cài; `ext-uninstall` từ chối extension chưa-cài-trên-đĩa trừ khi `--purge` (`--only-data`/`--purge` loại trừ nhau). |
| 2026-08-23 | gp247/core 2.1 | Chuẩn hóa hợp đồng output CLI (`--json` + mã thoát cho mọi lệnh); thêm họ vòng đời extension `gp247:ext-*`, và `gp247:install` / `gp247:update` / `gp247:cache-rebuild` / `gp247:doctor` / `gp247:info`. **Breaking:** `make-plugin`/`make-template` nay xuất envelope JSON (đường dẫn ở `data.path`). |
| 2026-08-22 | gp247/shop 2.1 | Thêm lệnh `gp247:shop-update` — nâng cấp shop không phá dữ liệu cho site đang chạy |

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-24 · ✍️ **Tác giả (Author):** GP247</sub>
