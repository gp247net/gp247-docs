> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./multi-store-setup.md)

# Multi-Store — Phần 1: Cài đặt

## Giới thiệu
Tài liệu này hướng dẫn cài plugin **Multi-Store** lên một website S-Cart đang chạy, tạo cửa hàng thứ hai, trỏ tên miền cho nó, cài thêm **bản Pro** nếu bạn đã mua, và kiểm tra mọi thứ chạy đúng. Dành cho chủ website hoặc người quản trị có kỹ thuật cơ bản; làm theo từng bước là chạy được, không cần biết lập trình. Cách vận hành nhiều cửa hàng và ý nghĩa từng cấu hình xem ở [Phần 2 — Vận hành](./multi-store-operations_vi.md).

## Yêu cầu trước khi bắt đầu
- Một website S-Cart **3.x** đang chạy, với `gp247/core` **3.0** và `gp247/shop` đã cài.
- Plugin multi-vendor **chưa cài** trên cùng website — hai mô hình loại trừ lẫn nhau.
- **Một tên miền cho mỗi cửa hàng mới**, và quyền chỉnh DNS cùng cấu hình web server để trỏ nó về đúng website này.
- Quyền chạy `php artisan` trên máy chủ, **hoặc** dùng cách cài qua giao diện admin ở Bước 1.

Chưa có website S-Cart? Cài nền tảng trước theo [Phần 1 của tài liệu Multi-Vendor](../multi-vendor/multi-vendor-setup_vi.md) (các bước cài S-Cart giống nhau), rồi quay lại đây.

## Bước 1: Cài plugin Multi-Store (bản miễn phí)
Chọn **một** trong ba cách.

**Cách 1 — Từ thư viện GP247 (khuyến nghị)**
1. Đăng nhập khu admin → **Phần mở rộng** → **Tiện ích (Plugins)**.
2. Mở tab **Thư viện**, tìm **MultiStore**, bấm **Cài đặt**.

**Cách 2 — Nhập file zip**
1. Vào **Phần mở rộng** → **Tiện ích** → tab **Nhập file**.
2. Chọn file zip của plugin và tải lên.

**Cách 3 — Chép tay (khi hai cách trên lỗi)**
1. Giải nén, chép thư mục mã nguồn vào `app/GP247/Plugins/MultiStore/`.
2. Chép thư mục `public` của plugin vào `public/GP247/Plugins/MultiStore/`.
3. Vào **Phần mở rộng** → **Tiện ích** → tab **Đã lưu trên máy**, tìm **MultiStore**, bấm **Cài đặt**.

Nếu website đang có plugin multi-vendor, trình cài **dừng ngay và không ghi gì** — đây là hành vi cố ý, xem mục Điều kiện & ràng buộc.

## Bước 2: Kích hoạt
1. Trong danh sách Tiện ích, bấm **Kích hoạt** ở dòng MultiStore. Menu **Multi-store** xuất hiện ở thanh bên admin, gồm:
   - **Danh sách cửa hàng** — màn chính của bản miễn phí;
   - bốn mục có nhãn *(Pro)*: Dashboard hợp nhất, Quản trị viên cửa hàng, Đồng bộ sản phẩm sang cửa hàng, Báo cáo doanh thu sản phẩm.
2. Bấm thử một mục *(Pro)*: khi chưa có bản Pro, nó mở **trang giải thích tính năng** — đó là trạng thái đúng, không phải lỗi.

Mở **Multi-store → Danh sách cửa hàng**: bạn thấy cửa hàng gốc (ROOT) đang có, chỉ báo hạn mức (ví dụ *1/3 cửa hàng*), và công tắc **Kiểm duyệt tên miền nghiêm ngặt (STRICT)**.

