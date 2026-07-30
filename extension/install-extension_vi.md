> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./install-extension.md)

# Hướng dẫn cài đặt Plugin & Template cho GP247

## Giới thiệu

Tài liệu này hướng dẫn cách **cài đặt một plugin hoặc template (gọi chung là extension) vào website
GP247**, dành cho chủ website — kể cả người không rành kỹ thuật. GP247 hỗ trợ **3 cách cài**: cài
**online** qua thư viện extension chính thức, **import** một file `.zip` có sẵn, hoặc cài **thủ công**
bằng cách chép thư mục vào máy chủ. Đọc xong, bạn sẽ biết chọn cách phù hợp và làm theo từng bước.

> 💡 Plugin và template cài **giống hệt nhau** — cùng 3 cách này. Khác biệt duy nhất là chúng nằm ở
> hai mục menu khác nhau trong admin: **Plugin** (tiện ích) và **Template** (giao diện). Trong tài
> liệu, chỗ nào ghi "extension" là áp dụng cho cả hai.

---

## 1. Trước khi cài — những điều cần biết

1. Bạn cần đăng nhập admin bằng tài khoản **có quyền quản lý extension**.
2. Extension phải **tương thích** với website của bạn. Khi cài, GP247 tự kiểm tra 3 điều kiện khai báo
   trong file `gp247.json` của extension:
   - `requireCore`: phiên bản `gp247/core` yêu cầu (chuẩn hiện tại là `2.0`).
   - `requirePackages`: các gói bắt buộc phải có (ví dụ template luôn cần `gp247/front`).
   - `requireExtensions`: các extension khác phải cài trước.

   Nếu thiếu điều kiện nào, GP247 sẽ **báo lỗi và không cho cài** — hãy cài phần còn thiếu trước.
3. Sau khi cài, GP247 **tự xoá cache** (route/config) nên bạn không cần làm thủ công. Với **template**,
   cài xong bạn còn phải **Kích hoạt (Activate)** thì giao diện mới có hiệu lực.

> Thuật ngữ nhanh:
> - **Extension**: tên gọi chung cho plugin và template.
> - **`gp247.json`**: file khai báo thông tin của extension (tên, phiên bản, điều kiện tương thích).
> - **`configKey`**: mã định danh duy nhất của extension, trùng tên thư mục của nó.

---

## 2. Cách 1 — Cài Online (qua thư viện extension)

Đây là cách **dễ nhất**: duyệt kho extension chính thức của GP247 ngay trong admin rồi bấm cài, không
cần tải file thủ công.

> ⚠️ Cách này chỉ hiện ra khi website của bạn đã **bật kết nối thư viện GP247** (cấu hình
> `api_plugins` cho plugin / `api_templates` cho template được bật). Nếu không thấy mục **Online**,
> hãy dùng Cách 2 hoặc Cách 3.

### Bước 0 (chỉ làm 1 lần) — Đăng ký API License để kết nối thư viện

Trước khi duyệt được kho extension, website của bạn cần một **API License** để nhận diện với thư viện
GP247. **License này miễn phí.**

1. Trong admin, mở mục **Cài đặt API License** (Register / Cài đặt license API) — thường nằm trong màn
   **Online** của Plugin/Template.
2. Bấm **Đăng ký / Cài đặt**. GP247 sẽ liên hệ thư viện, lấy về một khoá license cho tên miền của bạn.
3. Nếu thành công, khoá này được **tự động lưu vào biến `GP247_API_LICENSE`** trong file `.env` ở thư
   mục gốc website. Bạn **không cần** tự sửa file `.env` bằng tay.

Sau khi có `GP247_API_LICENSE`, website đã kết nối được thư viện — chuyển sang các bước cài bên dưới.
Bước 0 này chỉ làm một lần; các lần cài sau không phải làm lại.

> ℹ️ Phân biệt hai loại license:
> - **API License** (bước 0, **miễn phí**): khoá để website *kết nối và duyệt* thư viện. Lưu ở `GP247_API_LICENSE`.
> - **License extension trả phí**: bản quyền riêng cho *từng extension mất phí*, chỉ cần khi tải extension trả phí (xem cuối phần này).

