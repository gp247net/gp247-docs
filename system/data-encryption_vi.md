> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./data-encryption.md)

# Mã hoá dữ liệu nhạy cảm trong GP247

> ℹ️ **Yêu cầu phiên bản:** Chức năng mã hoá dữ liệu at-rest (khoá riêng `GP247_ENCRYPTION_KEY`, lệnh `gp247:encryption-key-rotate`, cast `Secret`) chỉ có **từ `gp247/core` 3.0.3 trở lên**. Bản cũ hơn lưu các bí mật này dạng thô. Kiểm tra phiên bản bằng `php artisan gp247:info`.

## Giới thiệu
Tài liệu này giải thích **GP247 bảo vệ các thông tin nhạy cảm như thế nào** (mật khẩu SMTP, secret Google Captcha, OAuth client secret, license plugin…) và — quan trọng nhất — **các bước đổi khoá mã hoá an toàn mà không mất dữ liệu**, bằng lệnh có sẵn. Dành cho chủ site và người triển khai; phần cuối có mục dành cho lập trình viên muốn mã hoá thêm dữ liệu riêng. Đọc xong bạn hiểu: dữ liệu nào được mã hoá, khoá nằm ở đâu, và đổi khoá thế nào cho đúng.

## Mã hoá at-rest là gì (nói ngắn gọn)
"At-rest" nghĩa là **dữ liệu được mã hoá khi nằm trong cơ sở dữ liệu**. Thay vì lưu mật khẩu SMTP dạng chữ thường, GP247 lưu một chuỗi rối bắt đầu bằng `enc:v2:…`. Chỉ hệ thống có đúng **khoá mã hoá** mới đọc lại được.

**Bảo vệ được gì:** ai lấy được bản sao lưu cơ sở dữ liệu hoặc đọc trộm bảng dữ liệu **không đọc được** bí mật vì không có khoá.

**Không bảo vệ được gì:** người có **cả** cơ sở dữ liệu **lẫn** khoá (toàn quyền máy chủ) thì vẫn đọc được. Đây là mức bảo vệ tiêu chuẩn cho bí mật lưu trong cơ sở dữ liệu, không phải két sắt tuyệt đối.

## Khoá mã hoá nằm ở đâu: `GP247_ENCRYPTION_KEY`
GP247 dùng một **khoá riêng** để mã hoá bí mật: biến `GP247_ENCRYPTION_KEY` trong file `.env`. Khoá này **tách riêng** khỏi `APP_KEY` (khoá chung của Laravel) có chủ đích: nhờ vậy, lỡ đổi hay tạo lại `APP_KEY` cũng **không ảnh hưởng** tới bí mật đã mã hoá.

Nếu bạn **chưa đặt** `GP247_ENCRYPTION_KEY`, GP247 tạm dùng `APP_KEY` để không gián đoạn — nhưng lệnh kiểm tra `gp247:doctor` sẽ **nhắc** bạn đặt khoá riêng. Nên đặt ngay từ đầu (xem mục dưới).

### Đặt khoá riêng lần đầu (khuyến nghị làm ngay sau khi cài)
1. Mở **Terminal** tại thư mục dự án, sinh một khoá mới bằng lệnh:

   ```
   php artisan key:generate --show
   ```

   Lệnh này **chỉ in ra** một khoá dạng `base64:…=`, **không** sửa gì. Chép lại giá trị đó.

2. Mở file `.env`, tìm dòng `GP247_ENCRYPTION_KEY=` và dán khoá vừa chép vào:

   ```
   GP247_ENCRYPTION_KEY=base64:KhoaVuaSinh...=
   ```

3. Xoá cache cấu hình để hệ thống nhận khoá mới:

   ```
   php artisan config:clear
   ```

4. **Sao lưu `.env`** (chứa khoá) ra nơi an toàn ngoài máy chủ. Mất khoá là mất khả năng đọc bí mật đã mã hoá.

> Nếu site đã có bí mật mã hoá bằng `APP_KEY` từ trước (khi chưa đặt khoá riêng), sau khi đặt khoá riêng hãy chạy **`php artisan gp247:encryption-key-rotate`** để mã hoá lại toàn bộ sang khoá riêng (xem mục đổi khoá bên dưới — quy trình y hệt).

## Những dữ liệu nào được mã hoá
Các trường được đánh dấu "bí mật" tự động mã hoá khi lưu:

| Dữ liệu | Ở màn hình nào |
| --- | --- |
| Mật khẩu SMTP (`smtp_password`) | Cấu hình → Email/SMTP |
| Secret key Google Captcha | Plugin GoogleCaptcha |
| OAuth client secret (Facebook/Google/GitHub…) | Plugin Đăng nhập mạng xã hội (LoginSocial) |
| License của plugin trả phí | Màn quản lý Extension/License |

Mật khẩu tài khoản quản trị **không** nằm ở đây — chúng được băm một chiều (bcrypt), không phải mã hoá đảo ngược.

