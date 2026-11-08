> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./cli-diagnostics.md) · [🇯🇵 日本語](./cli-diagnostics_ja.md)

# Lệnh kiểm tra và chẩn đoán

## Giới thiệu
Trang này mô tả các lệnh giúp bạn biết site đang ở trạng thái nào: `gp247:doctor` kiểm máy chủ, cài đặt và cấu hình bảo
mật; `gp247:info` và `gp247:core-info` cho biết phiên bản; `gp247:cache-rebuild` dựng lại cache. Tất cả đều **chỉ đọc**
(trừ việc dựng cache) nên chạy bất cứ lúc nào, kể cả trên site đang bán hàng. Tài liệu dành cho chủ site và người vận hành
máy chủ. Đọc xong, bạn hiểu từng dòng `gp247:doctor` báo và biết cách sửa.

## Nhìn nhanh
Dùng `gp247:doctor` ở ba thời điểm:

```mermaid
flowchart LR
    A["Trước khi cài<br/>máy chủ đủ chưa?"] --> B["Sau khi cài / cập nhật<br/>còn gì cần dọn?"]
    B --> C["Định kỳ<br/>cấu hình còn an toàn?"]
```

- **Ba trạng thái**: **PASS** ổn · **WARN** nên dọn nhưng không chặn · **FAIL** phải sửa.
- **Có FAIL thì lệnh thoát khác 0** — dùng làm cổng chặn trong CI hoặc trước khi cài. WARN không làm thoát khác 0.
- **Site đã cài thì doctor kiểm thêm cấu hình bảo mật** — cùng các mục mà trang **Cảnh báo hệ thống** hiện cho quản trị viên.
- **Cần biết phiên bản để báo lỗi hoặc kiểm bản vá** → `gp247:info`.

## `gp247:doctor` — kiểm tra sức khoẻ site
1. Tại thư mục site, gõ:

   ```bash
   php artisan gp247:doctor
   ```

2. Đọc bảng kết quả: mỗi dòng có tên mục, trạng thái và ghi chú (đường dẫn, số lượng, lệnh gợi ý sửa).
3. Sửa mọi dòng **FAIL** trước, rồi tới **WARN**. Chạy lại tới khi không còn FAIL.

Doctor đọc cấu hình và database, **không** mở site qua mạng. Việc `http://` có tự chuyển sang `https://` hay `.env` có bị tải
được từ ngoài không vẫn phải tự kiểm — xem [Cấu hình production an toàn](../security/security-production-config_vi.md) trong bộ
tài liệu [Bảo mật](../security/security-introduction_vi.md).

### Máy chủ và cài đặt
| Mục | Kiểm gì | Không đạt thì |
| --- | --- | --- |
| `php_version` | PHP từ 8.3 trở lên | FAIL — nâng PHP |
| `ext_pdo`, `ext_mbstring`, `ext_openssl`, `ext_tokenizer`, `ext_ctype`, `ext_json` | Extension PHP bắt buộc | FAIL — bật extension |
| `ext_zip`, `ext_curl`, `ext_gd`, `ext_fileinfo` | Extension PHP khuyến nghị | WARN — một số tính năng (cài `.zip`, ảnh) không chạy |
| `env_file` | Có file `.env` | FAIL |
| `writable_storage`, `writable_bootstrap_cache` | Ghi được `storage/`, `bootstrap/cache/` | FAIL — sửa quyền thư mục |
| `writable_app_gp247`, `writable_public_gp247` | Ghi được `app/GP247`, `public/GP247` | WARN — không cài / cập nhật extension được |
| `db_connection` | Kết nối được database | FAIL — kiểm `DB_*` trong `.env` |
| `installed` | Site đã cài chưa | WARN khi chưa cài (bình thường trước lần cài đầu) |

### Khoá mã hoá và bí mật
| Mục | Kiểm gì |
| --- | --- |
| `encryption_key_dedicated` | Đã đặt khoá riêng `GP247_ENCRYPTION_KEY` cho bí mật, hay đang dùng chung `APP_KEY` — xem [Mã hoá dữ liệu](../system/data-encryption_vi.md) |
| `secret_decryptable` | Bí mật đã mã hoá trong cấu hình còn giải mã được (không được: dấu hiệu khoá bị đổi mà không giữ khoá cũ) |
| `backup_post_incident_pending` | Còn việc chưa làm sau một lần phục hồi "sau sự cố bảo mật" (đổi khoá, cài lại mã, đổi mật khẩu database / hosting) |

### Extension và giao diện
| Mục | Kiểm gì | Lệnh sửa |
| --- | --- | --- |
| `extension_assets` | Extension đã cài thiếu CSS/JS/ảnh trong `public/GP247/...` | `gp247:ext-publish` |
| `extension_data_pending` | Extension có file mới hơn phiên bản GP247 ghi nhớ (vừa `git pull`, chép đè) mà chưa chạy phần cập nhật dữ liệu | `gp247:ext-update --local --all` |
| `template_source` | Số file template đã publish nhưng giống hệt package — sẽ không nhận bản vá | `gp247:template-prune` |
| `file_bom` | File `.php` / `.blade.php` bắt đầu bằng BOM UTF-8 | Lưu lại file dạng UTF-8 không BOM |

