> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./release.md) · [🇯🇵 日本語](./release_ja.md)

# Phát hành GP247

## Giới thiệu
Trang này liệt kê các bản phát hành **S-Cart** từ dòng **3.x**, mới nhất ở trên: có gì mới, bản vá bảo mật và cần làm gì
khi cập nhật. Mỗi bản S-Cart gom các bản phát hành của `gp247/core`, `gp247/front` và `gp247/shop`; S-Cart đánh số riêng
(bản *minor* có tính năng mới, bản *patch* chỉ sửa lỗi). Trang dành cho **chủ site và người vận hành**: đọc phần "Nhìn nhanh"
là biết có gì mới và site cần cập nhật hay chưa.

## Nhìn nhanh
| | S-Cart | Kiểm trên site: các package |
| --- | --- | --- |
| **Mới nhất** | **3.3.1** · 2026-10-29 | gp247/core 3.5.0 · gp247/front 3.3.0 · gp247/shop 3.3.1 |
| **Tối thiểu an toàn** | **3.3.0** | gp247/core ≥ **3.5.0** · gp247/front ≥ **3.2.3** · gp247/shop ≥ **3.3.1** |

**✨ Mới gần đây**
- Form liên hệ có hộp thư trong trang quản trị — xem lại mọi tin nhắn khách gửi ngay tại admin *(S-Cart 3.3)*
- Bật/tắt REST API và từng phiên bản API ngay trên màn quản lý API — không cần sửa file cấu hình *(S-Cart 3.3)*
- Đăng nhập quản trị giới hạn số lần thử sai — chặn dò mật khẩu *(S-Cart 3.3)*

> 💡 Kiểm bằng `php artisan gp247:info` (hoặc `composer show "gp247/*"`). Các package đạt dòng "Tối thiểu an toàn" thì site
> đã an toàn, kể cả khi bạn chưa cài lại S-Cart.

> ⚠️ **Lưu ý:** chỉ dòng **3.x** còn nhận bản vá bảo mật; site còn chạy 2.x cần nâng lên 3.x.
> `gp247/shop` 3.3.1 yêu cầu `gp247/core` 3.3 trở lên, `gp247/front` 3.2.3 yêu cầu `gp247/core` 3.2.3 trở lên — hãy cập nhật
> cả ba cùng lúc.

## Các bản phát hành

### S-Cart 3.3.1 — 2026-10-29
**Gồm:** gp247/core 3.5.0 · gp247/front 3.3.0 · gp247/shop 3.3.1

- 🐞 **Sửa lỗi:** web server theo kênh nginx ổn định để nhận bản vá; tắt tính năng PHP không dùng, giảm rủi ro PHP2Shell
  (chạy bằng Docker)
- ⚠️ **Cần làm khi cập nhật:** (chạy bằng Docker) kéo image mới và build lại theo mục "Cập nhật bản vá bảo mật" trong
  [DOCKER_vi.md](https://github.com/gp247net/s-cart/blob/master/DOCKER_vi.md)

Chi tiết: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.1)

### S-Cart 3.3.0 — 2026-10-28
**Form liên hệ có hộp thư và bật/tắt REST API ngay trong trang quản trị.**

**Gồm:** gp247/core 3.5.0 · gp247/front 3.3.0 · gp247/shop 3.3.1

- ✨ **Mới:**
  - Form liên hệ có hộp thư trong trang quản trị — xem lại mọi tin nhắn khách gửi ngay tại admin
  - Bật/tắt REST API và từng phiên bản API ngay trên màn quản lý API — không cần sửa file cấu hình
    ([tài liệu](../api/api-introduction_vi.md))
  - Đăng nhập quản trị giới hạn số lần thử sai — chặn dò mật khẩu
  - Cho lập trình viên: REST API v2 cho sản phẩm, đơn hàng, banner, trang (có bộ lọc, giá cuối); mỗi phản hồi API có mã
    yêu cầu để tra log
- ⚠️ **Bảo mật:** 5 bản vá — xem [Bản vá bảo mật](#bản-vá-bảo-mật). Nên cập nhật ngay.
- 🐞 **Sửa lỗi:** ảnh chia sẻ mạng xã hội (og:image) sai ảnh của trang; API đơn hàng quản trị lỗi 500
- ⚠️ **Cần làm khi cập nhật:**
  - Chạy `php artisan gp247:update`: chuyển khoá API sang dạng băm, đặt hạn cho token cũ
  - Đổi tên biến `GP247_API_LOGIN_THROTTLE` thành `GP247_LOGIN_THROTTLE`, `GP247_API_LOGIN_IP_THROTTLE` thành
    `GP247_LOGIN_IP_THROTTLE` (tên cũ không còn được đọc)
  - Ứng dụng gọi API: đăng xuất bằng POST (GET sắp bỏ), nên chuyển dần sang API v2

Chi tiết: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.0)