### Các bước cài online

1. Đăng nhập admin. Vào menu **Plugin** (hoặc **Template**), rồi chọn mục con **Online** (Thư viện /
   Cửa hàng extension).
2. Danh sách extension từ thư viện GP247 hiện ra. Bạn có thể **tìm theo từ khoá**, lọc **miễn phí / trả
   phí**, và sắp xếp. Mỗi extension hiển thị tên, phiên bản và giá.
3. Chọn extension muốn cài, bấm nút **Cài đặt (Install)**.
4. GP247 tự động: tải file về → kiểm tra tương thích → giải nén → cài đặt. Nếu thành công, bạn thấy
   thông báo cài đặt thành công và extension xuất hiện trong danh sách đã cài.

**Về extension trả phí (paid):** ngoài API License miễn phí ở Bước 0, extension trả phí còn cần **bản
quyền riêng cho chính extension đó** (cấp cho tên miền của bạn sau khi mua). Nếu chưa có, admin sẽ
hướng dẫn kích hoạt bản quyền trước khi tải về. Extension **miễn phí** thì tải và cài ngay, không cần
bản quyền riêng (chỉ cần API License ở Bước 0).

---

## 3. Cách 2 — Import (tải file `.zip` lên)

Dùng khi bạn **đã có sẵn file extension dạng `.zip`** (mua ngoài thư viện, nhận từ nhà phát triển, hoặc
tự đóng gói). Bạn tải file này lên qua admin, GP247 tự giải nén và cài.

Các bước:

1. Vào menu **Plugin** (hoặc **Template**), chọn mục **Import** (Nhập / Tải lên).
2. Bấm chọn file `.zip` extension từ máy tính của bạn, rồi bấm **Tải lên / Import**.
3. GP247 kiểm tra và cài tự động. Nếu thành công, extension xuất hiện trong danh sách đã cài.

**Yêu cầu đối với file `.zip`:**
- Phải đúng định dạng **`.zip`** (không phải `.rar`, `.7z`...).
- Dung lượng **tối đa 50MB** (và không vượt giới hạn upload của máy chủ — `upload_max_filesize` /
  `post_max_size` trong PHP).
- Bên trong `.zip` phải có file **`gp247.json`** (nếu không, GP247 báo lỗi "sai cấu hình").
- **Không trùng** với extension đã cài: nếu `configKey` đã tồn tại trên site, GP247 sẽ từ chối để tránh
  ghi đè nhầm. Muốn cập nhật bản mới, dùng chức năng **cập nhật (update)**, không import đè.

> ℹ️ Lưu ý nhỏ: sau khi import **template** thành công, màn hình có thể chuyển về **danh sách Plugin**
> thay vì danh sách Template (đây là đặc điểm hiện tại của hệ thống). Đừng lo — file template vẫn được
> cài đúng vào thư mục template; bạn chỉ cần sang menu **Template** để thấy và **Kích hoạt** nó.

---

## 4. Cách 3 — Cài thủ công (chép thư mục vào máy chủ)

Dùng khi bạn có **quyền truy cập file máy chủ** (FTP, SSH, hoặc File Manager của hosting) và muốn đưa
extension vào trực tiếp — thường dành cho lập trình viên hoặc khi hai cách trên không dùng được.

Các bước:

1. Chép **cả thư mục extension** (thư mục có chứa `AppConfig.php` và `gp247.json`) vào đúng vị trí trên
   máy chủ, tuỳ loại:
   - Plugin → `app/GP247/Plugins/<TênExtension>`
   - Template → `app/GP247/Templates/<TênExtension>`

   Trong đó `<TênExtension>` phải **trùng đúng** với `configKey` khai báo trong `gp247.json`.

   Ví dụ, plugin có `configKey` là `MyBanner` thì chép vào:

   ```
   app/GP247/Plugins/MyBanner/
   ├── AppConfig.php
   ├── gp247.json
   └── ...(các file còn lại)
   ```

