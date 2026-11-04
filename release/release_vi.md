> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./release.md) · [🇯🇵 日本語](./release_ja.md)

# Phát hành và bảo mật S-Cart

## Giới thiệu
Trang này dành cho **chủ site và người vận hành** S-Cart (dòng 3.x): cho biết mỗi bản phát hành có gì mới, nên dùng phiên
bản nào và cách cập nhật. Bản mới nhất: **S-Cart 3.5.1** (2026-11-05).

## Phiên bản nên dùng
Chạy `php artisan gp247:info` (hoặc `composer show "gp247/*"`) rồi so từng dòng với cột **Tối thiểu khuyến nghị**:

| Package | Tối thiểu khuyến nghị | Mới nhất |
| --- | --- | --- |
| gp247/core | **3.10.2** | 3.10.2 |
| gp247/front | **3.2.3** | 3.5.2 |
| gp247/shop | **3.6.0** | 3.7.0 |
| gp247/mcp | **2.0.1** | 2.0.1 |

- **Thấp hơn** cột "Tối thiểu khuyến nghị" → nên [cập nhật](#cập-nhật-thế-nào).
- **Đã đạt** → site đã có đủ các cải tiến quan trọng, kể cả khi bạn chưa cài lại S-Cart.
- **Thư viện bên thứ ba** → chạy thêm `composer audit` để kiểm, xem [Thư viện bên thứ ba](#thư-viện-bên-thứ-ba).

> ⚠️ **Lưu ý:** cập nhật **cả bộ package cùng lúc**: `gp247/mcp` 2.0.1 cần `gp247/core` ≥ 3.9, `gp247/shop` 3.6.0 cần `gp247/core` ≥ 3.7,
> `gp247/front` 3.2.3 cần `gp247/core` ≥ 3.2.3. Dòng nào còn được hỗ trợ: xem [Vòng đời hỗ trợ](#vòng-đời-hỗ-trợ).

## Vòng đời hỗ trợ
| Dòng | Nền tảng | Trạng thái | Vá bảo mật đến |
| --- | --- | --- | --- |
| **3.x** | Laravel 13 · PHP 8.3+ | ✅ Đang hỗ trợ | 3/2028 |
| 2.x trở xuống | Laravel 12–13 | ⛔ Ngừng hỗ trợ | — |

- **Tính năng mới** chỉ vào dòng mới nhất.
- **Khi có dòng mới:** dòng trước được sửa lỗi nghiêm trọng thêm 6 tháng và vá bảo mật tới thời điểm ở bảng — không quá hạn
  hỗ trợ bảo mật của bản Laravel mà dòng đó dùng.
- **Site còn chạy 2.x trở xuống:** vẫn chạy được nhưng không còn nhận bản sửa nào. Nâng lên 3.x bằng `composer update` và
  `php artisan gp247:update` — xem [ghi chú v3.0](../system/release-notes-3.0_vi.md).

## Điểm mới nổi bật
- **Cảnh báo hệ thống** — trang quản trị tự báo cấu hình chưa an toàn *(S-Cart 3.5.1)*
- **MCP server có sẵn trong S-Cart** — hỏi và quản lý cửa hàng bằng AI agent bạn đang dùng *(S-Cart 3.5)*
- **Agent tra cứu thay bạn** — doanh thu, hàng sắp hết, đơn, khách, đúng phân quyền *(S-Cart 3.5)*
- **Thao tác ghi luôn có xác nhận** — xem trước rồi mới đổi trạng thái đơn, kho *(S-Cart 3.5)*

## Cập nhật thế nào
**Bước 1 —** Sao lưu cơ sở dữ liệu và thư mục site.

**Bước 2 —** Cập nhật code:
```bash
composer update gp247/core gp247/front gp247/shop gp247/mcp
```
Site cài trước S-Cart 3.5.0 chưa có MCP server: chạy `composer require gp247/mcp -W` một lần thay cho lệnh trên.

**Bước 3 —** Áp dụng cập nhật dữ liệu, cấu hình, cache:
```bash
php artisan gp247:update
```

**Bước 4 —** Kiểm tra lại phiên bản bằng `php artisan gp247:info`.

Mỗi bản phát hành có thể có thêm việc riêng — xem mục **📋 Cần làm khi cập nhật** của bản đó bên dưới.
Chi tiết từng bước: [Hướng dẫn cập nhật GP247](https://gp247.net/vi/docs/system/how-to-update-gp247.html).

## Các bản phát hành
Mới nhất ở trên. Bản *x.**Y*** có tính năng mới; bản *x.y.**Z*** chỉ sửa lỗi.

### S-Cart 3.5.1 — 2026-11-05
**Trang quản trị tự báo cấu hình chưa an toàn; cài đặt qua web gọn hơn.**

**✨ Mới**
- **Cảnh báo hệ thống** — dashboard tóm tắt cấu hình chưa an toàn (debug, HTTPS, mật khẩu admin mặc định, MFA…) kèm trang
  chi tiết và hướng dẫn sửa; chỉ Administrator thấy ([tài liệu](../security/security-built-in_vi.md))
- **Cài đặt qua web xong trong một lượt** — chọn tên đăng nhập admin; tài khoản nhập ở trình cài thay tài khoản mặc định
- **Form 2 cột gọn hơn** — nút "+ Tạo mới" ở đầu form khi đang sửa, danh sách giữ nguyên trang và từ khoá *(màn quản trị
  của core)*
- **Cho lập trình viên:** `gp247:doctor` chạy cùng bộ kiểm tra bảo mật (WARN/PASS, mã thoát không đổi); package khác thêm
  được mục kiểm tra

**🛡️ Thư viện bên thứ ba:** Livewire 4.4.8 và symfony/mime 7.4.22 vá 2 lỗi đã công bố — [xem chi tiết](#thư-viện-bên-thứ-ba).
**Nên cập nhật ngay.**

**🔧 Cải thiện:** `gp247:update` giữ đúng một block "Cảnh báo hệ thống"; nút Gỡ bỏ nay xoá được thư mục extension còn sót;
ô chọn có tìm kiếm không tự mở khi sửa dòng khác

**📋 Cần làm khi cập nhật**
- Chạy `composer update` (kéo Livewire ≥ 4.4.8, symfony/mime ≥ 7.4.22) rồi `php artisan gp247:update`
- *(Site có `SESSION_DRIVER` khác `file`)* mọi người dùng phải đăng nhập lại một lần sau khi cập nhật

<sub>Gồm core 3.10.2 · front 3.5.2 · shop 3.7.0 · mcp 2.0.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.5.1)</sub>

### S-Cart 3.5.0 — 2026-11-03
**MCP server có sẵn: hỏi và quản lý cửa hàng bằng AI agent bạn đang dùng.**

**✨ Mới**
- **MCP server có sẵn trong S-Cart** — kết nối Claude, Cursor, VS Code với cửa hàng, hỏi bằng lời thường; tắt sẵn
  ([tài liệu](../mcp/mcp-introduction_vi.md))
- **Agent tra cứu thay bạn** — doanh thu, hàng sắp hết, đơn, khách, thay đổi gần đây; đúng phân quyền của tài khoản
- **Thao tác ghi luôn có xác nhận** — đổi trạng thái đơn, giao hàng, nhập/xuất kho: xem trước rồi mới làm; tắt sẵn
- **Thư viện extension ghi rõ "Core X.Y+"** — biết ngay extension cần core bản nào
- **Cho lập trình viên:** lệnh `gp247:mcp-install` / `gp247:mcp-update` / `gp247:mcp-uninstall`; `gp247:info` hiện `gp247/mcp`;
  khoá manifest `minUpdateFrom` thay `requireUpdateFrom` (khoá cũ vẫn đọc được)

**📋 Cần làm khi cập nhật**
- *(Site cài trước S-Cart 3.5.0)* chạy `composer require gp247/mcp -W` một lần rồi `php artisan gp247:update`
- *(Đang dùng plugin McpServer)* `gp247:update` tự chuyển token, cấu hình, quyền; sau đó xoá thư mục plugin bằng
  `php artisan gp247:ext-uninstall --type=plugin --key=McpServer --purge`; màn MCP nay ở **Cấu hình hệ thống → MCP Server**

<sub>Gồm core 3.9.0 · front 3.5.2 · shop 3.7.0 · mcp 2.0.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.5.0)</sub>

### S-Cart 3.4.0 — 2026-11-02
**Bán hàng qua ứng dụng riêng và đồng bộ với ERP qua REST API.**

**✨ Mới**
- **Bán hàng qua ứng dụng riêng** — app của cửa hàng giữ giỏ, đặt đơn, thanh toán online qua API; chủ site bật "Đặt đơn
  qua API" ([tài liệu](../api/api-introduction_vi.md))
- **Tài khoản khách trong ứng dụng** — đăng ký, quên mật khẩu, hồ sơ, sổ địa chỉ; giữ đăng nhập lâu bằng refresh token
- **Đồng bộ với ERP, phần mềm kho** — cập nhật trạng thái đơn, mã vận đơn, giá, tồn kho qua API quản trị, đúng phân quyền
  màn hình
- **Nhật ký sự kiện và webhook** — ERP biết ngay khi đơn, tồn, sản phẩm, khách thay đổi; mặc định tắt
- **Ứng dụng tải nhanh hơn** — chỉ lấy trường cần dùng; dữ liệu không đổi thì không tải lại (`304`)
- **Cho lập trình viên:** điểm cắm xác thực hai lớp (MFA) cho đăng nhập API; `Idempotency-Key` chống ghi trùng; chính sách
  ngừng hỗ trợ API; sinh client TypeScript từ `openapi.json`

**🔧 Cải thiện:** sản phẩm hết hàng vẫn hiện nút mua; đăng ký và đổi mật khẩu chấp nhận mật khẩu rỗng; API lỗi 500 khi thiếu
header `Accept`

**📋 Cần làm khi cập nhật**
- Chạy `php artisan gp247:update` — tạo bảng phiên API, nhật ký sự kiện, webhook và chỉ mục đồng bộ
- *(Bật Quản lý tồn kho nhưng không nhập tồn)* tắt *Quản lý tồn kho* hoặc bật *Cho bán khi hết hàng*, nếu không nút mua sẽ
  biến mất ở sản phẩm tồn 0
- *(Nhiều ứng dụng dùng chung một tài khoản API)* giới hạn 1000 lần/phút nay đếm theo tài khoản — tách tài khoản hoặc tăng
  `GP247_API_THROTTLE`

<sub>Gồm core 3.8.0 · front 3.5.0 · shop 3.7.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.4.0)</sub>

### S-Cart 3.3.1 — 2026-10-29
**Form liên hệ có hộp thư và bật/tắt REST API ngay trong trang quản trị.**

**✨ Mới**
- **Hộp thư liên hệ trong admin** — xem lại mọi tin nhắn khách gửi ngay tại trang quản trị
- **Bật/tắt REST API** và từng phiên bản API ngay trên màn quản lý API — không cần sửa file cấu hình
  ([tài liệu](../api/api-introduction_vi.md))
- **Giới hạn đăng nhập sai** — chặn dò mật khẩu quản trị
- **Cho lập trình viên:** REST API v2 cho sản phẩm, đơn hàng, banner, trang (có bộ lọc, giá cuối); mỗi phản hồi API có mã
  yêu cầu để tra log

**🔧 Cải thiện:** ảnh chia sẻ mạng xã hội (og:image) sai ảnh của trang; API đơn hàng quản trị lỗi 500; web server theo kênh
nginx ổn định, tắt tính năng PHP không dùng *(chạy bằng Docker)*

**📋 Cần làm khi cập nhật**
- Chạy `php artisan gp247:update` — chuyển khoá API sang dạng băm, đặt hạn cho token cũ
- Đổi tên biến `GP247_API_LOGIN_THROTTLE` → `GP247_LOGIN_THROTTLE`, `GP247_API_LOGIN_IP_THROTTLE` →
  `GP247_LOGIN_IP_THROTTLE` (tên cũ không còn được đọc)
- Ứng dụng gọi API: đăng xuất bằng POST (GET sắp bỏ), nên chuyển dần sang API v2
- *(Chạy bằng Docker)* kéo image mới và build lại theo mục "Cập nhật bản vá bảo mật" trong
  [DOCKER_vi.md](https://github.com/gp247net/s-cart/blob/master/DOCKER_vi.md)

<sub>Gồm core 3.5.0 · front 3.3.0 · shop 3.3.1 · gộp cả bản 3.3.0 (2026-10-28) · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.3.1)</sub>

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

**🔧 Cải thiện:** nút yêu thích/so sánh trên thẻ sản phẩm không hiện; lỗi "too many redirects" khi bỏ yêu thích; phân trang
bị xếp dọc

**📋 Cần làm khi cập nhật**
- Site đã publish cả cây giao diện có thể chạy `php artisan gp247:template-prune GP247Front --dry-run` để trả file chưa sửa
  về package

<sub>Gồm core 3.2.0 · front 3.2.0 · shop 3.2.0 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.2)</sub>

### Các bản cũ hơn
| Bản | Ngày | Tóm tắt |
| --- | --- | --- |
| [S-Cart 3.1](https://github.com/gp247net/s-cart/releases/tag/3.1) | 2026-10-03 | Yêu cầu thanh toán ngoài giỏ hàng; mã hoá mật khẩu, khoá thanh toán đã lưu |
| [S-Cart 3.0.1](https://github.com/gp247net/s-cart/releases/tag/3.0.1) | 2026-09-04 | Gỡ lệnh `sc:*` cũ, dùng lệnh `gp247:*` — [danh sách lệnh](../system/command-line-reference_vi.md) |
| [S-Cart 3.0](https://github.com/gp247net/s-cart/releases/tag/3.0) | 2026-08-30 | Mở đầu dòng 3.x: sổ thanh toán từng đơn, giảm giá trừ trước thuế — [ghi chú v3.0](../system/release-notes-3.0_vi.md) |

## Bảo mật

Giữ site an toàn không chỉ là cập nhật — cấu hình, khoá mã hoá, sao lưu, tài khoản và xử lý sự cố có ở bộ tài liệu [Bảo mật hệ thống GP247](../security/security-introduction_vi.md).

GP247 rà soát bảo mật thường xuyên, và các cải tiến được đưa vào từng bản phát hành. Cách đơn giản nhất để giữ site an
toàn là dùng ít nhất phiên bản ở bảng [Phiên bản nên dùng](#phiên-bản-nên-dùng) và cập nhật khi có bản mới.

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

## Thư viện bên thứ ba
Lỗi của thư viện mà GP247 dùng (Livewire, Symfony…) do chính nhà phát hành thư viện vá; GP247 nâng mức tối thiểu khi cần.
Mã của GP247 không dùng tính năng bị ảnh hưởng của hai lỗi dưới đây, nhưng plugin bên thứ ba có thể dùng — vẫn nên cập nhật.

| Sự cố | Mức độ | Đã vá ở |
| --- | --- | --- |
| **Livewire** — vượt kiểm tra quyền (GHSA-mhf7-xp6r-6qrc) | Trung bình | livewire/livewire 4.4.8 (core 3.10.2 yêu cầu bản này) |
| **symfony/mime** — chèn header email (CVE-2026-45070) | Trung bình | symfony/mime 7.4.22 |

**Cần làm gì**
- **Cập nhật:** `composer update`, rồi kiểm bằng `composer show livewire/livewire symfony/mime`.
- **Kiểm mọi thư viện cùng lúc:** `composer audit` liệt kê các thư viện trong site đang có lỗi đã công bố.

## Báo lỗ hổng bảo mật
Phát hiện lỗ hổng, vui lòng gửi email tới **support@gp247.net** — **không** mở issue công khai trên GitHub để tránh lộ lỗi
trước khi có bản vá.

---

<sub>📅 **Cập nhật lần cuối:** 2026-11-05 · ✍️ **Tác giả (Author):** GP247</sub>