### S-Cart 3.2 — 2026-10-23
**Thêm tiếng Nhật, vận chuyển chuẩn có vận đơn, giao diện tự cập nhật theo package.**

**Gồm:** gp247/core 3.2.0 · gp247/front 3.2.0 · gp247/shop 3.2.0

- ✨ **Mới:**
  - Vận chuyển chuẩn: nhà vận chuyển, vận đơn, phường/xã — theo dõi giao hàng ngay trong đơn
    ([tài liệu](../s-cart/shipping/shipping_vi.md))
  - Tiếng Nhật và đồng Yên (JPY) có sẵn khi cài mới — bán hàng cho thị trường Nhật
    ([tài liệu](../system/language-system_vi.md))
  - Giao diện GP247Front chạy thẳng từ package — `composer update` là nhận giao diện mới, chỉ publish file muốn sửa
    ([tài liệu](../extension/create-template_vi.md))
  - Trang thanh toán hiện hướng dẫn của phương thức thanh toán — khách biết cần chuyển khoản thế nào
    ([tài liệu](../s-cart/payment/payment_vi.md))
  - Yêu thích và so sánh ngay trên trang sản phẩm
  - Cho lập trình viên: plugin thêm được khối và điểm cắm vào storefront mà không cần sở hữu template; ô "Kiểm tra trước khi
    dùng" báo plugin còn thiếu cấu hình gì
- ⚠️ **Bảo mật:** 2 bản vá — xem [Bản vá bảo mật](#bản-vá-bảo-mật).
- 🐞 **Sửa lỗi:** nút yêu thích/so sánh trên thẻ sản phẩm không hiện; lỗi "too many redirects" khi bỏ yêu thích; phân trang
  bị xếp dọc
- ⚠️ **Cần làm khi cập nhật:** site đã publish cả cây giao diện có thể chạy
  `php artisan gp247:template-prune GP247Front --dry-run` để trả file chưa sửa về package

Chi tiết: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.2)

### S-Cart 3.1 — 2026-10-03
**Yêu cầu thanh toán ngoài giỏ hàng và mã hoá mật khẩu, khoá thanh toán đã lưu.**

**Gồm:** gp247/core 3.1.0 · gp247/front 3.0.0 · gp247/shop 3.1.0

- ✨ **Mới:**
  - Yêu cầu thanh toán: gửi link thanh toán online, ghi tay khoản thu/chi, hoàn tiền — không cần tạo đơn
    ([tài liệu](../s-cart/order-processing/payment-request_vi.md))
  - Mã hoá mật khẩu SMTP và khoá cổng thanh toán trong cơ sở dữ liệu, có lệnh đổi khoá
    ([tài liệu](../system/data-encryption_vi.md))
  - Cấu hình và bật/tắt plugin theo từng cửa hàng (site đa cửa hàng) ([tài liệu](../multi-store/multi-store_vi.md))
  - Nhật ký mọi thao tác trong trang quản trị — biết ai đã đổi gì
  - Cho lập trình viên: `gp247:doctor` báo file PHP có BOM; báo cáo lỗi ghi rõ đến từ site nào
- ⚠️ **Bảo mật:** 3 bản vá — xem [Bản vá bảo mật](#bản-vá-bảo-mật).
- 🐞 **Sửa lỗi:** lưu cài đặt Email/SMTP không ổn định; khách không có email bị báo trùng; mã khuyến mãi tổng đơn không được áp
- ⚠️ **Cần làm khi cập nhật:** chạy `php artisan gp247:update`; (chạy bằng Docker) môi trường production không khởi động nếu
  `.env` còn mật khẩu mẫu

Chi tiết: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.1)

### S-Cart 3.0.1 — 2026-09-04
**Gồm:** gp247/core 3.0.0 · gp247/front 3.0.0 · gp247/shop 3.0.0

- ⚠️ **Cần làm khi cập nhật:** các lệnh cũ `sc:*` đã bị gỡ, dùng lệnh `gp247:*` tương ứng
  ([tài liệu](../system/command-line-reference_vi.md))

Chi tiết: [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.0.1)