## Bước 3: Tạo cửa hàng thứ hai
1. Vào **Multi-store** → **Danh sách cửa hàng** → **Thêm mới**.
2. Điền các trường **bắt buộc**:

   | Trường | Quy tắc | Ví dụ |
   | --- | --- | --- |
   | Mã cửa hàng | duy nhất, tối đa 20 ký tự | `brandb` |
   | Tên miền | duy nhất, tối đa 200 ký tự, **viết không kèm `https://`** | `brand-b.com` |
   | Ngôn ngữ | chọn trong danh sách ngôn ngữ đang bật | `Tiếng Việt` |
   | Tiền tệ | chọn trong danh sách tiền tệ đang bật | `VND` |
   | Giao diện (template) | chọn template đã cài | `GP247Front` |
   | Tiêu đề (mỗi ngôn ngữ) | bắt buộc, tối đa 200 ký tự | `Thương hiệu B` |

   Các trường còn lại là tùy chọn: từ khóa SEO (tối đa 200), mô tả (tối đa 300), logo, điện thoại, email, địa chỉ, và **nội dung trang bảo trì** cho từng ngôn ngữ (đã điền sẵn mẫu, sửa được).

3. Bấm **Lưu**. Hệ thống tạo cửa hàng cùng dữ liệu mặc định của nó trong một giao dịch duy nhất — hoặc thành công trọn vẹn, hoặc không tạo gì.

Nếu bạn đã dùng hết hạn mức (bản miễn phí: 3 cửa hàng, **tính cả cửa hàng gốc**), nút thêm mới bị chặn và hệ thống báo rõ — đây là ranh giới giữa hai bản, không phải lỗi.

## Bước 4: Trỏ tên miền về hệ thống
Cửa hàng mới chỉ mở được khi tên miền của nó **thực sự trỏ về chính website này**. Hai việc, làm ở ngoài S-Cart:

1. **DNS**: trỏ tên miền (bản ghi `A` hoặc `CNAME`) về đúng máy chủ đang chạy website.
2. **Web server**: khai tên miền đó cho cùng một website — cùng thư mục gốc `public/` với tên miền gốc. Trên Apache là thêm `ServerAlias`, trên Nginx là thêm vào `server_name`; trên hosting có bảng điều khiển thì thêm "parked domain" / "addon domain" **trỏ vào cùng thư mục**.
3. Cài chứng chỉ SSL cho tên miền mới nếu website chạy `https`.

> Đây là bước hay bị bỏ sót nhất: cửa hàng đã tạo trong admin nhưng tên miền chưa trỏ về hệ thống thì mở ra sẽ là trang của nhà cung cấp tên miền, hoặc lỗi kết nối — không liên quan tới plugin.

## Bước 5: Bật kiểm duyệt tên miền (khuyến nghị)
Ở **Danh sách cửa hàng**, bật công tắc **Kiểm duyệt tên miền nghiêm ngặt (STRICT)**.

Khi bật, website **chỉ phục vụ** các tên miền đã khai cho cửa hàng, cộng với tên miền gốc trong `.env` (`APP_URL`). Mọi tên miền lạ trỏ tới máy chủ của bạn đều nhận trang **từ chối truy cập** thay vì mở website. Việc này ngăn người khác trỏ tên miền của họ vào website của bạn.

> ⚠️ Bật STRICT **trước khi** khai đủ tên miền sẽ khóa chính bạn ra ngoài các cửa hàng chưa khai. Hãy tạo cửa hàng và điền tên miền xong rồi mới bật.

## Bước 6: Kiểm tra
1. Mở tên miền của cửa hàng mới. Bạn phải thấy website chạy với **giao diện, ngôn ngữ và tiền tệ của cửa hàng đó**, không phải của cửa hàng gốc.
2. Vào admin, tạo (hoặc sửa) một sản phẩm và **gán nó cho cửa hàng mới**; kiểm tra sản phẩm xuất hiện trên tên miền mới và **không** xuất hiện ở cửa hàng gốc nếu bạn không gán.
3. Đặt thử một đơn hàng trên cửa hàng mới, rồi vào admin xem đơn đó ghi nhận đúng cửa hàng.
4. Kiểm tra sức khỏe hệ thống:

   ```bash
   php artisan gp247:info
   php artisan gp247:doctor
   ```

## Bước 7 (tùy chọn): Cài thêm bản Pro
Chỉ làm bước này nếu bạn đã mua bản Pro. Pro **cài thêm** lên bản miễn phí, không thay thế nó.

