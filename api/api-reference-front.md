> 🌐 **Language:** [🇻🇳 Tiếng Việt](./api-reference-front_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./api-reference-front_ja.md)

# API v2 reference — Storefront (Front)

> This page is generated from openapi.json by tools/openapi-to-markdown.php — do not edit it by hand.

Every endpoint under `api/v2/front` — the storefront, customer accounts, cart and checkout — with who may call it, its parameters, an example call and what it returns.

New to the API? Start with [Quick start](./api-quickstart.md). Rules shared by every endpoint (response format, paging, `fields`, `include`, `304`) are in [Conventions](./api-conventions.md); each error code is explained in [Troubleshooting](./api-troubleshooting.md).

## Contents

- [Front · Site info](#front--site-info) — What the storefront offers: API version, package versions, languages and currencies.
- [Front · Catalog](#front--catalog) — Products, categories and brands the storefront of the calling domain shows.
- [Front · Content](#front--content) — Banners and content pages of the storefront.
- [Front · Sign-in & sessions](#front--sign-in--sessions) — Customer sign-in, token refresh, two-step sign-in and the list of signed-in devices.
- [Front · Customer account](#front--customer-account) — Registration, password recovery, profile, address book and email verification of the signed-in customer.
- [Front · Cart](#front--cart) — The cart of a signed-in customer or of a guest (cart token): lines and promotion codes.
- [Front · Checkout](#front--checkout) — Address, delivery and payment choices, totals and placing the order.
- [Front · Orders](#front--orders) — Orders of the customer (or of a guest with the order access token): list, detail, payment and cancellation.
- [Data objects](#data-objects)

## Front · Site info

What the storefront offers: API version, package versions, languages and currencies.

### API description — `GET /api/v2/front/meta`

Returns the API version, the installed GP247 packages (names only), the active languages, the default language and the store that answers for the calling domain. Public.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `front 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/meta" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [Meta](#meta)

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

## Front · Catalog

Products, categories and brands the storefront of the calling domain shows.

### Get one brand — `GET /api/v2/front/brand/detail/{id}`

Returns one enabled brand. Public; a disabled brand answers 404.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/brand/detail/123" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [Brand](#brand)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

### List brands — `GET /api/v2/front/brand/list`

Returns the enabled brands, page by page. Sort by sort or name, search by name. Public.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/brand/list" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: list of [Brand](#brand) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Get one category — `GET /api/v2/front/category/detail/{id}`

Returns one category visible on the storefront. Public.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/category/detail/123" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [Category](#category)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

### List categories — `GET /api/v2/front/category/list`

Returns the categories the storefront shows, page by page, in the language given by ?lang=. Filter by parent or top, sort by sort or created_at, search by name. Public.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/category/list" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: list of [Category](#category) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Get one product — `GET /api/v2/front/product/detail/{id}`

Returns one product sold on the storefront with its HTML body, extra images, attribute choices and stock status. Public; a hidden product answers 404.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |
| `include` | query | `string` | Comma-separated related records to embed (one level, at most 3): images, brand (enabled brands only), categories (visible categories only). Anything else = 422. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/product/detail/123" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [Product](#product)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

### List products — `GET /api/v2/front/product/list`

Returns the products the storefront of the calling domain sells, page by page, with the price it charges (price_final), attribute choices and stock status (never the stock count). Prices are null when the store hides them. Filters: category_id, brand_id, kind, product_type, price_min, price_max; sort by price, created_at, sort or sku; search by SKU or name; fields[product] and include=images,brand,categories. Public and cacheable.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |
| `include` | query | `string` | Comma-separated related records to embed (one level, at most 3): images, brand (enabled brands only), categories (visible categories only). Anything else = 422. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/product/list" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: list of [Product](#product) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

## Front · Content

Banners and content pages of the storefront.

### Get one banner — `GET /api/v2/front/banner/detail/{id}`

Returns one banner visible on the storefront of the calling domain. Public; a hidden banner answers 404.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `front 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/banner/detail/123" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [Banner](#banner)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found)

### List banners — `GET /api/v2/front/banner/list`

Returns the banners the storefront of the calling domain shows, page by page. Public. Filter by type, sort by sort or created_at, search by name.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `front 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/banner/list" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: list of [Banner](#banner) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Get one CMS page — `GET /api/v2/front/page/detail/{id}`

Returns one visible CMS page with its HTML body, in the language given by ?lang=. Public.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `front 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/page/detail/123" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [Page](#page)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found)

### List CMS pages — `GET /api/v2/front/page/list`

Returns the CMS pages visible on the storefront, page by page, without the HTML body. Text in the language given by ?lang=. Public.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `front 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/page/list" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: list of [Page](#page) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

## Front · Sign-in & sessions

Customer sign-in, token refresh, two-step sign-in and the list of signed-in devices.

### Customer sign-in — `POST /api/v2/front/login`

Signs an active customer in with e-mail and password and returns a bearer token (with with_refresh=true also a refresh token). If the app sends the guest cart's X-Cart-Token, that cart is merged into the account and the answer reports cart_merge. A plugin second step answers 401 challenge_required. Failed attempts are limited.

| | |
|---|---|
| Sign-in | None — public. |
| Since | `shop 3.3.0` |

**Request body**: [CustomerLoginV2](#customerloginv2)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"","password":"","remember_me":false,"with_refresh":false,"device_name":""}'
```

**Response** `200` — `data`: [SessionToken](#sessiontoken)

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`unauthenticated`](./api-troubleshooting.md#unauthenticated) · [`too_many_attempts`](./api-troubleshooting.md#too_many_attempts) · [`challenge_required`](./api-troubleshooting.md#challenge_required) · [`challenge_invalid`](./api-troubleshooting.md#challenge_invalid)

### Customer sign-out — `POST /api/v2/front/logout`

Signs the current session out (access and refresh token).

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.3.0` |

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontLogoutResult](#frontlogoutresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

### Finish customer MFA sign-in — `POST /api/v2/front/login/challenge`

Sends the ticket from a challenge_required answer with the plugin's fields; on success answers like login (including guest cart merge).

| | |
|---|---|
| Sign-in | None — public. |
| Since | `shop 3.4.0` |

**Request body**: [LoginChallengeAnswer](#loginchallengeanswer)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/login/challenge" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"ticket":""}'
```

**Response** `200` — `data`: [SessionToken](#sessiontoken)

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`challenge_invalid`](./api-troubleshooting.md#challenge_invalid) · [`too_many_attempts`](./api-troubleshooting.md#too_many_attempts)

### Refresh customer access token — `POST /api/v2/front/refresh`

Exchanges a refresh token for a new access + refresh pair; each refresh token works once and a replay signs the session out.

| | |
|---|---|
| Sign-in | None — public. |
| Since | `shop 3.4.0` |

**Request body**: [RefreshRequest](#refreshrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/refresh" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":""}'
```

**Response** `200` — `data`: [SessionToken](#sessiontoken)

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`not_found`](./api-troubleshooting.md#not_found) · [`refresh_token_invalid`](./api-troubleshooting.md#refresh_token_invalid) · [`refresh_token_reused`](./api-troubleshooting.md#refresh_token_reused) · [`refresh_in_progress`](./api-troubleshooting.md#refresh_in_progress) · [`unauthenticated`](./api-troubleshooting.md#unauthenticated)

### List my sessions — `GET /api/v2/front/member/sessions`

Returns the live API sessions (devices) of the signed-in customer, current one flagged. Not paginated.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.4.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/member/sessions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: list of [Session](#session)

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

### Sign one of my sessions out — `DELETE /api/v2/front/member/sessions/{id}`

Signs one of the customer's own sessions out. Another owner's id answers 404.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.4.0` |

**Example request**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/member/sessions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontMemberSessionsDeleteResult](#frontmembersessionsdeleteresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

**Errors**: [`not_found`](./api-troubleshooting.md#not_found)

## Front · Customer account

Registration, password recovery, profile, address book and email verification of the signed-in customer.

### Signed-in customer (basic) — `GET /api/v2/front/info`

Returns the profile of the signed-in customer (no store or verification fields). member/profile returns the same plus verification state and default address.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/info" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [Customer](#customer)

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

### Register and sign in — `POST /api/v2/front/register`

Creates a customer account with the store's own field rules (and captcha when enabled) and signs it in; answers 201 with the profile and the token data. A guest cart sent with X-Cart-Token is merged. Limited per IP.

| | |
|---|---|
| Sign-in | None — public. |
| Since | `shop 3.4.0` |

**Request body**: [Register](#register)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","email":"","password":"","password_confirmation":"","with_refresh":false,"device_name":""}'
```

**Response** `201` — `data`: [FrontRegisterResult](#frontregisterresult)

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`challenge_required`](./api-troubleshooting.md#challenge_required) · [`challenge_invalid`](./api-troubleshooting.md#challenge_invalid)

### Send password-reset mail — `POST /api/v2/front/password/forgot`

Sends the password-reset e-mail. The answer is the same whether or not the e-mail has an account, so it cannot be used to find accounts. Limited per IP.

| | |
|---|---|
| Sign-in | None — public. |
| Since | `shop 3.4.0` |

**Request body**: [ForgotPassword](#forgotpassword)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/password/forgot" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":""}'
```

**Response** `200` — `data`: [FrontPasswordForgotResult](#frontpasswordforgotresult)

```json
{
    "data": {
        "sent": true
    }
}
```

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Reset password with token — `POST /api/v2/front/password/reset`

Sets a new password with the token from the reset e-mail and signs every API session of the account out. It does not sign in; call login with the new password. A wrong or expired token is a 422 on the token field.

| | |
|---|---|
| Sign-in | None — public. |
| Since | `shop 3.4.0` |

**Request body**: [ResetPassword](#resetpassword)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/password/reset" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"","token":"","password":"","password_confirmation":""}'
```

**Response** `200` — `data`: [FrontPasswordResetResult](#frontpasswordresetresult)

```json
{
    "data": {
        "reset": true
    }
}
```

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`too_many_attempts`](./api-troubleshooting.md#too_many_attempts)

### Get my profile — `GET /api/v2/front/member/profile`

Returns the signed-in customer's profile with e-mail verification state and the default address.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.4.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/member/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [CustomerProfile](#customerprofile)

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

### Update my profile — `PUT /api/v2/front/member/profile`

Updates the profile fields the store has turned on and answers the updated profile.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.4.0` |

**Request body**: [Profile](#profile)

**Example request**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","email":""}'
```

**Response** `200` — `data`: [CustomerProfile](#customerprofile)

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Change my password — `PUT /api/v2/front/member/password`

Changes the password after checking the old one (a wrong old password is a 422). This session stays signed in; every other session is signed out.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.4.0` |

**Request body**: [ChangePassword](#changepassword)

**Example request**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/password" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"password_old":"","password":"","password_confirmation":""}'
```

**Response** `200` — `data`: [FrontMemberPasswordUpdateResult](#frontmemberpasswordupdateresult)

```json
{
    "data": {
        "changed": true
    }
}
```

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`too_many_attempts`](./api-troubleshooting.md#too_many_attempts)

### Resend verification e-mail — `POST /api/v2/front/member/email/verify`

Sends the e-mail verification link again when the store requires verification; sent is false when it does not.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.4.0` |

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/email/verify" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontMemberEmailVerifyResult](#frontmemberemailverifyresult)

```json
{
    "data": {
        "sent": true
    }
}
```

### List my addresses — `GET /api/v2/front/member/address/list`

Returns the signed-in customer's address book, oldest first, with the default one flagged. Not paginated.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.4.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/member/address/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: list of [CustomerAddress](#customeraddress)

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

### Add an address — `POST /api/v2/front/member/address`

Adds an address with the store's address field rules; default: true makes it the default. Answers 201 with the address.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.4.0` |

**Request body**: [Address](#address)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/address" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","address1":"","default":false}'
```

**Response** `201` — `data`: [CustomerAddress](#customeraddress)

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Update an address — `PUT /api/v2/front/member/address/{id}`

Updates one of the customer's own addresses. Another customer's id answers 404.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.4.0` |

**Request body**: [Address](#address)

**Example request**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/address/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","address1":"","default":false}'
```

**Response** `200` — `data`: [CustomerAddress](#customeraddress)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Delete an address — `DELETE /api/v2/front/member/address/{id}`

Deletes one of the customer's own addresses. The default address cannot be deleted (422).

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.4.0` |

**Example request**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/member/address/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontMemberAddressDeleteResult](#frontmemberaddressdeleteresult)

```json
{
    "data": {
        "deleted": true
    }
}
```

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

## Front · Cart

The cart of a signed-in customer or of a guest (cart token): lines and promotion codes.

### Get the cart — `GET /api/v2/front/cart`

Returns the cart grouped by store, with prices computed by the server and each line flagged when it can no longer be bought. Works with a customer bearer token or, for a guest when the store allows guest checkout, the X-Cart-Token header.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/cart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [Cart](#cart)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict)

### Add a product to the cart — `POST /api/v2/front/cart/items`

Adds a product with its attribute choices (every attribute group must be chosen); the same product with the same choices raises that line's quantity. Answers 201 with the whole cart. A guest without a cart receives a new X-Cart-Token response header to keep.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Request body**: [FrontCartItemsRequest](#frontcartitemsrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/cart/items" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"product_id":"","qty":0,"options":{}}'
```

**Response** `201` — `data`: [Cart](#cart)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`not_found`](./api-troubleshooting.md#not_found) · [`product_not_for_sale`](./api-troubleshooting.md#product_not_for_sale) · [`cart_line_limit`](./api-troubleshooting.md#cart_line_limit) · [`insufficient_stock`](./api-troubleshooting.md#insufficient_stock)

### Set a line quantity — `PATCH /api/v2/front/cart/items/{rowId}`

Sets the quantity of a cart line; 0 or less removes the line. Answers the whole cart. Works with a customer bearer token or, for a guest when the store allows guest checkout, the X-Cart-Token header.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `rowId` *(required)* | path | `string` | Id of the cart line (from the cart). |

**Request body**: [FrontCartItemsUpdateRequest](#frontcartitemsupdaterequest)

**Example request**

```bash
curl -X PATCH "https://shop.example.com/api/v2/front/cart/items/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"qty":0}'
```

**Response** `200` — `data`: [Cart](#cart)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`not_found`](./api-troubleshooting.md#not_found) · [`insufficient_stock`](./api-troubleshooting.md#insufficient_stock)

### Remove a cart line — `DELETE /api/v2/front/cart/items/{rowId}`

Removes one line and answers the whole cart. Works with a customer bearer token or, for a guest when the store allows guest checkout, the X-Cart-Token header.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `rowId` *(required)* | path | `string` | Id of the cart line (from the cart). |

**Example request**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/cart/items/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [Cart](#cart)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`not_found`](./api-troubleshooting.md#not_found)

### Apply a promotion — `POST /api/v2/front/cart/promotions/{pluginKey}`

Applies a promotion (coupon, points...) through the promotion plugin named in the path, with its payload. Answers the plugin's message and the new totals; a refusal is 422 promotion_refused.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `pluginKey` *(required)* | path | `string` | Key of the promotion plugin (from the cart answer). |
| `store_id` | query | `string` | Store to check out when the cart holds several |

**Request body**: [FrontCartPromotionsRequest](#frontcartpromotionsrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/cart/promotions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"payload":{}}'
```

**Response** `200` — `data`: [FrontCartPromotionsResult](#frontcartpromotionsresult)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity) · [`not_found`](./api-troubleshooting.md#not_found) · [`promotion_refused`](./api-troubleshooting.md#promotion_refused)

### Remove a promotion — `DELETE /api/v2/front/cart/promotions/{pluginKey}`

Removes the promotion of the named plugin and answers the new totals.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `pluginKey` *(required)* | path | `string` | Key of the promotion plugin (from the cart answer). |
| `store_id` | query | `string` | Store to check out when the cart holds several |

**Example request**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/cart/promotions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontCartPromotionsDeleteResult](#frontcartpromotionsdeleteresult)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity) · [`not_found`](./api-troubleshooting.md#not_found)

## Front · Checkout

Address, delivery and payment choices, totals and placing the order.

### Get checkout state — `GET /api/v2/front/checkout`

Returns the checkout choices made so far for one store of the cart: address, delivery option, payment method and promotions. When the cart holds several stores, send store_id; choosing another store starts its checkout choices afresh.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `store_id` | query | `string` | Store to check out when the cart holds several |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/checkout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [CheckoutState](#checkoutstate)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity)

### Set delivery address — `PUT /api/v2/front/checkout/address`

Sets the delivery address from typed fields or from a saved address (address_id, signed-in customers). Changing the address clears the chosen delivery option. Answers the checkout state.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `store_id` | query | `string` | Store to check out when the cart holds several |

**Request body**: [FrontCheckoutAddressUpdateRequest](#frontcheckoutaddressupdaterequest)

**Example request**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/address" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","last_name":"","first_name_kana":"","last_name_kana":"","email":"","phone":"","city":"","district":"","ward":"","address1":"","address2":"","address3":"","postcode":"","country":"","company":"","comment":"","address_id":"","save_address":false}'
```

**Response** `200` — `data`: [CheckoutState](#checkoutstate)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`not_found`](./api-troubleshooting.md#not_found)

### Quote delivery options — `GET /api/v2/front/checkout/shipping-methods`

Returns the delivery options quoted for the checkout address and cart. When the store does not use shipping, required is false and rates is empty. The address must be set first.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `store_id` | query | `string` | Store to check out when the cart holds several |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/checkout/shipping-methods" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontCheckoutShippingMethodsResult](#frontcheckoutshippingmethodsresult)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity) · [`address_required`](./api-troubleshooting.md#address_required)

### Choose delivery option — `PUT /api/v2/front/checkout/shipping-method`

Chooses one quoted option by rate_id; the quoted price is kept for the order. Answers the checkout state.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `store_id` | query | `string` | Store to check out when the cart holds several |

**Request body**: [FrontCheckoutShippingMethodUpdateRequest](#frontcheckoutshippingmethodupdaterequest)

**Example request**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/shipping-method" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"rate_id":""}'
```

**Response** `200` — `data`: [CheckoutState](#checkoutstate)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`method_unavailable`](./api-troubleshooting.md#method_unavailable)

### List payment methods — `GET /api/v2/front/checkout/payment-methods`

Returns the payment methods usable through the API: those without an online step (type none) and gateways with a hosted payment page (type hosted). Methods that only work on the website are left out.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `store_id` | query | `string` | Store to check out when the cart holds several |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/checkout/payment-methods" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontCheckoutPaymentMethodsResult](#frontcheckoutpaymentmethodsresult)

```json
{
    "data": {
        "required": true,
        "methods": [],
        "selected": null
    }
}
```

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity)

### Choose payment method — `PUT /api/v2/front/checkout/payment-method`

Chooses a payment method by key among the usable ones. Answers the checkout state.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `store_id` | query | `string` | Store to check out when the cart holds several |

**Request body**: [FrontCheckoutPaymentMethodUpdateRequest](#frontcheckoutpaymentmethodupdaterequest)

**Example request**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/payment-method" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"key":""}'
```

**Response** `200` — `data`: [CheckoutState](#checkoutstate)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`method_unavailable`](./api-troubleshooting.md#method_unavailable)

### Get checkout totals — `GET /api/v2/front/checkout/totals`

Returns the total rows that will be charged (subtotal, shipping, tax, discount, total). These figures are kept and compared again when the order is placed.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `store_id` | query | `string` | Store to check out when the cart holds several |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/checkout/totals" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [CheckoutTotals](#checkouttotals)

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

**Errors**: [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity)

### Place the order — `POST /api/v2/front/checkout/place`

Places the order of the store being checked out, once per Idempotency-Key header: resending the same key answers the order already placed (200 instead of 201). Ordering through the API is off until the site owner turns it on (403 api_checkout_disabled). When prices changed since totals were shown, 409 price_changed carries the new totals; place again after showing them. A guest receives access_token to read or cancel the order later; a hosted payment method returns the URL of the payment page.

| | |
|---|---|
| Sign-in | Customer token (Bearer), or the cart token of a guest cart. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `Idempotency-Key` *(required)* | header | `string` | Unique key you choose per change (e.g. a UUID); sending the same request again with the same key does not apply it twice. |

**Request body**: [FrontCheckoutPlaceRequest](#frontcheckoutplacerequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/checkout/place" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"store_id":"","return_url":""}'
```

**Response** `201` — `data`: [FrontCheckoutPlaceResult](#frontcheckoutplaceresult)

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

**Errors**: [`api_checkout_disabled`](./api-troubleshooting.md#api_checkout_disabled) · [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`login_required`](./api-troubleshooting.md#login_required) · [`cart_conflict`](./api-troubleshooting.md#cart_conflict) · [`cart_empty`](./api-troubleshooting.md#cart_empty) · [`store_required`](./api-troubleshooting.md#store_required) · [`invalid_quantity`](./api-troubleshooting.md#invalid_quantity) · [`address_required`](./api-troubleshooting.md#address_required) · [`shipping_method_required`](./api-troubleshooting.md#shipping_method_required) · [`payment_method_required`](./api-troubleshooting.md#payment_method_required) · [`method_unavailable`](./api-troubleshooting.md#method_unavailable) · [`minimum_not_met`](./api-troubleshooting.md#minimum_not_met) · [`price_changed`](./api-troubleshooting.md#price_changed) · [`create_failed`](./api-troubleshooting.md#create_failed) · [`token_missing`](./api-troubleshooting.md#token_missing) · [`input_empty`](./api-troubleshooting.md#input_empty) · [`shipping_unquotable`](./api-troubleshooting.md#shipping_unquotable)

## Front · Orders

Orders of the customer (or of a guest with the order access token): list, detail, payment and cancellation.

### Get one of my orders — `GET /api/v2/front/member/order/detail/{id}`

Returns one order of the signed-in customer with lines and totals. Another customer's order answers 404.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/member/order/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [Order](#order)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

### List my orders — `GET /api/v2/front/member/order/list`

Returns the signed-in customer's own orders, page by page, with lines and totals but without admin request data. Filter by status, sort by created_at or total; fields[order] accepted.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/member/order/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: list of [Order](#order) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Payment status of my order — `GET /api/v2/front/member/orders/{id}/payment`

Returns the payment status and amounts of one of the signed-in customer's orders, as confirmed by the payment gateway. Another customer's order answers 404.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/member/orders/123/payment" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontMemberOrdersPaymentResult](#frontmemberorderspaymentresult)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found)

### Payment status of a guest order — `GET /api/v2/front/orders/{id}/payment`

Returns the payment status of a guest order opened by its order access token in the X-Order-Access header (from checkout/place). A wrong token answers the same 404 as a missing order.

| | |
|---|---|
| Sign-in | None — public. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.5.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `X-Order-Access` *(required)* | header | `string` | access_token returned when the guest placed the order |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/front/orders/123/payment" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [FrontOrdersPaymentResult](#frontorderspaymentresult)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found)

### Cancel my unpaid order — `POST /api/v2/front/member/orders/{id}/cancel`

Cancels one of the signed-in customer's unpaid orders placed through the API and not yet processed; its stock goes back. Answers the new payment status. Any other order is 409 order_not_cancellable.

| | |
|---|---|
| Sign-in | Customer token (Bearer). |
| Since | `shop 3.5.1` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/orders/123/cancel" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**Response** `200` — `data`: [FrontMemberOrdersCancelResult](#frontmemberorderscancelresult)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`order_not_cancellable`](./api-troubleshooting.md#order_not_cancellable)

### Cancel an unpaid guest order — `POST /api/v2/front/orders/{id}/cancel`

Cancels an unpaid guest order placed through the API, opened by its X-Order-Access token; stock goes back. Answers the new payment status.

| | |
|---|---|
| Sign-in | None — public. |
| Since | `shop 3.5.1` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `X-Order-Access` *(required)* | header | `string` | access_token returned when the guest placed the order |

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/front/orders/123/cancel" \
  -H "Accept: application/json"
```

**Response** `200` — `data`: [FrontOrdersCancelResult](#frontorderscancelresult)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`order_not_cancellable`](./api-troubleshooting.md#order_not_cancellable)

## Data objects

Fields of the objects the endpoints above send and return. Dates are ISO 8601 strings; money is a number in the currency of the record.

### Meta

| Field | Type | Meaning |
|---|---|---|
| `api_version` | `string` | Always '2'. |
| `packages` | list of `string` | Installed GP247 packages (names only, no versions). |
| `languages` | list of `string` | Codes of the active languages. |
| `locale` | `string` | Default language of the site. |
| `store_id` | `string` | Store answering for the calling domain. |

### Brand

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Brand id. |
| `name` | `string` | Brand name. |
| `alias` | `string` | URL slug. |
| `image` | `string \| null` | Logo path. |
| `url` | `string \| null` | Brand website. |
| `sort` | `integer` | Sort order. |
| `status` | `integer` | 1 = enabled. |
| `store_id` | `string \| null` | Store id. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |

### Category

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Category id. |
| `alias` | `string` | URL slug. |
| `name` | `string \| null` | Name in the requested language. |
| `keyword` | `string \| null` | SEO keywords in the requested language. |
| `description` | `string \| null` | Description in the requested language. |
| `image` | `string \| null` | Image path. |
| `parent` | `string \| null` | Parent category id. |
| `top` | `integer` | 1 = shown in the top menu. |
| `sort` | `integer` | Sort order. |
| `status` | `integer` | 1 = enabled. |
| `store_id` | `string \| null` | Store id. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |

### Product

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Product id (UUID). |
| `sku` | `string` | Stock keeping unit code. |
| `alias` | `string` | URL slug of the product. |
| `name` | `string \| null` | Product name in the requested language (falls back to the first language). |
| `keyword` | `string \| null` | SEO keywords in the requested language. |
| `description` | `string \| null` | Short description in the requested language. |
| `content` | `string \| null` | HTML body in the requested language; detail answers only (not in lists). *(detail only)* |
| `image` | `string \| null` | Main image path as stored. |
| `images` | list of `string` | Paths of the extra images; filled on detail answers or with include=images, empty array in lists otherwise. |
| `price` | `number \| null` | List price; null when the store hides prices on the storefront (admins always see it). |
| `price_final` | `number \| null` | Price the storefront charges (promotion applied); null when the store hides prices. |
| `tax_id` | `string \| null` | Tax rule id; '0' = no tax, 'auto' = the store's default tax. |
| `kind` | `integer` | Product kind: 0 single, 1 build (made of components), 2 group. |
| `product_type` | `string \| null` | Product type, e.g. physical or digital. |
| `brand_id` | `string \| null` | Brand id. |
| `category_ids` | list of `string` | Ids of the product's categories (storefront: only categories the storefront shows). |
| `minimum` | `number` | Minimum quantity per order. |
| `weight` | `number` | Weight. |
| `weight_class` | `string \| null` | Weight unit. |
| `length` | `number` | Length. |
| `width` | `number` | Width. |
| `height` | `number` | Height. |
| `length_class` | `string \| null` | Length unit. |
| `date_available` | `string \| null` | Date the product becomes available (Y-m-d, raw column value). |
| `store_id` | `string \| null` | Store that owns the product. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |
| `image_sizes` | `object` | Absolute URLs of the main image: {original, thumb} (site placeholder when there is no image). |
| `stock_status` | `string` | in_stock or out_of_stock; always in_stock when the shop does not track stock or sells out of stock. |
| `attributes` | list of [ProductAttributeGroup](#productattributegroup) | Attribute groups the buyer must choose to add the product to the cart. |
| `cost` | `number` | admin only. Cost price. *(admin only)* |
| `stock` | `number` | admin only. Units in stock. *(admin only)* |
| `sold` | `number` | admin only. Units sold. *(admin only)* |
| `status` | `integer` | admin only. 1 = enabled, 0 = disabled. *(admin only)* |
| `approve` | `integer` | admin only. Moderation flag: 1 = approved. *(admin only)* |
| `supplier_id` | `string \| null` | admin only. Supplier id. *(admin only)* |
| `view` | `integer` | admin only. Number of views. *(admin only)* |
| `brand` | [Brand](#brand) | Only with include=brand: the brand (storefront: only an enabled brand). *(only with include `brand`)* |
| `categories` | list of [Category](#category) | Only with include=categories: the categories in full. *(only with include `categories`)* |

### Banner

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Banner id. |
| `name` | `string \| null` | Banner name. |
| `image` | `string \| null` | Image path. |
| `url` | `string \| null` | Link target. |
| `target` | `string \| null` | Link target window, e.g. _self or _blank. |
| `html` | `string \| null` | Optional HTML content. |
| `type` | `string` | Banner position/type code. |
| `sort` | `integer` | Sort order. |
| `status` | `integer` | 1 = enabled. |
| `store_id` | `string \| null` | Store id. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |
| `click` | `integer` | admin only. Number of clicks. *(admin only)* |

### Page

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Page id. |
| `alias` | `string` | URL slug. |
| `image` | `string \| null` | Image path. |
| `name` | `string \| null` | Title in the requested language. |
| `keyword` | `string \| null` | SEO keywords. |
| `description` | `string \| null` | Short description. |
| `content` | `string \| null` | HTML body; detail answers only (not in lists). *(detail only)* |
| `status` | `integer` | 1 = enabled. |
| `store_id` | `string \| null` | Store id. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |

### SessionToken

| Field | Type | Meaning |
|---|---|---|
| `access_token` | `string` | Bearer token to send as Authorization: Bearer &lt;token&gt;. |
| `token_type` | `string` | Always Bearer. |
| `scopes` | list of `string` | Abilities of the token. |
| `expires_at` | `string \| null` | When the access token expires, 'Y-m-d H:i:s' in the site's timezone (not ISO 8601). |
| `expires_in` | `integer \| null` | Seconds until the access token expires. |
| `session_id` | `string` | API session id; present once the site has run gp247:update (session tables exist). |
| `refresh_token` | `string` | Only when with_refresh=true: single-use refresh token, shown once. |
| `refresh_expires_at` | `string` | Only when with_refresh=true: when the refresh token expires, 'Y-m-d H:i:s'. |
| `cart_merge` | `object` | Customer sign-in only, when an X-Cart-Token guest cart was sent: {merged, dropped} line counts. |

### CustomerLoginV2

| Field | Type | Meaning |
|---|---|---|
| `email` | `string` (email) | Email of the customer account. *(required)* |
| `password` | `string` | Password of the account. *(required)* |
| `remember_me` | `boolean` | Longer-lived token (same as "remember me" on the website). |
| `with_refresh` | `boolean` | Also issue a rotating refresh token; the access token then lives 60 minutes |
| `device_name` | `string` | Shown in the sessions list |

### FrontLogoutResult

| Field | Type | Meaning |
|---|---|---|
| `revoked` | `boolean` | Always true. |

### LoginChallengeAnswer

Plus the fields defined by the challenge plugin (e.g. code)

| Field | Type | Meaning |
|---|---|---|
| `ticket` | `string` | Ticket from the challenge_required answer of login. *(required)* |

### RefreshRequest

| Field | Type | Meaning |
|---|---|---|
| `refresh_token` | `string` | Refresh token from login or from the previous refresh; each one works once. *(required)* |

### Session

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Session id. |
| `name` | `string \| null` | Device name sent at sign-in, else the user agent. |
| `ip` | `string \| null` | IP address at sign-in. |
| `created_at` | `string \| null` | Sign-in time (ISO 8601). |
| `last_used_at` | `string \| null` | Last use (ISO 8601). |
| `refreshable` | `boolean` | Whether the session has a refresh token. |
| `refresh_expires_at` | `string \| null` | When the refresh window ends (ISO 8601). |
| `current` | `boolean` | True for the session of the calling token. |

### FrontMemberSessionsDeleteResult

| Field | Type | Meaning |
|---|---|---|
| `revoked` | `boolean` | Always true. |

### Customer

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Customer id. |
| `first_name` | `string \| null` | First name. |
| `last_name` | `string \| null` | Last name. |
| `first_name_kana` | `string \| null` | First name in kana (Japanese stores). |
| `last_name_kana` | `string \| null` | Last name in kana (Japanese stores). |
| `email` | `string` | E-mail address (sign-in name). |
| `phone` | `string \| null` | Phone number. |
| `sex` | `integer \| null` | 0 = female, 1 = male (raw column value). |
| `birthday` | `string \| null` | Birthday (Y-m-d, raw column value). |
| `company` | `string \| null` | Company. |
| `country` | `string \| null` | Country code. |
| `postcode` | `string \| null` | Postcode. |
| `city` | `string \| null` | City. |
| `district` | `string \| null` | District. |
| `ward` | `string \| null` | Ward. |
| `address1` | `string \| null` | Address line 1. |
| `address2` | `string \| null` | Address line 2. |
| `address3` | `string \| null` | Address line 3. |
| `status` | `integer` | 1 = active, 0 = locked. |
| `group` | `integer` | Customer group (raw column value). |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |
| `store_id` | `string \| null` | admin only. Store of the account. *(admin only)* |
| `email_verified_at` | `string \| null` | admin only. When the e-mail was verified, raw 'Y-m-d H:i:s' value (not ISO 8601); null if never. *(admin only)* |

### FrontRegisterResult

| Field | Type | Meaning |
|---|---|---|
| `customer` | [CustomerProfile](#customerprofile) | The new account. |
| `access_token` | `string` | Bearer token to send as Authorization: Bearer &lt;token&gt;. |
| `token_type` | `string` | Always Bearer. |
| `scopes` | list of `string` | Abilities of the token. |
| `expires_at` | `string \| null` | When the access token expires, 'Y-m-d H:i:s' in the site's timezone (not ISO 8601). |
| `expires_in` | `integer \| null` | Seconds until the access token expires. |
| `session_id` | `string` | API session id; present once the site has run gp247:update (session tables exist). |
| `refresh_token` | `string` | Only when with_refresh=true: single-use refresh token, shown once. |
| `refresh_expires_at` | `string` | Only when with_refresh=true: when the refresh token expires, 'Y-m-d H:i:s'. |
| `cart_merge` | `object` | Customer sign-in only, when an X-Cart-Token guest cart was sent: {merged, dropped} line counts. |

### Register

Other customer fields as configured for the store (last_name, phone, address1…, custom fields, captcha field when enabled); with_refresh, device_name as for login

| Field | Type | Meaning |
|---|---|---|
| `first_name` | `string` | First name. *(required)* |
| `email` | `string` (email) | Email; also the sign-in name. |
| `password` | `string` | Password (the site's password rules apply). *(required)* |
| `password_confirmation` | `string` | The same password again. *(required)* |
| `with_refresh` | `boolean` | Also return a refresh token. |
| `device_name` | `string` | Name shown in the list of signed-in devices. |

### FrontPasswordForgotResult

| Field | Type | Meaning |
|---|---|---|
| `sent` | `boolean` | Always true. |

### ForgotPassword

| Field | Type | Meaning |
|---|---|---|
| `email` | `string` (email) | Email of the account; the answer is the same whether it exists or not. *(required)* |

### FrontPasswordResetResult

| Field | Type | Meaning |
|---|---|---|
| `reset` | `boolean` | Always true. |

### ResetPassword

| Field | Type | Meaning |
|---|---|---|
| `email` | `string` (email) | Email of the account. *(required)* |
| `token` | `string` | Token from the reset email. *(required)* |
| `password` | `string` | New password. *(required)* |
| `password_confirmation` | `string` | The same password again. *(required)* |

### CustomerProfile

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Customer id. |
| `first_name` | `string \| null` | First name. |
| `last_name` | `string \| null` | Last name. |
| `first_name_kana` | `string \| null` | First name in kana (Japanese stores). |
| `last_name_kana` | `string \| null` | Last name in kana (Japanese stores). |
| `email` | `string` | E-mail address (sign-in name). |
| `phone` | `string \| null` | Phone number. |
| `sex` | `integer \| null` | 0 = female, 1 = male (raw column value). |
| `birthday` | `string \| null` | Birthday (Y-m-d, raw column value). |
| `company` | `string \| null` | Company. |
| `country` | `string \| null` | Country code. |
| `postcode` | `string \| null` | Postcode. |
| `city` | `string \| null` | City. |
| `district` | `string \| null` | District. |
| `ward` | `string \| null` | Ward. |
| `address1` | `string \| null` | Address line 1. |
| `address2` | `string \| null` | Address line 2. |
| `address3` | `string \| null` | Address line 3. |
| `status` | `integer` | 1 = active, 0 = locked. |
| `group` | `integer` | Customer group (raw column value). |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |
| `email_verified` | `boolean` | Whether the e-mail address is verified. |
| `email_verification_required` | `boolean` | Whether the store requires e-mail verification. |
| `default_address_id` | `string \| null` | Id of the default address. |
| `default_address` | [CustomerAddress](#customeraddress) | The default address (CustomerAddress shape), or null. |

### Profile

Customer fields configured for the store; status and password are ignored

| Field | Type | Meaning |
|---|---|---|
| `first_name` | `string` | First name. |
| `email` | `string` (email) | Email; changing it may require verifying the new address. |

### FrontMemberPasswordUpdateResult

| Field | Type | Meaning |
|---|---|---|
| `changed` | `boolean` | Always true. |

### ChangePassword

| Field | Type | Meaning |
|---|---|---|
| `password_old` | `string` | Current password. *(required)* |
| `password` | `string` | New password. *(required)* |
| `password_confirmation` | `string` | The same password again. *(required)* |

### FrontMemberEmailVerifyResult

| Field | Type | Meaning |
|---|---|---|
| `sent` | `boolean` | True when a mail was sent (the store requires verification). |

### CustomerAddress

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Address id. |
| `first_name` | `string \| null` | First name. |
| `last_name` | `string \| null` | Last name. |
| `first_name_kana` | `string \| null` | First name in kana. |
| `last_name_kana` | `string \| null` | Last name in kana. |
| `phone` | `string \| null` | Phone. |
| `country` | `string \| null` | Country code. |
| `postcode` | `string \| null` | Postcode. |
| `city` | `string \| null` | City. |
| `district` | `string \| null` | District. |
| `ward` | `string \| null` | Ward. |
| `address1` | `string \| null` | Address line 1. |
| `address2` | `string \| null` | Address line 2. |
| `address3` | `string \| null` | Address line 3. |
| `is_default` | `boolean` | True for the customer's default address. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |

### Address

Other address fields as configured for the store; "default": true makes it the default address

| Field | Type | Meaning |
|---|---|---|
| `first_name` | `string` | First name of the recipient. *(required)* |
| `address1` | `string` | First address line. *(required)* |
| `default` | `boolean` | Make it the default address. |

### FrontMemberAddressDeleteResult

| Field | Type | Meaning |
|---|---|---|
| `deleted` | `boolean` | Always true. |

### Cart

| Field | Type | Meaning |
|---|---|---|
| `stores` | list of `object` | Cart lines grouped by store: {store_id, lines: [CartLine], subtotal}; subtotal sums only purchasable lines. |
| `line_count` | `integer` | Number of lines. |
| `item_count` | `number` | Total quantity of all lines. |
| `currency` | `string` | Display currency of the prices. |

### FrontCartItemsRequest

| Field | Type | Meaning |
|---|---|---|
| `product_id` | `string` | Id of the product. *(required)* |
| `qty` | `number` | Quantity to add. *(required)* |
| `options` | `object` | attribute group id =&gt; option name |

### FrontCartItemsUpdateRequest

| Field | Type | Meaning |
|---|---|---|
| `qty` | `number` | New quantity of the line. *(required)* |

### FrontCartPromotionsResult

| Field | Type | Meaning |
|---|---|---|
| `result` | `object` | {msg}: message of the promotion plugin. |
| `totals` | [CheckoutTotals](#checkouttotals) | New totals (CheckoutTotals shape). |

### FrontCartPromotionsRequest

| Field | Type | Meaning |
|---|---|---|
| `payload` | `object` | As the plugin expects, e.g. {"code": "SUMMER"} |

### FrontCartPromotionsDeleteResult

| Field | Type | Meaning |
|---|---|---|
| `totals` | [CheckoutTotals](#checkouttotals) | New totals (CheckoutTotals shape). |

### CheckoutState

| Field | Type | Meaning |
|---|---|---|
| `store_id` | `string` | Store being checked out. |
| `address` | `object \| null` | Delivery address set so far: first_name, last_name, first_name_kana, last_name_kana, email, phone, city, district, ward, address1, address2, address3, postcode, country, company, comment (all strings). |
| `save_address` | `boolean` | Whether the address will be saved to the customer's address book. |
| `shipping_method` | `object \| null` | Chosen delivery option (ShippingRate shape, or {plugin_key} when no quote is kept); null when none. |
| `payment_method` | `string \| null` | Chosen payment plugin key. |
| `promotions` | list of `string` | Keys of the promotion plugins applied. |

### FrontCheckoutAddressUpdateRequest

| Field | Type | Meaning |
|---|---|---|
| `first_name` | `string` | First name of the recipient. |
| `last_name` | `string` | Last name of the recipient. |
| `first_name_kana` | `string` | First name in kana (Japanese stores). |
| `last_name_kana` | `string` | Last name in kana (Japanese stores). |
| `email` | `string` | Email for the order confirmation. |
| `phone` | `string` | Phone number. |
| `city` | `string` | City or province. |
| `district` | `string` | District. |
| `ward` | `string` | Ward. |
| `address1` | `string` | First address line. |
| `address2` | `string` | Second address line. |
| `address3` | `string` | Third address line. |
| `postcode` | `string` | Postal code. |
| `country` | `string` | Country code (ISO 3166-1 alpha-2, e.g. VN). |
| `company` | `string` | Company name. |
| `comment` | `string` | Note for the shop. |
| `address_id` | `string` | One of the customer's saved addresses |
| `save_address` | `boolean` | Signed-in customer: also save it to the address book. |

### FrontCheckoutShippingMethodsResult

| Field | Type | Meaning |
|---|---|---|
| `required` | `boolean` | Whether the store requires a delivery option. |
| `rates` | list of [ShippingRate](#shippingrate) | Quoted options. |
| `unquotable` | list of `string` | Keys of shipping plugins that could not quote this parcel. |
| `selected` | `string \| null` | Id of the option already chosen, when still valid for this address and cart. |

### FrontCheckoutShippingMethodUpdateRequest

| Field | Type | Meaning |
|---|---|---|
| `rate_id` | `string` | Id of a delivery option from checkout/shipping-methods. *(required)* |

### FrontCheckoutPaymentMethodsResult

| Field | Type | Meaning |
|---|---|---|
| `required` | `boolean` | Whether the store requires a payment method. |
| `methods` | list of [PaymentMethod](#paymentmethod) | Usable methods. |
| `selected` | `string \| null` | Key of the method already chosen. |

### FrontCheckoutPaymentMethodUpdateRequest

| Field | Type | Meaning |
|---|---|---|
| `key` | `string` | Key of a payment method from checkout/payment-methods. *(required)* |

### CheckoutTotals

| Field | Type | Meaning |
|---|---|---|
| `store_id` | `string` | Store being checked out. |
| `lines` | list of [OrderTotal](#ordertotal) | Total rows (same shape as OrderTotal): code, title, value, text. |
| `currency` | `string` | Display currency. |

### FrontCheckoutPlaceRequest

| Field | Type | Meaning |
|---|---|---|
| `store_id` | `string` | Store whose lines to order (a cart may hold lines of several stores). |
| `return_url` | `string` | Where the payer goes back to after the gateway; used only when on the site's allowed list (GP247_PAYMENT_RETURN_URLS) |

### FrontCheckoutPlaceResult

| Field | Type | Meaning |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, balance, currency} of the placed order. |
| `access_token` | `string` | Guest only: order access token, to send as X-Order-Access on orders/{id}/payment and orders/{id}/cancel. |
| `payment` | `object` | {type: none} or {type: hosted, url}: url of the gateway page, null when the gateway could not start (retry with the same key) or nothing is due. |

### Order

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Order id. |
| `customer_id` | `string \| null` | Customer id ('0' or empty for a guest order). |
| `status` | `integer` | Order status id (statuses configured on the admin order screen). |
| `payment_status` | `integer` | Payment status id. |
| `shipping_status` | `integer` | Shipping status id. |
| `currency` | `string` | Currency of the order. |
| `exchange_rate` | `number` | Exchange rate used for the order. |
| `subtotal` | `number` | Sum of the lines. |
| `tax` | `number` | Tax. |
| `shipping` | `number` | Shipping fee. |
| `discount` | `number` | Discount. |
| `other_fee` | `number` | Other fees. |
| `total` | `number` | Amount to pay. |
| `received` | `number` | Amount received so far. |
| `balance` | `number` | Amount still due. |
| `first_name` | `string \| null` | Delivery first name. |
| `last_name` | `string \| null` | Delivery last name. |
| `email` | `string \| null` | Buyer e-mail. |
| `phone` | `string \| null` | Buyer phone. |
| `company` | `string \| null` | Company. |
| `country` | `string \| null` | Country code. |
| `postcode` | `string \| null` | Postcode. |
| `city` | `string \| null` | City. |
| `district` | `string \| null` | District. |
| `ward` | `string \| null` | Ward. |
| `address1` | `string \| null` | Address line 1. |
| `address2` | `string \| null` | Address line 2. |
| `address3` | `string \| null` | Address line 3. |
| `comment` | `string \| null` | Buyer's note. |
| `payment_method` | `string \| null` | Payment plugin key. |
| `shipping_method` | `string \| null` | Shipping plugin key. |
| `store_id` | `string \| null` | Store of the order. |
| `details` | list of [OrderLine](#orderline) | Order lines. |
| `totals` | list of [OrderTotal](#ordertotal) | Total rows in display order. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |
| `ip` | `string \| null` | admin only. IP address the order came from. *(admin only)* |
| `user_agent` | `string \| null` | admin only. Browser / app user agent. *(admin only)* |
| `device_type` | `string \| null` | admin only. Device type (desktop, phone, tablet, other). *(admin only)* |
| `domain` | `string \| null` | admin only. Site domain the order was placed on. *(admin only)* |
| `transaction` | `string \| null` | admin only. Legacy transaction reference. *(admin only)* |
| `transactions` | list of [OrderTransaction](#ordertransaction) | admin only, with include=transactions: money movements of the order. *(admin only, only with include `transactions`)* |
| `histories` | list of [OrderHistory](#orderhistory) | admin only, with include=histories: order history lines (plain text). *(admin only, only with include `histories`)* |

### FrontMemberOrdersPaymentResult

| Field | Type | Meaning |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; no address, no lines. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} as recorded from the gateway. |

### FrontOrdersPaymentResult

| Field | Type | Meaning |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; no address, no lines. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} as recorded from the gateway. |

### FrontMemberOrdersCancelResult

| Field | Type | Meaning |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; no address, no lines. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} as recorded from the gateway. |

### FrontOrdersCancelResult

| Field | Type | Meaning |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}; no address, no lines. |
| `payment` | `object` | {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid} as recorded from the gateway. |

### ProductAttributeGroup

| Field | Type | Meaning |
|---|---|---|
| `group_id` | `string` | Attribute group id; the cart takes the choice as options[group_id]. |
| `name` | `string` | Group name, e.g. Color. |
| `required` | `boolean` | Always true: every group must be chosen. |
| `options` | list of `object` | Choices of the group: {value, add_price}; add_price is the surcharge, null when prices are hidden. |

### ShippingRate

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Rate id to send as rate_id. |
| `plugin_key` | `string` | Shipping plugin key. |
| `service_code` | `string` | Service code inside the plugin. |
| `title` | `string` | Label to show. |
| `amount` | `number` | Price in the display currency. |
| `currency` | `string` | Display currency. |
| `eta_min_days` | `integer \| null` | Fastest delivery in days. |
| `eta_max_days` | `integer \| null` | Slowest delivery in days. |

### PaymentMethod

| Field | Type | Meaning |
|---|---|---|
| `key` | `string` | Payment plugin key to send as key. |
| `title` | `string` | Label. |
| `note` | `string` | Extra note of the method. |
| `type` | `string` | none = no online payment step; hosted = the payer is sent to the gateway's page. |

### OrderTotal

| Field | Type | Meaning |
|---|---|---|
| `code` | `string` | Row code, e.g. subtotal, tax, shipping, discount, total. |
| `title` | `string` | Row label. |
| `value` | `number` | Amount. |
| `text` | `string` | Amount formatted for display. |

### OrderLine

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Line id. |
| `product_id` | `string` | Product id. |
| `sku` | `string \| null` | SKU at order time. |
| `name` | `string \| null` | Product name at order time. |
| `price` | `number` | Unit price. |
| `qty` | `number` | Quantity. |
| `discount` | `number` | Line discount. |
| `tax` | `number` | Line tax. |
| `tax_rate` | `number` | Tax rate in percent. |
| `total_price` | `number` | Line total. |
| `attribute` | `string \| null` | Chosen attributes as a JSON-encoded string {group_id: 'name__price[__slug]'}, raw column value. |

### OrderTransaction

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Transaction id. |
| `type` | `string` | Kind of money movement (e.g. payment, refund). |
| `amount` | `number` | Amount. |
| `currency` | `string` | Currency. |
| `method` | `string \| null` | Payment method key. |
| `gateway_transaction_id` | `string \| null` | Reference at the payment gateway. |
| `paid_at` | `string \| null` | When it was paid (ISO 8601). |
| `created_at` | `string \| null` | When it was recorded (ISO 8601). |

### OrderHistory

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | History line id. |
| `content` | `string` | Text of the history line (HTML removed). |
| `order_status_id` | `integer` | Order status at the time of the line. |
| `admin_id` | `string \| null` | Admin who wrote it. |
| `customer_id` | `string \| null` | Customer who wrote it. |
| `created_at` | `string \| null` | When it was written (ISO 8601). |

---

<sub>📅 **Last updated:** 2026-11-03 · ✍️ **Author:** GP247</sub>
