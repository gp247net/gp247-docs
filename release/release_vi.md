> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./release.md) · [🇯🇵 日本語](./release_ja.md)

# Phát hành và bảo mật S-Cart

## Giới thiệu
Trang này dành cho **chủ site và người vận hành** S-Cart (dòng 3.x): cho biết site đã an toàn chưa, mỗi bản phát hành có gì
mới và cách cập nhật. Bản mới nhất: **S-Cart 3.3.1** (2026-10-29).

## Site của bạn đã an toàn chưa?
Chạy `php artisan gp247:info` (hoặc `composer show "gp247/*"`) rồi so từng dòng với cột **Tối thiểu an toàn**:

| Package | Tối thiểu an toàn | Mới nhất |
| --- | --- | --- |
| gp247/core | **3.5.0** | 3.5.0 |
| gp247/front | **3.2.3** | 3.3.0 |
| gp247/shop | **3.3.1** | 3.3.1 |

- **Thấp hơn** cột "Tối thiểu an toàn" → [cập nhật ngay](#cập-nhật-thế-nào).
- **Đã đạt** → site đã an toàn, kể cả khi bạn chưa cài lại S-Cart.

> ⚠️ **Lưu ý:** chỉ dòng **3.x** còn nhận bản vá bảo mật — site còn chạy 2.x cần nâng lên 3.x. Cập nhật **cả ba package
> cùng lúc**: `gp247/shop` 3.3.1 cần `gp247/core` ≥ 3.3, `gp247/front` 3.2.3 cần `gp247/core` ≥ 3.2.3.

## Điểm mới nổi bật
- **Hộp thư liên hệ trong admin** — xem lại mọi tin nhắn khách gửi ngay tại trang quản trị *(S-Cart 3.3)*
- **Bật/tắt REST API ngay trên màn quản lý API** — không cần sửa file cấu hình *(S-Cart 3.3)*
- **Giới hạn đăng nhập sai** — chặn dò mật khẩu quản trị *(S-Cart 3.3)*
- **Vận chuyển chuẩn có vận đơn** — theo dõi giao hàng ngay trong đơn *(S-Cart 3.2)*
- **Tiếng Nhật và đồng Yên** — có sẵn khi cài mới *(S-Cart 3.2)*

## Cập nhật thế nào
**Bước 1 —** Sao lưu cơ sở dữ liệu và thư mục site.

**Bước 2 —** Cập nhật code:
```bash
composer update gp247/core gp247/front gp247/shop
```

**Bước 3 —** Áp dụng cập nhật dữ liệu, cấu hình, cache:
```bash
php artisan gp247:update
```

**Bước 4 —** Kiểm tra lại phiên bản bằng `php artisan gp247:info`.

Mỗi bản phát hành có thể có thêm việc riêng — xem mục **📋 Cần làm khi cập nhật** của bản đó bên dưới.
Chi tiết từng bước: [Hướng dẫn cập nhật GP247](https://gp247.net/vi/docs/system/how-to-update-gp247.html).

## Các bản phát hành
Mới nhất ở trên. Bản *x.**Y*** có tính năng mới; bản *x.y.**Z*** chỉ sửa lỗi.

### S-Cart 3.3.1 — 2026-10-29
Bản sửa lỗi cho site chạy bằng Docker.

**🐞 Sửa lỗi:** web server theo kênh nginx ổn định để nhận bản vá; tắt tính năng PHP không dùng, giảm rủi ro PHP2Shell
*(chạy bằng Docker)*

**📋 Cần làm khi cập nhật**
- *(Chạy bằng Docker)* kéo image mới và build lại theo mục "Cập nhật bản vá bảo mật" trong
  [DOCKER_vi.md](https://github.com/gp247net/s-cart/blob/master/DOCKER_vi.md)

<sub>Gồm core 3.5.0 · front 3.3.0 · shop 3.3.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.1)</sub>

### S-Cart 3.3.0 — 2026-10-28
**Form liên hệ có hộp thư và bật/tắt REST API ngay trong trang quản trị.**

**✨ Mới**
- **Hộp thư liên hệ trong admin** — xem lại mọi tin nhắn khách gửi ngay tại trang quản trị
- **Bật/tắt REST API** và từng phiên bản API ngay trên màn quản lý API — không cần sửa file cấu hình
  ([tài liệu](../api/api-introduction_vi.md))
- **Giới hạn đăng nhập sai** — chặn dò mật khẩu quản trị
- **Cho lập trình viên:** REST API v2 cho sản phẩm, đơn hàng, banner, trang (có bộ lọc, giá cuối); mỗi phản hồi API có mã
  yêu cầu để tra log

**🛡️ Bảo mật:** 5 bản vá — [xem chi tiết](#bản-vá-bảo-mật). **Nên cập nhật ngay.**

**🐞 Sửa lỗi:** ảnh chia sẻ mạng xã hội (og:image) sai ảnh của trang; API đơn hàng quản trị lỗi 500

**📋 Cần làm khi cập nhật**
- Chạy `php artisan gp247:update` — chuyển khoá API sang dạng băm, đặt hạn cho token cũ
- Đổi tên biến `GP247_API_LOGIN_THROTTLE` → `GP247_LOGIN_THROTTLE`, `GP247_API_LOGIN_IP_THROTTLE` →
  `GP247_LOGIN_IP_THROTTLE` (tên cũ không còn được đọc)
- Ứng dụng gọi API: đăng xuất bằng POST (GET sắp bỏ), nên chuyển dần sang API v2

<sub>Gồm core 3.5.0 · front 3.3.0 · shop 3.3.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.0)</sub>

### S-Cart 3.2 — 2026-10-23
**Thêm tiếng Nhật, vận chuyển chuẩn có vận đơn, giao diện tự cập nhật theo package.**

**✨ Mới**
- **Vận chuyển chuẩn** — nhà vận chuyển, vận đơn, phường/xã; theo dõi giao hàng ngay trong đơn
  ([tài liệu](../s-cart/shipping/shipping_vi.md))
- **Tiếng Nhật và đồng Yên (JPY)** có sẵn khi cài mới — bán hàng cho thị trường Nhật
  ([tài liệu](../system/language-system_vi.md))
- **Giao diện GP247Front chạy thẳng từ package** — `composer update` là nhận giao diện mới, chỉ publish file muốn sửa
  ([tài liệu](../extension/create-template_vi.md))
- **Hướng dẫn thanh toán ở trang thanh toán** — khách biết cần chuyển khoản thế nào
  ([tài liệu](../s-cart/payment/payment_vi.md))
- **Yêu thích và so sánh** ngay trên trang sản phẩm
- **Cho lập trình viên:** plugin thêm được khối và điểm cắm vào storefront mà không cần sở hữu template; ô "Kiểm tra trước khi
  dùng" báo plugin còn thiếu cấu hình gì

**🛡️ Bảo mật:** 2 bản vá — [xem chi tiết](#bản-vá-bảo-mật).

**🐞 Sửa lỗi:** nút yêu thích/so sánh trên thẻ sản phẩm không hiện; lỗi "too many redirects" khi bỏ yêu thích; phân trang
bị xếp dọc

**📋 Cần làm khi cập nhật**
- Site đã publish cả cây giao diện có thể chạy `php artisan gp247:template-prune GP247Front --dry-run` để trả file chưa sửa
  về package

<sub>Gồm core 3.2.0 · front 3.2.0 · shop 3.2.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.2)</sub>

### S-Cart 3.1 — 2026-10-03
**Yêu cầu thanh toán ngoài giỏ hàng và mã hoá mật khẩu, khoá thanh toán đã lưu.**

**✨ Mới**
- **Yêu cầu thanh toán** — gửi link thanh toán online, ghi tay khoản thu/chi, hoàn tiền mà không cần tạo đơn
  ([tài liệu](../s-cart/order-processing/payment-request_vi.md))
- **Mã hoá mật khẩu SMTP và khoá cổng thanh toán** trong cơ sở dữ liệu, có lệnh đổi khoá
  ([tài liệu](../system/data-encryption_vi.md))
- **Plugin theo từng cửa hàng** — cấu hình và bật/tắt riêng *(site đa cửa hàng)*
  ([tài liệu](../multi-store/multi-store_vi.md))
- **Nhật ký thao tác quản trị** — biết ai đã đổi gì
- **Cho lập trình viên:** `gp247:doctor` báo file PHP có BOM; báo cáo lỗi ghi rõ đến từ site nào

**🛡️ Bảo mật:** 3 bản vá — [xem chi tiết](#bản-vá-bảo-mật).

**🐞 Sửa lỗi:** lưu cài đặt Email/SMTP không ổn định; khách không có email bị báo trùng; mã khuyến mãi tổng đơn không được áp

**📋 Cần làm khi cập nhật**
- Chạy `php artisan gp247:update`
- *(Chạy bằng Docker)* môi trường production không khởi động nếu `.env` còn mật khẩu mẫu

<sub>Gồm core 3.1.0 · front 3.0.0 · shop 3.1.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.1)</sub>

### S-Cart 3.0.1 — 2026-09-04
**📋 Cần làm khi cập nhật**
- Các lệnh cũ `sc:*` đã bị gỡ, dùng lệnh `gp247:*` tương ứng ([tài liệu](../system/command-line-reference_vi.md))

<sub>Gồm core 3.0.0 · front 3.0.0 · shop 3.0.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.0.1)</sub>

### Các bản cũ hơn
| Bản | Ngày | Tóm tắt |
| --- | --- | --- |
| [S-Cart 3.0](https://github.com/gp247net/s-cart/releases/tag/3.0) | 2026-08-30 | Mở đầu dòng 3.x: sổ thanh toán từng đơn, giảm giá trừ trước thuế — [ghi chú v3.0](../system/release-notes-3.0_vi.md) |

## Bản vá bảo mật
Chỉ nêu loại lỗi, ai bị ảnh hưởng và bản đã vá — không mô tả cách khai thác. Dòng ghi *(site …)* chỉ ảnh hưởng site dùng
cấu hình đó. Ngày trong ngoặc là ngày package phát hành — site tự `composer update` đã an toàn từ ngày đó.

### S-Cart 3.3.0 — 5 bản vá
| Lỗi | Ai bị ảnh hưởng | Đã vá trong |
| --- | --- | --- |
| **Brute-force, CSRF** | Đăng nhập quản trị không giới hạn số lần thử; trình quản lý file đổi được file từ trang khác | core 3.5.0 · shop 3.3.1 |
| **Sửa giá** | Khách đặt số lượng âm trong giỏ, làm tổng đơn về 0 | core 3.5.0 · shop 3.3.1 |
| **Lộ bí mật** | Khoá kết nối API lưu dạng rõ trong cơ sở dữ liệu *(site có bật API)* | core 3.5.0 · shop 3.3.1 |
| **Phân quyền, rò dữ liệu, brute-force** | REST API bỏ qua quyền theo vai trò và cửa hàng, lộ giá vốn, tồn kho; đăng nhập không giới hạn, token không hết hạn *(site có bật API)* | core · front · shop 3.2.3 *(2026-10-27)* |
| **Xác thực** | Khách hàng đã bị khoá vẫn đăng nhập được | core · front · shop 3.2.3 *(2026-10-27)* |

### S-Cart 3.2 — 2 bản vá
| Lỗi | Ai bị ảnh hưởng | Đã vá trong |
| --- | --- | --- |
| **Path traversal, SSRF** | Cài plugin/template từ gói không tin cậy | core 3.1.2 *(2026-10-21)* |
| **Rò dữ liệu** | Trang, nhóm liên kết của cửa hàng khác hiện với quản trị cửa hàng con *(site đa cửa hàng)* | front 3.0.1 *(2026-09-02)* |

### S-Cart 3.1 — 3 bản vá
| Lỗi | Ai bị ảnh hưởng | Đã vá trong |
| --- | --- | --- |
| **Lộ bí mật** | Mật khẩu, khoá đã lưu ở màn cấu hình plugin bị gửi xuống trình duyệt | core 3.0.14 *(2026-09-29)* |
| **Rò dữ liệu** | Trang chủ quản trị hiện doanh thu, đơn của mọi cửa hàng với quản trị cửa hàng con *(site đa cửa hàng)* | shop 3.0.18 *(2026-09-28)* |
| **Rò dữ liệu** | Danh mục, sản phẩm khi tạo đơn của cửa hàng khác hiện với quản trị cửa hàng con *(site đa cửa hàng)* | shop 3.0.2 *(2026-09-02)* |

## Phần mềm máy chủ (PHP, web server)
Lỗi của PHP và web server do chính nhà phát hành của chúng vá, không nằm trong bản phát hành GP247 — hãy cập nhật cả chúng.

| Sự cố | Mức độ | Đã vá ở PHP |
| --- | --- | --- |
| **PHP2Shell** — thực thi mã từ xa (CVE-2026-109618, CVE-2026-109619) · 2026-10-21 | Nghiêm trọng | 8.3.36 · 8.4.27 · 8.5.12 |

**Cần làm gì**
- **Xem phiên bản PHP:** `php -v`, hoặc **Admin → Thông tin server**.
- **PHP từ bản phát hành chính thức** (php.net, Docker image chính thức, đa số bảng điều khiển cho chọn phiên bản PHP): nâng
  lên bản đã vá của nhánh đang dùng, hoặc mới hơn.
- **PHP do Linux (Ubuntu, Debian, RHEL…) hoặc nhà cung cấp hosting quản lý:** các bên này thường vá trên phiên bản cũ, nên
  **chỉ nhìn số phiên bản không biết đã an toàn hay chưa** — cài bản cập nhật mới nhất của hệ điều hành, hoặc hỏi nhà cung
  cấp đã vá CVE-2026-109618 và CVE-2026-109619 chưa.
- **Chưa cập nhật được ngay:** đặt `session.upload_progress.enabled = Off` trong `php.ini` (GP247 không dùng tính năng này)
  rồi khởi động lại PHP. Cách này giảm rủi ro, không thay được việc cập nhật.
- **Chạy bằng Docker:** làm theo mục "Cập nhật bản vá bảo mật" trong
  [DOCKER_vi.md](https://github.com/gp247net/s-cart/blob/master/DOCKER_vi.md).

## Báo lỗ hổng bảo mật
Phát hiện lỗ hổng, vui lòng gửi email tới **support@gp247.net** — **không** mở issue công khai trên GitHub để tránh lộ lỗi
trước khi có bản vá.

---

<sub>📅 **Cập nhật lần cuối:** 2026-10-31 · ✍️ **Tác giả (Author):** GP247</sub>
