> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./multi-vendor-customize.md)

# Multi-Vendor — Phần 3: Tùy chỉnh

## Giới thiệu
Tài liệu này hướng dẫn cách **làm cho sàn Multi-Vendor giống thương hiệu của bạn** mà không làm hỏng khả năng cập nhật plugin: đổi chữ hiển thị và nội dung email, đổi giao diện trang gian hàng, phân quyền cho nhân sự sàn, và những nguyên tắc dành cho lập trình viên. Dành cho người quản trị website và lập trình viên. Đọc xong, bạn biết chỗ nào được sửa, sửa bằng cách nào, và chỗ nào tuyệt đối không nên đụng vào.

Nguyên tắc xuyên suốt: **đừng sửa file trong thư mục plugin**. Mọi thay đổi ở đó sẽ mất khi cập nhật phiên bản. Ba đường chính thức dưới đây đều **giữ nguyên khi cập nhật**.

## Ba mức tùy chỉnh

| Mức | Làm được gì | Cần gì |
| --- | --- | --- |
| 1. Đổi chữ và email | Toàn bộ nhãn màn hình, thông báo, tiêu đề và nội dung email | Chỉ cần vào admin |
| 2. Đổi giao diện | Trang gian hàng, danh bạ, lưới sản phẩm, khối trang chủ | Biết sửa file blade cơ bản |
| 3. Mở rộng bằng code | Chèn nội dung của plugin khác, thêm quy tắc giá | Lập trình viên |

## Mức 1 — Đổi chữ hiển thị và nội dung email
Mọi chữ của plugin — nhãn trên màn hình, thông báo, **tiêu đề và nội dung email** — đều là **chuỗi ngôn ngữ lưu trong cơ sở dữ liệu**, sửa ngay trong admin, **không sửa file**:

1. Vào **Địa phương hóa → Quản lý ngôn ngữ** (`/gp247_admin/language_manager`).
2. Lọc **Nhóm** = `multi_vendor`, chọn ngôn ngữ, tìm theo mã hoặc theo chữ đang hiện trên màn hình.
3. Sửa và **Lưu** — có hiệu lực ngay, và **giữ nguyên khi cập nhật plugin**.

Quy ước mã để bạn tìm nhanh:

| Tiền tố mã | Nội dung |
| --- | --- |
| `multi_vendor.mail.*` | Tiêu đề và nội dung email (ví dụ `multi_vendor.mail.vendor_approved.subject`) |
| `multi_vendor.pro.*` | Chữ của các trang giới thiệu tính năng bản Pro |
| còn lại | Nhãn màn hình, nút bấm, thông báo |

Muốn quay về mặc định: xóa dòng đó rồi **mở lại màn Cấu hình nhanh** — plugin tự gieo lại chuỗi gốc.

> ⚠️ Đừng sửa các file ngôn ngữ nằm trong thư mục plugin — chúng chỉ dùng cho thông báo lúc cài đặt và **sẽ bị ghi đè khi cập nhật**.

## Mức 2 — Đổi giao diện trang gian hàng

### Khối "Gian hàng mới" trên trang chủ
Khối này được chép vào thư mục `blocks/` của template đang dùng khi cài plugin — sửa trực tiếp ở đó để đổi cách hiển thị, và thêm/bớt khối qua **Khối bố cục** trong admin.

### Thay hẳn một trang bằng bản của bạn
Các trang còn lại có thể **thay bằng bản của riêng bạn** mà không đụng vào plugin: tạo file cùng tên trong template đang dùng, theo đường dẫn

```
app/GP247/Templates/<TênTemplate>/Plugins/MultiVendor/<tên-view>.blade.php
```

Hệ thống ưu tiên file của template; không có thì mới dùng file của plugin. Nhờ vậy bản sửa của bạn **không bị mất khi cập nhật plugin**.

Các trang thay được:

| Tên view | Trang tương ứng |
| --- | --- |
| `vendor_index` | Danh bạ gian hàng `/shop` |
| `vendor_home` | Trang gian hàng `/shop/{mã}` |
| `vendor_info` | Tab **Thông tin** của gian hàng |
| `vendor_product_list` | Lưới sản phẩm trong trang gian hàng |
| `hooks.order_dispute_box` | Hộp khiếu nại dưới trang đơn của khách (Pro) |

**Cách làm an toàn**: chép nội dung file gốc của plugin sang đường dẫn template ở trên rồi sửa dần — như vậy bạn chắc chắn giữ đủ các biến mà trang cần.

## Phân quyền cho nhân sự sàn
Các màn **Chợ bán hàng** nằm trong hệ phân quyền chung của S-Cart: vào **Quyền hạn người dùng → Nhóm quyền / Quyền hạn**, cấp quyền theo **đường dẫn màn hình** (ví dụ màn gian hàng, màn thanh toán). Nhân viên không có quyền sẽ không thấy mục menu tương ứng.

Nhờ vậy bạn giao được việc theo vai: người kiểm duyệt chỉ thấy hàng chờ duyệt, kế toán chỉ thấy màn thanh toán và báo cáo.

> Tài khoản người bán **không** dùng hệ quyền này — họ đăng nhập khu riêng `/vendor_admin` và luôn bị giới hạn trong gian hàng của mình. Chi tiết về RBAC: [Phân quyền (Permission · Role · User)](../system/permission-and-role_vi.md).

