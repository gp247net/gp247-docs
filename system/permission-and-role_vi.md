> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./permission-and-role.md)

# Phân quyền quản trị trong GP247 (Quyền · Vai trò · Người dùng)

## Giới thiệu
Tài liệu này giải thích hệ thống **phân quyền (RBAC)** của trang quản trị GP247 và
hướng dẫn quản trị viên **tự cấu hình ai được làm gì**. Đọc xong bạn sẽ hiểu 3 khối
xây dựng (Quyền – Vai trò – Người dùng), cách hệ thống quyết định cho vào màn nào,
và cách **tự lập chiến lược phân quyền** cho nhân viên của mình (ví dụ: nhân viên chỉ
xử lý đơn hàng, quản lý chỉ được xem…). Không cần biết lập trình.

---

## 1. Ba khối xây dựng

Phân quyền GP247 gồm 3 thứ ghép lại:

| Khối | Là gì | Ví dụ đời thường |
| --- | --- | --- |
| **Quyền (Permission)** | Một "chùm chìa khoá" — mở được một hoặc vài **màn** trong admin | "Quản lý đơn hàng" mở các trang liên quan đến đơn |
| **Vai trò (Role)** | Một **nhóm quyền** gộp sẵn để gán nhanh | "Nhân viên bán hàng" = đơn hàng + khách hàng |
| **Người dùng (User)** | Tài khoản đăng nhập admin | Chị Lan, anh Nam… |

Cách ghép:
- Một **Người dùng** có thể mang **nhiều Vai trò**, **và/hoặc** được gán thẳng **một số Quyền** riêng.
- Quyền thực tế của một người = **(toàn bộ quyền từ các vai trò) + (các quyền gán trực tiếp)** cộng lại.

> Nhờ vậy bạn có thể tạo vài Vai trò dùng chung cho cả nhóm, rồi vẫn "thêm lẻ" một
> quyền đặc biệt cho đúng một người mà không phải tạo vai trò mới.

---

## 2. Bên trong một "Quyền" có gì?

Mỗi Quyền gồm:
- **Tên (Name):** để bạn dễ đọc, ví dụ "Quản lý đơn hàng".
- **Slug:** một mã định danh ngắn (ví dụ `order.full`). **Slug chỉ là nhãn** — không
  quyết định quyền; đặt sao cho bạn dễ nhớ là được.
- **Danh sách địa chỉ + phương thức (http_uri):** đây mới là **phần quyết định** —
  liệt kê những **trang (URL)** mà quyền này mở, kèm **kiểu thao tác** cho phép.

Kiểu thao tác gồm:
- **GET** = chỉ **xem** trang.
- **ANY** = **làm mọi thứ** trên trang đó (xem + thêm + sửa + xoá).

Ví dụ một dòng địa chỉ trong Quyền: `ANY::gp247_admin/order/*`
- `gp247_admin/order/*` = tất cả trang đơn hàng (dấu `*` nghĩa là "và mọi trang con").
- `ANY` = cho phép cả xem lẫn thao tác.

👉 **Mẹo quan trọng:** khi tạo Quyền, ô chọn địa chỉ đã **gom sẵn theo nhóm chức
năng** (đơn hàng, sản phẩm, khách hàng…). Mỗi nhóm có sẵn một dòng "toàn bộ" dạng
`ANY::<nhóm>/*`. **Chọn dòng "toàn bộ" của một nhóm = cấp trọn quyền cho chức năng
đó** — đây là cách nhanh và đúng nhất cho hầu hết nhu cầu.

---

## 3. Hai vai trò đặc biệt (nhớ kỹ)

GP247 có sẵn 2 vai trò "quyền lực" khác thường:

| Vai trò | Ý nghĩa |
| --- | --- |
| **Administrator** | **Toàn quyền** — vào và làm mọi thứ, bỏ qua mọi kiểm tra. Dành cho chủ/quản trị cao nhất. |
| **View.all** (Chỉ xem tất cả) | **Xem được mọi trang**, nhưng **không được thay đổi** (mọi thao tác thêm/sửa/xoá đều bị chặn). Hợp cho kế toán, kiểm tra, sếp muốn theo dõi. |