### Các bản cũ hơn
| Thời gian | S-Cart | Gồm | Tóm tắt |
| --- | --- | --- | --- |
| 2026-08-30 | [3.0](https://github.com/gp247net/s-cart/releases/tag/3.0) | core 3.0.0 · front 3.0.0 · shop 3.0.0 | Mở đầu dòng 3.x: sổ thanh toán từng đơn, giảm giá trừ trước thuế — xem [Ghi chú phát hành v3.0](../system/release-notes-3.0_vi.md) |

## Bản vá bảo mật
Mỗi bản S-Cart một hàng, chỉ nêu loại lỗi, phạm vi ảnh hưởng và bản đã vá — không mô tả cách khai thác. Ý ghi "(site …)" chỉ
ảnh hưởng site dùng cấu hình đó.

| Thời gian | S-Cart | Bản vá |
| --- | --- | --- |
| 2026-10-28 | **3.3.0** | **core 3.5.0 · shop 3.3.1**<br>• **Brute-force, CSRF** — đăng nhập quản trị không giới hạn số lần thử; trình quản lý file đổi được file từ trang khác<br>• **Sửa giá** — khách đặt số lượng âm trong giỏ, làm tổng đơn về 0<br>• **Lộ bí mật** — khoá kết nối API lưu dạng rõ trong cơ sở dữ liệu (site có bật API)<br>**core · front · shop 3.2.3** *(phát hành 2026-10-27)*<br>• **Phân quyền, rò dữ liệu, brute-force** — REST API bỏ qua quyền theo vai trò và cửa hàng, lộ giá vốn, tồn kho; đăng nhập không giới hạn, token không hết hạn (site có bật API)<br>• **Xác thực** — khách hàng đã bị khoá vẫn đăng nhập được |
| 2026-10-23 | **3.2** | **core 3.1.2** *(phát hành 2026-10-21)*<br>• **Path traversal, SSRF** — cài plugin/template từ gói không tin cậy<br>**front 3.0.1** *(phát hành 2026-09-02)*<br>• **Rò dữ liệu** — trang, nhóm liên kết của cửa hàng khác hiện với quản trị cửa hàng con (site đa cửa hàng) |
| 2026-10-03 | **3.1** | **core 3.0.14** *(phát hành 2026-09-29)*<br>• **Lộ bí mật** — mật khẩu, khoá đã lưu ở màn cấu hình plugin bị gửi xuống trình duyệt<br>**shop 3.0.18** *(phát hành 2026-09-28)*<br>• **Rò dữ liệu** — trang chủ quản trị hiện doanh thu, đơn của mọi cửa hàng với quản trị cửa hàng con (site đa cửa hàng)<br>**shop 3.0.2** *(phát hành 2026-09-02)*<br>• **Rò dữ liệu** — danh mục, sản phẩm khi tạo đơn của cửa hàng khác hiện với quản trị cửa hàng con (site đa cửa hàng) |

## Phần mềm máy chủ (PHP, web server)
GP247 chạy trên PHP và web server do bạn hoặc nhà cung cấp hosting cài đặt. Lỗi của chúng được chính nhà phát hành vá, không
nằm trong bản phát hành GP247 — hãy cập nhật cả chúng.

| Ngày | Phần mềm | Sự cố | Đã vá ở (bản phát hành chính thức của PHP) |
| --- | --- | --- | --- |
| 2026-10-21 | PHP | **PHP2Shell** — thực thi mã từ xa, mức nghiêm trọng (CVE-2026-109618, CVE-2026-109619) | 8.3.36 · 8.4.27 · 8.5.12 |

**Cần làm gì**
1. Xem phiên bản PHP đang chạy: `php -v`, hoặc **Admin → Thông tin server**.
2. PHP lấy từ bản phát hành chính thức (php.net, Docker image chính thức, đa số bảng điều khiển cho chọn phiên bản PHP):
   nâng lên bản đã vá của nhánh đang dùng, hoặc mới hơn.
3. PHP do bản phân phối Linux (Ubuntu, Debian, RHEL…) cài hoặc do nhà cung cấp hosting quản lý: các bên này thường vá trên một
   số phiên bản cũ, nên **chỉ nhìn số phiên bản không biết được đã an toàn hay chưa**. Hãy cài bản cập nhật mới nhất của hệ điều
   hành, hoặc hỏi nhà cung cấp đã vá CVE-2026-109618 và CVE-2026-109619 chưa.
4. Chưa cập nhật được ngay: đặt `session.upload_progress.enabled = Off` trong `php.ini` (GP247 không dùng tính năng này) rồi khởi
   động lại PHP. Cách này giảm rủi ro, không thay được việc cập nhật.
5. Chạy S-Cart bằng Docker: làm theo mục **Cập nhật bản vá bảo mật** trong [DOCKER_vi.md](https://github.com/gp247net/s-cart/blob/master/DOCKER_vi.md).

## Cập nhật thế nào
1. Sao lưu cơ sở dữ liệu và thư mục site.
2. Cập nhật code:
   ```bash
   composer update gp247/core gp247/front gp247/shop
   ```
3. Áp dụng cập nhật dữ liệu, cấu hình, cache:
   ```bash
   php artisan gp247:update
   ```
4. Kiểm tra lại phiên bản bằng `php artisan gp247:info`.

Chi tiết từng bước: [Hướng dẫn cập nhật GP247](https://gp247.net/vi/docs/system/how-to-update-gp247.html).

## Báo lỗ hổng bảo mật
Phát hiện lỗ hổng, vui lòng gửi email tới **support@gp247.net** — **không** mở issue công
khai trên GitHub để tránh lộ lỗi trước khi có bản vá.

---

<sub>📅 **Cập nhật lần cuối:** 2026-10-30 · ✍️ **Tác giả (Author):** GP247</sub>
