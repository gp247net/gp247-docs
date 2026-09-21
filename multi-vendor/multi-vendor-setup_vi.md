> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./multi-vendor-setup.md)

# Multi-Vendor — Phần 1: Cài đặt

## Giới thiệu
Tài liệu này hướng dẫn cài S-Cart 3.x, cài và kích hoạt plugin **Multi-Vendor** (bản miễn phí), cài thêm **bản Pro** nếu bạn đã mua, tạo gian hàng đầu tiên và kiểm tra sàn chạy đúng. Dành cho chủ website hoặc người quản trị có kỹ thuật cơ bản; làm theo từng bước là chạy được, không cần biết lập trình. Cách sàn vận hành và ý nghĩa từng cấu hình xem ở [Phần 2 — Vận hành](./multi-vendor-operations_vi.md).

## Yêu cầu trước khi bắt đầu
- PHP **8.3** trở lên, Composer, MySQL/MariaDB (SQLite chỉ để thử nhanh).
- S-Cart **3.x** với `gp247/core` **3.0** và `gp247/shop` đã cài (plugin dựa vào sản phẩm, giỏ hàng, đơn hàng của shop).
- **Chưa cài** plugin Multi-Store trên cùng website — hai mô hình loại trừ lẫn nhau.
- Có quyền chạy lệnh `php artisan` trên máy chủ, **hoặc** dùng cách cài qua giao diện admin ở Bước 2.

## Bước 1: Cài S-Cart 3.x
Bỏ qua bước này nếu website S-Cart 3.x của bạn đã chạy.

1. Mở **Terminal** (trên Windows là "Command Prompt") tại thư mục muốn đặt website, gõ đúng hai dòng sau rồi nhấn Enter:

   ```bash
   composer create-project gp247/s-cart website-folder
   cd website-folder
   ```

   Nếu thành công, thư mục `website-folder` xuất hiện với file `.env` được tạo sẵn.

2. Mở file `.env` bằng trình soạn thảo văn bản, sửa phần kết nối cơ sở dữ liệu cho đúng máy của bạn:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=s-cart
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. Tạo khóa ứng dụng:

   ```bash
   php artisan key:generate
   ```

4. Khởi tạo S-Cart (tạo bảng, dữ liệu mặc định, tài khoản admin):

   ```bash
   php artisan gp247:install
   ```

   Làm theo các câu hỏi trên màn hình. Nếu thành công, lệnh in ra đường dẫn khu admin và tài khoản đăng nhập — hãy lưu lại.

5. (Tùy chọn) Nạp dữ liệu mẫu cho cửa hàng để có sản phẩm thử:

   ```bash
   php artisan gp247:shop-sample
   ```