1. Kiểm tra dòng **MultiStore** trong danh sách Tiện ích đang ở trạng thái đã cài và **đã kích hoạt**. Plugin Pro khai bản miễn phí là phần mở rộng bắt buộc và sẽ từ chối cài khi thiếu.
2. Cài **MultiStorePro** bằng một trong ba cách ở Bước 1 (thường là **Nhập file zip** bạn nhận được từ GP247).
3. Bấm **Kích hoạt**. **Cài mà không kích hoạt thì Pro chưa có hiệu lực** — đây là nguyên nhân phổ biến nhất của "cài rồi mà vẫn thấy trang giải thích".
4. Chạy lệnh sau (hoặc bấm xóa cache trong admin) để các đường dẫn của Pro được đăng ký:

   ```bash
   php artisan gp247:cache-rebuild
   ```

5. Tải lại khu admin và kiểm ba dấu hiệu đã mở khóa:
   - Bốn mục *(Pro)* trong menu **Multi-store** mở **màn thật**, không còn trang giải thích.
   - **Danh sách cửa hàng**: chỉ báo hạn mức chuyển sang **không giới hạn**, và cột **Khóa** bấm được (trước đó bị vô hiệu kèm nhãn Pro).
   - Trong **Quyền hạn người dùng → Nhóm quyền**, xuất hiện hai vai trò mẫu **[M]Store Admin** và **[M]Store Member** để giao cho quản trị viên cửa hàng.

Không phải cài lại gì và không phải chuyển dữ liệu: cửa hàng, sản phẩm, đơn hàng và cấu hình giữ nguyên.

**Nếu bạn tắt hoặc gỡ bản Pro**: các màn Pro quay về trang giải thích, hạn mức trở lại 3 cửa hàng và cột Khóa bị vô hiệu. **Dữ liệu không bị xóa** — cửa hàng vượt hạn mức vẫn còn nguyên và vẫn chạy, các liên kết đồng bộ sản phẩm và việc phân công quản trị viên cửa hàng vẫn được giữ; cài lại Pro là dùng tiếp.

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi cài đặt**
- **Plugin từ chối cài nếu website đã có plugin multi-vendor** — hai plugin dùng chung dữ liệu cửa hàng theo hai mô hình khác nhau; trình cài dừng trước khi ghi bất cứ gì. Gỡ plugin kia trước.
- **Cần `gp247/shop` đã cài** — plugin dựa vào sản phẩm, đơn hàng của shop.
- **Bản Pro cần bản miễn phí đang kích hoạt** — Pro là phần mở rộng, không thay thế.
- **Cài mà không kích hoạt thì chưa có hiệu lực** — với cả hai bản.

**Khi tạo cửa hàng**
- **Mã cửa hàng là duy nhất, tối đa 20 ký tự**.
- **Tên miền là duy nhất, tối đa 200 ký tự** — hai cửa hàng không dùng chung một tên miền, vì hệ thống nhận diện cửa hàng bằng chính tên miền.
- **Tên miền nhập không kèm `http://`/`https://` và không có dấu `/` ở cuối** — hệ thống tự chuẩn hóa về chữ thường, nhưng nhập đúng dạng `brand-b.com` sẽ tránh nhầm lẫn.
- **Ngôn ngữ, tiền tệ, giao diện và tiêu đề là bắt buộc** — thiếu một trong số đó thì cửa hàng không mở ra được đúng cách.
- **Bản miễn phí giới hạn 3 cửa hàng, tính cả cửa hàng gốc** — hệ thống kiểm tra ở phía máy chủ, không chỉ ẩn nút.

**Khi đổi tên miền của một cửa hàng đang chạy**
- **Tên miền cũ sẽ không còn mở được cửa hàng đó** — nếu website đã chạy thật và được Google lập chỉ mục, hãy chuẩn bị chuyển hướng 301 trước khi đổi.
- **Bật STRICT mà quên khai tên miền** thì chính tên miền đó bị từ chối — khai đủ rồi hãy bật.

