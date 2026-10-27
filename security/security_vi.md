> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./security.md) · [🇯🇵 日本語](./security_ja.md)

# Phát hành bảo mật GP247

## Giới thiệu
Trang này liệt kê các bản phát hành có **sửa lỗi bảo mật** của `gp247/core`, `gp247/front` và
`gp247/shop` từ dòng **3.x**, mới nhất ở trên. Trang dành cho **chủ site và người vận hành**: chỉ cần so phiên
bản đang chạy với bảng "Nhìn nhanh" là biết site đã an toàn hay cần cập nhật.

## Nhìn nhanh — phiên bản tối thiểu an toàn
| Repo | Bản tối thiểu an toàn | Bản vá gần nhất |
| --- | --- | --- |
| gp247/core | **3.2.3** | 2026-10-27 |
| gp247/front | **3.0.1** | 2026-09-02 |
| gp247/shop | **3.2.3** | 2026-10-27 |

> ⚠️ **Lưu ý:** chỉ dòng **3.x** còn nhận bản vá bảo mật. Site còn chạy 2.x cần nâng lên 3.x.
> `gp247/shop` 3.2.3 yêu cầu `gp247/core` 3.2.3 trở lên — hãy cập nhật cả hai cùng lúc.

> 💡 **Xem phiên bản đang chạy:** `php artisan gp247:info`, hoặc `composer show "gp247/*"`.

## Dòng thời gian
| Thời gian | Bản phát hành | Repo | Sự cố bảo mật |
| --- | --- | --- | --- |
| 2026-10-27 | 3.2.3<br>3.2.3 | gp247/core<br>gp247/shop | **Phân quyền / Rò dữ liệu** — REST API (site có bật API): API quản trị không áp dụng phân quyền và phạm vi cửa hàng; đăng nhập API không giới hạn số lần thử; token không hết hạn; khoá API lưu dạng rõ; API công khai lộ trường nội bộ (giá vốn, nhà cung cấp, tồn kho) và sản phẩm chưa hiển thị. Ngoài ra: khách hàng bị khoá vẫn đăng nhập được |
| 2026-10-21 | 3.1.2 | gp247/core | **Path traversal / SSRF** — cài và cập nhật extension tin vào gói và đường dẫn tải không đáng tin; license có thể bị gửi sang máy chủ khác qua chuyển hướng |
| 2026-09-29 | 3.0.14 | gp247/core | **Lộ bí mật** — màn cấu hình gửi giá trị bí mật đã giải mã (mật khẩu SMTP, secret key) xuống trình duyệt |
| 2026-09-04 | 3.0.3 | gp247/core | **Lộ bí mật** — bí mật cấu hình (mật khẩu SMTP, client secret, license) lưu dạng rõ trong cơ sở dữ liệu; nay được mã hoá |
| 2026-09-02 | 3.0.1<br>3.0.2 | gp247/front<br>gp247/shop | **Rò dữ liệu** — một số màn quản trị (nhóm liên kết, danh mục, chọn sản phẩm khi tạo đơn) hiện dữ liệu của cửa hàng khác (site đa cửa hàng) |

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

Chi tiết từng bước: [Hướng dẫn cập nhật GP247](../system/update-gp247_vi.md).

## Báo lỗ hổng bảo mật
Phát hiện lỗ hổng, vui lòng gửi email tới **gp247.net@gmail.com** — **không** mở issue công
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

<sub>📅 **Cập nhật lần cuối:** 2026-10-27 · ✍️ **Tác giả (Author):** GP247</sub>
