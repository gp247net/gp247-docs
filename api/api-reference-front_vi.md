> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./api-reference-front.md) · [🇯🇵 日本語](./api-reference-front_ja.md)

# Tra cứu API v2 — Cửa hàng (Front)

> Trang này được sinh từ openapi.json bằng tools/openapi-to-markdown.php — không sửa tay.

Mọi endpoint dưới `api/v2/front` — cửa hàng, tài khoản khách hàng, giỏ hàng và đặt hàng — kèm ai được gọi, tham số, ví dụ gọi và kết quả trả về.

Mới dùng API? Bắt đầu với [Bắt đầu nhanh](./api-quickstart_vi.md). Quy tắc chung cho mọi endpoint (định dạng phản hồi, phân trang, `fields`, `include`, `304`) nằm ở [Quy ước](./api-conventions_vi.md); từng mã lỗi được giải thích ở [Xử lý sự cố](./api-troubleshooting_vi.md).

## Mục lục

- [Front · Thông tin site](#front--thông-tin-site) — Những gì cửa hàng cung cấp: phiên bản API, phiên bản các gói, ngôn ngữ và tiền tệ.
- [Front · Danh mục sản phẩm](#front--danh-mục-sản-phẩm) — Sản phẩm, danh mục và thương hiệu mà cửa hàng của tên miền đang gọi hiển thị.
- [Front · Nội dung](#front--nội-dung) — Banner và trang nội dung của cửa hàng.
- [Front · Đăng nhập & phiên](#front--đăng-nhập--phiên) — Đăng nhập khách hàng, làm mới token, đăng nhập hai bước và danh sách thiết bị đang đăng nhập.
- [Front · Tài khoản khách hàng](#front--tài-khoản-khách-hàng) — Đăng ký, lấy lại mật khẩu, hồ sơ, sổ địa chỉ và xác minh email của khách hàng đang đăng nhập.
- [Front · Giỏ hàng](#front--giỏ-hàng) — Giỏ hàng của khách hàng đã đăng nhập hoặc của khách vãng lai (cart token): các dòng hàng và mã khuyến mãi.
- [Front · Đặt hàng](#front--đặt-hàng) — Chọn địa chỉ, giao hàng và thanh toán, xem tổng tiền và đặt đơn.
- [Front · Đơn hàng](#front--đơn-hàng) — Đơn hàng của khách hàng (hoặc của khách vãng lai có token truy cập đơn): danh sách, chi tiết, thanh toán và huỷ đơn.
- [Đối tượng dữ liệu](#đối-tượng-dữ-liệu)

## Front · Thông tin site

Những gì cửa hàng cung cấp: phiên bản API, phiên bản các gói, ngôn ngữ và tiền tệ.

### Mô tả API — `GET /api/v2/front/meta`

Trả về phiên bản API, các gói GP247 đã cài (chỉ tên), các ngôn ngữ đang bật, ngôn ngữ mặc định và cửa hàng phục vụ tên miền đang gọi. Công khai.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `front 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/meta" \
  -H "Accept: application/json"
```

**Phản hồi** `200` — `data`: [Meta](#meta)

```json
{
    "data": {
        "api_version": "2",
        "packages": [
            "gp247/core",
            "gp247/front",
            "gp247/shop"
        ],
        "languages": [
            "en",
            "vi"
        ],
        "locale": "en",
        "store_id": "1"
    }
}
```

## Front · Danh mục sản phẩm

Sản phẩm, danh mục và thương hiệu mà cửa hàng của tên miền đang gọi hiển thị.

### Lấy một thương hiệu — `GET /api/v2/front/brand/detail/{id}`

Trả về một thương hiệu đang bật. Công khai; thương hiệu đã tắt trả 404.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
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
curl "https://shop.example.com/api/v2/front/brand/detail/123" \
  -H "Accept: application/json"
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

### Danh sách thương hiệu — `GET /api/v2/front/brand/list`

Trả về các thương hiệu đang bật, theo từng trang. Sắp xếp theo sort hoặc name, tìm theo tên. Công khai.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/brand/list" \
  -H "Accept: application/json"
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

### Lấy một danh mục — `GET /api/v2/front/category/detail/{id}`

Trả về một danh mục đang hiển thị trên cửa hàng. Công khai.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
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
curl "https://shop.example.com/api/v2/front/category/detail/123" \
  -H "Accept: application/json"
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

### Danh sách danh mục — `GET /api/v2/front/category/list`

Trả về các danh mục cửa hàng hiển thị, theo từng trang, bằng ngôn ngữ chọn qua ?lang=. Lọc theo danh mục cha hoặc top, sắp xếp theo sort hoặc created_at, tìm theo tên. Công khai.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/category/list" \
  -H "Accept: application/json"
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

### Lấy một sản phẩm — `GET /api/v2/front/product/detail/{id}`

Trả về một sản phẩm đang bán trên cửa hàng kèm nội dung HTML, ảnh phụ, lựa chọn thuộc tính và tình trạng còn hàng. Công khai; sản phẩm bị ẩn trả 404.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |
| `include` | query | `string` | Các bản ghi liên quan cần lấy kèm, cách nhau bằng dấu phẩy (một cấp, tối đa 3): images, brand (chỉ thương hiệu đang bật), categories (chỉ danh mục đang hiển thị). Giá trị khác = 422. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/product/detail/123" \
  -H "Accept: application/json"
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

### Danh sách sản phẩm — `GET /api/v2/front/product/list`

Trả về các sản phẩm mà cửa hàng của tên miền đang gọi đang bán, theo từng trang, kèm giá bán (price_final), lựa chọn thuộc tính và tình trạng còn hàng (không bao giờ kèm số lượng tồn kho). Giá là null khi cửa hàng ẩn giá. Bộ lọc: category_id, brand_id, kind, product_type, price_min, price_max; sắp xếp theo price, created_at, sort hoặc sku; tìm theo SKU hoặc tên; fields[product] và include=images,brand,categories. Công khai và lưu tạm được.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |
| `include` | query | `string` | Các bản ghi liên quan cần lấy kèm, cách nhau bằng dấu phẩy (một cấp, tối đa 3): images, brand (chỉ thương hiệu đang bật), categories (chỉ danh mục đang hiển thị). Giá trị khác = 422. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/product/list" \
  -H "Accept: application/json"
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

## Front · Nội dung

Banner và trang nội dung của cửa hàng.

### Lấy một banner — `GET /api/v2/front/banner/detail/{id}`

Trả về một banner đang hiển thị trên cửa hàng của tên miền đang gọi. Công khai; banner bị ẩn trả 404.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
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
curl "https://shop.example.com/api/v2/front/banner/detail/123" \
  -H "Accept: application/json"
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

### Danh sách banner — `GET /api/v2/front/banner/list`

Trả về các banner mà cửa hàng của tên miền đang gọi hiển thị, theo từng trang. Công khai. Lọc theo type, sắp xếp theo sort hoặc created_at, tìm theo tên.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `front 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/banner/list" \
  -H "Accept: application/json"
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

### Lấy một trang CMS — `GET /api/v2/front/page/detail/{id}`

Trả về một trang CMS đang hiển thị kèm nội dung HTML, bằng ngôn ngữ chọn qua ?lang=. Công khai.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
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
curl "https://shop.example.com/api/v2/front/page/detail/123" \
  -H "Accept: application/json"
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

### Danh sách trang CMS — `GET /api/v2/front/page/list`

Trả về các trang CMS đang hiển thị trên cửa hàng, theo từng trang, không kèm nội dung HTML. Văn bản bằng ngôn ngữ chọn qua ?lang=. Công khai.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `front 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/page/list" \
  -H "Accept: application/json"
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

## Front · Đăng nhập & phiên

Đăng nhập khách hàng, làm mới token, đăng nhập hai bước và danh sách thiết bị đang đăng nhập.

### Khách hàng đăng nhập — `POST /api/v2/front/login`

Đăng nhập khách hàng đang hoạt động bằng e-mail và mật khẩu, trả về bearer token (kèm with_refresh=true thì có thêm refresh token). Nếu ứng dụng gửi X-Cart-Token của giỏ khách vãng lai, giỏ đó được gộp vào tài khoản và phản hồi báo cart_merge. Khi plugin yêu cầu bước thứ hai thì trả 401 challenge_required. Số lần đăng nhập sai bị giới hạn.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Có từ | `shop 3.3.0` |

**Nội dung yêu cầu**: [CustomerLoginV2](#customerloginv2)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"","password":"","remember_me":false,"with_refresh":false,"device_name":""}'
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

### Khách hàng đăng xuất — `POST /api/v2/front/logout`

Đăng xuất phiên hiện tại (cả access token và refresh token).

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.3.0` |

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontLogoutResult](#frontlogoutresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

### Hoàn tất đăng nhập MFA của khách hàng — `POST /api/v2/front/login/challenge`

Gửi ticket từ phản hồi challenge_required kèm các trường của plugin; khi thành công thì trả lời như login (kể cả gộp giỏ hàng khách vãng lai).

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [LoginChallengeAnswer](#loginchallengeanswer)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/login/challenge" \
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

### Làm mới access token của khách hàng — `POST /api/v2/front/refresh`

Đổi một refresh token lấy cặp access + refresh token mới; mỗi refresh token chỉ dùng được một lần và gửi lại lần nữa sẽ đăng xuất phiên.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [RefreshRequest](#refreshrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/refresh" \
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

### Danh sách phiên của tôi — `GET /api/v2/front/member/sessions`

Trả về các phiên API còn hiệu lực (thiết bị) của khách hàng đang đăng nhập, có đánh dấu phiên hiện tại. Không phân trang.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.4.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/member/sessions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
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

### Đăng xuất một phiên của tôi — `DELETE /api/v2/front/member/sessions/{id}`

Đăng xuất một phiên của chính khách hàng. Id phiên của người khác trả 404.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.4.0` |

**Ví dụ yêu cầu**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/member/sessions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontMemberSessionsDeleteResult](#frontmembersessionsdeleteresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found)

## Front · Tài khoản khách hàng

Đăng ký, lấy lại mật khẩu, hồ sơ, sổ địa chỉ và xác minh email của khách hàng đang đăng nhập.

### Khách hàng đang đăng nhập (cơ bản) — `GET /api/v2/front/info`

Trả về hồ sơ của khách hàng đang đăng nhập (không có trường cửa hàng hay xác minh). member/profile trả về cùng nội dung, thêm trạng thái xác minh và địa chỉ mặc định.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/info" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
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

### Đăng ký và đăng nhập — `POST /api/v2/front/register`

Tạo tài khoản khách hàng theo quy tắc trường riêng của cửa hàng (và captcha khi được bật) rồi đăng nhập luôn; trả 201 kèm hồ sơ và dữ liệu token. Giỏ khách vãng lai gửi kèm X-Cart-Token sẽ được gộp. Giới hạn theo IP.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [Register](#register)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","email":"","password":"","password_confirmation":"","with_refresh":false,"device_name":""}'
```

**Phản hồi** `201` — `data`: [FrontRegisterResult](#frontregisterresult)

```json
{
    "data": {
        "customer": {
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
            "email_verified": true,
            "email_verification_required": false,
            "default_address_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "default_address": {}
        },
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

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`challenge_required`](./api-troubleshooting_vi.md#challenge_required) · [`challenge_invalid`](./api-troubleshooting_vi.md#challenge_invalid)

### Gửi mail đặt lại mật khẩu — `POST /api/v2/front/password/forgot`

Gửi e-mail đặt lại mật khẩu. Phản hồi giống nhau dù e-mail có tài khoản hay không, nên không thể dùng để dò tài khoản. Giới hạn theo IP.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [ForgotPassword](#forgotpassword)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/password/forgot" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":""}'
```

**Phản hồi** `200` — `data`: [FrontPasswordForgotResult](#frontpasswordforgotresult)

```json
{
    "data": {
        "sent": true
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Đặt lại mật khẩu bằng token — `POST /api/v2/front/password/reset`

Đặt mật khẩu mới bằng token trong e-mail đặt lại và đăng xuất mọi phiên API của tài khoản. Không tự đăng nhập; hãy gọi login với mật khẩu mới. Token sai hoặc hết hạn trả 422 ở trường token.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [ResetPassword](#resetpassword)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/password/reset" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"","token":"","password":"","password_confirmation":""}'
```

**Phản hồi** `200` — `data`: [FrontPasswordResetResult](#frontpasswordresetresult)

```json
{
    "data": {
        "reset": true
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`too_many_attempts`](./api-troubleshooting_vi.md#too_many_attempts)

### Lấy hồ sơ của tôi — `GET /api/v2/front/member/profile`

Trả về hồ sơ của khách hàng đang đăng nhập kèm trạng thái xác minh e-mail và địa chỉ mặc định.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.4.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/member/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [CustomerProfile](#customerprofile)

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
        "email_verified": true,
        "email_verification_required": false,
        "default_address_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "default_address": {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "first_name": "An",
            "last_name": "Nguyen",
            "first_name_kana": null,
            "last_name_kana": null,
            "phone": "0901234567",
            "country": "VN",
            "postcode": "700000",
            "city": "Ho Chi Minh City",
            "district": "District 1",
            "ward": "Ben Nghe",
            "address1": "1 Le Loi",
            "address2": null,
            "address3": null,
            "is_default": true,
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00"
        }
    }
}
```

### Cập nhật hồ sơ của tôi — `PUT /api/v2/front/member/profile`

Cập nhật các trường hồ sơ mà cửa hàng đã bật và trả về hồ sơ sau khi cập nhật.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [Profile](#profile)

**Ví dụ yêu cầu**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","email":""}'
```

**Phản hồi** `200` — `data`: [CustomerProfile](#customerprofile)

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
        "email_verified": true,
        "email_verification_required": false,
        "default_address_id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "default_address": {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "first_name": "An",
            "last_name": "Nguyen",
            "first_name_kana": null,
            "last_name_kana": null,
            "phone": "0901234567",
            "country": "VN",
            "postcode": "700000",
            "city": "Ho Chi Minh City",
            "district": "District 1",
            "ward": "Ben Nghe",
            "address1": "1 Le Loi",
            "address2": null,
            "address3": null,
            "is_default": true,
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00"
        }
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Đổi mật khẩu của tôi — `PUT /api/v2/front/member/password`

Đổi mật khẩu sau khi kiểm tra mật khẩu cũ (mật khẩu cũ sai trả 422). Phiên này vẫn giữ đăng nhập; mọi phiên khác bị đăng xuất.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [ChangePassword](#changepassword)

**Ví dụ yêu cầu**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/password" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"password_old":"","password":"","password_confirmation":""}'
```

**Phản hồi** `200` — `data`: [FrontMemberPasswordUpdateResult](#frontmemberpasswordupdateresult)

```json
{
    "data": {
        "changed": true
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`too_many_attempts`](./api-troubleshooting_vi.md#too_many_attempts)

### Gửi lại e-mail xác minh — `POST /api/v2/front/member/email/verify`

Gửi lại đường link xác minh e-mail khi cửa hàng yêu cầu xác minh; sent là false khi cửa hàng không yêu cầu.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.4.0` |

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/email/verify" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontMemberEmailVerifyResult](#frontmemberemailverifyresult)

```json
{
    "data": {
        "sent": true
    }
}
```

### Danh sách địa chỉ của tôi — `GET /api/v2/front/member/address/list`

Trả về sổ địa chỉ của khách hàng đang đăng nhập, cũ nhất trước, có đánh dấu địa chỉ mặc định. Không phân trang.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.4.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/member/address/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: danh sách [CustomerAddress](#customeraddress)

```json
{
    "data": [
        {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "first_name": "An",
            "last_name": "Nguyen",
            "first_name_kana": null,
            "last_name_kana": null,
            "phone": "0901234567",
            "country": "VN",
            "postcode": "700000",
            "city": "Ho Chi Minh City",
            "district": "District 1",
            "ward": "Ben Nghe",
            "address1": "1 Le Loi",
            "address2": null,
            "address3": null,
            "is_default": true,
            "created_at": "2026-10-31T08:00:00+07:00",
            "updated_at": "2026-10-31T08:00:00+07:00"
        }
    ]
}
```

### Thêm địa chỉ — `POST /api/v2/front/member/address`

Thêm một địa chỉ theo quy tắc trường địa chỉ của cửa hàng; default: true đặt nó làm địa chỉ mặc định. Trả 201 kèm địa chỉ.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [Address](#address)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/address" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","address1":"","default":false}'
```

**Phản hồi** `201` — `data`: [CustomerAddress](#customeraddress)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "first_name": "An",
        "last_name": "Nguyen",
        "first_name_kana": null,
        "last_name_kana": null,
        "phone": "0901234567",
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "is_default": true,
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00"
    }
}
```

**Lỗi**: [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Cập nhật địa chỉ — `PUT /api/v2/front/member/address/{id}`

Cập nhật một địa chỉ của chính khách hàng. Id của khách hàng khác trả 404.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.4.0` |

**Nội dung yêu cầu**: [Address](#address)

**Ví dụ yêu cầu**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/address/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","address1":"","default":false}'
```

**Phản hồi** `200` — `data`: [CustomerAddress](#customeraddress)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "first_name": "An",
        "last_name": "Nguyen",
        "first_name_kana": null,
        "last_name_kana": null,
        "phone": "0901234567",
        "country": "VN",
        "postcode": "700000",
        "city": "Ho Chi Minh City",
        "district": "District 1",
        "ward": "Ben Nghe",
        "address1": "1 Le Loi",
        "address2": null,
        "address3": null,
        "is_default": true,
        "created_at": "2026-10-31T08:00:00+07:00",
        "updated_at": "2026-10-31T08:00:00+07:00"
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

### Xoá địa chỉ — `DELETE /api/v2/front/member/address/{id}`

Xoá một địa chỉ của chính khách hàng. Không xoá được địa chỉ mặc định (422).

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.4.0` |

**Ví dụ yêu cầu**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/member/address/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontMemberAddressDeleteResult](#frontmemberaddressdeleteresult)

```json
{
    "data": {
        "deleted": true
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed)

## Front · Giỏ hàng

Giỏ hàng của khách hàng đã đăng nhập hoặc của khách vãng lai (cart token): các dòng hàng và mã khuyến mãi.

### Lấy giỏ hàng — `GET /api/v2/front/cart`

Trả về giỏ hàng nhóm theo cửa hàng, giá do máy chủ tính, và đánh dấu từng dòng không còn mua được. Dùng được với bearer token của khách hàng, hoặc với header X-Cart-Token cho khách vãng lai khi cửa hàng cho phép khách vãng lai đặt hàng.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/cart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [Cart](#cart)

```json
{
    "data": {
        "stores": [
            {
                "store_id": "1",
                "lines": [],
                "subtotal": 360000
            }
        ],
        "line_count": 1,
        "item_count": 2,
        "currency": "VND"
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict)

### Thêm sản phẩm vào giỏ — `POST /api/v2/front/cart/items`

Thêm một sản phẩm kèm lựa chọn thuộc tính (phải chọn đủ mọi nhóm thuộc tính); cùng sản phẩm với cùng lựa chọn thì tăng số lượng của dòng đó. Trả 201 kèm toàn bộ giỏ. Khách vãng lai chưa có giỏ nhận header phản hồi X-Cart-Token mới để giữ lại.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Nội dung yêu cầu**: [FrontCartItemsRequest](#frontcartitemsrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/cart/items" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"product_id":"","qty":0,"options":{}}'
```

**Phản hồi** `201` — `data`: [Cart](#cart)

```json
{
    "data": {
        "stores": [
            {
                "store_id": "1",
                "lines": [],
                "subtotal": 360000
            }
        ],
        "line_count": 1,
        "item_count": 2,
        "currency": "VND"
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`not_found`](./api-troubleshooting_vi.md#not_found) · [`product_not_for_sale`](./api-troubleshooting_vi.md#product_not_for_sale) · [`cart_line_limit`](./api-troubleshooting_vi.md#cart_line_limit) · [`insufficient_stock`](./api-troubleshooting_vi.md#insufficient_stock)

### Đặt số lượng của một dòng — `PATCH /api/v2/front/cart/items/{rowId}`

Đặt số lượng của một dòng trong giỏ; 0 hoặc nhỏ hơn thì xoá dòng. Trả về toàn bộ giỏ. Dùng được với bearer token của khách hàng, hoặc với header X-Cart-Token cho khách vãng lai khi cửa hàng cho phép khách vãng lai đặt hàng.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `rowId` *(bắt buộc)* | path | `string` | Id của dòng trong giỏ (lấy từ giỏ hàng). |

**Nội dung yêu cầu**: [FrontCartItemsUpdateRequest](#frontcartitemsupdaterequest)

**Ví dụ yêu cầu**

```bash
curl -X PATCH "https://shop.example.com/api/v2/front/cart/items/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"qty":0}'
```

**Phản hồi** `200` — `data`: [Cart](#cart)

```json
{
    "data": {
        "stores": [
            {
                "store_id": "1",
                "lines": [],
                "subtotal": 360000
            }
        ],
        "line_count": 1,
        "item_count": 2,
        "currency": "VND"
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`not_found`](./api-troubleshooting_vi.md#not_found) · [`insufficient_stock`](./api-troubleshooting_vi.md#insufficient_stock)

### Xoá một dòng trong giỏ — `DELETE /api/v2/front/cart/items/{rowId}`

Xoá một dòng và trả về toàn bộ giỏ. Dùng được với bearer token của khách hàng, hoặc với header X-Cart-Token cho khách vãng lai khi cửa hàng cho phép khách vãng lai đặt hàng.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `rowId` *(bắt buộc)* | path | `string` | Id của dòng trong giỏ (lấy từ giỏ hàng). |

**Ví dụ yêu cầu**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/cart/items/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [Cart](#cart)

```json
{
    "data": {
        "stores": [
            {
                "store_id": "1",
                "lines": [],
                "subtotal": 360000
            }
        ],
        "line_count": 1,
        "item_count": 2,
        "currency": "VND"
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`not_found`](./api-troubleshooting_vi.md#not_found)

### Áp dụng khuyến mãi — `POST /api/v2/front/cart/promotions/{pluginKey}`

Áp dụng khuyến mãi (mã giảm giá, điểm...) qua plugin khuyến mãi có tên trong đường dẫn, kèm payload của nó. Trả về thông báo của plugin và tổng tiền mới; bị từ chối thì trả 422 promotion_refused.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `pluginKey` *(bắt buộc)* | path | `string` | Khoá của plugin khuyến mãi (lấy từ phản hồi giỏ hàng). |
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |

**Nội dung yêu cầu**: [FrontCartPromotionsRequest](#frontcartpromotionsrequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/cart/promotions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"payload":{}}'
```

**Phản hồi** `200` — `data`: [FrontCartPromotionsResult](#frontcartpromotionsresult)

```json
{
    "data": {
        "result": {
            "msg": "Coupon applied"
        },
        "totals": {
            "store_id": "1",
            "lines": [
                {
                    "code": "total",
                    "title": "Total",
                    "value": 426000,
                    "text": "426,000 VND"
                }
            ],
            "currency": "VND"
        }
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity) · [`not_found`](./api-troubleshooting_vi.md#not_found) · [`promotion_refused`](./api-troubleshooting_vi.md#promotion_refused)

### Gỡ khuyến mãi — `DELETE /api/v2/front/cart/promotions/{pluginKey}`

Gỡ khuyến mãi của plugin được chỉ định và trả về tổng tiền mới.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `pluginKey` *(bắt buộc)* | path | `string` | Khoá của plugin khuyến mãi (lấy từ phản hồi giỏ hàng). |
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |

**Ví dụ yêu cầu**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/cart/promotions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontCartPromotionsDeleteResult](#frontcartpromotionsdeleteresult)

```json
{
    "data": {
        "totals": {
            "store_id": "1",
            "lines": [
                {
                    "code": "total",
                    "title": "Total",
                    "value": 426000,
                    "text": "426,000 VND"
                }
            ],
            "currency": "VND"
        }
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity) · [`not_found`](./api-troubleshooting_vi.md#not_found)

## Front · Đặt hàng

Chọn địa chỉ, giao hàng và thanh toán, xem tổng tiền và đặt đơn.

### Lấy trạng thái đặt hàng — `GET /api/v2/front/checkout`

Trả về các lựa chọn đặt hàng đã chọn đến lúc này cho một cửa hàng trong giỏ: địa chỉ, phương thức giao hàng, phương thức thanh toán và khuyến mãi. Khi giỏ có hàng của nhiều cửa hàng, gửi store_id; chọn cửa hàng khác thì các lựa chọn đặt hàng của cửa hàng đó bắt đầu lại từ đầu.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/checkout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [CheckoutState](#checkoutstate)

```json
{
    "data": {
        "store_id": "1",
        "address": null,
        "save_address": false,
        "shipping_method": null,
        "payment_method": null,
        "promotions": []
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity)

### Đặt địa chỉ giao hàng — `PUT /api/v2/front/checkout/address`

Đặt địa chỉ giao hàng từ các trường nhập vào hoặc từ một địa chỉ đã lưu (address_id, cho khách hàng đã đăng nhập). Đổi địa chỉ sẽ xoá phương thức giao hàng đã chọn. Trả về trạng thái đặt hàng.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |

**Nội dung yêu cầu**: [FrontCheckoutAddressUpdateRequest](#frontcheckoutaddressupdaterequest)

**Ví dụ yêu cầu**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/address" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","last_name":"","first_name_kana":"","last_name_kana":"","email":"","phone":"","city":"","district":"","ward":"","address1":"","address2":"","address3":"","postcode":"","country":"","company":"","comment":"","address_id":"","save_address":false}'
```

**Phản hồi** `200` — `data`: [CheckoutState](#checkoutstate)

```json
{
    "data": {
        "store_id": "1",
        "address": null,
        "save_address": false,
        "shipping_method": null,
        "payment_method": null,
        "promotions": []
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`not_found`](./api-troubleshooting_vi.md#not_found)

### Báo giá các phương thức giao hàng — `GET /api/v2/front/checkout/shipping-methods`

Trả về các phương thức giao hàng được báo giá cho địa chỉ và giỏ hàng đang đặt. Khi cửa hàng không dùng giao hàng, required là false và rates rỗng. Phải đặt địa chỉ trước.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/checkout/shipping-methods" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontCheckoutShippingMethodsResult](#frontcheckoutshippingmethodsresult)

```json
{
    "data": {
        "required": true,
        "rates": [],
        "unquotable": [
            "ShippingExpress"
        ],
        "selected": null
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity) · [`address_required`](./api-troubleshooting_vi.md#address_required)

### Chọn phương thức giao hàng — `PUT /api/v2/front/checkout/shipping-method`

Chọn một phương thức đã báo giá theo rate_id; giá đã báo được giữ cho đơn hàng. Trả về trạng thái đặt hàng.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |

**Nội dung yêu cầu**: [FrontCheckoutShippingMethodUpdateRequest](#frontcheckoutshippingmethodupdaterequest)

**Ví dụ yêu cầu**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/shipping-method" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"rate_id":""}'
```

**Phản hồi** `200` — `data`: [CheckoutState](#checkoutstate)

```json
{
    "data": {
        "store_id": "1",
        "address": null,
        "save_address": false,
        "shipping_method": null,
        "payment_method": null,
        "promotions": []
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`method_unavailable`](./api-troubleshooting_vi.md#method_unavailable)

### Danh sách phương thức thanh toán — `GET /api/v2/front/checkout/payment-methods`

Trả về các phương thức thanh toán dùng được qua API: loại không có bước trực tuyến (type none) và cổng thanh toán có trang thanh toán riêng (type hosted). Phương thức chỉ chạy trên website thì bị bỏ ra.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/checkout/payment-methods" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontCheckoutPaymentMethodsResult](#frontcheckoutpaymentmethodsresult)

```json
{
    "data": {
        "required": true,
        "methods": [],
        "selected": null
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity)

### Chọn phương thức thanh toán — `PUT /api/v2/front/checkout/payment-method`

Chọn một phương thức thanh toán theo key trong số các phương thức dùng được. Trả về trạng thái đặt hàng.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |

**Nội dung yêu cầu**: [FrontCheckoutPaymentMethodUpdateRequest](#frontcheckoutpaymentmethodupdaterequest)

**Ví dụ yêu cầu**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/payment-method" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"key":""}'
```

**Phản hồi** `200` — `data`: [CheckoutState](#checkoutstate)

```json
{
    "data": {
        "store_id": "1",
        "address": null,
        "save_address": false,
        "shipping_method": null,
        "payment_method": null,
        "promotions": []
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting_vi.md#validation_failed) · [`method_unavailable`](./api-troubleshooting_vi.md#method_unavailable)

### Lấy tổng tiền đặt hàng — `GET /api/v2/front/checkout/totals`

Trả về các dòng tổng tiền sẽ tính (tạm tính, phí giao hàng, thuế, giảm giá, tổng cộng). Các con số này được lưu lại và so lại khi đặt đơn.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `store_id` | query | `string` | Cửa hàng cần đặt hàng khi giỏ có hàng của nhiều cửa hàng |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/checkout/totals" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [CheckoutTotals](#checkouttotals)

```json
{
    "data": {
        "store_id": "1",
        "lines": [
            {
                "code": "total",
                "title": "Total",
                "value": 426000,
                "text": "426,000 VND"
            }
        ],
        "currency": "VND"
    }
}
```

**Lỗi**: [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity)

### Đặt đơn — `POST /api/v2/front/checkout/place`

Đặt đơn cho cửa hàng đang đặt hàng, một lần cho mỗi header Idempotency-Key: gửi lại cùng khoá sẽ trả về đơn đã đặt (200 thay vì 201). Đặt đơn qua API bị tắt cho đến khi chủ site bật (403 api_checkout_disabled). Khi giá đã thay đổi kể từ lúc hiện tổng tiền, 409 price_changed kèm tổng tiền mới; hiện tổng tiền mới rồi đặt lại. Khách vãng lai nhận access_token để xem hoặc huỷ đơn sau này; phương thức thanh toán dạng hosted trả về URL của trang thanh toán.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer), hoặc cart token của giỏ khách vãng lai. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `Idempotency-Key` *(bắt buộc)* | header | `string` | Khoá duy nhất bạn tự chọn cho mỗi thay đổi (vd một UUID); gửi lại cùng yêu cầu với cùng khoá sẽ không áp dụng hai lần. |

**Nội dung yêu cầu**: [FrontCheckoutPlaceRequest](#frontcheckoutplacerequest)

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/checkout/place" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"store_id":"","return_url":""}'
```

**Phản hồi** `201` — `data`: [FrontCheckoutPlaceResult](#frontcheckoutplaceresult)

```json
{
    "data": {
        "order": {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "store_id": "1",
            "status": 1,
            "payment_status": 1,
            "total": 426000,
            "balance": 426000,
            "currency": "VND"
        },
        "access_token": "oat_8f3c...",
        "payment": {
            "type": "hosted",
            "url": "https://pay.example.com/checkout/abc"
        }
    }
}
```

**Lỗi**: [`api_checkout_disabled`](./api-troubleshooting_vi.md#api_checkout_disabled) · [`idempotency_key_required`](./api-troubleshooting_vi.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_vi.md#idempotency_key_reused) · [`login_required`](./api-troubleshooting_vi.md#login_required) · [`cart_conflict`](./api-troubleshooting_vi.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_vi.md#cart_empty) · [`store_required`](./api-troubleshooting_vi.md#store_required) · [`invalid_quantity`](./api-troubleshooting_vi.md#invalid_quantity) · [`address_required`](./api-troubleshooting_vi.md#address_required) · [`shipping_method_required`](./api-troubleshooting_vi.md#shipping_method_required) · [`payment_method_required`](./api-troubleshooting_vi.md#payment_method_required) · [`method_unavailable`](./api-troubleshooting_vi.md#method_unavailable) · [`minimum_not_met`](./api-troubleshooting_vi.md#minimum_not_met) · [`price_changed`](./api-troubleshooting_vi.md#price_changed) · [`create_failed`](./api-troubleshooting_vi.md#create_failed) · [`token_missing`](./api-troubleshooting_vi.md#token_missing) · [`input_empty`](./api-troubleshooting_vi.md#input_empty) · [`shipping_unquotable`](./api-troubleshooting_vi.md#shipping_unquotable)

## Front · Đơn hàng

Đơn hàng của khách hàng (hoặc của khách vãng lai có token truy cập đơn): danh sách, chi tiết, thanh toán và huỷ đơn.

### Lấy một đơn hàng của tôi — `GET /api/v2/front/member/order/detail/{id}`

Trả về một đơn hàng của khách hàng đang đăng nhập kèm các dòng hàng và tổng tiền. Đơn của khách hàng khác trả 404.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
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
curl "https://shop.example.com/api/v2/front/member/order/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
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

### Danh sách đơn hàng của tôi — `GET /api/v2/front/member/order/list`

Trả về đơn hàng của chính khách hàng đang đăng nhập, theo từng trang, kèm các dòng hàng và tổng tiền nhưng không có dữ liệu yêu cầu dành cho quản trị. Lọc theo status, sắp xếp theo created_at hoặc total; nhận fields[order].

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.3.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `page[number]` | query | `integer` | Trang cần đọc, bắt đầu từ 1. |
| `page[size]` | query | `integer` | Số dòng mỗi trang (mặc định 20, tối đa 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — tên được phép theo từng loại dữ liệu, xem api-introduction mục 8; tên không có = 422 |
| `sort` | query | `string` | &lt;name&gt; tăng dần, -&lt;name&gt; giảm dần; tên không có = 422 |
| `q` | query | `string` | Từ khoá tìm kiếm |
| `lang` | query | `string` | Ngôn ngữ của tên/mô tả |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b chỉ giữ các trường đó (và id) của bản ghi và của các bản ghi lấy kèm. Kiểu hoặc trường không có = 422. Không gửi thì nội dung giữ nguyên. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/member/order/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
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

### Trạng thái thanh toán đơn của tôi — `GET /api/v2/front/member/orders/{id}/payment`

Trả về trạng thái thanh toán và số tiền của một đơn hàng của khách hàng đang đăng nhập, theo xác nhận của cổng thanh toán. Đơn của khách hàng khác trả 404.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/member/orders/123/payment" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontMemberOrdersPaymentResult](#frontmemberorderspaymentresult)

```json
{
    "data": {
        "order": {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "store_id": "1",
            "status": 1,
            "payment_status": 2,
            "total": 426000,
            "received": 426000,
            "balance": 0,
            "currency": "VND"
        },
        "payment": {
            "type": "hosted",
            "status": "paid"
        }
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found)

### Trạng thái thanh toán đơn của khách vãng lai — `GET /api/v2/front/orders/{id}/payment`

Trả về trạng thái thanh toán của đơn khách vãng lai, mở bằng token truy cập đơn trong header X-Order-Access (lấy từ checkout/place). Token sai trả cùng lỗi 404 như đơn không tồn tại.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Lưu tạm (cache) | `ETag` — gửi `If-None-Match` để nhận `304` khi không có gì thay đổi. |
| Có từ | `shop 3.5.0` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `X-Order-Access` *(bắt buộc)* | header | `string` | access_token nhận được khi khách vãng lai đặt đơn |
| `If-None-Match` | header | `string` | ETag của lần trả lời trước; trả 304 với nội dung rỗng khi không có gì thay đổi. Có từ core 3.8.0. |

**Ví dụ yêu cầu**

```bash
curl "https://shop.example.com/api/v2/front/orders/123/payment" \
  -H "Accept: application/json"
```

**Phản hồi** `200` — `data`: [FrontOrdersPaymentResult](#frontorderspaymentresult)

```json
{
    "data": {
        "order": {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "store_id": "1",
            "status": 1,
            "payment_status": 2,
            "total": 426000,
            "received": 426000,
            "balance": 0,
            "currency": "VND"
        },
        "payment": {
            "type": "hosted",
            "status": "paid"
        }
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found)

### Huỷ đơn chưa thanh toán của tôi — `POST /api/v2/front/member/orders/{id}/cancel`

Huỷ một đơn chưa thanh toán của khách hàng đang đăng nhập, đặt qua API và chưa được xử lý; tồn kho được trả lại. Trả về trạng thái thanh toán mới. Đơn khác trả 409 order_not_cancellable.

| | |
|---|---|
| Đăng nhập | Token của khách hàng (Bearer). |
| Có từ | `shop 3.5.1` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/orders/123/cancel" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Phản hồi** `200` — `data`: [FrontMemberOrdersCancelResult](#frontmemberorderscancelresult)

```json
{
    "data": {
        "order": {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "store_id": "1",
            "status": 1,
            "payment_status": 2,
            "total": 426000,
            "received": 426000,
            "balance": 0,
            "currency": "VND"
        },
        "payment": {
            "type": "hosted",
            "status": "paid"
        }
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`order_not_cancellable`](./api-troubleshooting_vi.md#order_not_cancellable)

### Huỷ đơn chưa thanh toán của khách vãng lai — `POST /api/v2/front/orders/{id}/cancel`

Huỷ một đơn chưa thanh toán của khách vãng lai đặt qua API, mở bằng token X-Order-Access; tồn kho được trả lại. Trả về trạng thái thanh toán mới.

| | |
|---|---|
| Đăng nhập | Không cần — công khai. |
| Có từ | `shop 3.5.1` |

**Tham số**

| Tên | Vị trí | Kiểu | Ý nghĩa |
|---|---|---|---|
| `id` *(bắt buộc)* | path | `string` | Id của bản ghi (UUID). |
| `X-Order-Access` *(bắt buộc)* | header | `string` | access_token nhận được khi khách vãng lai đặt đơn |

**Ví dụ yêu cầu**

```bash
curl -X POST "https://shop.example.com/api/v2/front/orders/123/cancel" \
  -H "Accept: application/json"
```

**Phản hồi** `200` — `data`: [FrontOrdersCancelResult](#frontorderscancelresult)

```json
{
    "data": {
        "order": {
            "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
            "store_id": "1",
            "status": 1,
            "payment_status": 2,
            "total": 426000,
            "received": 426000,
            "balance": 0,
            "currency": "VND"
        },
        "payment": {
            "type": "hosted",
            "status": "paid"
        }
    }
}
```

**Lỗi**: [`not_found`](./api-troubleshooting_vi.md#not_found) · [`order_not_cancellable`](./api-troubleshooting_vi.md#order_not_cancellable)

## Đối tượng dữ liệu

Các trường của đối tượng mà các endpoint ở trên gửi và trả về. Ngày giờ là chuỗi ISO 8601; tiền là số theo tiền tệ của bản ghi.

### Meta

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `api_version` | `string` | Luôn là '2'. |
| `packages` | danh sách `string` | Các gói GP247 đã cài (chỉ tên, không có phiên bản). |
| `languages` | danh sách `string` | Mã các ngôn ngữ đang bật. |
| `locale` | `string` | Ngôn ngữ mặc định của site. |
| `store_id` | `string` | Cửa hàng phục vụ tên miền đang gọi. |

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

### CustomerLoginV2

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `email` | `string` (email) | Email của tài khoản khách hàng. *(bắt buộc)* |
| `password` | `string` | Mật khẩu của tài khoản. *(bắt buộc)* |
| `remember_me` | `boolean` | Token sống lâu hơn (giống "ghi nhớ đăng nhập" trên website). |
| `with_refresh` | `boolean` | Cấp thêm refresh token xoay vòng; khi đó access token chỉ sống 60 phút |
| `device_name` | `string` | Hiện trong danh sách phiên |

### FrontLogoutResult

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

### FrontMemberSessionsDeleteResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `revoked` | `boolean` | Luôn là true. |

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

### FrontRegisterResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `customer` | [CustomerProfile](#customerprofile) | Tài khoản mới. |
| `access_token` | `string` | Bearer token để gửi dưới dạng Authorization: Bearer &lt;token&gt;. |
| `token_type` | `string` | Luôn là Bearer. |
| `scopes` | danh sách `string` | Các quyền (ability) của token. |
| `expires_at` | `string \| null` | Thời điểm access token hết hạn, dạng 'Y-m-d H:i:s' theo múi giờ của site (không phải ISO 8601). |
| `expires_in` | `integer \| null` | Số giây còn lại trước khi access token hết hạn. |
| `session_id` | `string` | Id phiên API; có khi site đã chạy gp247:update (đã có bảng phiên). |
| `refresh_token` | `string` | Chỉ khi with_refresh=true: refresh token dùng một lần, chỉ hiện một lần. |
| `refresh_expires_at` | `string` | Chỉ khi with_refresh=true: thời điểm refresh token hết hạn, dạng 'Y-m-d H:i:s'. |
| `cart_merge` | `object` | Chỉ ở đăng nhập khách hàng, khi có gửi giỏ khách vãng lai qua X-Cart-Token: số dòng {merged, dropped}. |

### Register

Các trường khách hàng khác theo cấu hình của cửa hàng (last_name, phone, address1…, trường tuỳ biến, trường captcha khi được bật); with_refresh, device_name như ở login

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `first_name` | `string` | Tên. *(bắt buộc)* |
| `email` | `string` (email) | Email; cũng là tên đăng nhập. |
| `password` | `string` | Mật khẩu (áp dụng quy tắc mật khẩu của site). *(bắt buộc)* |
| `password_confirmation` | `string` | Nhập lại mật khẩu. *(bắt buộc)* |
| `with_refresh` | `boolean` | Trả thêm refresh token. |
| `device_name` | `string` | Tên hiện trong danh sách thiết bị đang đăng nhập. |

### FrontPasswordForgotResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `sent` | `boolean` | Luôn là true. |

### ForgotPassword

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `email` | `string` (email) | Email của tài khoản; phản hồi giống nhau dù tài khoản có tồn tại hay không. *(bắt buộc)* |

### FrontPasswordResetResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `reset` | `boolean` | Luôn là true. |

### ResetPassword

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `email` | `string` (email) | Email của tài khoản. *(bắt buộc)* |
| `token` | `string` | Token trong email đặt lại mật khẩu. *(bắt buộc)* |
| `password` | `string` | Mật khẩu mới. *(bắt buộc)* |
| `password_confirmation` | `string` | Nhập lại mật khẩu. *(bắt buộc)* |

### CustomerProfile

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
| `email_verified` | `boolean` | Địa chỉ e-mail đã được xác minh hay chưa. |
| `email_verification_required` | `boolean` | Cửa hàng có yêu cầu xác minh e-mail hay không. |
| `default_address_id` | `string \| null` | Id của địa chỉ mặc định. |
| `default_address` | [CustomerAddress](#customeraddress) | Địa chỉ mặc định (dạng CustomerAddress), hoặc null. |

### Profile

Các trường khách hàng theo cấu hình của cửa hàng; status và password bị bỏ qua

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `first_name` | `string` | Tên. |
| `email` | `string` (email) | Email; đổi email có thể cần xác minh địa chỉ mới. |

### FrontMemberPasswordUpdateResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `changed` | `boolean` | Luôn là true. |

### ChangePassword

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `password_old` | `string` | Mật khẩu hiện tại. *(bắt buộc)* |
| `password` | `string` | Mật khẩu mới. *(bắt buộc)* |
| `password_confirmation` | `string` | Nhập lại mật khẩu. *(bắt buộc)* |

### FrontMemberEmailVerifyResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `sent` | `boolean` | True khi đã gửi mail (cửa hàng yêu cầu xác minh). |

### CustomerAddress

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id địa chỉ. |
| `first_name` | `string \| null` | Tên. |
| `last_name` | `string \| null` | Họ. |
| `first_name_kana` | `string \| null` | Tên viết kana. |
| `last_name_kana` | `string \| null` | Họ viết kana. |
| `phone` | `string \| null` | Điện thoại. |
| `country` | `string \| null` | Mã quốc gia. |
| `postcode` | `string \| null` | Mã bưu chính. |
| `city` | `string \| null` | Thành phố. |
| `district` | `string \| null` | Quận/huyện. |
| `ward` | `string \| null` | Phường/xã. |
| `address1` | `string \| null` | Địa chỉ dòng 1. |
| `address2` | `string \| null` | Địa chỉ dòng 2. |
| `address3` | `string \| null` | Địa chỉ dòng 3. |
| `is_default` | `boolean` | True với địa chỉ mặc định của khách hàng. |
| `created_at` | `string \| null` | Thời điểm tạo (ISO 8601). |
| `updated_at` | `string \| null` | Thời điểm thay đổi gần nhất (ISO 8601). |

### Address

Các trường địa chỉ khác theo cấu hình của cửa hàng; "default": true đặt nó làm địa chỉ mặc định

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `first_name` | `string` | Tên người nhận. *(bắt buộc)* |
| `address1` | `string` | Dòng địa chỉ thứ nhất. *(bắt buộc)* |
| `default` | `boolean` | Đặt làm địa chỉ mặc định. |

### FrontMemberAddressDeleteResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `deleted` | `boolean` | Luôn là true. |

### Cart

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `stores` | danh sách `object` | Các dòng trong giỏ nhóm theo cửa hàng: {store_id, lines: [CartLine], subtotal}; subtotal chỉ cộng các dòng còn mua được. |
| `line_count` | `integer` | Số dòng. |
| `item_count` | `number` | Tổng số lượng của mọi dòng. |
| `currency` | `string` | Tiền tệ hiển thị của giá. |

### FrontCartItemsRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `product_id` | `string` | Id của sản phẩm. *(bắt buộc)* |
| `qty` | `number` | Số lượng cần thêm. *(bắt buộc)* |
| `options` | `object` | id nhóm thuộc tính =&gt; tên lựa chọn |

### FrontCartItemsUpdateRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `qty` | `number` | Số lượng mới của dòng. *(bắt buộc)* |

### FrontCartPromotionsResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `result` | `object` | {msg}: thông báo của plugin khuyến mãi. |
| `totals` | [CheckoutTotals](#checkouttotals) | Tổng tiền mới (dạng CheckoutTotals). |

### FrontCartPromotionsRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `payload` | `object` | Theo dữ liệu plugin cần, vd {"code": "SUMMER"} |

### FrontCartPromotionsDeleteResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `totals` | [CheckoutTotals](#checkouttotals) | Tổng tiền mới (dạng CheckoutTotals). |

### CheckoutState

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `store_id` | `string` | Cửa hàng đang đặt hàng. |
| `address` | `object \| null` | Địa chỉ giao hàng đã đặt đến lúc này: first_name, last_name, first_name_kana, last_name_kana, email, phone, city, district, ward, address1, address2, address3, postcode, country, company, comment (tất cả là chuỗi). |
| `save_address` | `boolean` | Địa chỉ có được lưu vào sổ địa chỉ của khách hàng hay không. |
| `shipping_method` | `object \| null` | Phương thức giao hàng đã chọn (dạng ShippingRate, hoặc {plugin_key} khi không giữ báo giá); null khi chưa chọn. |
| `payment_method` | `string \| null` | Key của plugin thanh toán đã chọn. |
| `promotions` | danh sách `string` | Key của các plugin khuyến mãi đã áp dụng. |

### FrontCheckoutAddressUpdateRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `first_name` | `string` | Tên người nhận. |
| `last_name` | `string` | Họ người nhận. |
| `first_name_kana` | `string` | Tên viết kana (cửa hàng Nhật Bản). |
| `last_name_kana` | `string` | Họ viết kana (cửa hàng Nhật Bản). |
| `email` | `string` | Email nhận xác nhận đơn hàng. |
| `phone` | `string` | Số điện thoại. |
| `city` | `string` | Thành phố hoặc tỉnh. |
| `district` | `string` | Quận/huyện. |
| `ward` | `string` | Phường/xã. |
| `address1` | `string` | Dòng địa chỉ thứ nhất. |
| `address2` | `string` | Dòng địa chỉ thứ hai. |
| `address3` | `string` | Dòng địa chỉ thứ ba. |
| `postcode` | `string` | Mã bưu chính. |
| `country` | `string` | Mã quốc gia (ISO 3166-1 alpha-2, vd VN). |
| `company` | `string` | Tên công ty. |
| `comment` | `string` | Ghi chú cho cửa hàng. |
| `address_id` | `string` | Một trong các địa chỉ đã lưu của khách hàng |
| `save_address` | `boolean` | Khách hàng đã đăng nhập: lưu luôn vào sổ địa chỉ. |

### FrontCheckoutShippingMethodsResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `required` | `boolean` | Cửa hàng có yêu cầu chọn phương thức giao hàng hay không. |
| `rates` | danh sách [ShippingRate](#shippingrate) | Các phương thức đã báo giá. |
| `unquotable` | danh sách `string` | Key của các plugin giao hàng không báo giá được cho kiện hàng này. |
| `selected` | `string \| null` | Id của phương thức đã chọn, khi còn hợp lệ với địa chỉ và giỏ hàng này. |

### FrontCheckoutShippingMethodUpdateRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `rate_id` | `string` | Id của một phương thức giao hàng lấy từ checkout/shipping-methods. *(bắt buộc)* |

### FrontCheckoutPaymentMethodsResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `required` | `boolean` | Cửa hàng có yêu cầu phương thức thanh toán hay không. |
| `methods` | danh sách [PaymentMethod](#paymentmethod) | Các phương thức dùng được. |
| `selected` | `string \| null` | Key của phương thức đã chọn. |

### FrontCheckoutPaymentMethodUpdateRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `key` | `string` | Key của một phương thức thanh toán lấy từ checkout/payment-methods. *(bắt buộc)* |

### CheckoutTotals

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `store_id` | `string` | Cửa hàng đang đặt hàng. |
| `lines` | danh sách [OrderTotal](#ordertotal) | Các dòng tổng tiền (cùng dạng với OrderTotal): code, title, value, text. |
| `currency` | `string` | Tiền tệ hiển thị. |

### FrontCheckoutPlaceRequest

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `store_id` | `string` | Cửa hàng có các dòng cần đặt (một giỏ có thể chứa hàng của nhiều cửa hàng). |
| `return_url` | `string` | Nơi người trả tiền quay về sau cổng thanh toán; chỉ dùng khi nằm trong danh sách được phép của site (GP247_PAYMENT_RETURN_URLS) |

### FrontCheckoutPlaceResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, balance, currency} của đơn vừa đặt. |
| `access_token` | `string` | Chỉ khách vãng lai: token truy cập đơn, gửi dưới dạng X-Order-Access ở orders/{id}/payment và orders/{id}/cancel. |
| `payment` | `object` | {type: none} hoặc {type: hosted, url}: url của trang cổng thanh toán, null khi cổng thanh toán không khởi động được (thử lại với cùng khoá) hoặc không có gì phải trả. |

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

### FrontMemberOrdersPaymentResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; không có địa chỉ, không có các dòng hàng. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} theo ghi nhận từ cổng thanh toán. |

### FrontOrdersPaymentResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; không có địa chỉ, không có các dòng hàng. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} theo ghi nhận từ cổng thanh toán. |

### FrontMemberOrdersCancelResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; không có địa chỉ, không có các dòng hàng. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} theo ghi nhận từ cổng thanh toán. |

### FrontOrdersCancelResult

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; không có địa chỉ, không có các dòng hàng. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} theo ghi nhận từ cổng thanh toán. |

### ProductAttributeGroup

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `group_id` | `string` | Id nhóm thuộc tính; giỏ hàng nhận lựa chọn dưới dạng options[group_id]. |
| `name` | `string` | Tên nhóm, vd Màu sắc. |
| `required` | `boolean` | Luôn là true: phải chọn mọi nhóm. |
| `options` | danh sách `object` | Các lựa chọn của nhóm: {value, add_price}; add_price là phụ phí, null khi giá bị ẩn. |

### ShippingRate

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `id` | `string` | Id báo giá để gửi dưới dạng rate_id. |
| `plugin_key` | `string` | Key của plugin giao hàng. |
| `service_code` | `string` | Mã dịch vụ bên trong plugin. |
| `title` | `string` | Nhãn để hiển thị. |
| `amount` | `number` | Giá theo tiền tệ hiển thị. |
| `currency` | `string` | Tiền tệ hiển thị. |
| `eta_min_days` | `integer \| null` | Thời gian giao nhanh nhất, tính bằng ngày. |
| `eta_max_days` | `integer \| null` | Thời gian giao chậm nhất, tính bằng ngày. |

### PaymentMethod

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `key` | `string` | Key của plugin thanh toán để gửi dưới dạng key. |
| `title` | `string` | Nhãn. |
| `note` | `string` | Ghi chú thêm của phương thức. |
| `type` | `string` | none = không có bước thanh toán trực tuyến; hosted = người trả tiền được chuyển tới trang của cổng thanh toán. |

### OrderTotal

| Trường | Kiểu | Ý nghĩa |
|---|---|---|
| `code` | `string` | Mã dòng, vd subtotal, tax, shipping, discount, total. |
| `title` | `string` | Nhãn của dòng. |
| `value` | `number` | Số tiền. |
| `text` | `string` | Số tiền đã định dạng để hiển thị. |

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
