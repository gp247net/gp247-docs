> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./cli-maintenance.md) · [🇯🇵 日本語](./cli-maintenance_ja.md)

# Lệnh bảo trì: tài khoản, khoá mã hoá, sao lưu, dọn dẹp

## Giới thiệu
Trang này gom các lệnh dùng trong lúc vận hành site: đặt lại mật khẩu quản trị khi bị mất, đổi khoá mã hoá bí mật, sao
lưu và phục hồi, dọn giỏ hàng hết hạn. Tài liệu dành cho chủ site có quyền vào máy chủ. Mỗi mục cho biết lệnh làm gì, gõ thế
nào và dẫn tới trang hướng dẫn đầy đủ khi việc đó dài. Đọc xong, bạn xử lý được các tình huống bảo trì thường gặp mà không
cần trang quản trị.

## Nhìn nhanh
| Tình huống | Lệnh | Mức an toàn |
| --- | --- | --- |
| Mất mật khẩu quản trị, **Quên mật khẩu** không dùng được | `gp247:admin-password` | ⚠️ |
| Khoá mã hoá bị lộ, cần đổi khoá | `gp247:encryption-key-rotate` | ⚠️ |
| Sao lưu trước một thay đổi lớn | `gp247:backup-run` | ✅ |
| Site hỏng, cần lấy lại dữ liệu | `gp247:backup-restore` | 🛑 |
| Bảng giỏ hàng phình to | `gp247:shop-clear-cart` | ✅ |