## Mức 3 — Dành cho lập trình viên

- **Không sửa file trong thư mục plugin.** Mọi thay đổi ở `app/GP247/Plugins/MultiVendor/` sẽ mất khi cập nhật. Dùng ba đường chính thức: chuỗi ngôn ngữ (Mức 1), view của template (Mức 2), và điểm cắm dưới đây.
- **Chèn nội dung vào trang đơn của khách**: plugin gắn hộp khiếu nại vào cơ chế hook chung của storefront (`gp247-config.front.plugin_hooks`, vị trí `shop_order_detail_bottom`). Plugin khác của bạn dùng đúng cơ chế này để chèn nội dung của mình, không cần sửa template.
- **Thêm quy tắc giá**: giá theo nhóm khách của gian hàng (Pro) đăng ký vào seam giá của `gp247/shop` (`gp247-config.shop.price_resolvers`), nên trang sản phẩm, giỏ hàng, thanh toán, đặt hàng nhanh và báo giá luôn thấy cùng một giá. Plugin giá khác đăng ký thêm theo cùng khuôn đó.
- **Đường dẫn gian hàng trong code**: dùng helper của plugin thay vì tự nối chuỗi `/shop/...`, để khi đổi biến `.env` thì mọi link đổi theo.
- **Cập nhật an toàn**: plugin tự hội tụ (menu, chuỗi ngôn ngữ, bảng dữ liệu) ở mọi đường vào — cài mới, cài lại, cập nhật. Sau khi thay file bằng tay, chạy `php artisan gp247:cache-rebuild` rồi mở một lần màn **Cấu hình nhanh**.

Xem thêm: [Hướng dẫn tạo Plugin (chuẩn v2)](../extension/create-plugin_vi.md) và [Hướng dẫn tạo Template](../extension/create-template_vi.md).

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi sửa chuỗi ngôn ngữ**
- **Sửa trong admin thì giữ được khi cập nhật; sửa file trong thư mục plugin thì không** — file ngôn ngữ của plugin bị ghi đè mỗi lần cập nhật.
- **Xóa một dòng chuỗi = quay về mặc định**, nhưng phải mở lại màn Cấu hình nhanh để plugin gieo lại — nếu không, màn hình sẽ hiện mã khóa thô thay vì chữ.

**Khi thay view của template**
- **Tên file phải trùng đúng tên view trong bảng trên** — sai tên thì hệ thống vẫn dùng file của plugin và bạn tưởng mình sửa không ăn.
- **Phải giữ các biến mà trang cần** — chép từ file gốc rồi sửa dần, đừng viết lại từ đầu.
- **Đổi template đang dùng thì bản sửa không theo sang** — mỗi template có thư mục override riêng.

**Khi đổi đường dẫn**
- Xem bốn lưu ý ở [Phần 1 — Cài đặt](./multi-vendor-setup_vi.md) trước khi đổi: không trùng thư mục trong `public/`, không trùng đường dẫn admin, đường dẫn cũ sẽ 404.

**Khi phân quyền**
- **Quyền cấp theo đường dẫn màn hình, không theo tên menu** — đổi tên hiển thị không ảnh hưởng gì tới quyền.
- **Tài khoản người bán không nằm trong hệ quyền admin** — không cấp thêm quyền admin cho họ bằng cách này được.

## Hỏi & Đáp (Q&A)
**Câu 1: Tôi sửa file blade trong thư mục plugin thì sao?**

→ Sửa được và hiệu lực ngay, nhưng **sẽ mất khi cập nhật plugin**. Hãy chép sang thư mục template theo Mức 2 để giữ lâu dài.

**Câu 2: Muốn đổi nội dung email gửi cho người bán thì sửa ở đâu?**

→ **Địa phương hóa → Quản lý ngôn ngữ**, lọc nhóm `multi_vendor`, tìm các mã bắt đầu bằng `multi_vendor.mail.`. Không cần sửa file.

**Câu 3: Màn hình hiện mã khóa thô (kiểu `multi_vendor.something`) thay vì chữ?**

→ Chuỗi ngôn ngữ chưa được gieo. Mở lại màn **Cấu hình nhanh** một lần, hoặc tắt rồi bật lại plugin.

**Câu 4: Tôi đổi giao diện trang gian hàng rồi cập nhật plugin, bản sửa có mất không?**

→ Không, nếu bạn đặt file trong thư mục template theo Mức 2. Có, nếu bạn sửa thẳng file trong thư mục plugin.

**Câu 5: Nhân viên kế toán của tôi chỉ nên thấy màn thanh toán, làm được không?**

→ Được. Tạo một nhóm quyền chỉ chứa các màn thanh toán/báo cáo và gán cho tài khoản đó; các mục menu khác sẽ không hiện.

**Câu 6: Đổi template storefront thì trang gian hàng có chạy tiếp không?**

→ Có. Không có file override trong template mới thì hệ thống dùng lại giao diện mặc định của plugin. Muốn giữ bản sửa, hãy chép thư mục override sang template mới.

---

⬅️ [Phần 2 — Vận hành](./multi-vendor-operations_vi.md) · [Mục lục](./multi-vendor_vi.md)

---

<sub>📅 **Cập nhật lần cuối:** 2026-09-21 · ✍️ **Tác giả (Author):** GP247</sub>