2. Nếu extension có thư mục `public/` (chứa css/js/ảnh), chép nội dung đó sang:
   - Plugin → `public/GP247/Plugins/<TênExtension>`
   - Template → `public/GP247/Templates/<TênExtension>`

3. Vào admin, mở menu **Plugin** (hoặc **Template**). Extension bạn vừa chép sẽ **tự động xuất hiện**
   trong danh sách (GP247 quét thư mục để phát hiện). Nó ở trạng thái **chưa cài**.
4. Bấm nút **Cài đặt (Install)** bên cạnh extension đó. GP247 kiểm tra tương thích rồi cài. Nếu thành
   công, nút chuyển sang trạng thái đã cài.

> ⚠️ Thư mục extension **bắt buộc** phải có `AppConfig.php` — GP247 dựa vào file này để phát hiện. Nếu
> chép thiếu file này, extension sẽ **không hiện** trong danh sách admin.

---

## 5. Sau khi cài — kích hoạt và kiểm tra

1. **Với plugin:** cài xong thường đã dùng được. Một số plugin có nút **Bật/Tắt (Enable/Disable)** và
   **Cấu hình (Config)** — vào chỉnh nếu cần.
2. **Với template:** sau khi cài, phải bấm **Kích hoạt (Activate)** để đặt làm giao diện đang dùng của
   cửa hàng. Chỉ một template được kích hoạt tại một thời điểm.
3. GP247 tự xoá cache sau khi cài. Nếu vì lý do nào đó giao diện/chức năng chưa cập nhật, chạy lệnh sau
   tại thư mục gốc website để xoá cache thủ công:

   ```bash
   php artisan optimize:clear
   ```

4. Mở website (hoặc màn admin liên quan) để kiểm tra extension đã hoạt động đúng.

---

## 6. Nên dùng cách nào? (so sánh nhanh)

| Cách | Khi nào dùng | Ưu điểm | Cần gì |
|---|---|---|---|
| **Online** | Muốn duyệt & cài nhanh từ kho chính thức | Dễ nhất, tự động hoàn toàn | Site bật kết nối thư viện; license nếu là bản trả phí |
| **Import** | Đã có sẵn file `.zip` | Không cần truy cập máy chủ | File `.zip` hợp lệ, ≤ 50MB, có `gp247.json` |
| **Thủ công** | Có quyền file máy chủ / hai cách trên không dùng được | Chủ động, không phụ thuộc upload/API | Quyền FTP/SSH/File Manager; chép đúng thư mục |

---

## 7. Xử lý sự cố thường gặp

- **Báo lỗi tương thích khi cài:** thiếu điều kiện `requireCore` / `requirePackages` / `requireExtensions`.
  Cài phần còn thiếu (ví dụ cài `gp247/front` trước khi cài template) rồi thử lại.
- **Import báo "sai cấu hình":** file `.zip` không chứa `gp247.json` ở đúng cấp, hoặc bạn nén sai (nén cả
  thư mục cha thừa một lớp). Kiểm tra lại cấu trúc file nén.
- **Import báo trùng:** extension đã cài rồi. Nếu muốn lên bản mới, dùng chức năng **cập nhật**, đừng import đè.
- **Cài thủ công nhưng không thấy trong admin:** kiểm tra đã chép **đúng thư mục** (`app/GP247/Plugins`
  hay `Templates`) và thư mục có `AppConfig.php` chưa; sau đó `php artisan optimize:clear` rồi tải lại admin.
- **File `.zip` quá lớn không tải lên được:** vượt 50MB hoặc vượt giới hạn upload của máy chủ. Dùng Cách 3
  (thủ công) thay thế.

---

## 8. Hỏi & Đáp (Q&A)

**Câu 1: Cài plugin và cài template có khác nhau không?**
Cách cài giống hệt nhau (cùng 3 cách). Chỉ khác nơi thao tác: plugin ở menu **Plugin**, template ở menu
**Template**. Riêng template phải **Kích hoạt** sau khi cài thì mới có hiệu lực.

