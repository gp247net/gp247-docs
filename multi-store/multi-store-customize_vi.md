> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./multi-store-customize.md)

# Multi-Store — Phần 3: Tùy chỉnh

## Giới thiệu
Tài liệu này hướng dẫn cách **làm cho mỗi cửa hàng mang bản sắc riêng** mà không làm hỏng khả năng cập nhật: đổi giao diện và bố cục trang chủ theo từng cửa hàng, đổi chữ hiển thị, tổ chức phân quyền cho nhân sự, và những nguyên tắc dành cho lập trình viên viết plugin chạy được trên hệ nhiều cửa hàng. Dành cho người quản trị website và lập trình viên. Đọc xong, bạn biết chỗ nào được sửa, sửa bằng cách nào, và chỗ nào tuyệt đối không nên đụng vào.

Nguyên tắc xuyên suốt: **đừng sửa file trong thư mục plugin**. Mọi thay đổi ở đó sẽ mất khi cập nhật phiên bản.

## Bốn mức tùy chỉnh

| Mức | Làm được gì | Cần gì |
| --- | --- | --- |
| 1. Thương hiệu từng cửa hàng | Giao diện, logo, màu sắc thương hiệu, trang bảo trì | Chỉ cần vào admin |
| 2. Bố cục trang chủ | Sắp xếp khối hiển thị riêng cho từng cửa hàng | Chỉ cần vào admin |
| 3. Chữ hiển thị và phân quyền | Nhãn màn hình, ngôn ngữ, ai được làm gì | Chỉ cần vào admin |
| 4. Mở rộng bằng code | Viết plugin hoạt động đúng trên hệ nhiều cửa hàng | Lập trình viên |

## Mức 1 — Thương hiệu riêng cho từng cửa hàng
Vào **Multi-store → Danh sách cửa hàng → Cấu hình** của cửa hàng cần sửa. Ở đây bạn đặt riêng cho cửa hàng đó:

- **Giao diện (template)** — mỗi cửa hàng chọn một template đã cài trên hệ thống.
- **Logo, biểu tượng (favicon), ảnh chia sẻ mạng xã hội**.
- **Tên, mô tả và từ khóa SEO** theo từng ngôn ngữ.
- **Thông tin liên hệ**: điện thoại, đường dây nóng, email, giờ làm việc, địa chỉ, văn phòng, kho.
- **Nội dung trang bảo trì** theo từng ngôn ngữ — trang khách thấy khi bạn tạm ngừng cửa hàng.
- **Ngôn ngữ và tiền tệ mặc định** của cửa hàng.

> ⚠️ **Đổi giao diện là thao tác nặng và phải xác nhận.** Hệ thống gỡ dữ liệu giao diện cũ của cửa hàng đó rồi dựng dữ liệu của giao diện mới. Cụ thể: **sắp xếp khối bố cục của cửa hàng sẽ mất và phải làm lại** theo giao diện mới. Sản phẩm, đơn hàng, khách hàng không bị ảnh hưởng.

Muốn có giao diện hoàn toàn riêng (không chỉ đổi logo), hãy tạo một template mới rồi gán cho cửa hàng: xem [Hướng dẫn tạo Template](../extension/create-template_vi.md).

## Mức 2 — Bố cục trang chủ riêng từng cửa hàng
**Khối bố cục (layout block)** là những khối nội dung xếp trên trang chủ: banner lớn, sản phẩm nổi bật, danh mục, tin tức…

Khác với sản phẩm hay banner (có thể thuộc nhiều cửa hàng), **khối bố cục là riêng hẳn của từng cửa hàng**. Mỗi cửa hàng tự quyết trang chủ của mình hiển thị gì, theo thứ tự nào — mà không ảnh hưởng cửa hàng khác. Vào **Khối bố cục** trong admin, chọn đúng cửa hàng rồi sắp xếp.

Đây là cách rẻ nhất để hai cửa hàng dùng chung một template mà vẫn trông khác nhau rõ rệt.

## Mức 3 — Chữ hiển thị và phân quyền

