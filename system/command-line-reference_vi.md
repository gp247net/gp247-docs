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
| `gp247:shop-uninstall` | shop | Gỡ module shop (xóa bảng shop) |
| `gp247:shop-sample` | shop | Tạo dữ liệu mẫu (⚠️ xóa dữ liệu shop hiện có) |
| `gp247:shop-clear-cart` | shop | Xóa giỏ hàng / wishlist / so sánh đã hết hạn |

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
| `--download` | `0` (mặc định) hoặc `1` | `0`: chép thẳng plugin vào `app/GP247/Plugins/<Tên>` và `public/GP247/Plugins/<Tên>`. `1`: **không** chép vào app mà đóng gói thành file `.zip` trong `storage/tmp` (kết quả JSON trả về đường dẫn file zip). |

**Cách dùng:**

```bash
php artisan gp247:make-plugin --name=MyBlog --download=0
```

Chỉ tạo gói zip để tải về (không cài vào app):

```bash
php artisan gp247:make-plugin --name=MyBlog --download=1
```

Lệnh trả về một chuỗi JSON dạng `{"error":0,"path":"...","msg":"Success"}`.

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

### 11. `gp247:shop-uninstall`

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

### 12. `gp247:shop-sample`

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

### 13. `gp247:shop-clear-cart`

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

<sub>📅 **Cập nhật lần cuối:** 2026-07-29 · ✍️ **Tác giả (Author):** GP247</sub>
