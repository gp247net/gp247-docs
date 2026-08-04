> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./api-introduction.md)

# Giới thiệu API trong hệ thống GP247

## Giới thiệu

Tài liệu này giới thiệu **hệ thống API (giao diện lập trình ứng dụng) của GP247** — cách các
ứng dụng bên ngoài (app di động, website khác, phần mềm quản lý, tích hợp bên thứ ba…) có thể
"nói chuyện" với site GP247 của bạn để lấy dữ liệu và thực hiện thao tác. Tài liệu dành cho
**lập trình viên tích hợp** và **người quản trị site** muốn hiểu tổng quan API gồm những gì, chia
làm mấy nhóm, xác thực (đăng nhập) ra sao và gọi thử như thế nào. Đọc xong, bạn biết **API nào
dùng để làm gì, gọi ở địa chỉ nào, cần chìa khóa gì**, và có ví dụ copy chạy được ngay.

> ℹ️ **API là gì (giải thích đời thường):** hãy hình dung API như một "quầy giao dịch" của site.
> Ứng dụng bên ngoài không vào thẳng kho dữ liệu, mà gửi yêu cầu tới quầy (một địa chỉ URL), quầy
> kiểm tra giấy tờ (token/khóa) rồi trả về kết quả dưới dạng **JSON** (một định dạng dữ liệu dạng
> văn bản mà máy tính đọc được). Toàn bộ trao đổi diễn ra qua giao thức web thông thường (HTTP).

---

## 1. Hai nhóm API chính

Hệ thống GP247 chia API thành **hai nhóm**, phục vụ hai đối tượng khác nhau:

| Nhóm | Tiền tố địa chỉ (prefix) | Dành cho | Ai đăng nhập |
| --- | --- | --- | --- |
| **API Core (Admin)** | `api/core` | Quản trị hệ thống từ xa (app quản lý, dashboard riêng) | Tài khoản **quản trị viên** (admin) |
| **API Front (Storefront)** | `api/front` | Hiển thị dữ liệu cửa hàng, tài khoản khách | Khách/thành viên, hoặc **không cần đăng nhập** với dữ liệu công khai |

- **`api/core`** do gói `gp247/core` cung cấp — thao tác ở "phía sau" (backend) như đăng nhập admin,
  lấy thông tin quản trị viên.
- **`api/front`** do gói `gp247/front` và `gp247/shop` cung cấp — dữ liệu "mặt tiền" cửa hàng như
  danh sách sản phẩm, danh mục, thương hiệu, trang nội dung, banner, và tài khoản/đơn hàng của khách.

> **Địa chỉ đầy đủ** = tên miền site + tiền tố + đường dẫn. Ví dụ nếu site của bạn là
> `https://shop.example.com` thì endpoint lấy danh sách sản phẩm là:
> `https://shop.example.com/api/front/product/list`.

---

## 2. Xác thực (đăng nhập vào API)

GP247 dùng **Laravel Sanctum** để cấp **token** (một chuỗi ký tự dài đóng vai trò "vé vào cửa").
Cơ chế hoạt động chung cho cả hai nhóm:

1. Ứng dụng gửi **username/email + password** tới endpoint `login`.
2. Nếu đúng, hệ thống trả về một **`access_token`** (token dạng Bearer) kèm hạn dùng (`expires_at`).
3. Từ đó về sau, mọi yêu cầu cần đăng nhập đều gắn kèm token này trong phần **header**:

   ```
   Authorization: Bearer <access_token>
   ```

4. Khi muốn thoát, gọi endpoint `logout` để **thu hồi token** (token cũ hết hiệu lực ngay).

### Phạm vi quyền (scope / ability)

Mỗi token được cấp kèm **phạm vi quyền** để giới hạn nó được làm gì:

| Scope | Ý nghĩa |
| --- | --- |
| `admin` | Quản trị viên thường (API Core) |
| `admin-supper` | Quản trị viên cấp cao — vai trò `administrator` (API Core) |
| `user` | Thành viên đã kích hoạt (API Front) |
| `user-guest` | Thành viên chưa kích hoạt / khách (API Front) |

### Ghi nhớ đăng nhập (`remember_me`)

Khi gọi `login`, nếu gửi kèm `remember_me = true`, token sẽ có hạn dài hơn (mặc định 30 ngày,
cấu hình qua biến môi trường `GP247_API_RECOMMEMBER`). Nếu không, token hết hạn theo mặc định
(7 ngày, qua `GP247_API_TOKEN_EXPIRE_DEFAULT`).

---

## 3. Lớp bảo vệ kết nối (API Connection) — tùy chọn

Ngoài token, GP247 còn có **một lớp khóa bổ sung** đứng trước tất cả API, gọi là **API Connection**.
Đây là "chốt bảo vệ" thứ hai, giúp chỉ những ứng dụng được cấp phép mới gọi được API — kể cả các
endpoint công khai.

