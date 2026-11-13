> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./release.md) · [🇯🇵 日本語](./release_ja.md)

# Phát hành và bảo mật S-Cart

## Giới thiệu
Trang này dành cho **chủ site và người vận hành** S-Cart (dòng 3.x): cho biết mỗi bản phát hành có gì mới, nên dùng phiên
bản nào và cách cập nhật. Bản mới nhất: **S-Cart 3.7.0** (2026-11-13).

## Phiên bản nên dùng
Chạy `php artisan gp247:info` (hoặc `composer show "gp247/*"`) rồi so từng dòng với cột **Tối thiểu khuyến nghị**:

| Package | Tối thiểu khuyến nghị | Mới nhất |
| --- | --- | --- |
| gp247/core | **3.12.1** | 3.12.1 |
| gp247/front | **3.8.1** | 3.8.1 |
| gp247/shop | **3.10.1** | 3.10.1 |
| gp247/mcp | **2.0.1** | 2.0.2 |

- **Thấp hơn** cột "Tối thiểu khuyến nghị" → nên [cập nhật](#cập-nhật-thế-nào).
- **Đã đạt** → site đã có đủ các cải tiến quan trọng, kể cả khi bạn chưa cài lại S-Cart.
- **Thư viện bên thứ ba** → chạy thêm `composer audit` để kiểm, xem [Thư viện bên thứ ba](#thư-viện-bên-thứ-ba).

> ⚠️ **Lưu ý:** cập nhật **cả bộ package cùng lúc**: `gp247/front` 3.8.1 và `gp247/shop` 3.10.1 cần `gp247/core` ≥ 3.12.1, `gp247/front` 3.7.0 và `gp247/shop` 3.9.0 cần `gp247/core` ≥ 3.11, `gp247/shop` 3.8.1 cần `gp247/core` ≥ 3.10.3, `gp247/mcp` 2.0.1 cần `gp247/core` ≥ 3.9, `gp247/shop` 3.6.0 cần `gp247/core` ≥ 3.7,
> `gp247/front` 3.2.3 cần `gp247/core` ≥ 3.2.3. Dòng nào còn được hỗ trợ: xem [Vòng đời hỗ trợ](#vòng-đời-hỗ-trợ).

## Vòng đời hỗ trợ
| Dòng | Nền tảng | Trạng thái | Vá bảo mật đến |
| --- | --- | --- | --- |
| **3.x** | Laravel 13 · PHP 8.3+ | ✅ Đang hỗ trợ | 3/2028 |
| 2.x trở xuống | Laravel 12–13 | ⛔ Ngừng hỗ trợ | — |

- **Tính năng mới** chỉ vào dòng mới nhất.
- **Khi có dòng mới:** dòng trước được sửa lỗi nghiêm trọng thêm 6 tháng và vá bảo mật tới thời điểm ở bảng — không quá hạn
  hỗ trợ bảo mật của bản Laravel mà dòng đó dùng.
- **Site còn chạy 2.x trở xuống:** vẫn chạy được nhưng không còn nhận bản sửa nào. Site 2.1 / 2.2 nâng lên 3.x bằng
  `composer require … -W` và `php artisan gp247:update` — xem [Nâng cấp lên dòng 3.x](../install-upgrade/upgrade_vi.md).

## Điểm mới nổi bật
- **Chữ lưu đúng như gõ** — tên có `&`, `<`, `"` hiện đúng ở quản trị, cửa hàng và API *(S-Cart 3.7)*
- **Mật khẩu và khoá bí mật giữ nguyên ký tự** — mật khẩu có `& < > "` đăng nhập được *(S-Cart 3.7)*
- **Cảnh báo chỗ in HTML thô** — chỉ ra template, plugin in biến bằng `{!! !!}` *(S-Cart 3.7)*
- **Cảnh báo proxy chưa khai báo** — kèm giá trị cần thêm vào `TRUSTED_PROXIES` *(S-Cart 3.7)*

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
Chi tiết từng bước: [Cập nhật GP247 và S-Cart](../install-upgrade/update_vi.md).

## Các bản phát hành
Mới nhất ở trên. Bản *x.**Y*** có tính năng mới; bản *x.y.**Z*** chỉ sửa lỗi.

### S-Cart 3.7.0 — 2026-11-13
**Tên và nội dung hiển thị đúng như bạn gõ — hết `&amp;`.**

**✨ Mới**
- **Chữ lưu đúng như gõ** — tên sản phẩm, danh mục, trang có `&`, `<`, `"` hiện đúng ở quản trị, cửa hàng và API; dữ liệu cũ
  tự chuyển khi cập nhật
- **Mật khẩu và khoá bí mật giữ nguyên ký tự** — mật khẩu có `& < > "` đăng nhập được; mật khẩu SMTP, khoá thanh toán gửi
  đúng tới máy chủ
- **Cảnh báo chỗ in HTML thô** — Cảnh báo hệ thống và `gp247:doctor` chỉ ra file template, plugin của site in biến bằng
  `{!! !!}` ([tài liệu](../security/security-built-in_vi.md))
- **Cảnh báo proxy chưa khai báo** — báo khi trang quản trị đi qua proxy chưa có trong `TRUSTED_PROXIES`, kèm giá trị cần
  thêm ([tài liệu](../security/security-production-config_vi.md))
- **Thông tin server hiện phiên bản GP247 trước tiên** — so ngay với bảng "Phiên bản nên dùng", không cần dòng lệnh
- **Cho lập trình viên:** `gp247_text_plain()` thay `gp247_clean()` (deprecated, gỡ ở 4.0), escape khi in bằng `{{ }}`;
  REST API trả chữ gốc; dịch vụ ghi dùng chung cho sản phẩm, danh mục, thương hiệu, nhà cung cấp, khách hàng

**🔧 Cải thiện:** nội dung khách và quản trị nhập luôn hiện dạng chữ ở cửa hàng và màn đơn hàng; sắp xếp danh sách chỉ nhận
cột hợp lệ; `gp247:info` in phiên bản đúng như tag (3.12.1, không còn 3.12.1.0)

**📋 Cần làm khi cập nhật**
- **Sao lưu cơ sở dữ liệu trước** — `gp247:update` chuyển một lần chữ đã lưu dạng mã hoá (`&amp;` → `&`), không hoàn tác được
- Chạy `composer update` rồi `php artisan gp247:update`
- Mở **Cảnh báo hệ thống** (hoặc `php artisan gp247:doctor`): chỗ template, plugin tự viết in biến bằng `{!! !!}` → đổi sang
  `{{ }}`, trừ nội dung soạn bằng trình soạn thảo HTML
- Mật khẩu SMTP hoặc khoá bí mật có `& < > "` đã lưu trước bản này: nhập lại một lần
- *(Ứng dụng dùng REST API)* chuỗi nay trả đúng như gõ (`&`, không còn `&amp;`) — ứng dụng phải tự escape khi hiển thị

<sub>Gồm core 3.12.1 · front 3.8.1 · shop 3.10.1 · mcp 2.0.2 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.7.0)</sub>

### S-Cart 3.6.1 — 2026-11-11
Bản cải tiến sao lưu và phục hồi cho site thật và shared host — nên cập nhật.

**✨ Mới**
- **Mỗi nhóm sao lưu ghi rõ chứa gì** — màn sao lưu và phục hồi nói nhóm gồm những dữ liệu nào trước khi chọn
  ([tài liệu](../backup/backup-and-restore_vi.md))
- **Xem trước phục hồi nói rõ phần không mang về** — cột site không còn phải xác nhận riêng; bản mới hơn site nêu bản cập
  nhật còn thiếu ([tài liệu](../backup/restore-guide_vi.md))
- **Cảnh báo khi thiếu bản sao lưu toàn bộ** — màn sao lưu báo khi chưa có bản toàn bộ đã kiểm hoặc bản đó đã cũ

**🔧 Cải thiện:** phục hồi bị bỏ dở tự kết thúc và đưa site ra khỏi bảo trì; sao lưu chạy được với bảng lớn không có khoá
chính và bảng dòng rộng trong 128 MB, giữ bản luôn giữ bản toàn bộ mới nhất; màn tải file lên trong quản trị không còn lỗi
hết bộ nhớ

**📋 Cần làm khi cập nhật**
- Chạy `composer update` rồi `php artisan gp247:update`
- Kiểm lại, bỏ ghim, xoá bản sao lưu và lưu cài đặt sao lưu nay hỏi xác nhận đăng nhập; nhập sai mật khẩu sao lưu nhiều lần
  bị tạm chặn
- *(Phục hồi bằng dòng lệnh)* bản có cột mà site không còn: xem trước rồi chạy lại với `--accept-column-loss`

<sub>Gồm core 3.11.2 · front 3.7.1 · shop 3.9.1 · mcp 2.0.2 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.6.1)</sub>

### S-Cart 3.6.0 — 2026-11-09
**Sao lưu và phục hồi có sẵn trong S-Cart, chạy được cả trên shared host.**

**✨ Mới**
- **Sao lưu mã hoá có sẵn** — chọn một nhóm dữ liệu, nhiều nhóm hoặc toàn site; bản được kiểm ngay sau khi tạo, chỉ mở
  được bằng mật khẩu sao lưu hoặc recovery key ([tài liệu](../backup/backup-and-restore_vi.md))
- **Phục hồi đúng thứ cần** — một nhóm dữ liệu, bản ghi đã xoá, cấu hình hay toàn cơ sở dữ liệu, có xem trước và hoàn tác
  ([tài liệu](../backup/restore-guide_vi.md))
- **Tự sao lưu không cần cron** — chạy khi admin mở trang quản trị, hoặc theo cron nếu có; tự chụp nhanh trước
  `gp247:update` và trước khi cập nhật extension
- **Chép bản ra ngoài máy chủ** — S3-compatible hoặc SFTP, dùng được khoá chỉ-ghi để kẻ tấn công không xoá được bản
  ([tài liệu](../backup/backup-offsite_vi.md))
- **Chính sách mật khẩu theo nhóm tài khoản** — đặt độ dài, độ mạnh riêng cho admin và khách trên một màn
  ([tài liệu](../security/security-accounts-and-access_vi.md))
- **Màn Đăng nhập sai** — xem các lần đăng nhập thất bại, sớm nhận ra dò mật khẩu
  ([tài liệu](../security/security-built-in_vi.md))
- **Cho lập trình viên:** lệnh `gp247:backup-*` (tạo, kiểm, phục hồi, chép ra ngoài); `gp247:admin-password` đặt lại mật
  khẩu admin từ dòng lệnh; plugin khai báo bảng của mình vào nhóm sao lưu

**🔧 Cải thiện:** ô tìm kiếm danh sách cho biết tìm theo cột nào, bỏ qua khoảng trắng thừa; `gp247:install` in thông tin
đăng nhập admin ở cuối; bộ Docker chạy lịch bằng `schedule:work`, bỏ tiến trình hàng đợi thừa mỗi phút *(chạy bằng Docker)*

**📋 Cần làm khi cập nhật**
- Sao lưu cơ sở dữ liệu và thư mục site, chạy `composer update` rồi `php artisan gp247:update`
- Sao lưu **tắt sẵn**: vào **Sao lưu & phục hồi**, đặt mật khẩu sao lưu, cất recovery key ở nơi khác máy chủ
- Cài mới chỉ còn bằng dòng lệnh `php artisan gp247:install` — trình cài đặt trên web đã bỏ
  ([hướng dẫn cài đặt](../install-upgrade/install_vi.md))
- *(Site cài trước 3.6.0, muốn chép bản ra SFTP)* chạy `composer require league/flysystem-sftp-v3`
- *(Chạy bằng Docker)* `composer update` không sửa file của site: lấy `docker-compose*.yml` mới rồi chạy `docker compose up -d`

<sub>Gồm core 3.11.0 · front 3.7.0 · shop 3.9.0 · mcp 2.0.2 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.6.0)</sub>