- **Lệnh cần bí mật (mật khẩu sao lưu) luôn hỏi khi chạy**, không nhận qua tham số — để bí mật không nằm trong lịch sử lệnh.
- **Lệnh xoá hoặc ghi đè hỏi lại mã bản sao lưu** trước khi làm.
- **Các việc định kỳ** (sao lưu theo lịch, hàng đợi mail) chỉ cần **một** dòng cron — xem [Lịch chạy định kỳ](#lịch-chạy-định-kỳ).

## Đặt lại mật khẩu quản trị
`gp247:admin-password` đặt mật khẩu mới cho một tài khoản quản trị, khi bạn mất mật khẩu (ví dụ không kịp chép mật khẩu in
một lần lúc cài) và **Quên mật khẩu** không giúp được — tài khoản còn email lúc cài `admin@gp247.local`, hoặc site chưa gửi
được mail. Chỉ người vào được máy chủ mới chạy được lệnh này.

> ℹ️ **Có từ:** gp247/core 3.11.0

| Tham số | Giá trị | Ý nghĩa |
| --- | --- | --- |
| `username` | bắt buộc | Tên đăng nhập của tài khoản (thường là `admin`) |
| `--password` | tuỳ chọn | Mật khẩu mới — phải đạt **chính sách mật khẩu Quản trị viên** đang đặt. Bỏ trống: lệnh sinh ngẫu nhiên |
| `--force` | `0` (mặc định) hoặc `1` | `1`: không hỏi xác nhận — bắt buộc khi chạy trong script hoặc với `--json` |

1. Gõ:

   ```bash
   php artisan gp247:admin-password admin
   ```

2. Lệnh hỏi xác nhận (mặc định **không**); gõ `yes`.
3. Khối cuối cùng in tên đăng nhập và **mật khẩu mới — chỉ hiện một lần**. Chép lại ngay, đăng nhập, rồi đổi email tài khoản
   sang email thật để lần sau dùng được **Quên mật khẩu**.

Sau khi đặt lại:
- **Mọi ứng dụng dùng REST API và mọi token MCP của tài khoản bị thu hồi**; trình duyệt đã "ghi nhớ đăng nhập" phải đăng nhập
  lại. Phiên đang mở (không ghi nhớ) hết hiệu lực khi phiên hết hạn.
- **Tài khoản đang bị khoá không tự mở khoá** — mở ở màn **Người dùng**.
- **Site ghi một dòng log** gồm tên tài khoản, không có mật khẩu.

Với `--json`, mật khẩu sinh ra chỉ nằm trong `data.password`; mật khẩu bạn tự chọn không bao giờ được in lại. Xem thêm
[Tài khoản & quyền truy cập](../security/security-accounts-and-access_vi.md).

## Đổi khoá mã hoá bí mật
GP247 mã hoá các bí mật lưu trong cấu hình (mật khẩu SMTP, khoá cổng thanh toán…) bằng `GP247_ENCRYPTION_KEY`.
`gp247:encryption-key-rotate` đọc từng bí mật bằng **khoá cũ** rồi ghi lại bằng **khoá mới** — cách duy nhất đổi khoá mà
không mất dữ liệu.

| Tham số | Ý nghĩa |
| --- | --- |
| `--dry-run` | Chỉ đếm số dòng sẽ chuyển, không ghi |

Thứ tự bắt buộc (chi tiết 8 bước ở [Mã hoá dữ liệu](../system/data-encryption_vi.md#các-bước-đổi-khoá-mã-hoá-an-toàn-không-mất-dữ-liệu)):

1. Sao lưu `.env` và database.
2. Đưa khoá hiện tại vào `GP247_ENCRYPTION_PREVIOUS_KEYS`, đặt khoá mới vào `GP247_ENCRYPTION_KEY`.
3. Xoá cache cấu hình: `php artisan config:clear`.
4. Mã hoá lại:

   ```bash
   php artisan gp247:encryption-key-rotate
   ```

   Nếu thành công, mỗi cột in một dòng như `admin_config.value: 5 converted, 0 already current, 0 undecryptable`.
5. Kiểm tra: `php artisan gp247:doctor` — mục `secret_decryptable` phải PASS.

Lệnh chạy lại an toàn: dòng đã dùng khoá mới được bỏ qua, dòng không giải mã được không bị ghi đè.

## Sao lưu và phục hồi
Họ lệnh `gp247:backup-*` làm được mọi việc của màn **Sao lưu & phục hồi**, và vẫn chạy được khi trang quản trị không vào được.
Thay `<mã>` bằng mã bản sao lưu (xem bằng `gp247:backup-list`).

| Lệnh | Làm gì | An toàn |
| --- | --- | --- |
| `gp247:backup-run` | Tạo bản đã mã hoá rồi tự kiểm. `--kind=snapshot\|selective\|full` (mặc định `full`), `--group=`, `--files=`, `--exclude-logs` | ✅ |
| `gp247:backup-run --scheduled` | Bản theo lịch, nội dung lấy từ **Cài đặt sao lưu**; không làm gì khi lịch đang tắt | ✅ |
| `gp247:backup-list` | Liệt kê bản (cả bản tìm thấy trong thư mục sao lưu) | ✅ |
| `gp247:backup-verify <mã>` | Kiểm lại toàn vẹn một bản | ✅ |
| `gp247:backup-pin <mã>` | Ghim để không bị dọn tự động; `--unpin` bỏ ghim | ✅ |
| `gp247:backup-import <file>` | Kiểm rồi đưa file `.gp247bak` vào danh sách | ✅ |
| `gp247:backup-offsite test\|list\|push\|fetch` | Đích ngoài: kiểm kết nối, liệt kê, chép bản ra, kéo bản về | ✅ |
| `gp247:backup-export-sql <mã>` | Xuất dữ liệu thành file `.sql` **không mã hoá** (`--output=`) | ⚠️ |
| `gp247:backup-restore <mã>` | Phục hồi dữ liệu và / hoặc file: `--level=`, `--files=`, `--preview`, `--undo`, `--post-incident` | 🛑 |
| `gp247:backup-delete <mã>` | Xoá một bản | 🛑 |

Ví dụ: tạo bản toàn bộ rồi xem danh sách:

```bash
php artisan gp247:backup-run
php artisan gp247:backup-list
```

Xem trước một lần phục hồi trước khi làm thật:

```bash
php artisan gp247:backup-restore <mã> --level=full --preview
```

- **Mật khẩu sao lưu được hỏi khi chạy**; trong script truyền qua một dòng của đầu vào chuẩn với `--secret-stdin`
  (`--recovery-key` nếu thứ bạn đưa là recovery key).
- **Lệnh xoá / phục hồi / xuất `.sql` hỏi lại mã bản**; không tương tác thì truyền `--confirm=<mã>`.
- **File `.sql` xuất ra không được mã hoá** — giữ nó như giữ chính database, xoá khi dùng xong.

Hướng dẫn đầy đủ: [Sao lưu & phục hồi](../backup/backup-and-restore_vi.md#dòng-lệnh-cho-người-có-quyền-cli) ·
[Phục hồi theo tình huống](../backup/restore-guide_vi.md#phục-hồi-bằng-dòng-lệnh) · [Đích ngoài](../backup/backup-offsite_vi.md).

## Dọn giỏ hàng hết hạn
`gp247:shop-clear-cart` xoá giỏ hàng, danh sách yêu thích và danh sách so sánh đã quá số ngày giữ, để bảng giỏ hàng không
phình to. Số ngày đặt trong cấu hình `gp247-config.shop.cart_expire` (`cart`, `wishlist`, `compare`). Lệnh có khi site cài
`gp247/shop`.

```bash
php artisan gp247:shop-clear-cart
```

Chạy hằng ngày bằng cron, ví dụ lúc 2 giờ sáng:

```bash
0 2 * * * cd /duong-dan-site && php artisan gp247:shop-clear-cart >> /dev/null 2>&1
```

## Lịch chạy định kỳ
GP247 tự đăng ký các việc định kỳ của nó (xử lý hàng đợi mail, sao lưu theo lịch ở chế độ **Vào giờ cố định**) vào bộ lịch
của Laravel. Trên máy chủ, bạn chỉ cần **một** dòng cron gọi bộ lịch mỗi phút:

```bash
* * * * * cd /duong-dan-site && php artisan schedule:run >> /dev/null 2>&1
```

Hosting không có cron, VPS, Docker: xem [Lịch chạy và hàng đợi](../system/schedule-and-queue_vi.md).

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Đặt lại mật khẩu**
- **Mật khẩu tự chọn phải đạt chính sách mật khẩu Quản trị viên** — không đạt thì lệnh báo `invalid_password` (thông báo không
  lặp lại mật khẩu) và không đổi gì.
- **Tên đăng nhập không tồn tại** → `user_not_found`.
- **Script / `--json` cần `--force=1`** → nếu không: `confirmation_required`, không đổi gì.

**Đổi khoá mã hoá**
- **Phải giữ khoá cũ trong `GP247_ENCRYPTION_PREVIOUS_KEYS` khi chạy lệnh** — thiếu khoá cũ thì bí mật không đọc được để mã
  hoá lại (báo "undecryptable").
- **Phải `config:clear` trước** — cấu hình đang cache vẫn giữ khoá cũ.

**Sao lưu & phục hồi**
- **Chưa thiết lập mật khẩu sao lưu** thì lệnh báo `not_set_up` — thiết lập lần đầu ở màn **Sao lưu & phục hồi**.
- **Sai mật khẩu sao lưu** → `wrong_secret`; bản sao lưu không bị chạm tới.
- **Bản không xác minh được nguồn gốc** (tạo ở site khác) chỉ phục hồi được khi bạn thêm `--accept-unverified`.

## Hỏi & Đáp (Q&A)
**Câu 1: Hosting không có dòng lệnh, mất mật khẩu quản trị thì sao?**

→ Dùng **Quên mật khẩu** ở trang đăng nhập — cần email tài khoản là email thật và site gửi được mail. Vì vậy, ngay sau khi
cài, hãy đổi email tài khoản quản trị.

**Câu 2: Đặt lại mật khẩu xong, ứng dụng dùng API của tôi ngừng chạy?**

→ Đúng như thiết kế: token API và MCP của tài khoản bị thu hồi để kẻ đã lấy mật khẩu cũ không còn dùng được. Tạo token mới
trong trang quản trị.

**Câu 3: Tôi đổi `GP247_ENCRYPTION_KEY` mà quên chạy lệnh đổi khoá?**

→ Đưa khoá cũ vào `GP247_ENCRYPTION_PREVIOUS_KEYS`, chạy `php artisan config:clear` rồi `gp247:encryption-key-rotate`.
`gp247:doctor` mục `secret_decryptable` cho biết đã ổn chưa.

**Câu 4: Sao lưu bằng dòng lệnh khác gì bấm nút trên màn?**

→ Cùng một bộ máy, cùng định dạng file. Dòng lệnh không bị giới hạn thời gian của request web nên an toàn hơn với site lớn.

**Câu 5: Chạy `backup-restore` trong script, lệnh báo `confirmation_required`?**

→ Thêm `--confirm=<mã>` (gõ lại đúng mã bản), và truyền mật khẩu sao lưu qua `--secret-stdin`.

**Câu 6: `shop-clear-cart` có xoá giỏ hàng khách đang mua không?**

→ Không. Lệnh chỉ xoá giỏ quá số ngày đặt trong `cart_expire`.

**Câu 7: Có cần cron riêng cho `backup-run --scheduled` không?**

→ Không. Dòng cron `schedule:run` chung đã đủ; GP247 tự đăng ký giờ sao lưu theo **Cài đặt sao lưu**.

---

<sub>📅 **Cập nhật lần cuối:** 2026-11-08 · ✍️ **Tác giả (Author):** GP247</sub>