- Lớp này **mặc định có thể tắt/bật** qua cấu hình `api_connection_required` trong quản trị.
- **Khi bật:** mọi yêu cầu phải kèm thêm **hai header**:

  ```
  apiconnection: <mã kết nối>
  apikey: <khóa bí mật>
  ```

- Cặp `apiconnection`/`apikey` được tạo và quản lý trong **trang quản trị GP247** (mục quản lý
  API Connection), mỗi cặp có thể đặt ngày hết hạn (`expire`) và trạng thái bật/tắt (`status`).
- Nếu thiếu hoặc sai, API trả về lỗi `{"error": 1, "msg": "Connection not correct"}`.

> Thứ tự các lớp kiểm tra (middleware) là: **trả JSON → kiểm tra API Connection → giới hạn tần suất
> 1000 lần**. Đây là thứ tự cố định, không nên đổi.

---

## 4. Luồng làm việc API Core (Admin)

Sơ đồ dưới đây mô tả trình tự một ứng dụng quản trị gọi API Core: kiểm tra kết nối (nếu bật) →
đăng nhập lấy token → gọi các endpoint quản trị kèm token.

![Luồng làm việc API Core](https://static.gp247.net/file/api/gp247-api-sequence-core.png)

**Các endpoint API Core** (tiền tố `api/core`):

| Phương thức | Đường dẫn | Cần đăng nhập | Chức năng |
| --- | --- | --- | --- |
| `POST` | `api/core/login` | Không | Đăng nhập admin, nhận `access_token` |
| `GET` | `api/core/logout` | Có (Bearer) | Đăng xuất, thu hồi token hiện tại |
| `GET` | `api/core/info` | Có (Bearer) | Lấy thông tin quản trị viên đang đăng nhập |

Ví dụ đăng nhập admin (dùng `curl` trong Terminal):

```bash
curl -X POST "https://shop.example.com/api/core/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin@example.com","password":"your-password","remember_me":true}'
```

Nếu thành công, bạn nhận về JSON dạng:

```json
{
  "access_token": "1|aBcD...rất-dài...XyZ",
  "token_type": "Bearer",
  "scopes": ["admin-supper"],
  "expires_at": "2026-08-29 10:00:00"
}
```

Sau đó lấy thông tin admin bằng token vừa nhận:

```bash
curl -X GET "https://shop.example.com/api/core/info" \
  -H "Authorization: Bearer 1|aBcD...rất-dài...XyZ"
```

---

## 5. Luồng làm việc API Front (Storefront)

API Front phục vụ hai loại dữ liệu: **dữ liệu công khai** (không cần đăng nhập — danh sách sản
phẩm, danh mục…) và **dữ liệu riêng của khách** (cần đăng nhập — đơn hàng, thông tin cá nhân).

![Luồng làm việc API Front](https://static.gp247.net/file/api/gp247-api-sequence-front.png)

**Các endpoint công khai** (tiền tố `api/front`, không cần token):

| Phương thức | Đường dẫn | Chức năng |
| --- | --- | --- |
| `GET` | `api/front/product/list` | Danh sách sản phẩm |
| `GET` | `api/front/product/detail/{id}` | Chi tiết một sản phẩm |
| `GET` | `api/front/category/list` | Danh sách danh mục |
| `GET` | `api/front/category/detail/{id}` | Chi tiết một danh mục (kèm sản phẩm) |
| `GET` | `api/front/brand/list` | Danh sách thương hiệu |
| `GET` | `api/front/brand/detail/{id}` | Chi tiết một thương hiệu |
| `GET` | `api/front/banner/list` | Danh sách banner |
| `GET` | `api/front/banner/detail/{id}` | Chi tiết một banner |
| `GET` | `api/front/page/list` | Danh sách trang nội dung |
| `GET` | `api/front/page/detail/{id}` | Chi tiết một trang nội dung |

**Các endpoint tài khoản khách** (tiền tố `api/front`, cần token của khách):

| Phương thức | Đường dẫn | Cần đăng nhập | Chức năng |
| --- | --- | --- | --- |
| `POST` | `api/front/login` | Không | Đăng nhập bằng email/mật khẩu khách, nhận token |
| `GET` | `api/front/logout` | Có (Bearer) | Đăng xuất khách |
| `GET` | `api/front/info` | Có (Bearer) | Thông tin tài khoản khách |
| `GET` | `api/front/member/order/list` | Có (Bearer) | Danh sách đơn hàng của khách |
| `GET` | `api/front/member/order/detail/{id}` | Có (Bearer) | Chi tiết một đơn hàng |

Ví dụ lấy danh sách sản phẩm (công khai, không cần token):

```bash
curl -X GET "https://shop.example.com/api/front/product/list" \
  -H "Accept: application/json"
```

Ví dụ đăng nhập khách rồi xem đơn hàng:

```bash
# 1) Đăng nhập khách
curl -X POST "https://shop.example.com/api/front/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"customer@example.com","password":"your-password"}'

# 2) Dùng token nhận được để lấy danh sách đơn hàng
curl -X GET "https://shop.example.com/api/front/member/order/list" \
  -H "Authorization: Bearer 2|zzz...token-khach...zzz"
```

---

## 6. Định dạng phản hồi & mã lỗi

- **Mọi phản hồi đều là JSON** (nhờ middleware `json.response`).
- Endpoint có phân trang (như danh sách sản phẩm) trả về theo chuẩn phân trang của Laravel
  (có `data`, `current_page`, `total`…).
- Lỗi thường gặp:

| Trường hợp | Phản hồi |
| --- | --- |
| Sai tài khoản/mật khẩu | HTTP `401` — `{"error": 1, "msg": "Unauthorized"}` |
| Thiếu/sai API Connection | `{"error": 1, "msg": "apiconnection or apikey not found"}` hoặc `"Connection not correct"` |
| Không tìm thấy dữ liệu (chi tiết) | HTTP `404` với body rỗng |
| Gọi quá nhiều lần | HTTP `429` (vượt giới hạn tần suất) |

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi nên dùng nhóm API nào?**

→ Nếu bạn xây app/khách hàng cho cửa hàng (xem sản phẩm, đặt hàng, xem đơn) → dùng **API Front**
(`api/front`). Nếu bạn xây công cụ quản trị từ xa cho admin → dùng **API Core** (`api/core`).

**Câu 2: Token lấy ở đâu và tồn tại bao lâu?**

→ Token do endpoint `login` cấp. Mặc định hết hạn sau 7 ngày; nếu gửi `remember_me = true` thì
kéo dài (mặc định 30 ngày). Bạn có thể đổi qua biến môi trường `GP247_API_TOKEN_EXPIRE_DEFAULT`
và `GP247_API_RECOMMEMBER`.

**Câu 3: `apiconnection`/`apikey` khác gì với `access_token`?**

→ `access_token` xác thực **người dùng** (bạn là ai). `apiconnection`/`apikey` xác thực **ứng dụng**
(app nào được phép gọi API) và là **lớp tùy chọn** — chỉ bắt buộc khi admin bật
`api_connection_required`.

**Câu 4: Gọi endpoint công khai có cần token không?**

→ Không. Các endpoint danh sách/chi tiết sản phẩm, danh mục, thương hiệu, banner, trang nội dung
là công khai. Nhưng nếu lớp API Connection đang bật, bạn vẫn phải gửi header `apiconnection`/`apikey`.

**Câu 5: Tôi bị lỗi `Unauthorized` (401) dù gõ đúng mật khẩu?**

→ Với API Core, tài khoản admin phải có `status = 1` (đang hoạt động). Với API Front, kiểm tra email
đã đăng ký đúng chưa. Ngoài ra hãy chắc chắn gửi đúng trường: Core dùng `username`, Front dùng `email`.

**Câu 6: Vì sao tôi nhận lỗi `Connection not correct`?**

→ Lớp API Connection đang bật và cặp `apiconnection`/`apikey` của bạn sai, hết hạn (`expire`), hoặc
bị tắt (`status`). Vào trang quản trị GP247, mục quản lý API Connection để kiểm tra/tạo lại.

**Câu 7: Có giới hạn số lần gọi không?**

→ Có. Middleware giới hạn mặc định **1000 lần** trong một chu kỳ. Gọi vượt sẽ nhận HTTP `429` —
hãy giãn nhịp gọi hoặc cache lại kết quả phía ứng dụng.

**Câu 8: Địa chỉ đầy đủ của một endpoint viết thế nào?**

→ `https://<tên-miền-site>/<prefix>/<đường-dẫn>`. Ví dụ: `https://shop.example.com/api/front/product/list`.
Tiền tố là `api/core` hoặc `api/front` tùy nhóm.

**Câu 9: Làm sao đăng xuất an toàn?**

→ Gọi `logout` (`api/core/logout` hoặc `api/front/logout`) với token hiện tại. Hệ thống sẽ **xóa
token** đó khỏi cơ sở dữ liệu, nên token cũ không dùng lại được nữa.

**Câu 10: Tôi cần thêm endpoint không có trong danh sách?**

→ Danh sách trên là các endpoint sẵn có của `core`/`front`/`shop`. Nếu cần thêm, lập trình viên có
thể mở rộng bằng cách khai báo route trong plugin riêng — không sửa code lõi (theo nguyên tắc
"plugin cắm vào core" của GP247).

---

<sub>📅 **Cập nhật lần cuối:** 2026-07-30 · ✍️ **Tác giả (Author):** GP247</sub>