### Đổi chữ hiển thị
Mọi nhãn trên màn hình của plugin đều là **chuỗi ngôn ngữ lưu trong cơ sở dữ liệu**, sửa ngay trong admin, **không sửa file**:

1. Vào **Địa phương hóa → Quản lý ngôn ngữ**.
2. Lọc **Nhóm** = `multi_store`, chọn ngôn ngữ, tìm theo mã hoặc theo chữ đang hiện trên màn hình.
3. Sửa và **Lưu** — có hiệu lực ngay, và **giữ nguyên khi cập nhật plugin**.

Muốn quay về mặc định: xóa dòng đó rồi **mở lại màn Danh sách cửa hàng** — plugin tự gieo lại chuỗi gốc. Chi tiết cách hệ thống đa ngôn ngữ hoạt động: [Hệ thống đa ngôn ngữ](../system/language-system_vi.md).

### Phân quyền cho nhân sự
Các màn Multi-store nằm trong hệ phân quyền chung của S-Cart: vào **Quyền hạn người dùng → Nhóm quyền / Quyền hạn**, cấp quyền theo **đường dẫn màn hình**. Nhân viên không có quyền sẽ không thấy mục menu tương ứng.

Với bản Pro, bạn có thêm **hai vai trò mẫu** dựng sẵn để giao cửa hàng cho người khác:

| Vai trò mẫu | Phạm vi |
| --- | --- |
| **Store Admin** | Toàn bộ nghiệp vụ của một cửa hàng, gồm cấu hình và báo cáo của cửa hàng đó |
| **Store Member** | Vận hành hằng ngày: sản phẩm, đơn hàng, khách hàng, nội dung |

Bạn có thể tạo vai trò riêng thay cho hai mẫu này. Nhưng nhớ: **ranh giới cửa hàng không nằm ở phân quyền**. Dù cấp vai trò hay quyền gì, quản trị viên một cửa hàng vẫn luôn bị hệ thống chặn khỏi thao tác cấp hệ thống và dữ liệu của cửa hàng khác — chi tiết ở [Phần 2 — Vận hành](./multi-store-operations_vi.md).

Nền tảng RBAC: [Phân quyền (Quyền · Vai trò · Người dùng)](../system/permission-and-role_vi.md).

## Mức 4 — Dành cho lập trình viên

**Viết plugin chạy đúng trên hệ nhiều cửa hàng.** Nếu plugin của bạn có cấu hình mà mỗi cửa hàng cần đặt khác nhau (phí vận chuyển, khóa cổng thanh toán, ngưỡng khuyến mãi…), hãy khai báo nó **hoạt động theo phạm vi cửa hàng** trong manifest `gp247.json`:

```json
"storeScope": "store"
```

Khi đó màn quản lý plugin tự cho người quản trị **bật/tắt và cấu hình riêng cho từng cửa hàng**, và plugin của bạn đọc đúng giá trị của cửa hàng đang phục vụ — bạn không phải tự viết cơ chế này. Các plugin thanh toán, vận chuyển, khuyến mãi, tin tức và đánh giá sản phẩm của GP247 đều khai báo như vậy.

**Những điều cần nhớ khi viết code trên hệ nhiều cửa hàng:**

- **Luôn hỏi "cửa hàng nào?" trước khi đọc hay ghi dữ liệu.** Truy vấn không lọc theo cửa hàng sẽ trả về dữ liệu của mọi cửa hàng — đúng khi bạn làm báo cáo tổng, sai ở gần như mọi chỗ khác.
- **Đừng giả định chỉ có một cửa hàng.** Mã cứng theo tên miền, hay lấy cấu hình toàn hệ thống cho một việc vốn riêng theo cửa hàng, là hai lỗi thường gặp nhất.
- **Đừng giả định cửa hàng gốc luôn là cửa hàng đang phục vụ** — người dùng có thể đang ở tên miền của cửa hàng khác.
- **Không sửa file trong thư mục plugin đã cài** (`app/GP247/Plugins/...`) — mọi thay đổi sẽ mất khi cập nhật. Dùng chuỗi ngôn ngữ, view của template, và các điểm cắm chính thức.
- **Sau khi thay file bằng tay**, chạy `php artisan gp247:cache-rebuild` rồi mở lại màn quản trị của plugin một lần.