> ⚠️ Khi gán **Administrator** hoặc **View.all** cho một người, hệ thống sẽ **ghi đè**
> và **bỏ các vai trò/quyền khác** của người đó — vì hai vai trò này đã bao trùm rồi.
> Màn Người dùng cũng nhắc dòng này ngay dưới ô chọn vai trò.

---

## 4. Hệ thống quyết định cho vào ở đâu như thế nào?

Chỉ cần nhớ **một nguyên tắc thống nhất**:

> **Được thấy menu = được vào xem. Được thao tác = tuỳ theo kiểu (GET/ANY).**

Cụ thể:
- **Menu bên trái** chỉ hiện những mục mà người đó **có quyền xem (GET)**. Không có
  quyền thì mục đó **ẩn luôn**.
- **Vào một trang:** cần quyền **xem (GET hoặc ANY)** đúng địa chỉ trang đó.
- **Thao tác (thêm/sửa/xoá):** cần quyền **ANY** (hoặc POST) đúng địa chỉ trang đó.
- **Administrator:** vào & làm tất cả. **View.all:** vào xem tất cả, không sửa được.

Nói cách khác: **menu, việc vào màn và việc thao tác đều dựa trên cùng một danh sách
địa chỉ (http_uri)** trong các Quyền của người đó. Slug không ảnh hưởng — nó chỉ là nhãn.

> 💡 Vì thế, nếu chọn dòng "toàn bộ" `ANY::<nhóm>/*` cho một chức năng, người dùng
> vừa **thấy menu**, vừa **vào xem**, vừa **thao tác** được chức năng đó — trọn gói.

---

## 5. Thực hành: tạo Quyền

> Các trang quản trị nằm trong khu quản lý người dùng/phân quyền. Nếu không thấy trên
> menu, bạn có thể gõ thẳng địa chỉ vào trình duyệt (thay `ten-mien` bằng tên miền của
> bạn): `https://ten-mien/gp247_admin/permission`.

1. Vào trang **Quyền hạn** (`.../gp247_admin/permission`).
2. Ở khung tạo mới, nhập **Tên** dễ hiểu, ví dụ: `Quản lý đơn hàng`.
3. Nhập **Slug** ngắn gọn, ví dụ: `order.full` (chỉ dùng chữ thường, số, dấu chấm,
   gạch ngang, gạch dưới).
4. Ở ô **chọn địa chỉ**, tìm nhóm **order** (đơn hàng) và **tích dòng "toàn bộ"**
   `ANY::gp247_admin/order/*`. Muốn cấp thêm chức năng khác (ví dụ khách hàng), tích
   thêm dòng "toàn bộ" của nhóm đó (`ANY::gp247_admin/customer/*`).
5. Bấm **Lưu**. Nếu thành công, Quyền mới xuất hiện trong danh sách bên phải.

> Muốn tạo quyền **chỉ xem** một chức năng? Ở bước 4, thay vì dòng `ANY::…`, hãy chọn
> các dòng **`GET::…`** của nhóm đó (chỉ cho xem, không cho sửa).

---

## 6. Thực hành: tạo Vai trò và gán Quyền

1. Vào trang **Vai trò** (`.../gp247_admin/role`).
2. Nhập **Tên** vai trò, ví dụ: `Nhân viên bán hàng`.
3. Nhập **Slug**, ví dụ: `sales_staff`.
4. Ở ô **Quyền**, tìm và chọn các Quyền bạn muốn gộp vào vai trò này (ví dụ
   "Quản lý đơn hàng", "Quản lý khách hàng").
5. Bấm **Lưu**.

> Từ giờ, chỉ cần gán vai trò `Nhân viên bán hàng` cho một người là họ có ngay tất cả
> quyền bên trong — không phải chọn lại từng quyền.

---

## 7. Thực hành: tạo Người dùng và cấp quyền