## Các bước ĐỔI KHOÁ mã hoá an toàn (không mất dữ liệu)
Đây là phần quan trọng nhất. Chỉ đổi khoá khi **nghi khoá bị lộ** (lỡ đưa `.env` lên Git…) hoặc **chính sách bảo mật yêu cầu**. GP247 có sẵn một lệnh làm việc nặng cho bạn: **`gp247:encryption-key-rotate`** — nó đọc mọi bí mật bằng **khoá cũ** rồi mã hoá lại bằng **khoá mới**.

> ⚠️ **KHÔNG** sửa tay `GP247_ENCRYPTION_KEY` thành khoá mới rồi thôi. Làm vậy sẽ khiến toàn bộ bí mật cũ **không đọc được**. Hãy làm đúng 8 bước sau.

1. **Sao lưu trước khi làm.** Chép file `.env` ra một chỗ an toàn (ví dụ `.env.backup`) và sao lưu cơ sở dữ liệu. Đây là lưới an toàn.

2. **Ghi lại khoá hiện tại.** Mở `.env`, chép lại **nguyên văn** giá trị của `GP247_ENCRYPTION_KEY` (dạng `base64:…=`) ra một chỗ. Đây là **khoá cũ**.

3. **Sinh khoá mới (chưa áp dụng).** Trong Terminal:

   ```
   php artisan key:generate --show
   ```

   Chép lại giá trị `base64:…=` in ra — đây là **khoá mới**.

4. **Sửa file `.env`** — làm **hai** việc:
   - Đưa **khoá cũ** (Bước 2) vào dòng `GP247_ENCRYPTION_PREVIOUS_KEYS`.
   - Đổi `GP247_ENCRYPTION_KEY` thành **khoá mới** (Bước 3).

   Kết quả trông như thế này (thay bằng giá trị thật):

   ```
   GP247_ENCRYPTION_KEY=base64:KhoaMoi...=
   GP247_ENCRYPTION_PREVIOUS_KEYS=base64:KhoaCu...=
   ```

   > `GP247_ENCRYPTION_PREVIOUS_KEYS` có thể chứa nhiều khoá cũ, ngăn cách bằng dấu phẩy.

5. **Xoá cache cấu hình:**

   ```
   php artisan config:clear
   ```

6. **Chạy lệnh đổi khoá** để mã hoá lại toàn bộ bí mật sang khoá mới:

   ```
   php artisan gp247:encryption-key-rotate
   ```

   Nếu thành công, màn hình liệt kê từng bảng và số dòng đã chuyển, ví dụ:
   `admin_config.value: 5 converted, 0 already current, 0 undecryptable`.
   Nếu có dòng **"undecryptable"** (không giải mã được) → khoá cũ ở Bước 4 chưa đúng; kiểm tra lại `GP247_ENCRYPTION_PREVIOUS_KEYS` rồi chạy lại. Lệnh **không** ghi đè dòng lỗi, nên dữ liệu không bị hỏng.

   > Muốn xem trước mà chưa ghi, chạy `php artisan gp247:encryption-key-rotate --dry-run`.

7. **Kiểm tra lại bằng lệnh chẩn đoán:**

   ```
   php artisan gp247:doctor
   ```

   Tìm dòng `secret_decryptable` — nếu **PASS** kèm "N secrets OK" là mọi bí mật đọc được bằng khoá mới.

8. **Dọn dẹp.** Khi Bước 6 báo mọi dòng đã chuyển (0 "undecryptable") và `doctor` PASS, bạn có thể **xoá** dòng `GP247_ENCRYPTION_PREVIOUS_KEYS` khỏi `.env` rồi chạy lại `php artisan config:clear`. Không chắc thì cứ để nguyên — giữ khoá cũ không gây hại.

## Nếu lỡ đổi khoá sai / mất khoá
- **Còn giữ khoá cũ** (trong `.env.backup` hoặc đã chép ra): đưa vào `GP247_ENCRYPTION_PREVIOUS_KEYS`, chạy `php artisan config:clear`, rồi làm lại từ Bước 6. Dữ liệu đọc lại được.
- **Không còn khoá cũ ở bất cứ đâu:** bí mật cũ **không khôi phục được** — phải **nhập lại** thủ công ở từng màn cấu hình (mật khẩu SMTP, secret, license…). Hệ thống không sập, chỉ là các tính năng dùng bí mật (gửi mail, thanh toán…) ngừng cho tới khi nhập lại. Đây là lý do **Bước 1 (sao lưu) là bắt buộc**.

## Dành cho lập trình viên: mã hoá một cột dữ liệu riêng (ngoài bảng cấu hình)
Nếu plugin/tính năng của bạn có một cột chứa bí mật (ví dụ token của bên thứ ba) **không nằm** trong bảng cấu hình, mã hoá nó bằng **cast dùng chung** của GP247 — chỉ một dòng, không sửa core:

```php
// Trong model:
protected $casts = [
    'api_token' => \GP247\Core\Casts\Secret::class,
];
```

Từ đó, đọc/ghi `$model->api_token` là plaintext trong PHP, còn trong cơ sở dữ liệu luôn là `enc:v2:…`.