### REST API
| Mục | Kiểm gì |
| --- | --- |
| `api_config_legacy_names` | `.env` còn dùng tên biến API cũ đã đổi tên (bỏ qua khi cấu hình đang được cache) |
| `api_route_gate` | Khi route đang được cache: mọi route REST API đều đi qua lớp bảo vệ chung của API |
| `api_event_retention` | Bảng sự kiện đồng bộ của API có sự kiện quá hạn giữ (dọn tự động khi có request API; giảm `GP247_API_EVENT_DAYS` nếu cần) |

### Cấu hình bảo mật (khi site đã cài)
Các mục này chỉ **PASS** hoặc **WARN** — kể cả khi trang quản trị xếp mức nghiêm trọng — nên không đổi mã thoát.

| Mục | WARN khi |
| --- | --- |
| `app_debug` | `APP_DEBUG` đang bật |
| `https` | `APP_URL` không bắt đầu bằng `https://` hoặc `SESSION_SECURE_COOKIE` chưa bật (chỉ WARN khi `APP_ENV=production`) |
| `trusted_proxies` | `TRUSTED_PROXIES` tin mọi nguồn (`*`, `REMOTE_ADDR`…) — ai cũng tự đặt được IP của mình, giới hạn đăng nhập và quy tắc IP mất tác dụng |
| `default_admin_password` | Tài khoản `admin` đang hoạt động vẫn dùng mật khẩu mặc định |
| `password_policy` | Độ dài mật khẩu tối thiểu của quản trị viên dưới 12 ký tự |
| `mfa_enabled` | Plugin xác thực hai lớp MFA chưa cài hoặc chưa bật |
| `api_exposure` | (Chỉ để biết, PASS) REST API / MCP đang bật |
| `support_line` | Dòng phiên bản đang dùng còn dưới 6 tháng hỗ trợ bảo mật, hoặc đã hết |

### Vì sao `file_bom` đáng kiểm
BOM là **3 byte vô hình** mà nhiều trình soạn thảo trên Windows tự thêm vào đầu file khi lưu "UTF-8". PHP in chúng ra
**trước mọi thứ khác**, gây lỗi "headers already sent", JSON/XML/CSV hỏng ngay ký tự đầu, hay HTML mở đầu bằng một ký tự lạ.
Mở file lên trông hoàn toàn bình thường, nên chỉ máy phát hiện được. Doctor quét `app/GP247/**` và mã nguồn các package
`gp247/*`.

**Cách sửa:** mở file bằng trình soạn thảo và lưu lại dạng **UTF-8 without BOM** (VS Code: bấm `UTF-8 with BOM` ở thanh dưới
bên phải → *Save with Encoding* → *UTF-8*). Chạy lại `gp247:doctor` để thấy mục trở về PASS.

## `gp247:info` và `gp247:core-info` — xem phiên bản
| Lệnh | In ra |
| --- | --- |
| `gp247:info` | Phiên bản từng package đã cài (core, front, shop, mcp), site đã cài chưa, số plugin / template, địa chỉ thư viện extension |
| `gp247:core-info` | Tên, phiên bản core và phiên bản con, trang chủ, kho mã nguồn |

```bash
php artisan gp247:info
```

> 💡 **Mẹo:** muốn biết site đã có bản vá bảo mật chưa, so phiên bản package `gp247:info` in ra với bảng ở
> [Ghi chú phát hành](../release/release_vi.md). Số phiên bản S-Cart ở chân trang quản trị không đáng tin sau lần
> `composer update` đầu tiên — luôn dựa vào phiên bản package.

## `gp247:cache-rebuild` — dựng lại cache
Xoá rồi dựng lại cache route và cấu hình. GP247 tự gọi lệnh này sau khi cài, bật, cập nhật extension và ở cuối
`gp247:update`. Chạy tay khi bạn vừa sửa `.env` hay route mà site chưa nhận.

```bash
php artisan gp247:cache-rebuild
```

## Hỏi & Đáp (Q&A)
**Câu 1: Doctor có WARN thì site có chạy được không?**

→ Có. WARN là việc nên dọn (bảo mật, file thừa), không chặn site. Chỉ FAIL mới là thứ phải sửa ngay.

**Câu 2: Tôi chạy doctor trước khi cài thì có ý nghĩa không?**

→ Có — đó là một công dụng chính. Doctor có ngay sau `composer require`, kể cả khi chưa cài; các mục cần database đã cài sẽ
được bỏ qua.

**Câu 3: Doctor báo `secret_decryptable` không đạt?**

→ Khoá mã hoá đã đổi mà khoá cũ không còn. Đưa khoá cũ vào `GP247_ENCRYPTION_PREVIOUS_KEYS` rồi chạy
`gp247:encryption-key-rotate` — xem [Mã hoá dữ liệu](../system/data-encryption_vi.md).

**Câu 4: Doctor báo `https` WARN trên máy dev?**

→ Chỉ WARN khi `APP_ENV=production`; môi trường khác hiện PASS kèm ghi chú. Nếu thấy WARN, kiểm lại `APP_ENV` của bạn.

**Câu 5: Dùng doctor trong CI thế nào?**

→ `php artisan gp247:doctor --json`: lệnh thoát khác 0 khi có FAIL, nên bước CI tự dừng. Xem [Script, CI & Docker](./cli-automation_vi.md).

**Câu 6: Doctor kiểm bảo mật có thay được việc rà soát bảo mật không?**

→ Không. Doctor chỉ đọc cấu hình. Rà đủ theo [Danh sách kiểm tra bảo mật](../security/security-checklist_vi.md).

---

<sub>📅 **Cập nhật lần cuối:** 2026-11-08 · ✍️ **Tác giả (Author):** GP247</sub>