1. Vào trang **Người dùng** (`.../gp247_admin/user`).
2. Điền **Tên đầy đủ**, **Tên đăng nhập**, **Email**, **Mật khẩu**. (Có thể chọn ảnh
   đại diện; giữ ô **Kích hoạt** được tích để tài khoản dùng được.)
3. Ở ô **Vai trò**, chọn một hoặc nhiều vai trò đã tạo (ví dụ `Nhân viên bán hàng`).
4. (Tuỳ chọn) Ở ô **Quyền hạn**, gán thêm **quyền lẻ** riêng cho đúng người này —
   dùng khi họ cần thêm một chức năng mà vai trò chung chưa có.
5. Bấm **Gửi / Lưu**. Người dùng mới xuất hiện trong danh sách bên phải.

> Người vừa tạo đăng nhập vào sẽ **chỉ thấy** những menu ứng với quyền của họ.

---

## 8. Công thức chiến lược (chọn nhanh)

Dưới đây là vài "công thức" thường dùng — bạn ghép lại theo nhu cầu:

- **Chủ / quản trị cao nhất** → gán vai trò **Administrator**. Xong, khỏi cấu hình gì thêm.
- **Sếp / kế toán chỉ theo dõi** → gán vai trò **View.all**. Họ xem được mọi thứ, không sửa được gì.
- **Nhân viên chỉ xử lý đơn hàng** → tạo Quyền `ANY::gp247_admin/order/*` (và có thể
  thêm `ANY::gp247_admin/customer/*`), gộp vào vai trò "Nhân viên đơn hàng", gán cho họ.
- **Nhân viên nội dung/sản phẩm** → tạo Quyền gồm `ANY::gp247_admin/product/*`,
  `ANY::gp247_admin/category/*`, `ANY::gp247_admin/brand/*`; gộp thành vai trò "Biên tập sản phẩm".
- **Người chỉ được xem báo cáo** → tạo Quyền chỉ gồm các dòng `GET::` của nhóm `report`.
- **Một người cần thêm 1 chức năng lẻ** → giữ nguyên vai trò chung, vào màn Người dùng
  gán thêm **quyền trực tiếp** cho riêng người đó (mục 7, bước 4).

> Nguyên tắc chọn: **ưu tiên tạo Vai trò dùng chung** cho nhóm; chỉ dùng **quyền trực
> tiếp** cho các ngoại lệ của từng người. Như vậy về sau dễ quản lý.

---

## 9. Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi gán vai trò cho người dùng**
- **Chọn Administrator hoặc View.all sẽ xoá các vai trò/quyền khác** của người đó —
  vì hai vai trò này đã bao trùm toàn bộ; giữ thêm quyền khác là thừa.
- **View.all không bao giờ thao tác được** — mọi nút thêm/sửa/xoá đều bị chặn ở máy
  chủ, kể cả khi bạn cố gán thêm quyền ghi cho họ.

**Khi tạo/sửa Người dùng**
- **Tên đăng nhập:** bắt buộc, dài 3–100 ký tự, **chỉ gồm** chữ, số, `@`, dấu chấm,
  gạch dưới; và **không được trùng** người khác.
- **Email:** bắt buộc, đúng định dạng email, **không được trùng**.
- **Mật khẩu:** bắt buộc khi tạo mới; khi sửa, để trống nghĩa là **giữ mật khẩu cũ**.
  Độ mạnh mật khẩu theo **Chính sách mật khẩu** của shop (độ dài tối thiểu/tối đa, có
  thể yêu cầu chữ hoa–thường/số/ký tự đặc biệt).
- **Không sửa/xoá được tài khoản quản trị được bảo vệ, và không tự xoá chính mình** ở
  màn Người dùng — để tránh tự khoá mình ra ngoài. Muốn đổi thông tin của chính bạn,
  dùng trang **Cấu hình cá nhân**.

**Khi tạo Quyền**
- **Slug:** bắt buộc, dài 3–50 ký tự, **chỉ gồm** chữ, số, dấu chấm, gạch ngang, gạch
  dưới; và **không được trùng** quyền khác.
- **Địa chỉ (http_uri):** nên chọn từ danh sách gợi ý (đã gom nhóm). Dòng `ANY::…/*`
  = trọn quyền một nhóm; dòng `GET::…` = chỉ xem.