### S-Cart 3.5.2 — 2026-11-07
Bản cải tiến nên cập nhật ngay, nhất là site chạy Docker hoặc đứng sau proxy / CDN.

**✨ Mới**
- **Cảnh báo `TRUSTED_PROXIES`** — trang Cảnh báo hệ thống và `gp247:doctor` nhắc khi cấu hình proxy tin mọi người truy cập
  ([tài liệu](../security/security-production-config_vi.md))

**🔧 Cải thiện:** link đặt lại mật khẩu và xác minh email luôn trỏ về tên miền đã cấu hình của cửa hàng; IP và tên miền chỉ
lấy từ proxy đã khai báo; bộ Docker mặc định không tin proxy nào *(chạy bằng Docker)*

**📋 Cần làm khi cập nhật**
- Chạy `composer update` rồi `php artisan gp247:update`
- Kiểm `APP_URL` trong `.env` đúng tên miền thật (`https://…`) — link trong email lấy theo giá trị này
- *(Site sau proxy / CDN)* proxy phải giữ header `Host`; GP247 không còn đọc `X-Forwarded-Host` / `X-Forwarded-Prefix`
- *(Chạy bằng Docker sau nginx / Caddy / Traefik trên cùng máy chủ)* `composer update` không sửa file của site: lấy
  `docker-compose*.yml` và `docker/nginx/default.conf` mới, đặt `TRUSTED_PROXIES=172.16.0.0/12` và
  `SC_DOCKER_APP_PORT=127.0.0.1:8000` trong `.env`, rồi chạy `docker compose up -d`