Để lệnh `gp247:doctor` và `gp247:encryption-key-rotate` **cũng phủ** cột này (kiểm tra và đổi khoá), khai báo nó trong `Provider.php` của plugin (trong khối plugin đang bật):

```php
config(['gp247-config.security.encrypted_columns.my_plugin_tokens' => ['api_token']]);
```

Ba lưu ý: (1) cột phải kiểu **TEXT** vì bản mã dài; (2) **không tìm kiếm/lọc** được trên cột đã mã (cần cột "chỉ mục mù" riêng nếu muốn tra cứu); (3) bảng phải có cột khoá chính `id` để lệnh đổi khoá cập nhật từng dòng.

## Điều kiện & ràng buộc (hiểu trước khi thao tác)
- **Phải sao lưu khoá mã hoá (`GP247_ENCRYPTION_KEY`) và file `.env`** — mất khoá là mất khả năng đọc mọi bí mật đã mã hoá; không có cách khôi phục nào khác.
- **Trước khi đổi khoá, bắt buộc đưa khoá cũ vào `GP247_ENCRYPTION_PREVIOUS_KEYS`** — nếu không, lệnh đổi khoá không đọc được bí mật cũ. Đây là bước dễ bỏ sót nhất.
- **Không sửa tay `GP247_ENCRYPTION_KEY` để "đổi khoá"** — luôn đổi qua quy trình `gp247:encryption-key-rotate`, nếu không dữ liệu cũ sẽ không đọc được.
- **Giải mã lỗi không làm sập site** — khoá sai thì bí mật bị coi như rỗng và tính năng liên quan tạm ngừng, thay vì báo lỗi trắng trang (chủ đích, để site vẫn chạy trên hosting hạn chế).
- **Không tìm kiếm/lọc trên dữ liệu đã mã hoá** — giá trị lưu là chuỗi rối, không thể `WHERE = giá trị thật`.
- **Sau mọi thay đổi `.env` phải `php artisan config:clear`** — nếu không, hệ thống có thể vẫn dùng cấu hình cũ trong cache.

## Hỏi & Đáp (Q&A)
**Câu 1: Tôi có bắt buộc đặt `GP247_ENCRYPTION_KEY` không?**

→ Rất nên, ngay sau khi cài. Chưa đặt thì GP247 tạm dùng `APP_KEY` và `gp247:doctor` sẽ nhắc. Đặt khoá riêng giúp đổi `APP_KEY` không ảnh hưởng bí mật.

**Câu 2: Đổi khoá thì dùng lệnh nào?**

→ `php artisan gp247:encryption-key-rotate` — nó mã hoá lại toàn bộ bí mật bằng khoá mới. Nhưng phải làm đủ các bước ở mục "Các bước ĐỔI KHOÁ" (đặt khoá cũ vào `GP247_ENCRYPTION_PREVIOUS_KEYS` trước).

**Câu 3: `gp247:doctor` báo "secrets undecryptable" nghĩa là gì?**

→ Có bí mật không giải mã được — thường do khoá đã đổi mà chưa khai khoá cũ. Đưa khoá cũ vào `GP247_ENCRYPTION_PREVIOUS_KEYS`, chạy `php artisan config:clear`, rồi chạy lại `gp247:encryption-key-rotate`. Nếu mất hẳn khoá cũ, phải nhập lại các bí mật.

**Câu 4: Lệnh đổi khoá có an toàn để chạy nhiều lần không?**

→ Có. Nó bỏ qua các dòng đã dùng khoá mới, nên chạy lại là vô hại. Muốn xem trước không ghi thì thêm `--dry-run`.

**Câu 5: Đổi `APP_KEY` (không phải khoá mã hoá) có mất bí mật không?**

→ Không, nếu bạn đã đặt `GP247_ENCRYPTION_KEY` riêng. Bí mật gắn với khoá riêng, không phụ thuộc `APP_KEY`.

**Câu 6: Mật khẩu SMTP có bị lộ trên màn admin không?**

→ Không hiển thị chữ thường — ô che ●●●, và lưu mã hoá (`enc:v2:…`) trong cơ sở dữ liệu.

**Câu 7: Plugin của tôi có cột bí mật riêng, làm sao mã hoá?**

→ Dùng cast `\GP247\Core\Casts\Secret::class` trong `$casts` của model, và khai cột đó vào `gp247-config.security.encrypted_columns` để lệnh doctor/đổi khoá phủ tới. Xem mục "Dành cho lập trình viên".

**Câu 8: Người có quyền máy chủ vẫn đọc được bí mật đúng không?**

→ Đúng. Ai có cả cơ sở dữ liệu lẫn khoá đều giải mã được. Mã hoá at-rest chống rò qua bản sao lưu/đọc trộm DB, không chống người đã có toàn quyền máy chủ.

---

<sub>📅 **Cập nhật lần cuối:** 2026-09-04 · ✍️ **Tác giả (Author):** GP247</sub>