## Xử lý sự cố
| Hiện tượng | Cách xử lý |
| --- | --- |
| Không thấy menu **Multi-store** | Kiểm tra plugin đã **Kích hoạt** (không chỉ Cài đặt); chạy `php artisan gp247:cache-rebuild` |
| Mục *(Pro)* vẫn ra trang giải thích | Kiểm tra MultiStorePro đã **Kích hoạt**, rồi chạy `php artisan gp247:cache-rebuild` |
| Cài báo lỗi xung đột | Website đang có plugin multi-vendor — hai mô hình loại trừ nhau, phải gỡ plugin kia trước |
| Cài Pro báo thiếu phần mở rộng | Cài và kích hoạt plugin **MultiStore** miễn phí trước |
| Mở tên miền mới ra trang của nhà cung cấp tên miền hoặc lỗi kết nối | DNS/web server chưa trỏ tên miền về website này — xem Bước 4 |
| Mở tên miền mới ra **trang từ chối truy cập** | STRICT đang bật mà tên miền chưa được khai cho cửa hàng nào; kiểm tra lại ô Tên miền trong cấu hình cửa hàng |
| Mở tên miền mới nhưng hiện nội dung của cửa hàng gốc | Tên miền trong cấu hình cửa hàng không khớp tên miền thật đang gõ (khác `www`, khác chính tả); sửa lại cho khớp |
| Không thêm được cửa hàng mới | Đã chạm hạn mức của bản miễn phí (3 cửa hàng kể cả gốc) — nâng lên Pro |
| Màn hình hiện mã khóa thô thay vì chữ | Mở lại màn **Danh sách cửa hàng** một lần, hoặc tắt rồi bật lại plugin |
| Muốn xem lỗi chi tiết | Mở `storage/logs/laravel.log` |

## Hỏi & Đáp (Q&A)
**Câu 1: Tôi phải mua thêm hosting cho cửa hàng thứ hai không?**

→ Không. Mọi cửa hàng chạy trên cùng một hệ thống, cùng một cơ sở dữ liệu. Bạn chỉ cần thêm **tên miền** và trỏ nó về website này.

**Câu 2: Cửa hàng gốc (ROOT) là gì?**

→ Là cửa hàng có sẵn từ khi cài S-Cart, dùng tên miền trong `.env`. Nó là nền của cả hệ thống nên không xóa được và không khóa được.

**Câu 3: Tôi dùng subdomain (`b.brand-a.com`) thay vì tên miền riêng được không?**

→ Được. Hệ thống chỉ so tên miền của request với tên miền bạn khai, nên subdomain hoạt động y hệt tên miền riêng — miễn là nó trỏ về cùng website.

**Câu 4: Bật STRICT có bắt buộc không?**

→ Không bắt buộc, nhưng nên bật khi đã khai đủ tên miền: nó chặn người khác trỏ tên miền lạ vào website của bạn.

**Câu 5: Tôi đang có 3 cửa hàng ở bản miễn phí, gỡ Pro ra thì cửa hàng thứ 4, 5 có mất không?**

→ Không mất. Dữ liệu cửa hàng vẫn còn và vẫn chạy; bạn chỉ không tạo thêm được cửa hàng mới cho tới khi cài lại Pro.

**Câu 6: Cài Pro có phải cấu hình lại menu không?**

→ Không. Các mục *(Pro)* vốn đã trỏ vào một đường dẫn trung gian; có Pro thì chính đường dẫn đó tự chuyển tới màn thật.

**Câu 7: Xóa một cửa hàng có mất sản phẩm không?**

→ Sản phẩm, danh mục, banner và trang nội dung **không bị xóa** — chỉ mất liên kết tới cửa hàng đó. Nhưng cửa hàng còn đơn hàng, khách hàng hoặc nhà cung cấp thì **không xóa được**; chi tiết ở [Phần 2 — Vận hành](./multi-store-operations_vi.md).

---

⬅️ [Mục lục](./multi-store_vi.md) · [Phần 2 — Vận hành](./multi-store-operations_vi.md) ➡️

---

<sub>📅 **Cập nhật lần cuối:** 2026-09-21 · ✍️ **Tác giả (Author):** GP247</sub>