- *(`TRUSTED_PROXIES` đang có `REMOTE_ADDR` hoặc `*`)* thay bằng dải IP của proxy thật hoặc để trống
- *(Site dùng plugin CheckIP)* cập nhật CheckIP ≥ 2.1.7

<sub>Gồm core 3.10.3 · front 3.5.2 · shop 3.8.1 · mcp 2.0.1 · [GitHub Release](https://github.com/gp247net/s-cart/releases/tag/3.5.2)</sub>

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

### Các bản cũ hơn
| Bản | Ngày | Tóm tắt |
| --- | --- | --- |
| [S-Cart 3.5.0](https://github.com/gp247net/s-cart/releases/tag/3.5.0) | 2026-11-03 | MCP server có sẵn: hỏi và quản lý cửa hàng bằng AI agent |
| [S-Cart 3.4.0](https://github.com/gp247net/s-cart/releases/tag/3.4.0) | 2026-11-02 | Bán hàng qua ứng dụng riêng và đồng bộ ERP qua REST API |
| [S-Cart 3.3.1](https://github.com/gp247net/s-cart/releases/tag/3.3.1) | 2026-10-29 | Form liên hệ có hộp thư; bật/tắt REST API trong trang quản trị |
| [S-Cart 3.2](https://github.com/gp247net/s-cart/releases/tag/3.2) | 2026-10-23 | Thêm tiếng Nhật, vận chuyển chuẩn có vận đơn, giao diện tự cập nhật theo package |
| [S-Cart 3.1](https://github.com/gp247net/s-cart/releases/tag/3.1) | 2026-10-03 | Yêu cầu thanh toán ngoài giỏ hàng; mã hoá mật khẩu, khoá thanh toán đã lưu |
| [S-Cart 3.0.1](https://github.com/gp247net/s-cart/releases/tag/3.0.1) | 2026-09-04 | Gỡ lệnh `sc:*` cũ, dùng lệnh `gp247:*` — [danh sách lệnh](../cli/cli-introduction_vi.md) |
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

<sub>📅 **Cập nhật lần cuối:** 2026-11-13 · ✍️ **Tác giả (Author):** GP247</sub>