Xem thêm: [Hướng dẫn tạo Plugin (chuẩn v2)](../extension/create-plugin_vi.md) và [Hướng dẫn tạo Template](../extension/create-template_vi.md).

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi đổi giao diện của một cửa hàng**
- **Phải xác nhận trước khi chạy** — đây là thao tác phá hủy dữ liệu giao diện cũ của cửa hàng đó.
- **Sắp xếp khối bố cục của cửa hàng sẽ mất** và phải làm lại theo giao diện mới.
- **Chỉ ảnh hưởng cửa hàng đó** — các cửa hàng khác giữ nguyên giao diện của mình.

**Khi sửa chuỗi ngôn ngữ**
- **Sửa trong admin thì giữ được khi cập nhật; sửa file trong thư mục plugin thì không**.
- **Xóa một dòng chuỗi = quay về mặc định**, nhưng phải mở lại màn Danh sách cửa hàng để plugin gieo lại — nếu không, màn hình sẽ hiện mã khóa thô thay vì chữ.

**Khi phân quyền**
- **Quyền cấp theo đường dẫn màn hình, không theo tên menu**.
- **Không thể dùng phân quyền để mở rộng phạm vi của quản trị viên cửa hàng** — ranh giới cửa hàng do hệ thống giữ, cấp thêm quyền cũng không vượt qua được.

**Khi viết plugin**
- **Plugin không khai `storeScope` thì cấu hình của nó là chung cho toàn hệ thống** — mọi cửa hàng dùng cùng một giá trị. Hãy khai báo nếu cấu hình cần tách theo cửa hàng.
- **Gỡ plugin Multi-Store không xóa dữ liệu cửa hàng** đã tạo, nhưng website sẽ không còn phân giải theo tên miền nữa — cân nhắc kỹ trước khi gỡ trên site thật.

## Hỏi & Đáp (Q&A)
**Câu 1: Hai cửa hàng của tôi dùng chung template, làm sao cho chúng trông khác nhau?**

→ Đổi logo, màu và **sắp xếp khối bố cục trang chủ riêng cho từng cửa hàng** — khối bố cục vốn là dữ liệu riêng của mỗi cửa hàng. Cần khác hẳn thì tạo template riêng rồi gán cho cửa hàng.

**Câu 2: Đổi giao diện của cửa hàng có mất sản phẩm hay đơn hàng không?**

→ Không. Chỉ dữ liệu của chính giao diện đó bị dựng lại, đáng kể nhất là sắp xếp khối bố cục của cửa hàng.

**Câu 3: Mỗi cửa hàng nói một ngôn ngữ khác nhau được không?**

→ Được. Mỗi cửa hàng có ngôn ngữ mặc định riêng, và nội dung (tên, mô tả, trang bảo trì) khai theo từng ngôn ngữ.

**Câu 4: Nhân viên của tôi chỉ nên quản lý một cửa hàng, làm thế nào?**

→ Với bản Pro: giao cửa hàng cho tài khoản đó ở màn **Quản trị viên cửa hàng** và chọn vai trò mẫu Store Admin hoặc Store Member. Họ đăng nhập trên tên miền cửa hàng đó và chỉ thấy phạm vi của mình.

**Câu 5: Plugin tôi tự viết có chạy được trên hệ nhiều cửa hàng không?**

→ Có, nhưng hãy khai `"storeScope": "store"` trong `gp247.json` nếu cấu hình của nó cần khác nhau theo cửa hàng, và luôn lọc dữ liệu theo cửa hàng đang phục vụ.

**Câu 6: Màn hình hiện mã khóa thô (kiểu `multi_store.something`) thay vì chữ?**

→ Chuỗi ngôn ngữ chưa được gieo. Mở lại màn **Danh sách cửa hàng** một lần, hoặc tắt rồi bật lại plugin.

---

⬅️ [Phần 2 — Vận hành](./multi-store-operations_vi.md) · [Mục lục](./multi-store_vi.md)

---

<sub>📅 **Cập nhật lần cuối:** 2026-09-21 · ✍️ **Tác giả (Author):** GP247</sub>