**Khi người dùng đăng nhập mà không có quyền**
- Người **không có vai trò và không có quyền nào** vẫn đăng nhập được, nhưng **menu
  gần như trống** và vào trang chức năng sẽ báo **"Truy cập bị từ chối"** — đúng theo
  thiết kế (không cấp thì không vào). Hãy cấp ít nhất một vai trò/quyền phù hợp.

---

## 10. Hỏi & Đáp (Q&A)

**Câu 1: Tôi gán quyền cho nhân viên nhưng họ bấm vào vẫn báo "Truy cập bị từ chối"?**

→ Kiểm tra Quyền đó có **địa chỉ (http_uri) đúng nhóm chức năng** chưa (ví dụ đơn
hàng phải có `ANY::gp247_admin/order/*`). Nếu chỉ đặt tên/slug mà **chưa chọn địa
chỉ**, quyền đó rỗng và không mở được gì.

**Câu 2: Nhân viên vào xem được nhưng bấm Lưu/Xoá thì bị chặn?**

→ Quyền đang là **chỉ xem (GET)**. Muốn cho thao tác, sửa quyền sang dòng **`ANY::…/*`**
của nhóm đó (ANY = cho cả xem lẫn thêm/sửa/xoá).

**Câu 3: Slug có quan trọng không? Đặt sai có sao không?**

→ Slug **chỉ là nhãn** để bạn dễ nhớ, **không quyết định quyền**. Quyền do **danh
sách địa chỉ (http_uri)** quyết định. Cứ đặt slug sao cho dễ hiểu là được.

**Câu 4: Khác nhau giữa "gán qua Vai trò" và "gán quyền trực tiếp cho người dùng"?**

→ Giống nhau về hiệu lực. Vai trò dùng để **gộp quyền và tái sử dụng cho nhiều người**;
quyền trực tiếp dùng cho **ngoại lệ của riêng một người**. Quyền cuối cùng = cộng cả hai.

**Câu 5: View.all khác gì so với gán quyền GET cho mọi nhóm?**

→ View.all là lối tắt: **xem được mọi trang** và **chặn mọi thao tác** ở máy chủ,
không cần tạo quyền GET cho từng nhóm. Nhanh và an toàn hơn cho vai trò "chỉ theo dõi".

**Câu 6: Tôi lỡ gán Administrator cho một người, giờ họ có toàn quyền?**

→ Đúng. Administrator bỏ qua mọi kiểm tra. Gỡ vai trò Administrator khỏi người đó rồi
gán lại vai trò/quyền phù hợp là được.

**Câu 7: Vì sao có tài khoản không xoá/sửa được ở màn Người dùng?**

→ Đó là **tài khoản quản trị được bảo vệ** (và chính tài khoản bạn đang đăng nhập).
Hệ thống khoá để tránh lỡ tay tự khoá mình ra ngoài. Đổi thông tin của mình qua trang
**Cấu hình cá nhân**.

**Câu 8: Plugin tôi cài thêm có tự vào được hệ phân quyền này không?**

→ Có. Plugin khai báo quyền của nó theo **cùng cơ chế địa chỉ (http_uri)**, nên bạn
gán quyền cho màn của plugin y như các chức năng khác của GP247.

**Câu 9: Tôi muốn một người xem được tất cả nhưng chỉ sửa được đơn hàng?**

→ Không nên dùng View.all (vì View.all chặn mọi thao tác). Thay vào đó tạo/gán các
quyền **GET** cho những nhóm cần xem, và một quyền **`ANY::gp247_admin/order/*`** cho
riêng đơn hàng.

**Câu 10: Sau khi đổi quyền, có cần người dùng đăng xuất/đăng nhập lại không?**

→ Nên tải lại trang; thông thường thay đổi có hiệu lực ngay ở lần truy cập kế tiếp.
Nếu menu chưa cập nhật, bảo họ tải lại trang (hoặc đăng nhập lại) cho chắc.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-16 · ✍️ **Tác giả (Author):** GP247</sub>
