> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./api-reference-admin.md) · [🇯🇵 日本語](./api-reference-admin_ja.md)

# Tra cứu API v2 — Quản trị (Core)

> Trang này được sinh từ openapi.json bằng tools/openapi-to-markdown.php — không sửa tay.

Mọi endpoint dưới `api/v2/core` — dữ liệu cho quản trị viên và hệ thống hậu trường (ERP, CRM, kho) — kèm quyền quản trị cần có, tham số, ví dụ gọi và kết quả trả về.

Mới dùng API? Bắt đầu với [Bắt đầu nhanh](./api-quickstart_vi.md). Quy tắc chung cho mọi endpoint (định dạng phản hồi, phân trang, `fields`, `include`, `304`) nằm ở [Quy ước](./api-conventions_vi.md); từng mã lỗi được giải thích ở [Xử lý sự cố](./api-troubleshooting_vi.md).

## Mục lục

- [Admin · Đăng nhập & phiên](#admin--đăng-nhập--phiên) — Đăng nhập quản trị viên, làm mới token, đăng nhập hai bước, tài khoản của chính mình và các thiết bị đang đăng nhập.
- [Admin · Danh mục sản phẩm](#admin--danh-mục-sản-phẩm) — Sản phẩm, danh mục, thương hiệu và nhà cung cấp như màn quản trị hiển thị; thay đổi sản phẩm.
- [Admin · Tồn kho](#admin--tồn-kho) — Thay đổi tồn kho cho một sản phẩm hoặc nhiều sản phẩm cùng lúc (đồng bộ kho / ERP).
- [Admin · Nội dung](#admin--nội-dung) — Banner và trang nội dung như màn quản trị hiển thị.
- [Admin · Khách hàng](#admin--khách-hàng) — Tài khoản khách hàng và khoá hoặc mở khoá tài khoản.
- [Admin · Đơn hàng](#admin--đơn-hàng) — Đơn hàng, trạng thái, thông tin giao hàng và ghi chú.
- [Admin · Đồng bộ](#admin--đồng-bộ) — Nhật ký sự kiện cho ERP / công cụ đồng bộ: mọi thay đổi của đơn hàng, tồn kho, sản phẩm và khách hàng, đọc theo con trỏ.
- [Đối tượng dữ liệu](#đối-tượng-dữ-liệu)

## Admin · Đăng nhập & phiên

Đăng nhập quản trị viên, làm mới token, đăng nhập hai bước, tài khoản của chính mình và các thiết bị đang đăng nhập.

### Hồ sơ quản trị viên đang đăng nhập — `GET /api/v2/core/info`

Trả về hồ sơ và slug vai trò của quản trị viên sở hữu token. Mọi token quản trị viên đang hoạt động đều gọi được; không cần quyền màn hình nào.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Không cần gì ngoài một token quản trị viên hợp lệ. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `core 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/info" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [AdminInfo](#admininfo)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "username": "admin",
        "name": "Administrator",
        "email": "admin@example.com",
        "avatar": null,
        "status": 1,
        "roles": [
            "administrator"
        ],
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00"
    }
}
```

### Quản trị viên đăng nhập — `POST /api/v2/core/login`

Đăng nhập quản trị viên đang hoạt động bằng username và mật khẩu, trả về bearer token. Kèm with_refresh=true thì phản hồi có thêm refresh token và access token chỉ sống thời gian ngắn. Khi plugin yêu cầu bước thứ hai (MFA), phản hồi là 401 challenge_required kèm ticket để hoàn tất ở login/challenge. Số lần đăng nhập sai bị giới hạn theo tài khoản và IP.

| | |
|---|---|
| Đăng nhập | Không cần — lệnh này dùng để đăng nhập. |
| Quyền | Không cần gì ngoài một token quản trị viên hợp lệ. |
| Có từ | `core 3.3.0` |

**Nội dung yêu cầu**: [AdminLoginV2](#adminloginv2)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"","password":"","remember_me":false,"with_refresh":false,"device_name":""}'
```

**Phản hồi** `200` — `data`: [SessionToken](#sessiontoken)

```json
{
    "data": {
        "access_token": "12|XyZ...plain-token",
        "token_type": "Bearer",
        "scopes": [
            "user"
        ],
        "expires_at": "2026-11-03 09:00:00",
        "expires_in": 3600,
        "session_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "refresh_token": "a1b2c3...64chars",
        "refresh_expires_at": "2026-12-03 08:00:00",
        "cart_merge": {
            "merged": 2,
            "dropped": 0
        }
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`unauthenticated`](./api-troubleshooting_vi.md#unauthenticated) · [`too_many_attempts`](./api-troubleshooting_vi.md#too_many_attempts) · [`challenge_required`](./api-troubleshooting_vi.md#challenge_required) · [`challenge_invalid`](./api-troubleshooting_vi.md#challenge_invalid)

### Quản trị viên đăng xuất — `POST /api/v2/core/logout`

Đăng xuất phiên hiện tại: access token của phiên ngừng hoạt động và refresh token không dùng được nữa. Mọi token quản trị viên đang hoạt động đều gọi được.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Không cần gì ngoài một token quản trị viên hợp lệ. |
| Có từ | `core 3.3.0` |

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [AdminLogoutResult](#adminlogoutresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

### Hoàn tất đăng nhập MFA của quản trị viên — `POST /api/v2/core/login/challenge`

Gửi ticket từ phản hồi challenge_required kèm các trường riêng của plugin (vd mã dùng một lần). Khi thành công thì trả lời như login. Trả lời sai bị tính vào giới hạn đăng nhập; một ticket sống 5 phút và cho phép sai 5 lần.

| | |
|---|---|
| Đăng nhập | Không cần — lệnh này dùng để đăng nhập. |
| Quyền | Không cần gì ngoài một token quản trị viên hợp lệ. |
| Có từ | `core 3.5.1` |

**Nội dung yêu cầu**: [LoginChallengeAnswer](#loginchallengeanswer)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/login/challenge" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"ticket":""}'
```

**Phản hồi** `200` — `data`: [SessionToken](#sessiontoken)

```json
{
    "data": {
        "access_token": "12|XyZ...plain-token",
        "token_type": "Bearer",
        "scopes": [
            "user"
        ],
        "expires_at": "2026-11-03 09:00:00",
        "expires_in": 3600,
        "session_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "refresh_token": "a1b2c3...64chars",
        "refresh_expires_at": "2026-12-03 08:00:00",
        "cart_merge": {
            "merged": 2,
            "dropped": 0
        }
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`challenge_invalid`](./api-troubleshooting_vi.md#challenge_invalid) · [`too_many_attempts`](./api-troubleshooting_vi.md#too_many_attempts)

### Làm mới access token của quản trị viên — `POST /api/v2/core/refresh`

Đổi một refresh token lấy access token mới và refresh token mới; mỗi refresh token chỉ dùng được một lần. Dùng lại token đã dùng sẽ đăng xuất cả phiên (refresh_token_reused), trừ khi trong vòng 10 giây kể từ lần dùng (refresh_in_progress). Trả 404 khi site chưa chạy gp247:update.

| | |
|---|---|
| Đăng nhập | Không cần — lệnh này dùng để đăng nhập. |
| Quyền | Không cần gì ngoài một token quản trị viên hợp lệ. |
| Có từ | `core 3.5.1` |

**Nội dung yêu cầu**: [RefreshRequest](#refreshrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/refresh" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":""}'
```

**Phản hồi** `200` — `data`: [SessionToken](#sessiontoken)

```json
{
    "data": {
        "access_token": "12|XyZ...plain-token",
        "token_type": "Bearer",
        "scopes": [
            "user"
        ],
        "expires_at": "2026-11-03 09:00:00",
        "expires_in": 3600,
        "session_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "refresh_token": "a1b2c3...64chars",
        "refresh_expires_at": "2026-12-03 08:00:00",
        "cart_merge": {
            "merged": 2,
            "dropped": 0
        }
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`not_found`](./api-troubleshooting_vi.md#not_found) · [`refresh_token_invalid`](./api-troubleshooting_vi.md#refresh_token_invalid) · [`refresh_token_reused`](./api-troubleshooting_vi.md#refresh_token_reused) · [`refresh_in_progress`](./api-troubleshooting_vi.md#refresh_in_progress) · [`unauthenticated`](./api-troubleshooting_vi.md#unauthenticated)

### Danh sách phiên của quản trị viên — `GET /api/v2/core/sessions`

Trả về các phiên API còn hiệu lực (thiết bị) của quản trị viên đang đăng nhập, mới nhất trước, có đánh dấu phiên hiện tại. Không trả về dữ liệu token nào. Không phân trang; là mảng rỗng khi site chưa chạy gp247:update.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Không cần gì ngoài một token quản trị viên hợp lệ. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `core 3.5.1` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/sessions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Session](#session)

```json
{
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "name": "Pixel 8",
            "ip": "203.0.113.5",
            "created_at": "2026-10-31T08:00:00+07:00",
            "last_used_at": "2026-10-31T08:00:00+07:00",
            "refreshable": true,
            "refresh_expires_at": "2026-11-30T08:00:00+07:00",
            "current": true
        }
    ]
}
```

### Đăng xuất một phiên quản trị viên — `DELETE /api/v2/core/sessions/{id}`

Đăng xuất một phiên của chính quản trị viên (cả access token và refresh token). Id phiên của người khác trả 404.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Không cần gì ngoài một token quản trị viên hợp lệ. |
| Có từ | `core 3.5.1` |

**Ví dụ yêu cầu**

```bash
curl -X DELETE "https://shop.example.com/api/v2/core/sessions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [AdminSessionsDeleteResult](#adminsessionsdeleteresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found)

## Admin · Danh mục sản phẩm

Sản phẩm, danh mục, thương hiệu và nhà cung cấp như màn quản trị hiển thị; thay đổi sản phẩm.

### Lấy một thương hiệu (quản trị) — `GET /api/v2/core/brand/detail/{id}`

Trả về một thương hiệu của cửa hàng của quản trị viên kèm các trường chỉ dành cho quản trị. Bản ghi của cửa hàng khác trả 404. Nhận fields[brand]=a,b.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Thương hiệu (gp247_admin/brand). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/brand/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Brand](#brand)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "name": "Acme",
        "alias": "acme",
        "image": "/data/brand/acme.png",
        "url": "https://acme.example.com",
        "sort": 0,
        "status": 1,
        "store_id": "1",
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00"
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Danh sách thương hiệu (quản trị) — `GET /api/v2/core/brand/list`

Trả về các thương hiệu của cửa hàng của quản trị viên, theo từng trang. Cần token quản trị viên được phép mở màn quản trị tương ứng. Lọc theo status, sắp xếp theo sort hoặc name, tìm theo tên. Nhận fields[brand]=a,b để chỉ trả một số trường. Hỗ trợ chế độ đồng bộ updated_since=&lt;ISO 8601&gt;: thay đổi cũ nhất trước, phân trang bằng page[after] (khi đó meta có per_page, next_after, has_more, sync_until; links chỉ có next).

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Thương hiệu (gp247_admin/brand). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/brand/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Brand](#brand) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "name": "Acme",
            "alias": "acme",
            "image": "/data/brand/acme.png",
            "url": "https://acme.example.com",
            "sort": 0,
            "status": 1,
            "store_id": "1",
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00"
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Lấy một danh mục (quản trị) — `GET /api/v2/core/category/detail/{id}`

Trả về một danh mục của cửa hàng của quản trị viên kèm các trường chỉ dành cho quản trị. Bản ghi của cửa hàng khác trả 404. Nhận fields[category]=a,b.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Danh mục (gp247_admin/category). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/category/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Category](#category)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "alias": "t-shirts",
        "name": "T-shirts",
        "keyword": "t-shirt",
        "description": "All our T-shirts.",
        "image": "/data/category/tshirts.jpg",
        "parent": null,
        "top": 1,
        "sort": 0,
        "status": 1,
        "store_id": "1",
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00"
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Danh sách danh mục (quản trị) — `GET /api/v2/core/category/list`

Trả về các danh mục của cửa hàng của quản trị viên, theo từng trang. Cần token quản trị viên được phép mở màn quản trị tương ứng. Các trường văn bản bằng ngôn ngữ chọn qua ?lang=. Lọc theo danh mục cha hoặc status, sắp xếp theo sort hoặc created_at, tìm theo tên. Nhận fields[category]=a,b để chỉ trả một số trường. Hỗ trợ chế độ đồng bộ updated_since=&lt;ISO 8601&gt;: thay đổi cũ nhất trước, phân trang bằng page[after] (khi đó meta có per_page, next_after, has_more, sync_until; links chỉ có next).

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Danh mục (gp247_admin/category). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/category/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Category](#category) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "alias": "t-shirts",
            "name": "T-shirts",
            "keyword": "t-shirt",
            "description": "All our T-shirts.",
            "image": "/data/category/tshirts.jpg",
            "parent": null,
            "top": 1,
            "sort": 0,
            "status": 1,
            "store_id": "1",
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00"
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Lấy một sản phẩm (quản trị) — `GET /api/v2/core/product/detail/{id}`

Trả về một sản phẩm của cửa hàng của quản trị viên kèm các trường chỉ dành cho quản trị. Bản ghi của cửa hàng khác trả 404. Nhận fields[product]=a,b và include=images,brand,categories.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Sản phẩm (gp247_admin/product). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |
| `include` | query | `string` | Các bản ghi liên quan cần lấy kèm, cách nhau bằng dấu phẩy (một cấp, tối đa 3): images, brand, categories. Giá trị khác = 422. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/product/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Product](#product)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "sku": "TSHIRT-RED-M",
        "alias": "red-t-shirt",
        "name": "Red T-shirt",
        "keyword": "t-shirt, red",
        "description": "Soft cotton T-shirt.",
        "content": "<p>Soft cotton T-shirt.</p>",
        "image": "/data/product/tshirt.jpg",
        "images": [
            "/data/product/tshirt-2.jpg"
        ],
        "price": 200000,
        "price_final": 180000,
        "tax_id": "auto",
        "kind": 0,
        "product_type": "physical",
        "brand_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "category_ids": [
            "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f"
        ],
        "minimum": 1,
        "weight": 0.3,
        "weight_class": "kg",
        "length": 30,
        "width": 20,
        "height": 2,
        "length_class": "cm",
        "date_available": "2026-11-01",
        "store_id": "1",
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "image_sizes": {
            "original": "https://shop.example.com/data/product/tshirt.jpg",
            "thumb": "https://shop.example.com/data/product/thumb/tshirt.jpg"
        },
        "stock_status": "in_stock",
        "attributes": [
            {
                "group_id": "1",
                "name": "Color",
                "required": true,
                "options": [
                    {
                        "value": "Red",
                        "add_price": 0
                    }
                ]
            }
        ],
        "cost": 120000,
        "stock": 15,
        "sold": 42,
        "status": 1,
        "approve": 1,
        "supplier_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "view": 310
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Cập nhật giá bán, giá vốn, trạng thái sản phẩm — `PATCH /api/v2/core/product/{id}`

Đổi giá bán, giá vốn và/hoặc trạng thái (0 hoặc 1) của một sản phẩm thuộc cửa hàng của quản trị viên. Trường khác, hoặc trường giá mà cửa hàng đã tắt trong cài đặt sản phẩm, bị từ chối với 422. Trả về đầy đủ sản phẩm dạng quản trị. Idempotency-Key không bắt buộc.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền ghi màn quản trị Sản phẩm (gp247_admin/product). |
| Có từ | `shop 3.6.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `Idempotency-Key` | header | `string` | Không bắt buộc; 8-255 ký tự in được (vd một UUID). Gửi lại với cùng khoá và cùng nội dung sẽ trả về kết quả lần đầu |

**Nội dung yêu cầu**: [AdminProductUpdateRequest](#adminproductupdaterequest)

**Ví dụ yêu cầu**

```bash
curl -X PATCH "https://shop.example.com/api/v2/core/product/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"price":0,"cost":0,"status":0}'
```

**Phản hồi** `200` — `data`: [Product](#product)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "sku": "TSHIRT-RED-M",
        "alias": "red-t-shirt",
        "name": "Red T-shirt",
        "keyword": "t-shirt, red",
        "description": "Soft cotton T-shirt.",
        "content": "<p>Soft cotton T-shirt.</p>",
        "image": "/data/product/tshirt.jpg",
        "images": [
            "/data/product/tshirt-2.jpg"
        ],
        "price": 200000,
        "price_final": 180000,
        "tax_id": "auto",
        "kind": 0,
        "product_type": "physical",
        "brand_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "category_ids": [
            "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f"
        ],
        "minimum": 1,
        "weight": 0.3,
        "weight_class": "kg",
        "length": 30,
        "width": 20,
        "height": 2,
        "length_class": "cm",
        "date_available": "2026-11-01",
        "store_id": "1",
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "image_sizes": {
            "original": "https://shop.example.com/data/product/tshirt.jpg",
            "thumb": "https://shop.example.com/data/product/thumb/tshirt.jpg"
        },
        "stock_status": "in_stock",
        "attributes": [
            {
                "group_id": "1",
                "name": "Color",
                "required": true,
                "options": [
                    {
                        "value": "Red",
                        "add_price": 0
                    }
                ]
            }
        ],
        "cost": 120000,
        "stock": 15,
        "sold": 42,
        "status": 1,
        "approve": 1,
        "supplier_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "view": 310
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`forbidden`](./api-troubleshooting_vi.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_vi.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_vi.md#idempotency_unavailable)

### Danh sách sản phẩm (quản trị) — `GET /api/v2/core/product/list`

Trả về các sản phẩm của cửa hàng của quản trị viên, theo từng trang. Cần token quản trị viên được phép mở màn quản trị tương ứng. Có các trường kho và kiểm duyệt (cost, stock, sold, status, approve, supplier_id, view) và mọi sản phẩm bất kể trạng thái. Bộ lọc: category_id, brand_id, kind, product_type, price_min, price_max, status, approve, supplier_id; sắp xếp theo price, created_at, sort hoặc sku; tìm theo SKU hoặc tên; include=images,brand,categories. Nhận fields[product]=a,b để chỉ trả một số trường. Hỗ trợ chế độ đồng bộ updated_since=&lt;ISO 8601&gt;: thay đổi cũ nhất trước, phân trang bằng page[after] (khi đó meta có per_page, next_after, has_more, sync_until; links chỉ có next).

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Sản phẩm (gp247_admin/product). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |
| `include` | query | `string` | Các bản ghi liên quan cần lấy kèm, cách nhau bằng dấu phẩy (một cấp, tối đa 3): images, brand, categories. Giá trị khác = 422. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/product/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Product](#product) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "sku": "TSHIRT-RED-M",
            "alias": "red-t-shirt",
            "name": "Red T-shirt",
            "keyword": "t-shirt, red",
            "description": "Soft cotton T-shirt.",
            "content": "<p>Soft cotton T-shirt.</p>",
            "image": "/data/product/tshirt.jpg",
            "images": [
                "/data/product/tshirt-2.jpg"
            ],
            "price": 200000,
            "price_final": 180000,
            "tax_id": "auto",
            "kind": 0,
            "product_type": "physical",
            "brand_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "category_ids": [
                "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f"
            ],
            "minimum": 1,
            "weight": 0.3,
            "weight_class": "kg",
            "length": 30,
            "width": 20,
            "height": 2,
            "length_class": "cm",
            "date_available": "2026-11-01",
            "store_id": "1",
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00",
            "image_sizes": {
                "original": "https://shop.example.com/data/product/tshirt.jpg",
                "thumb": "https://shop.example.com/data/product/thumb/tshirt.jpg"
            },
            "stock_status": "in_stock",
            "attributes": [
                {
                    "group_id": "1",
                    "name": "Color",
                    "required": true,
                    "options": [
                        {
                            "value": "Red",
                            "add_price": 0
                        }
                    ]
                }
            ],
            "cost": 120000,
            "stock": 15,
            "sold": 42,
            "status": 1,
            "approve": 1,
            "supplier_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "view": 310
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Lấy một nhà cung cấp (quản trị) — `GET /api/v2/core/supplier/detail/{id}`

Trả về một nhà cung cấp của cửa hàng của quản trị viên kèm các trường chỉ dành cho quản trị. Bản ghi của cửa hàng khác trả 404. Nhận fields[supplier]=a,b.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Nhà cung cấp (gp247_admin/supplier). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/supplier/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Supplier](#supplier)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "name": "Acme Supply",
        "alias": "acme-supply",
        "email": "sales@acme.example.com",
        "phone": "+84 28 1234 5678",
        "image": null,
        "address": "1 Main St, Ho Chi Minh City",
        "url": null,
        "sort": 0,
        "status": 1,
        "store_id": "1",
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00"
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Danh sách nhà cung cấp (quản trị) — `GET /api/v2/core/supplier/list`

Trả về các nhà cung cấp của cửa hàng của quản trị viên, theo từng trang. Cần token quản trị viên được phép mở màn quản trị tương ứng. Lọc theo status, sắp xếp theo sort hoặc name, tìm theo tên hoặc e-mail. Nhận fields[supplier]=a,b để chỉ trả một số trường. Hỗ trợ chế độ đồng bộ updated_since=&lt;ISO 8601&gt;: thay đổi cũ nhất trước, phân trang bằng page[after] (khi đó meta có per_page, next_after, has_more, sync_until; links chỉ có next).

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Nhà cung cấp (gp247_admin/supplier). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/supplier/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Supplier](#supplier) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "name": "Acme Supply",
            "alias": "acme-supply",
            "email": "sales@acme.example.com",
            "phone": "+84 28 1234 5678",
            "image": null,
            "address": "1 Main St, Ho Chi Minh City",
            "url": null,
            "sort": 0,
            "status": 1,
            "store_id": "1",
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00"
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

## Admin · Tồn kho

Thay đổi tồn kho cho một sản phẩm hoặc nhiều sản phẩm cùng lúc (đồng bộ kho / ERP).

### Nhập/xuất hoặc đặt tồn kho sản phẩm — `POST /api/v2/core/product/{id}/stock`

Nhập hoặc xuất hàng bằng delta (dương hoặc âm), hoặc ghi đè tồn kho sau khi kiểm kê bằng set (chỉ vai trò administrator). Tồn kho không bao giờ xuống dưới 0 và bộ đếm đã bán giữ nguyên; sản phẩm build bị từ chối. Bắt buộc có header Idempotency-Key để gửi lại không bao giờ trừ/cộng tồn kho hai lần.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền ghi màn quản trị Sản phẩm (gp247_admin/product). |
| Có từ | `shop 3.6.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `Idempotency-Key` *(bắt buộc)* | header | `string` | Bắt buộc; 8-255 ký tự in được (vd một UUID). Gửi lại với cùng khoá và cùng nội dung chỉ thay đổi tồn kho một lần và trả về kết quả lần đầu (header Idempotent-Replayed: true) |

**Nội dung yêu cầu**: [AdminProductStockRequest](#adminproductstockrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/product/123/stock" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"delta":0,"set":0}'
```

**Phản hồi** `200` — `data`: [AdminProductStockResult](#adminproductstockresult)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "sku": "TSHIRT-RED-M",
        "stock": 15
    }
}
```

**Lỗi**: [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_vi.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_vi.md#idempotency_unavailable) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`not_found`](./api-troubleshooting_vi.md#not_found) · [`forbidden`](./api-troubleshooting_vi.md#forbidden) · [`stock_managed_by_components`](./api-troubleshooting_vi.md#stock_managed_by_components) · [`stock_would_be_negative`](./api-troubleshooting_vi.md#stock_would_be_negative)

### Nhập/xuất tồn kho nhiều sản phẩm — `POST /api/v2/core/stock/batch`

Áp dụng từ 1 đến 100 dòng tồn kho, mỗi dòng chỉ định một sản phẩm theo id hoặc SKU kèm delta hoặc set. Mỗi dòng có kết quả riêng và một dòng lỗi không bao giờ làm dừng các dòng khác; phản hồi là 200 kể cả khi có dòng thất bại. Bắt buộc có Idempotency-Key.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền ghi màn quản trị Sản phẩm (gp247_admin/product). |
| Có từ | `shop 3.6.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `Idempotency-Key` *(bắt buộc)* | header | `string` | Bắt buộc; 8-255 ký tự in được (vd một UUID). Gửi lại với cùng khoá và cùng nội dung chỉ thay đổi tồn kho một lần và trả về kết quả lần đầu (header Idempotent-Replayed: true) |

**Nội dung yêu cầu**: [AdminStockBatchRequest](#adminstockbatchrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/stock/batch" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"items":[[]]}'
```

**Phản hồi** `200` — `data`: [AdminStockBatchResult](#adminstockbatchresult)

```json
{
    "data": {
        "results": [],
        "ok": 9,
        "failed": 1
    }
}
```

**Lỗi**: [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_vi.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_vi.md#idempotency_unavailable) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`forbidden`](./api-troubleshooting_vi.md#forbidden)

## Admin · Nội dung

Banner và trang nội dung như màn quản trị hiển thị.

### Lấy một banner (quản trị) — `GET /api/v2/core/banner/detail/{id}`

Trả về một banner của cửa hàng của quản trị viên, kèm bộ đếm lượt nhấp. Cần token quản trị viên được phép mở màn Banner; banner của cửa hàng khác trả 404.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Banner (gp247_admin/banner). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `front 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/banner/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Banner](#banner)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "name": "Autumn sale",
        "image": "/data/banner/autumn.jpg",
        "url": "https://shop.example.com/sale",
        "target": "_self",
        "html": null,
        "type": "banner",
        "sort": 0,
        "status": 1,
        "store_id": "1",
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "click": 57
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found)

### Danh sách banner (quản trị) — `GET /api/v2/core/banner/list`

Trả về các banner của cửa hàng của quản trị viên, theo từng trang, kèm bộ đếm lượt nhấp. Cần token quản trị viên được phép mở màn Banner. Lọc theo type, sắp xếp theo sort hoặc created_at, tìm theo tên.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Banner (gp247_admin/banner). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `front 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/banner/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Banner](#banner) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "name": "Autumn sale",
            "image": "/data/banner/autumn.jpg",
            "url": "https://shop.example.com/sale",
            "target": "_self",
            "html": null,
            "type": "banner",
            "sort": 0,
            "status": 1,
            "store_id": "1",
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00",
            "click": 57
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Lấy một trang CMS (quản trị) — `GET /api/v2/core/page/detail/{id}`

Trả về một trang CMS của cửa hàng của quản trị viên kèm nội dung HTML, bằng ngôn ngữ chọn qua ?lang=. Cần quyền vào màn Trang.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Trang (gp247_admin/page). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `front 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/page/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Page](#page)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "alias": "about-us",
        "image": null,
        "name": "About us",
        "keyword": "about",
        "description": "Who we are.",
        "content": "<p>Who we are.</p>",
        "status": 1,
        "store_id": "1",
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00"
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found)

### Danh sách trang CMS (quản trị) — `GET /api/v2/core/page/list`

Trả về các trang CMS của cửa hàng của quản trị viên, theo từng trang, không kèm nội dung HTML. Các trường văn bản bằng ngôn ngữ chọn qua ?lang= (nếu không thì theo ngôn ngữ mặc định của site). Cần quyền vào màn Trang.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Trang (gp247_admin/page). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `front 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/page/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Page](#page) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "alias": "about-us",
            "image": null,
            "name": "About us",
            "keyword": "about",
            "description": "Who we are.",
            "content": "<p>Who we are.</p>",
            "status": 1,
            "store_id": "1",
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00"
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

## Admin · Khách hàng

Tài khoản khách hàng và khoá hoặc mở khoá tài khoản.

### Lấy một khách hàng (quản trị) — `GET /api/v2/core/customer/detail/{id}`

Trả về một khách hàng của cửa hàng của quản trị viên kèm các trường chỉ dành cho quản trị. Bản ghi của cửa hàng khác trả 404. Nhận fields[customer]=a,b.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Khách hàng (gp247_admin/customer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/customer/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Customer](#customer)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "first_name": "An",
        "last_name": "Nguyen",
        "first_name_kana": null,
        "last_name_kana": null,
        "email": "an@example.com",
        "phone": "0901234567",
        "sex": 1,
        "birthday": "1990-05-20",
        "company": null,
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "status": 1,
        "group": 1,
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "store_id": "1",
        "email_verified_at": "2026-10-01 08:00:00"
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Danh sách khách hàng (quản trị) — `GET /api/v2/core/customer/list`

Trả về các khách hàng của cửa hàng của quản trị viên, theo từng trang. Cần token quản trị viên được phép mở màn quản trị tương ứng. Có các trường chỉ dành cho quản trị store_id và email_verified_at; không bao giờ có mật khẩu. Lọc theo status hoặc group, sắp xếp theo created_at hoặc email, tìm theo e-mail, điện thoại hoặc tên. Nhận fields[customer]=a,b để chỉ trả một số trường. Hỗ trợ chế độ đồng bộ updated_since=&lt;ISO 8601&gt;: thay đổi cũ nhất trước, phân trang bằng page[after] (khi đó meta có per_page, next_after, has_more, sync_until; links chỉ có next).

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Khách hàng (gp247_admin/customer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/customer/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Customer](#customer) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "first_name": "An",
            "last_name": "Nguyen",
            "first_name_kana": null,
            "last_name_kana": null,
            "email": "an@example.com",
            "phone": "0901234567",
            "sex": 1,
            "birthday": "1990-05-20",
            "company": null,
            "country": "VN",
            "postcode": "700000",
            "city": "Ho Chi Minh City",
            "district": "District 1",
            "ward": "Ben Nghe",
            "address1": "1 Le Loi",
            "address2": null,
            "address3": null,
            "status": 1,
            "group": 1,
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00",
            "store_id": "1",
            "email_verified_at": "2026-10-01 08:00:00"
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Khoá hoặc mở khoá khách hàng — `PATCH /api/v2/core/customer/{id}`

Đặt trạng thái của một khách hàng thuộc cửa hàng của quản trị viên: 0 khoá tài khoản ngay lập tức (cả API lẫn cửa hàng), 1 mở khoá. Trường khác bị từ chối với 422. Trả về khách hàng kèm các trường chỉ dành cho quản trị. Idempotency-Key không bắt buộc.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền ghi màn quản trị Khách hàng (gp247_admin/customer). |
| Có từ | `shop 3.6.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `Idempotency-Key` | header | `string` | Không bắt buộc; 8-255 ký tự in được (vd một UUID). Gửi lại với cùng khoá và cùng nội dung sẽ trả về kết quả lần đầu |

**Nội dung yêu cầu**: [AdminCustomerUpdateRequest](#admincustomerupdaterequest)

**Ví dụ yêu cầu**

```bash
curl -X PATCH "https://shop.example.com/api/v2/core/customer/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"status":0}'
```

**Phản hồi** `200` — `data`: [Customer](#customer)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "first_name": "An",
        "last_name": "Nguyen",
        "first_name_kana": null,
        "last_name_kana": null,
        "email": "an@example.com",
        "phone": "0901234567",
        "sex": 1,
        "birthday": "1990-05-20",
        "company": null,
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "status": 1,
        "group": 1,
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "store_id": "1",
        "email_verified_at": "2026-10-01 08:00:00"
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`forbidden`](./api-troubleshooting_vi.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_vi.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_vi.md#idempotency_unavailable)

## Admin · Đơn hàng

Đơn hàng, trạng thái, thông tin giao hàng và ghi chú.

### Lấy một đơn hàng (quản trị) — `GET /api/v2/core/order/detail/{id}`

Trả về một đơn hàng của cửa hàng của quản trị viên kèm các trường chỉ dành cho quản trị. Bản ghi của cửa hàng khác trả 404. Nhận fields[order]=a,b và include=transactions,histories.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị chi tiết đơn hàng (gp247_admin/order/detail/{id}). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |
| `include` | query | `string` | Các bản ghi liên quan cần lấy kèm, cách nhau bằng dấu phẩy (một cấp, tối đa 3): transactions (sổ thu chi thanh toán), histories (lịch sử xử lý, văn bản thuần). Giá trị khác = 422. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/order/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: [Order](#order)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "customer_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "status": 1,
        "payment_status": 1,
        "shipping_status": 1,
        "currency": "VND",
        "exchange_rate": 1,
        "subtotal": 360000,
        "tax": 36000,
        "shipping": 30000,
        "discount": 0,
        "other_fee": 0,
        "total": 426000,
        "received": 0,
        "balance": 426000,
        "first_name": "An",
        "last_name": "Nguyen",
        "email": "an@example.com",
        "phone": "0901234567",
        "company": null,
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "comment": "Call before delivery",
        "payment_method": "Cash",
        "shipping_method": "ShippingStandard",
        "store_id": "1",
        "details": [],
        "totals": [],
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "ip": "203.0.113.5",
        "user_agent": "MyShopApp/1.0",
        "device_type": "phone",
        "domain": "shop.example.com",
        "transaction": null
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Đổi trạng thái đơn hàng — `POST /api/v2/core/order/{id}/status`

Đặt trạng thái đơn hàng đúng như màn quản trị đơn hàng: huỷ đơn thì trả lại tồn kho, mở lại đơn đã huỷ thì lấy lại tồn kho (bị từ chối với 409 khi không đủ hàng) và ghi một dòng lịch sử. Trả về đơn hàng giống order/detail. Header Idempotency-Key không bắt buộc; gửi lại với cùng khoá sẽ trả lại phản hồi lần đầu.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền ghi màn quản trị Đơn hàng (gp247_admin/order). |
| Có từ | `shop 3.6.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `Idempotency-Key` | header | `string` | Không bắt buộc; 8-255 ký tự in được (vd một UUID). Gửi lại với cùng khoá và cùng nội dung sẽ trả về kết quả lần đầu |

**Nội dung yêu cầu**: [AdminOrderStatusRequest](#adminorderstatusrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"status":0}'
```

**Phản hồi** `200` — `data`: [Order](#order)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "customer_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "status": 1,
        "payment_status": 1,
        "shipping_status": 1,
        "currency": "VND",
        "exchange_rate": 1,
        "subtotal": 360000,
        "tax": 36000,
        "shipping": 30000,
        "discount": 0,
        "other_fee": 0,
        "total": 426000,
        "received": 0,
        "balance": 426000,
        "first_name": "An",
        "last_name": "Nguyen",
        "email": "an@example.com",
        "phone": "0901234567",
        "company": null,
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "comment": "Call before delivery",
        "payment_method": "Cash",
        "shipping_method": "ShippingStandard",
        "store_id": "1",
        "details": [],
        "totals": [],
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "ip": "203.0.113.5",
        "user_agent": "MyShopApp/1.0",
        "device_type": "phone",
        "domain": "shop.example.com",
        "transaction": null
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`forbidden`](./api-troubleshooting_vi.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_vi.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_vi.md#idempotency_unavailable) · [`admin.order.reopen_blocked_stock`](./api-troubleshooting_vi.md#adminorderreopen_blocked_stock)

### Đặt trạng thái giao hàng hoặc mã vận đơn — `POST /api/v2/core/order/{id}/shipping`

Đặt trạng thái giao hàng và/hoặc ghi mã vận đơn (và URL) làm lô giao thủ công của đơn hàng. Không ghi được trạng thái thanh toán ở đây. Trả về đơn hàng sau khi cập nhật. Idempotency-Key không bắt buộc.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền ghi màn quản trị Đơn hàng (gp247_admin/order). |
| Có từ | `shop 3.6.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `Idempotency-Key` | header | `string` | Không bắt buộc; 8-255 ký tự in được (vd một UUID). Gửi lại với cùng khoá và cùng nội dung sẽ trả về kết quả lần đầu |

**Nội dung yêu cầu**: [AdminOrderShippingRequest](#adminordershippingrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/shipping" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"shipping_status":0,"tracking_number":"","tracking_url":""}'
```

**Phản hồi** `200` — `data`: [Order](#order)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "customer_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "status": 1,
        "payment_status": 1,
        "shipping_status": 1,
        "currency": "VND",
        "exchange_rate": 1,
        "subtotal": 360000,
        "tax": 36000,
        "shipping": 30000,
        "discount": 0,
        "other_fee": 0,
        "total": 426000,
        "received": 0,
        "balance": 426000,
        "first_name": "An",
        "last_name": "Nguyen",
        "email": "an@example.com",
        "phone": "0901234567",
        "company": null,
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "comment": "Call before delivery",
        "payment_method": "Cash",
        "shipping_method": "ShippingStandard",
        "store_id": "1",
        "details": [],
        "totals": [],
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "ip": "203.0.113.5",
        "user_agent": "MyShopApp/1.0",
        "device_type": "phone",
        "domain": "shop.example.com",
        "transaction": null
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`forbidden`](./api-troubleshooting_vi.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_vi.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_vi.md#idempotency_unavailable) · [`admin.order.shipping_status_invalid`](./api-troubleshooting_vi.md#adminordershipping_status_invalid)

### Thêm ghi chú đơn hàng — `POST /api/v2/core/order/{id}/notes`

Thêm một ghi chú (tối đa 1000 ký tự) vào lịch sử đơn hàng, đánh dấu là ghi qua API. Trả về đơn hàng. Idempotency-Key không bắt buộc.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền ghi màn quản trị Đơn hàng (gp247_admin/order). |
| Có từ | `shop 3.6.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `Idempotency-Key` | header | `string` | Không bắt buộc; 8-255 ký tự in được (vd một UUID). Gửi lại với cùng khoá và cùng nội dung sẽ trả về kết quả lần đầu |

**Nội dung yêu cầu**: [AdminOrderNotesRequest](#adminordernotesrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/notes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"content":""}'
```

**Phản hồi** `200` — `data`: [Order](#order)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "customer_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "status": 1,
        "payment_status": 1,
        "shipping_status": 1,
        "currency": "VND",
        "exchange_rate": 1,
        "subtotal": 360000,
        "tax": 36000,
        "shipping": 30000,
        "discount": 0,
        "other_fee": 0,
        "total": 426000,
        "received": 0,
        "balance": 426000,
        "first_name": "An",
        "last_name": "Nguyen",
        "email": "an@example.com",
        "phone": "0901234567",
        "company": null,
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "comment": "Call before delivery",
        "payment_method": "Cash",
        "shipping_method": "ShippingStandard",
        "store_id": "1",
        "details": [],
        "totals": [],
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00",
        "ip": "203.0.113.5",
        "user_agent": "MyShopApp/1.0",
        "device_type": "phone",
        "domain": "shop.example.com",
        "transaction": null
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`forbidden`](./api-troubleshooting_vi.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_vi.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_vi.md#idempotency_unavailable)

### Danh sách đơn hàng (quản trị) — `GET /api/v2/core/order/list`

Trả về các đơn hàng của cửa hàng của quản trị viên, theo từng trang. Cần token quản trị viên được phép mở màn quản trị tương ứng. Mỗi đơn kèm các dòng hàng (details) và tổng tiền, cùng dữ liệu yêu cầu chỉ dành cho quản trị (ip, user_agent, device_type, domain, transaction). Bộ lọc: status, payment_status, shipping_status, customer_id; sắp xếp theo created_at hoặc total; tìm theo id đơn, e-mail hoặc điện thoại; include=transactions,histories. Nhận fields[order]=a,b để chỉ trả một số trường. Hỗ trợ chế độ đồng bộ updated_since=&lt;ISO 8601&gt;: thay đổi cũ nhất trước, phân trang bằng page[after] (khi đó meta có per_page, next_after, has_more, sync_until; links chỉ có next).

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị Đơn hàng (gp247_admin/order). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `updated_since` | query | `string` (date-time) | Chế độ đồng bộ (có từ core 3.7.0): ngày giờ ISO-8601, vd 2026-10-31T08:00:00+07:00 (chỉ có ngày hoặc không có múi giờ = múi giờ của site). Trả về các dòng có updated_at &gt;= updated_since, thay đổi cũ nhất trước (theo updated_at, rồi id). Không dùng chung được với sort hoặc page[number] (422) |
| `page[after]` | query | `string` | Chỉ ở chế độ đồng bộ: con trỏ lấy từ meta.next_after của trang trước; giá trị khác = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |
| `include` | query | `string` | Các bản ghi liên quan cần lấy kèm, cách nhau bằng dấu phẩy (một cấp, tối đa 3): transactions (sổ thu chi thanh toán), histories (lịch sử xử lý, văn bản thuần). Giá trị khác = 422. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/order/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Order](#order) — kèm `meta` (phân trang) và `links`.

```json
{
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 135,
        "last_page": 7,
        "next_after": "eyJ0IjoiMjAyNi0xMC0zMSAwODowMDowMCIsImsiOiI5ZjBjIn0",
        "has_more": true,
        "sync_until": "2026-10-31T08:00:00+07:00"
    },
    "links": {
        "first": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=1&page%5Bsize%5D=20",
        "last": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=7&page%5Bsize%5D=20",
        "prev": null,
        "next": "https://shop.example.com/api/v2/front/product/list?page%5Bnumber%5D=2&page%5Bsize%5D=20"
    },
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "customer_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "status": 1,
            "payment_status": 1,
            "shipping_status": 1,
            "currency": "VND",
            "exchange_rate": 1,
            "subtotal": 360000,
            "tax": 36000,
            "shipping": 30000,
            "discount": 0,
            "other_fee": 0,
            "total": 426000,
            "received": 0,
            "balance": 426000,
            "first_name": "An",
            "last_name": "Nguyen",
            "email": "an@example.com",
            "phone": "0901234567",
            "company": null,
            "country": "VN",
            "postcode": "700000",
            "city": "Ho Chi Minh City",
            "district": "District 1",
            "ward": "Ben Nghe",
            "address1": "1 Le Loi",
            "address2": null,
            "address3": null,
            "comment": "Call before delivery",
            "payment_method": "Cash",
            "shipping_method": "ShippingStandard",
            "store_id": "1",
            "details": [],
            "totals": [],
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00",
            "ip": "203.0.113.5",
            "user_agent": "MyShopApp/1.0",
            "device_type": "phone",
            "domain": "shop.example.com",
            "transaction": null
        }
    ]
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

## Admin · Đồng bộ

Nhật ký sự kiện cho ERP / công cụ đồng bộ: mọi thay đổi của đơn hàng, tồn kho, sản phẩm và khách hàng, đọc theo con trỏ.

### Đọc nhật ký sự kiện — `GET /api/v2/core/events`

Trả về các sự kiện thay đổi (đơn hàng, tồn kho, sản phẩm, khách hàng), cũ nhất trước, dành cho ERP và công cụ đồng bộ. Gửi ?after=&lt;id cuối cùng đã xử lý&gt; để đọc tiếp; page[size] mặc định 100, tối đa 500; filter[type]=a,b thu hẹp các loại. Chỉ trả về loại thuộc màn hình mà quản trị viên được mở và thuộc cửa hàng của quản trị viên; sự kiện mới hơn vài giây bị giữ lại. 409 khi nhật ký sự kiện đang tắt, 410 khi con trỏ trỏ tới sự kiện đã bị dọn.

| | |
|---|---|
| Đăng nhập | Token của quản trị viên (Bearer). |
| Quyền | Quyền đọc màn quản trị của từng loại sự kiện được trả về: Đơn hàng cho order.*, Sản phẩm cho product.* và stock.*, Khách hàng cho customer.*. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `core 3.7.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `after` | query | `integer` | id của sự kiện cuối cùng mà bên đọc đã xử lý; trả về các sự kiện sau nó. Không gửi = bắt đầu từ sự kiện cũ nhất còn giữ |
| `filter[type]` | query | `string` | Các loại sự kiện cách nhau bằng dấu phẩy (&lt;resource&gt;.&lt;action&gt;); chỉ thu hẹp trong các loại quản trị viên được đọc. Các loại của gp247/shop 3.6.0: order.created, order.status_changed, order.payment_status_changed, stock.changed, product.updated, product.deleted, customer.created, customer.updated |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/core/events" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Phản hồi** `200` — `data`: danh sách [Event](#event)

```json
{
    "data": [
        {
            "id": 1024,
            "type": "order.status_changed",
            "subject_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "store_id": "1",
            "source": "admin",
            "actor": {
                "type": "admin",
                "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f"
            },
            "data": {
                "field": "status",
                "from": 1,
                "to": 2
            },
            "occurred_at": "2026-10-31T08:00:00+07:00"
        }
    ],
    "meta": {
        "per_page": 100,
        "next_after": 4512,
        "has_more": false
    }
}
```

**Lỗi**: [`event_log_disabled`](./api-troubleshooting_vi.md#event_log_disabled) · [`cursor_expired`](./api-troubleshooting_vi.md#cursor_expired) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`forbidden`](./api-troubleshooting_vi.md#forbidden)

## Đối tượng dữ liệu

Các trường của đối tượng mà các endpoint ở trên gửi và trả về. Ngày giờ là chuỗi ISO 8601; tiền là số theo tiền tệ của bản ghi.

### AdminInfo

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id người dùng quản trị. |
| `username` | `string` | Tên đăng nhập. |
| `name` | `string \| null` | Tên hiển thị. |
| `email` | `string \| null` | E-mail. |
| `avatar` | `string \| null` | Đường dẫn ảnh đại diện. |
| `status` | `integer` | 1 = đang hoạt động. |
| `roles` | danh sách `string` | Slug các vai trò. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |

### SessionToken

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `access_token` | `string` | Bearer token để gửi dưới dạng Authorization: Bearer &lt;token&gt;. |
| `token_type` | `string` | Luôn là Bearer. |
| `scopes` | danh sách `string` | Các quyền (ability) của token. |
| `expires_at` | `string \| null` | Thời điểm access token hết hạn, dạng 'Y-m-d H:i:s' theo múi giờ của site (không phải ISO 8601). |
| `expires_in` | `integer \| null` | Số giây còn lại trước khi access token hết hạn. |
| `session_id` | `string` | Id phiên API; có khi site đã chạy gp247:update (đã có bảng phiên). |
| `refresh_token` | `string` | Chỉ khi with_refresh=true: refresh token dùng một lần, chỉ hiện một lần. |
| `refresh_expires_at` | `string` | Chỉ khi with_refresh=true: thời điểm refresh token hết hạn, dạng 'Y-m-d H:i:s'. |
| `cart_merge` | `object` | Chỉ ở đăng nhập khách hàng, khi có gửi giỏ khách vãng lai qua X-Cart-Token: số dòng {merged, dropped}. |

### AdminLoginV2

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `username` | `string` | Username của quản trị viên. *(bắt buộc)* |
| `password` | `string` | Mật khẩu của quản trị viên. *(bắt buộc)* |
| `remember_me` | `boolean` | Token sống lâu hơn. |
| `with_refresh` | `boolean` | Cấp thêm refresh token xoay vòng; khi đó access token chỉ sống 60 phút |
| `device_name` | `string` | Hiện trong danh sách phiên |

### AdminLogoutResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `revoked` | `boolean` | Luôn là true. |

### LoginChallengeAnswer

Cộng các trường do plugin xác thực bước hai định nghĩa (vd code)

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `ticket` | `string` | Ticket lấy từ phản hồi challenge_required của login. *(bắt buộc)* |

### RefreshRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `refresh_token` | `string` | Refresh token lấy từ login hoặc từ lần làm mới trước; mỗi token chỉ dùng được một lần. *(bắt buộc)* |

### Session

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id phiên. |
| `name` | `string \| null` | Tên thiết bị gửi lúc đăng nhập, nếu không có thì là user agent. |
| `ip` | `string \| null` | Địa chỉ IP lúc đăng nhập. |
| `created_at` | `string \| null` | Thời điểm đăng nhập (ISO 8601). |
| `last_used_at` | `string \| null` | Lần dùng gần nhất (ISO 8601). |
| `refreshable` | `boolean` | Phiên có refresh token hay không. |
| `refresh_expires_at` | `string \| null` | Thời điểm hết hạn làm mới (ISO 8601). |
| `current` | `boolean` | True với phiên của token đang gọi. |

### AdminSessionsDeleteResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `revoked` | `boolean` | Luôn là true. |

### Brand

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id thương hiệu. |
| `name` | `string` | Tên thương hiệu. |
| `alias` | `string` | Slug trên URL. |
| `image` | `string \| null` | Đường dẫn logo. |
| `url` | `string \| null` | Website của thương hiệu. |
| `sort` | `integer` | Thứ tự sắp xếp. |
| `status` | `integer` | 1 = đang bật. |
| `store_id` | `string \| null` | Id cửa hàng. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |

### Category

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id danh mục. |
| `alias` | `string` | Slug trên URL. |
| `name` | `string \| null` | Tên bằng ngôn ngữ được yêu cầu. |
| `keyword` | `string \| null` | Từ khoá SEO bằng ngôn ngữ được yêu cầu. |
| `description` | `string \| null` | Mô tả bằng ngôn ngữ được yêu cầu. |
| `image` | `string \| null` | Đường dẫn ảnh. |
| `parent` | `string \| null` | Id danh mục cha. |
| `top` | `integer` | 1 = hiện ở menu trên cùng. |
| `sort` | `integer` | Thứ tự sắp xếp. |
| `status` | `integer` | 1 = đang bật. |
| `store_id` | `string \| null` | Id cửa hàng. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |

### Product

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id sản phẩm (UUID). |
| `sku` | `string` | Mã SKU (mã quản lý hàng tồn kho). |
| `alias` | `string` | Slug URL của sản phẩm. |
| `name` | `string \| null` | Tên sản phẩm bằng ngôn ngữ được yêu cầu (không có thì dùng ngôn ngữ đầu tiên). |
| `keyword` | `string \| null` | Từ khoá SEO bằng ngôn ngữ được yêu cầu. |
| `description` | `string \| null` | Mô tả ngắn bằng ngôn ngữ được yêu cầu. |
| `content` | `string \| null` | Nội dung HTML bằng ngôn ngữ được yêu cầu; chỉ có ở phản hồi chi tiết (không có trong danh sách). *(chỉ ở chi tiết)* |
| `image` | `string \| null` | Đường dẫn ảnh chính như đã lưu. |
| `images` | danh sách `string` | Đường dẫn các ảnh phụ; có giá trị ở phản hồi chi tiết hoặc khi gửi include=images, còn lại là mảng rỗng trong danh sách. |
| `price` | `number \| null` | Giá niêm yết; null khi cửa hàng ẩn giá trên cửa hàng (quản trị viên luôn thấy). |
| `price_final` | `number \| null` | Giá cửa hàng tính cho khách (đã áp khuyến mãi); null khi cửa hàng ẩn giá. |
| `tax_id` | `string \| null` | Id quy tắc thuế; '0' = không thuế, 'auto' = thuế mặc định của cửa hàng. |
| `kind` | `integer` | Loại sản phẩm: 0 đơn lẻ, 1 build (ghép từ các thành phần), 2 nhóm. |
| `product_type` | `string \| null` | Kiểu sản phẩm, vd hàng vật lý hoặc hàng số. |
| `brand_id` | `string \| null` | Id thương hiệu. |
| `category_ids` | danh sách `string` | Id các danh mục của sản phẩm (ở cửa hàng: chỉ danh mục mà cửa hàng hiển thị). |
| `minimum` | `number` | Số lượng tối thiểu mỗi đơn. |
| `weight` | `number` | Khối lượng. |
| `weight_class` | `string \| null` | Đơn vị khối lượng. |
| `length` | `number` | Chiều dài. |
| `width` | `number` | Chiều rộng. |
| `height` | `number` | Chiều cao. |
| `length_class` | `string \| null` | Đơn vị chiều dài. |
| `date_available` | `string \| null` | Ngày sản phẩm bắt đầu có hàng (Y-m-d, giá trị gốc của cột). |
| `store_id` | `string \| null` | Cửa hàng sở hữu sản phẩm. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |
| `image_sizes` | `object` | URL tuyệt đối của ảnh chính: {original, thumb} (ảnh thay thế của site khi không có ảnh). |
| `stock_status` | `string` | in_stock hoặc out_of_stock; luôn là in_stock khi cửa hàng không theo dõi tồn kho hoặc cho bán khi hết hàng. |
| `attributes` | danh sách [ProductAttributeGroup](#productattributegroup) | Các nhóm thuộc tính người mua phải chọn để thêm sản phẩm vào giỏ. |
| `cost` | `number` | chỉ API quản trị. Giá vốn. *(chỉ API quản trị)* |
| `stock` | `number` | chỉ API quản trị. Số lượng tồn kho. *(chỉ API quản trị)* |
| `sold` | `number` | chỉ API quản trị. Số lượng đã bán. *(chỉ API quản trị)* |
| `status` | `integer` | chỉ API quản trị. 1 = đang bật, 0 = đã tắt. *(chỉ API quản trị)* |
| `approve` | `integer` | chỉ API quản trị. Cờ kiểm duyệt: 1 = đã duyệt. *(chỉ API quản trị)* |
| `supplier_id` | `string \| null` | chỉ API quản trị. Id nhà cung cấp. *(chỉ API quản trị)* |
| `view` | `integer` | chỉ API quản trị. Số lượt xem. *(chỉ API quản trị)* |
| `brand` | [Brand](#brand) | Chỉ khi có include=brand: thương hiệu (ở cửa hàng: chỉ thương hiệu đang bật). *(chỉ khi có include `brand`)* |
| `categories` | danh sách [Category](#category) | Chỉ khi có include=categories: đầy đủ các danh mục. *(chỉ khi có include `categories`)* |

### AdminProductUpdateRequest

Ít nhất một trong price / cost / status; không có gì khác

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `price` | `number` | Giá bán; bị từ chối (422) khi cửa hàng đã tắt trường giá |
| `cost` | `number` | Giá vốn; bị từ chối (422) khi cửa hàng đã tắt trường giá vốn |
| `status` | `integer` | 1 = đang bán, 0 = ẩn. |

### Supplier

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id nhà cung cấp. |
| `name` | `string` | Tên nhà cung cấp. |
| `alias` | `string` | Slug trên URL. |
| `email` | `string \| null` | E-mail liên hệ. |
| `phone` | `string \| null` | Điện thoại liên hệ. |
| `image` | `string \| null` | Đường dẫn logo. |
| `address` | `string \| null` | Địa chỉ bưu chính. |
| `url` | `string \| null` | Website. |
| `sort` | `integer` | Thứ tự sắp xếp. |
| `status` | `integer` | 1 = đang bật. |
| `store_id` | `string \| null` | Id cửa hàng. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |

### AdminProductStockRequest

Đúng một trong delta / set (có cả hai hoặc không có cái nào: 422 validation_failed)

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `delta` | `number` | Nhập (+) hoặc xuất (-) hàng, khác 0; là số nguyên trừ khi cài đặt sản phẩm cho phép số lượng thập phân. Nguyên tử: đơn đặt cùng lúc không bị mất gì. Không bao giờ xuống dưới 0 (422 stock_would_be_negative, không có gì thay đổi). Bộ đếm đã bán giữ nguyên |
| `set` | `number` | Đặt thẳng tồn kho sau khi kiểm kê; chỉ vai trò administrator (vai trò khác: 403) |

### AdminProductStockResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id sản phẩm. |
| `sku` | `string` | SKU của sản phẩm. |
| `stock` | `number` | Tồn kho sau khi thay đổi (là số nguyên khi không có phần thập phân). |

### AdminStockBatchRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `items` | danh sách `object` | Hơn 100 dòng hoặc có dòng sai dạng: 422, không áp dụng gì *(bắt buộc)* |

### AdminStockBatchResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `results` | danh sách [StockBatchResult](#stockbatchresult) | Một kết quả cho mỗi dòng, theo thứ tự trong yêu cầu. |
| `ok` | `integer` | Số dòng đã áp dụng. |
| `failed` | `integer` | Số dòng bị từ chối. |

### Banner

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id banner. |
| `name` | `string \| null` | Tên banner. |
| `image` | `string \| null` | Đường dẫn ảnh. |
| `url` | `string \| null` | Đích của liên kết. |
| `target` | `string \| null` | Cửa sổ mở liên kết, vd _self hoặc _blank. |
| `html` | `string \| null` | Nội dung HTML (không bắt buộc). |
| `type` | `string` | Mã vị trí/loại banner. |
| `sort` | `integer` | Thứ tự sắp xếp. |
| `status` | `integer` | 1 = đang bật. |
| `store_id` | `string \| null` | Id cửa hàng. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |
| `click` | `integer` | chỉ API quản trị. Số lượt nhấp. *(chỉ API quản trị)* |

### Page

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id trang. |
| `alias` | `string` | Slug trên URL. |
| `image` | `string \| null` | Đường dẫn ảnh. |
| `name` | `string \| null` | Tiêu đề bằng ngôn ngữ được yêu cầu. |
| `keyword` | `string \| null` | Từ khoá SEO. |
| `description` | `string \| null` | Mô tả ngắn. |
| `content` | `string \| null` | Nội dung HTML; chỉ có ở phản hồi chi tiết (không có trong danh sách). *(chỉ ở chi tiết)* |
| `status` | `integer` | 1 = đang bật. |
| `store_id` | `string \| null` | Id cửa hàng. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |

### Customer

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id khách hàng. |
| `first_name` | `string \| null` | Tên. |
| `last_name` | `string \| null` | Họ. |
| `first_name_kana` | `string \| null` | Tên viết kana (cửa hàng Nhật Bản). |
| `last_name_kana` | `string \| null` | Họ viết kana (cửa hàng Nhật Bản). |
| `email` | `string` | Địa chỉ e-mail (tên đăng nhập). |
| `phone` | `string \| null` | Số điện thoại. |
| `sex` | `integer \| null` | 0 = nữ, 1 = nam (giá trị gốc của cột). |
| `birthday` | `string \| null` | Ngày sinh (Y-m-d, giá trị gốc của cột). |
| `company` | `string \| null` | Công ty. |
| `country` | `string \| null` | Mã quốc gia. |
| `postcode` | `string \| null` | Mã bưu chính. |
| `city` | `string \| null` | Thành phố. |
| `district` | `string \| null` | Quận/huyện. |
| `ward` | `string \| null` | Phường/xã. |
| `address1` | `string \| null` | Địa chỉ dòng 1. |
| `address2` | `string \| null` | Địa chỉ dòng 2. |
| `address3` | `string \| null` | Địa chỉ dòng 3. |
| `status` | `integer` | 1 = đang hoạt động, 0 = bị khoá. |
| `group` | `integer` | Nhóm khách hàng (giá trị gốc của cột). |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |
| `store_id` | `string \| null` | chỉ API quản trị. Cửa hàng của tài khoản. *(chỉ API quản trị)* |
| `email_verified_at` | `string \| null` | chỉ API quản trị. Thời điểm e-mail được xác minh, giá trị gốc 'Y-m-d H:i:s' (không phải ISO 8601); null nếu chưa từng xác minh. *(chỉ API quản trị)* |

### AdminCustomerUpdateRequest

Chỉ status; không có gì khác

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `status` | `integer` | 0 = khoá, 1 = mở khoá *(bắt buộc)* |

### Order

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id đơn hàng. |
| `customer_id` | `string \| null` | Id khách hàng ('0' hoặc rỗng với đơn của khách vãng lai). |
| `status` | `integer` | Id trạng thái đơn hàng (các trạng thái cấu hình trên màn quản trị đơn hàng). |
| `payment_status` | `integer` | Id trạng thái thanh toán. |
| `shipping_status` | `integer` | Id trạng thái giao hàng. |
| `currency` | `string` | Tiền tệ của đơn hàng. |
| `exchange_rate` | `number` | Tỷ giá dùng cho đơn hàng. |
| `subtotal` | `number` | Tổng các dòng. |
| `tax` | `number` | Thuế. |
| `shipping` | `number` | Phí giao hàng. |
| `discount` | `number` | Giảm giá. |
| `other_fee` | `number` | Phí khác. |
| `total` | `number` | Số tiền phải trả. |
| `received` | `number` | Số tiền đã nhận đến lúc này. |
| `balance` | `number` | Số tiền còn phải trả. |
| `first_name` | `string \| null` | Tên người nhận hàng. |
| `last_name` | `string \| null` | Họ người nhận hàng. |
| `email` | `string \| null` | E-mail người mua. |
| `phone` | `string \| null` | Điện thoại người mua. |
| `company` | `string \| null` | Công ty. |
| `country` | `string \| null` | Mã quốc gia. |
| `postcode` | `string \| null` | Mã bưu chính. |
| `city` | `string \| null` | Thành phố. |
| `district` | `string \| null` | Quận/huyện. |
| `ward` | `string \| null` | Phường/xã. |
| `address1` | `string \| null` | Địa chỉ dòng 1. |
| `address2` | `string \| null` | Địa chỉ dòng 2. |
| `address3` | `string \| null` | Địa chỉ dòng 3. |
| `comment` | `string \| null` | Ghi chú của người mua. |
| `payment_method` | `string \| null` | Key của plugin thanh toán. |
| `shipping_method` | `string \| null` | Key của plugin giao hàng. |
| `store_id` | `string \| null` | Cửa hàng của đơn hàng. |
| `details` | danh sách [OrderLine](#orderline) | Các dòng của đơn hàng. |
| `totals` | danh sách [OrderTotal](#ordertotal) | Các dòng tổng tiền theo thứ tự hiển thị. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |
| `ip` | `string \| null` | chỉ API quản trị. Địa chỉ IP mà đơn hàng gửi tới từ đó. *(chỉ API quản trị)* |
| `user_agent` | `string \| null` | chỉ API quản trị. User agent của trình duyệt / ứng dụng. *(chỉ API quản trị)* |
| `device_type` | `string \| null` | chỉ API quản trị. Loại thiết bị (desktop, phone, tablet, other). *(chỉ API quản trị)* |
| `domain` | `string \| null` | chỉ API quản trị. Tên miền site nơi đặt đơn. *(chỉ API quản trị)* |
| `transaction` | `string \| null` | chỉ API quản trị. Mã giao dịch kiểu cũ. *(chỉ API quản trị)* |
| `transactions` | danh sách [OrderTransaction](#ordertransaction) | chỉ API quản trị, khi có include=transactions: các khoản thu chi tiền của đơn hàng. *(chỉ API quản trị, chỉ khi có include `transactions`)* |
| `histories` | danh sách [OrderHistory](#orderhistory) | chỉ API quản trị, khi có include=histories: các dòng lịch sử đơn hàng (văn bản thuần). *(chỉ API quản trị, chỉ khi có include `histories`)* |

### AdminOrderStatusRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `status` | `integer` | Id của một trong các trạng thái đơn hàng của site. Huỷ đơn thì trả tồn kho về kho, mở lại thì lấy lại; đơn đã hoàn tất hoặc đã huỷ có thể mở lại. Gửi đúng trạng thái hiện tại thì trả 200 và không ghi gì *(bắt buộc)* |

### AdminOrderShippingRequest

Ít nhất một trong shipping_status / tracking_number. payment_status bị từ chối (422): nó đi theo các khoản thanh toán của đơn

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `shipping_status` | `integer` | Id của một trong các trạng thái giao hàng của site; mỗi lần đổi sẽ thêm một dòng lịch sử đơn hàng |
| `tracking_number` | `string` | Lưu làm lô giao thủ công của đơn hàng (mỗi đơn một lô; gửi số khác sẽ cập nhật nó), hiện trên màn quản trị đơn hàng |
| `tracking_url` | `string` (uri) | Địa chỉ http(s); cần có tracking_number |

### AdminOrderNotesRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `content` | `string` | Lưu kèm người viết và nhãn [API]; hiển thị dưới dạng văn bản thuần (mã đánh dấu bị vô hiệu) *(bắt buộc)* |

### Event

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `integer` | Id sự kiện; gửi id cuối cùng đã xử lý dưới dạng ?after= để đọc tiếp. |
| `type` | `string` | Loại sự kiện, vd order.created, order.status_changed, order.payment_status_changed, stock.changed, product.updated, product.deleted, customer.created, customer.updated. |
| `subject_id` | `string` | Id của bản ghi mà sự kiện nói tới. |
| `store_id` | `string \| null` | Cửa hàng của bản ghi. |
| `source` | `string` | Nguồn của thay đổi: api, admin, web hoặc system. |
| `actor` | `object \| null` | Ai thực hiện thay đổi: {type: admin\|customer, id}; null khi không rõ (hệ thống). |
| `data` | `object` | Các trường khai báo cho loại sự kiện (vd {field, from, to} cho order.status_changed); đối tượng rỗng khi không có. |
| `occurred_at` | `string` | Thời điểm xảy ra (ISO 8601). |

### EventListMeta

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `per_page` | `integer` | Số dòng mỗi trang. |
| `next_after` | `integer \| null` | Id của sự kiện cuối cùng trong trang này; gửi nó dưới dạng after= để đọc tiếp. Null khi trang rỗng. |
| `has_more` | `boolean` | Còn sự kiện sẵn sàng để đọc ngay hay không. |

### ProductAttributeGroup

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `group_id` | `string` | Id nhóm thuộc tính; giỏ hàng nhận lựa chọn dưới dạng options[group_id]. |
| `name` | `string` | Tên nhóm, vd Màu sắc. |
| `required` | `boolean` | Luôn là true: phải chọn mọi nhóm. |
| `options` | danh sách `object` | Các lựa chọn của nhóm: {value, add_price}; add_price là phụ phí, null khi giá bị ẩn. |

### StockBatchResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id sản phẩm (id đã gửi, hoặc id tìm được theo SKU). |
| `sku` | `string` | Chỉ khi dòng chỉ định sản phẩm theo SKU. |
| `ok` | `boolean` | Dòng này đã được áp dụng hay chưa. |
| `stock` | `number` | Tồn kho sau khi thay đổi (khi ok). |
| `code` | `string` | Khi không ok: not_found, sku_not_found, sku_ambiguous, forbidden, stock_managed_by_components, stock_would_be_negative. |
| `msg` | `string` | Khi không ok: lý do dễ đọc cho người dùng. |

### OrderLine

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id dòng. |
| `product_id` | `string` | Id sản phẩm. |
| `sku` | `string \| null` | SKU tại thời điểm đặt đơn. |
| `name` | `string \| null` | Tên sản phẩm tại thời điểm đặt đơn. |
| `price` | `number` | Đơn giá. |
| `qty` | `number` | Số lượng. |
| `discount` | `number` | Giảm giá của dòng. |
| `tax` | `number` | Thuế của dòng. |
| `tax_rate` | `number` | Thuế suất tính bằng phần trăm. |
| `total_price` | `number` | Thành tiền của dòng. |
| `attribute` | `string \| null` | Các thuộc tính đã chọn dưới dạng chuỗi mã hoá JSON {group_id: 'name__price[__slug]'}, giá trị gốc của cột. |

### OrderTotal

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `code` | `string` | Mã dòng, vd subtotal, tax, shipping, discount, total. |
| `title` | `string` | Nhãn của dòng. |
| `value` | `number` | Số tiền. |
| `text` | `string` | Số tiền đã định dạng để hiển thị. |

### OrderTransaction

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id giao dịch. |
| `type` | `string` | Loại khoản thu chi (vd payment, refund). |
| `amount` | `number` | Số tiền. |
| `currency` | `string` | Tiền tệ. |
| `method` | `string \| null` | Key của phương thức thanh toán. |
| `gateway_transaction_id` | `string \| null` | Mã tham chiếu tại cổng thanh toán. |
| `paid_at` | `string \| null` | Thời điểm thanh toán (ISO 8601). |
| `created_at` | `string \| null` | Thời điểm ghi nhận (ISO 8601). |

### OrderHistory

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id dòng lịch sử. |
| `content` | `string` | Nội dung dòng lịch sử (đã bỏ HTML). |
| `order_status_id` | `integer` | Trạng thái đơn hàng tại thời điểm ghi dòng. |
| `admin_id` | `string \| null` | Quản trị viên đã ghi dòng này. |
| `customer_id` | `string \| null` | Khách hàng đã ghi dòng này. |
| `created_at` | `string \| null` | Thời điểm ghi (ISO 8601). |

---

<sub>📅 **Cập nhật lần cuối:** 2026-11-03 · ✍️ **Tác giả (Author):** GP247</sub>
