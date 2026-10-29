> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./security.md) · [🇯🇵 日本語](./security_ja.md)

# Phát hành bảo mật GP247

## Giới thiệu
Trang này liệt kê các bản phát hành có **sửa lỗi bảo mật** của `gp247/core`, `gp247/front` và
`gp247/shop` từ dòng **3.x**, mới nhất ở trên. Trang dành cho **chủ site và người vận hành**: chỉ cần so phiên
bản đang chạy với bảng "Nhìn nhanh" là biết site đã an toàn hay cần cập nhật.

## Nhìn nhanh — phiên bản tối thiểu an toàn
| Repo | Bản tối thiểu an toàn | Bản vá gần nhất |
| --- | --- | --- |
| gp247/core | **3.5.0** | 2026-10-28 |
| gp247/front | **3.0.1** | 2026-09-02 |
| gp247/shop | **3.3.1** | 2026-10-28 |

> ⚠️ **Lưu ý:** chỉ dòng **3.x** còn nhận bản vá bảo mật. Site còn chạy 2.x cần nâng lên 3.x.
> `gp247/shop` 3.3.1 yêu cầu `gp247/core` 3.3 trở lên — hãy cập nhật cả hai cùng lúc.

> 💡 **Xem phiên bản đang chạy:** `php artisan gp247:info`, hoặc `composer show "gp247/*"`.

## Dòng thời gian
*Đang cập nhật.*

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

## Hỏi & Đáp (Q&A)
**Câu 1: Site tôi có bị ảnh hưởng không?**

→ So phiên bản đang chạy với bảng "Nhìn nhanh". Thấp hơn bản tối thiểu ở bất kỳ repo nào thì cần cập nhật.

**Câu 2: Tôi không dùng đa cửa hàng / không bật API thì có cần cập nhật không?**

→ Các dòng ghi "(site đa cửa hàng)" hoặc "(site có bật API)" không ảnh hưởng site của bạn, nhưng các dòng khác vẫn có. Luôn nên lên bản tối thiểu.

**Câu 3: Tôi còn chạy 2.x thì sao?**

→ Dòng 2.x không còn nhận bản vá bảo mật và không được liệt kê ở trang này. Hãy nâng lên 3.x (bản tối thiểu ở bảng "Nhìn nhanh") theo hướng dẫn cập nhật.

**Câu 4: Vì sao trang không mô tả chi tiết cách lỗi bị khai thác?**

→ Để không chỉ đường cho kẻ tấn công nhắm vào các site chưa cập nhật. Trang chỉ nêu loại lỗi, phạm vi ảnh hưởng và bản đã vá.

---

<sub>📅 **Cập nhật lần cuối:** 2026-10-29 · ✍️ **Tác giả (Author):** GP247</sub>