Tham khảo thêm: [Tổng quan S-Cart](https://gp247.net/vi/s-cart/s-cart-overview.html).

## Bước 2: Cài plugin Multi-Vendor (bản miễn phí)
Chọn **một** trong ba cách.

**Cách 1 — Từ thư viện GP247 (khuyến nghị)**
1. Đăng nhập khu admin → **Phần mở rộng** → **Tiện ích (Plugins)**.
2. Mở tab **Thư viện**, tìm **MultiVendor**, bấm **Cài đặt**.

**Cách 2 — Nhập file zip**
1. Vào **Phần mở rộng** → **Tiện ích** → tab **Nhập file**.
2. Chọn file zip của plugin và tải lên.

**Cách 3 — Chép tay (khi hai cách trên lỗi)**
1. Giải nén, chép thư mục mã nguồn vào `app/GP247/Plugins/MultiVendor/`.
2. Chép thư mục `public` của plugin vào `public/GP247/Plugins/MultiVendor/`.
3. Vào **Phần mở rộng** → **Tiện ích** → tab **Đã lưu trên máy**, tìm **MultiVendor**, bấm **Cài đặt**.

Nếu thành công, plugin hiện trong danh sách với nút **Kích hoạt**. Chi tiết: [Hướng dẫn cài phần mở rộng](https://gp247.net/vi/user-guide-extension/guide-to-installing-the-extension.html).

## Bước 3: Kích hoạt và cấu hình sàn
1. Trong danh sách Tiện ích, bấm **Kích hoạt** ở dòng MultiVendor. Menu **Chợ bán hàng** xuất hiện ở thanh bên admin.
2. Vào **Chợ bán hàng** → **Cấu hình nhanh**, đặt các giá trị cơ bản:
   - **Tỷ lệ hoa hồng (%)** sàn giữ lại.
   - **Cho phép đăng ký vendor** — bật nếu muốn người bán tự đăng ký.
   - **Tự động duyệt vendor** / **Tự động duyệt sản phẩm** — tắt nếu muốn kiểm duyệt tay.
3. Bấm **Lưu**. Ý nghĩa đầy đủ từng cấu hình xem [Phần 2 — Vận hành](./multi-vendor-operations_vi.md).

Nếu bạn dùng bản miễn phí, các công tắc thuộc bản Pro hiện ở trạng thái **khóa** kèm link giải thích — đó là trạng thái đúng, không phải lỗi.
Công tắc bị khóa luôn hiển thị **đúng thứ đang chạy**, không phải giá trị đã lưu trước đó: nếu site từng dùng bản Pro rồi tắt đi, các ô đó hiện **không bật**
và ô *Phạm vi xử lý đơn của người bán* hiện **Chỉ trạng thái vận chuyển** — vì đó mới là những gì bản miễn phí thực sự áp dụng. Giá trị bạn từng lưu
**không mất**: mở lại bản Pro là nó trở về như cũ.

## Bước 4: Tạo gian hàng đầu tiên

**Cách A — Admin tạo**
1. **Chợ bán hàng** → **Gian hàng người bán** → **Thêm mới**: nhập mã gian hàng (chỉ chữ và số, tối đa 20 ký tự, ví dụ `vendor01`), tên, mô tả → **Lưu**.
2. **Chợ bán hàng** → **Tài khoản người bán** → **Thêm mới**: nhập email, mật khẩu, chọn gian hàng vừa tạo, trạng thái **Kích hoạt** → **Lưu**.
3. Mở `https://ten-mien-cua-ban/vendor_admin`, đăng nhập bằng email/mật khẩu trên. Nếu thành công, bạn thấy bảng điều khiển của gian hàng.

**Cách B — Người bán tự đăng ký** (khi đã bật "Cho phép đăng ký vendor")
1. Người bán mở `https://ten-mien-cua-ban/vendor_admin/register`, điền thông tin và mã gian hàng.
2. Nếu sàn tắt "Tự động duyệt vendor", gian hàng ở trạng thái chờ; admin vào **Gian hàng người bán** bật trạng thái mở để họ đăng nhập được.

**Dữ liệu mẫu để thử nhanh** (chỉ dùng trên site thử nghiệm):

```bash
php artisan gp247:vendor-sample
```

Lệnh tạo 3 gian hàng mẫu `vendor01`–`vendor03`. Mỗi gian hàng có sẵn một tài khoản đăng nhập, một nhà cung cấp, 3 danh mục riêng và 9 sản phẩm chia đều 3 sản phẩm mỗi danh mục, để cả trang gian hàng lẫn bộ lọc danh mục đều có nội dung để xem:

| Trang gian hàng | Tài khoản người bán | Mật khẩu |
| --- | --- | --- |
| `/shop/vendor01` | `vendor01@gp247.local` | `123456` |
| `/shop/vendor02` | `vendor02@gp247.local` | `123456` |
| `/shop/vendor03` | `vendor03@gp247.local` | `123456` |

Ba gian hàng đúng bằng giới hạn của bản miễn phí, nên dữ liệu mẫu dùng được cho cả hai bản. Chạy lại lệnh sẽ thay thế chính các gian hàng mẫu đó chứ không tạo thêm, và không đụng tới gian hàng bạn tự tạo. **Hãy đổi hoặc xóa các tài khoản này trước khi đưa site lên chạy thật.**

Lệnh còn **tự lo plugin *Product Rating & Review*** để trang gian hàng có sẵn tab **Đánh giá**: plugin đã cài thì giữ nguyên (chỉ bật lên nếu đang tắt),
chưa cài thì cài — lấy từ thư mục plugin nếu site đã có sẵn file, không có thì tải từ kho plugin. Nếu site không ra được Internet, hoặc bản bạn dùng cần
giấy phép, lệnh **vẫn seed xong dữ liệu mẫu** và chỉ in ra câu lệnh cần chạy để cài tiếp. Không muốn lệnh chạm tới plugin nào khác:

```bash
php artisan gp247:vendor-sample --skip-rating
```

## Bước 5: Kiểm tra sàn chạy đúng
1. Mở `https://ten-mien-cua-ban/shop/vendor01` — trang gian hàng hiện danh mục và sản phẩm của người bán (trống nếu chưa đăng sản phẩm).
2. Đăng nhập `/vendor_admin`, tạo một sản phẩm; nếu sàn tắt tự duyệt, vào admin S-Cart để duyệt.
3. **Phép thử quan trọng nhất**: mua sản phẩm của hai gian hàng khác nhau trong một giỏ rồi thanh toán — kết quả phải ra **hai đơn hàng**, mỗi đơn thuộc một gian hàng.
4. Kiểm tra sức khỏe hệ thống:

   ```bash
   php artisan gp247:info
   php artisan gp247:doctor
   ```

## Bước 6 (tùy chọn): Cài thêm bản Pro
Chỉ làm bước này nếu bạn đã mua bản Pro. Bản Pro **cài thêm** lên bản miễn phí, không thay thế nó.

1. Kiểm tra dòng **MultiVendor** trong danh sách Tiện ích đang ở trạng thái đã cài và **đã kích hoạt**. Plugin Pro khai bản miễn phí là phần mở rộng bắt buộc và sẽ từ chối cài khi thiếu.
2. Cài **MultiVendorPro** bằng một trong ba cách giống Bước 2 (thường là **Nhập file zip** bạn nhận được từ GP247).
3. Bấm **Kích hoạt**. **Cài mà không kích hoạt thì Pro chưa có hiệu lực** — đây là nguyên nhân phổ biến nhất của "cài rồi mà vẫn thấy trang giải thích".
4. Chạy lệnh sau (hoặc bấm xóa cache trong admin) để các đường dẫn của Pro được đăng ký:

   ```bash
   php artisan gp247:cache-rebuild
   ```

5. Tải lại khu admin và kiểm ba dấu hiệu đã mở khóa:
   - Menu **Chợ bán hàng** → bấm một mục có nhãn *(Pro)*: mở **màn thật**, không còn trang giải thích.
   - **Cấu hình nhanh**: các công tắc từng bị khóa (đặt hàng nhanh, phạm vi xử lý đơn, xác minh danh tính, khiếu nại, các email bổ sung) nay **sửa được**.
   - Đăng nhập một tài khoản người bán ở `/vendor_admin`: thanh bên có thêm các mục Pro (Xác minh danh tính, Gói của tôi, Tạo đơn hàng, Nhóm giá, Đánh giá, Plugin của gian hàng).

Không phải cài lại gì và không phải chuyển dữ liệu: gian hàng, tài khoản người bán, đơn hàng và sổ chi trả giữ nguyên. Tỷ lệ hoa hồng riêng bạn đã đặt khi còn dùng bản miễn phí có hiệu lực ngay.

**Bật những gì bạn cần** — bản Pro không tự bật tính năng nào có thể làm phiền người bán:

| Cấu hình | Nên bật khi | Mặc định |
| --- | --- | --- |
| Đặt hàng nhanh | Bạn bán sỉ cho đại lý, khách đặt nhiều dòng một lần | Tắt |
| Vendor được làm gì với đơn | Bạn muốn người bán tự xác nhận hoặc tự hoàn tất đơn | Xác nhận + giao hàng |
| Bắt buộc xác minh danh tính (KYC) | Sàn cần biết người bán là ai trước khi cho bán hoặc trước khi chi tiền | Tắt |
| Cửa sổ khiếu nại / số ngày người bán phải phản hồi | Bạn mở bàn khiếu nại cho khách | 14 ngày / 3 ngày |
| Các email bổ sung | Bạn muốn hệ thống tự báo cho người bán và cho sàn | Bật |
| Vendor tự cấu hình plugin | Bạn muốn người bán tự đặt phí vận chuyển, khuyến mãi của gian hàng họ | Không mở plugin nào |

**Nếu bạn tắt hoặc gỡ bản Pro**: các màn Pro quay về trang giải thích và công tắc Pro trở lại trạng thái khóa, nhưng **dữ liệu do bản Pro tạo ra không bao giờ bị xóa** — hồ sơ xác minh, khiếu nại, gói và kỳ đăng ký, nhóm giá khách, nhật ký lô chi trả vẫn còn; cài lại là thấy đủ. Bản miễn phí tiếp tục chạy bình thường trong cả hai trường hợp: sàn không dừng, đơn hàng không mất.

## Tùy chỉnh đường dẫn (tùy chọn)
Thêm vào `.env` nếu muốn đổi đường dẫn mặc định, rồi chạy `php artisan gp247:cache-rebuild`:

```env
MULTIVENDOR_FRONT_PATH=shop
MULTIVENDOR_ADMIN_PATH=vendor_admin
PREFIX_QUICK_ORDER_VENDOR=quick-order
PREFIX_CATEGORY_VENDOR=category-vendor
```

| Biến | Đổi đường dẫn nào | Mặc định |
| --- | --- | --- |
| `MULTIVENDOR_FRONT_PATH` | Danh bạ gian hàng và trang gian hàng khách xem: `/shop`, `/shop/{mã}` | `shop` |
| `MULTIVENDOR_ADMIN_PATH` | Khu quản trị của người bán: `/vendor_admin`, kể cả trang đăng nhập và đăng ký | `vendor_admin` |
| `PREFIX_QUICK_ORDER_VENDOR` | Đoạn cuối của trang đặt hàng nhanh: `/shop/{mã}/quick-order` | `quick-order` |
| `PREFIX_CATEGORY_VENDOR` | Trang danh mục riêng của gian hàng | `category-vendor` |

Bốn lưu ý trước khi đổi:

- **Không đặt trùng** với đường dẫn đang có: tiền tố admin (mặc định `gp247_admin`), các biến còn lại trong bảng, hoặc đường dẫn trang/sản phẩm của storefront. Trùng thì một trong hai trang sẽ không mở được.
- **Tuyệt đối không dùng tên đang là thư mục trong `public/`** — hiện là `vendor`, `storage` và `GP247`. Thư mục thật do web server tự trả, request không bao giờ vào tới S-Cart, nên trang báo **403 Forbidden** (hoặc trắng) bất kể cấu hình thế nào. Đây chính là lý do đường dẫn gian hàng mặc định là `shop` **chứ không phải** `vendor`. Triệu chứng rất dễ đọc sai: trang gian hàng `/{đường-dẫn}/{mã}` vẫn chạy bình thường, chỉ trang danh bạ `/{đường-dẫn}` chết, và khi bật tiền tố ngôn ngữ SEO thì lỗi bị che luôn.
- **Đường dẫn cũ sẽ báo 404.** Site đã chạy thật và đã được Google lập chỉ mục thì nên tạo chuyển hướng 301 từ đường cũ sang đường mới trước khi đổi.
- Viết **không có dấu `/`** ở đầu và cuối. Trên hosting không dùng được dòng lệnh, sau khi sửa `.env` hãy xóa file `bootstrap/cache/config.php` (nếu có) thay cho lệnh `gp247:cache-rebuild`.

Muốn đổi chữ hiển thị, nội dung email, giao diện trang gian hàng hay phân quyền cho nhân sự: xem [Phần 3 — Tùy chỉnh](./multi-vendor-customize_vi.md).

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi cài đặt**
- **Plugin từ chối cài nếu website đã cài Multi-Store** — hai plugin dùng chung cơ chế cửa hàng theo hai mô hình khác nhau; trình cài dừng ngay, không ghi gì. Gỡ Multi-Store trước rồi cài lại.
- **Cần `gp247/shop` đã cài** — plugin dựa vào sản phẩm, giỏ hàng, đơn hàng của shop.
- **Bản Pro cần bản miễn phí đang kích hoạt** — Pro là phần mở rộng, không thay thế; gỡ bản miễn phí thì Pro mất nền để chạy.
- **Cài mà không kích hoạt thì chưa có hiệu lực** — với cả hai bản.

**Khi tạo gian hàng / tài khoản người bán**
- **Mã gian hàng là duy nhất, tối đa 20 ký tự** — mã dùng làm đường dẫn `/shop/{mã}`, trùng thì hệ thống không biết trỏ tới ai.
- **Mỗi tài khoản người bán gắn đúng một gian hàng** — quyền và dữ liệu (đơn, sản phẩm) phân theo gian hàng đó.
- **Tài khoản bị khóa hoặc gian hàng đang đóng thì không vào được khu người bán** — hệ thống chuyển tới trang "tài khoản chưa kích hoạt"; admin cần mở lại.
- **Bản miễn phí giới hạn 3 gian hàng** — muốn nhiều hơn thì cài thêm bản Pro.

**Khi gỡ plugin**
- **Gỡ bản miễn phí sẽ xóa tài khoản người bán, danh mục gian hàng và sổ trả tiền** (sản phẩm và đơn hàng của S-Cart giữ nguyên) — xuất dữ liệu cần giữ trước khi gỡ.
- **Gỡ bản Pro không mất dữ liệu** — các dữ liệu do Pro tạo ra không bao giờ bị xóa, cài lại là thấy đủ.

## Xử lý sự cố
| Hiện tượng | Cách xử lý |
| --- | --- |
| Không thấy menu **Chợ bán hàng** | Kiểm tra plugin đã **Kích hoạt** (không chỉ Cài đặt); chạy `php artisan gp247:cache-rebuild` |
| Bấm mục *(Pro)* vẫn ra trang giải thích | Kiểm tra MultiVendorPro đã **Kích hoạt**, rồi chạy `php artisan gp247:cache-rebuild` |
| Cài báo lỗi thiếu phần mở rộng | Cài và kích hoạt plugin miễn phí **MultiVendor** trước |
| Cài báo lỗi xung đột | Website đang cài Multi-Store — hai mô hình loại trừ nhau, phải gỡ Multi-Store trước |
| Vào `/vendor_admin` báo "tài khoản chưa kích hoạt" | Admin mở trạng thái tài khoản người bán **và** trạng thái gian hàng |
| Màn hình hiện mã khóa thô thay vì chữ | Mở lại màn **Cấu hình nhanh** một lần (plugin tự gieo lại chuỗi ngôn ngữ), hoặc tắt rồi bật lại plugin |
| Người bán không thấy mục Pro trong khu của họ | Kiểm tra tài khoản và gian hàng đang ở trạng thái mở; tính năng tương ứng đã bật ở Cấu hình nhanh |
| Lỗi quyền ghi | Cấp quyền ghi cho `storage/` và `bootstrap/cache/` |
| Lỗi kết nối cơ sở dữ liệu | Kiểm tra lại các dòng `DB_*` trong `.env` |
| Muốn xem lỗi chi tiết | Mở `storage/logs/laravel.log` |

## Hỏi & Đáp (Q&A)
**Câu 1: Site của tôi đang chạy S-Cart 2.x hoặc còn dùng bộ lệnh `sc:*` cũ, có cài được không?**

→ Plugin yêu cầu `gp247/core` 3.0. Hãy nâng cấp S-Cart lên 3.x trước; bộ lệnh `gp247:*` thay cho `sc:*` cũ.

**Câu 2: Tôi không có quyền chạy lệnh trên hosting thì sao?**

→ Dùng Cách 1 hoặc Cách 2 ở Bước 2 (cài qua giao diện admin). Plugin không cần cron hay queue để chạy.

**Câu 3: Người bán đăng nhập ở đâu, admin sàn đăng nhập ở đâu?**

→ Người bán: `/vendor_admin`. Admin sàn: khu admin S-Cart như bình thường. Hai khu tách biệt, tài khoản không dùng chéo được.

**Câu 4: Đổi đường dẫn `/shop` thành tên khác được không?**

→ Được, đặt `MULTIVENDOR_FRONT_PATH` trong `.env` rồi chạy `php artisan gp247:cache-rebuild`. Chọn tên **không** trùng thư mục trong `public/` (tránh `vendor`, `storage`, `GP247`) — xem bốn lưu ý ở trên.

**Câu 5: Tôi phải gỡ bản miễn phí trước khi cài Pro không?**

→ Không. Bản Pro cài **thêm** lên bản miễn phí và cần bản miễn phí ở trạng thái kích hoạt.

**Câu 6: Dữ liệu tạo trong thời gian dùng bản miễn phí có giữ được khi lên Pro không?**

→ Giữ nguyên toàn bộ: gian hàng, tài khoản người bán, sản phẩm, đơn hàng, sổ chi trả, và cả tỷ lệ hoa hồng riêng từng gian hàng.

**Câu 7: Dữ liệu mẫu có xóa được không?**

→ Xóa tay các gian hàng `vendor01`–`vendor03` và tài khoản tương ứng trong **Chợ bán hàng**. Đừng chạy lệnh tạo dữ liệu mẫu trên site thật.

---

⬅️ [Mục lục](./multi-vendor_vi.md) · [Phần 2 — Vận hành](./multi-vendor-operations_vi.md) ➡️

---

<sub>📅 **Cập nhật lần cuối:** 2026-09-21 · ✍️ **Tác giả (Author):** GP247</sub>