**Câu 2: Tôi không thấy mục "Online" trong admin?**
Website của bạn chưa bật kết nối thư viện GP247 (`api_plugins`/`api_templates`). Hãy dùng Cách 2 (Import)
hoặc Cách 3 (thủ công).

**Câu 3: Muốn dùng thư viện online thì phải làm gì đầu tiên?**
Đăng ký **API License** (miễn phí) một lần trong admin — xem Bước 0 phần Cách 1. Khoá này tự lưu vào
biến `GP247_API_LICENSE` trong file `.env`, giúp website kết nối và duyệt kho extension.

**Câu 4: Extension trả phí cài thế nào?**
Cài online như bình thường, nhưng ngoài API License miễn phí, extension trả phí còn cần **bản quyền
riêng** cho tên miền của bạn (sau khi mua). Extension miễn phí thì không cần.

**Câu 5: File `.zip` cần cấu trúc như thế nào để import được?**
Bên trong `.zip` phải có file `gp247.json` (cùng `AppConfig.php` và các file của extension). Đừng nén dư
một lớp thư mục cha khiến `gp247.json` bị đẩy xuống quá sâu. File cũng phải ≤ 50MB; lớn hơn thì cài thủ công.

**Câu 6: Cài thủ công xong mà admin không thấy extension?**
Kiểm tra: đã chép đúng thư mục `app/GP247/Plugins/<Key>` hoặc `app/GP247/Templates/<Key>` chưa, thư mục có
`AppConfig.php` không, và `<Key>` có trùng `configKey` trong `gp247.json` không. Sau đó chạy
`php artisan optimize:clear` rồi tải lại trang admin.

**Câu 7: Import template xong sao lại nhảy về danh sách Plugin?**
Đây là đặc điểm hiện tại của hệ thống — file template vẫn được cài đúng chỗ. Bạn chỉ cần sang menu
**Template** để thấy và kích hoạt nó.

**Câu 8: Cài xong có phải xoá cache thủ công không?**
Thường là không — GP247 tự xoá cache sau khi cài. Nếu chưa cập nhật, chạy `php artisan optimize:clear`.

**Câu 9: Cài đè bản mới lên bản cũ bằng cách import được không?**
Không nên. Import từ chối nếu `configKey` đã tồn tại. Muốn lên bản mới, dùng chức năng **cập nhật
(update)** — nó giữ nguyên cấu hình đã lưu trong database.

**Câu 10: Gỡ/xóa extension như thế nào, và làm sao tránh lỡ tay xóa mất source?**
Trong danh sách extension có 2 mức xóa: **"Xóa dữ liệu"** (chỉ xóa dữ liệu/cấu hình trong database,
**giữ nguyên file source**) và **"Xóa file"** (xóa cả dữ liệu lẫn toàn bộ file source trên máy chủ).
Lưu ý: không xóa được template đang được kích hoạt (phải chuyển sang template khác trước).

Để **chống lỡ tay xóa** những extension quan trọng, GP247 có cơ chế **bảo vệ**: khai báo `configKey`
của chúng vào biến `GP247_PROTECTED_PLUGINS` (cho plugin) và `GP247_PROTECTED_TEMPLATES` (cho template)
trong file `.env`, ngăn cách bằng dấu phẩy. Ví dụ:

```
GP247_PROTECTED_PLUGINS="Payment,ShippingVN"
GP247_PROTECTED_TEMPLATES="GP247Front"
```

Với extension đã được bảo vệ, admin sẽ **ẩn cả nút "Xóa dữ liệu" lẫn "Xóa file"** — tức không thể gỡ
hay xóa chúng từ giao diện, tránh mất source/dữ liệu do thao tác nhầm. Muốn gỡ thật sự, hãy bỏ tên
extension đó khỏi biến `.env` tương ứng rồi thử lại.

---

<sub>📅 **Cập nhật lần cuối:** 2026-07-30 · ✍️ **Tác giả (Author):** GP247</sub>
