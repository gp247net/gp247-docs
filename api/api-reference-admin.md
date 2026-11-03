> 🌐 **Language:** [🇻🇳 Tiếng Việt](./api-reference-admin_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./api-reference-admin_ja.md)

# API v2 reference — Admin (Core)

> This page is generated from openapi.json by tools/openapi-to-markdown.php — do not edit it by hand.

Every endpoint under `api/v2/core` — data for administrators and back-office systems (ERP, CRM, warehouse) — with the admin permission it needs, its parameters, an example call and what it returns.

New to the API? Start with [Quick start](./api-quickstart.md). Rules shared by every endpoint (response format, paging, `fields`, `include`, `304`) are in [Conventions](./api-conventions.md); each error code is explained in [Troubleshooting](./api-troubleshooting.md).

## Contents

- [Admin · Sign-in & sessions](#admin--sign-in--sessions) — Administrator sign-in, token refresh, two-step sign-in, own account and signed-in devices.
- [Admin · Catalog](#admin--catalog) — Products, categories, brands and suppliers as the admin screens show them; product changes.
- [Admin · Stock](#admin--stock) — Stock changes for one product or many at once (warehouse / ERP sync).
- [Admin · Content](#admin--content) — Banners and content pages as the admin screens show them.
- [Admin · Customers](#admin--customers) — Customer accounts and locking or unlocking an account.
- [Admin · Orders](#admin--orders) — Orders, their status, delivery details and notes.
- [Admin · Sync](#admin--sync) — Event log for ERP / sync tools: every change of orders, stock, products and customers, read by cursor.
- [Data objects](#data-objects)

## Admin · Sign-in & sessions

Administrator sign-in, token refresh, two-step sign-in, own account and signed-in devices.

### Signed-in admin profile — `GET /api/v2/core/info`

Returns the profile and role slugs of the admin who owns the token. Any active admin token may call it; no screen permission is needed.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | None beyond a valid admin token. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `core 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/info" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Response** `200` — `data`: [AdminInfo](#admininfo)

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

### Admin sign-in — `POST /api/v2/core/login`

Signs an active admin in with username and password and returns a bearer token. With with_refresh=true the answer adds a refresh token and the access token lives a short time. When a plugin requires a second step (MFA), the answer is 401 challenge_required with a ticket to finish on login/challenge. Failed attempts are limited per account and IP.

| | |
|---|---|
| Sign-in | None — this call signs in. |
| Permission | None beyond a valid admin token. |
| Since | `core 3.3.0` |

**Request body**: [AdminLoginV2](#adminloginv2)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"","password":"","remember_me":false,"with_refresh":false,"device_name":""}'
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

### Admin sign-out — `POST /api/v2/core/logout`

Signs the current session out: its access token stops working and its refresh token can no longer be used. Any active admin token may call it.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | None beyond a valid admin token. |
| Since | `core 3.3.0` |

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Response** `200` — `data`: [AdminLogoutResult](#adminlogoutresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

### Finish admin MFA sign-in — `POST /api/v2/core/login/challenge`

Sends the ticket from a challenge_required answer together with the plugin's own fields (e.g. a one-time code). On success it answers like login. A wrong answer counts against the login limit; a ticket lives 5 minutes and allows 5 wrong answers.

| | |
|---|---|
| Sign-in | None — this call signs in. |
| Permission | None beyond a valid admin token. |
| Since | `core 3.5.1` |

**Request body**: [LoginChallengeAnswer](#loginchallengeanswer)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/login/challenge" \
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

### Refresh admin access token — `POST /api/v2/core/refresh`

Exchanges a refresh token for a new access token and a new refresh token; each refresh token works once. Using a spent token again signs the whole session out (refresh_token_reused), except within 10 seconds of its use (refresh_in_progress). Answers 404 when the site has not run gp247:update yet.

| | |
|---|---|
| Sign-in | None — this call signs in. |
| Permission | None beyond a valid admin token. |
| Since | `core 3.5.1` |

**Request body**: [RefreshRequest](#refreshrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/refresh" \
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

### List admin sessions — `GET /api/v2/core/sessions`

Returns the live API sessions (devices) of the signed-in admin, newest first, with the current one flagged. No token material is returned. Not paginated; an empty array before the site runs gp247:update.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | None beyond a valid admin token. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `core 3.5.1` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/sessions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### Sign one admin session out — `DELETE /api/v2/core/sessions/{id}`

Signs one of the admin's own sessions out (its access and refresh token). Another owner's session id answers 404.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | None beyond a valid admin token. |
| Since | `core 3.5.1` |

**Example request**

```bash
curl -X DELETE "https://shop.example.com/api/v2/core/sessions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Response** `200` — `data`: [AdminSessionsDeleteResult](#adminsessionsdeleteresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

**Errors**: [`not_found`](./api-troubleshooting.md#not_found)

## Admin · Catalog

Products, categories, brands and suppliers as the admin screens show them; product changes.

### Get one brand (admin) — `GET /api/v2/core/brand/detail/{id}`

Returns one brand of the admin's store with the admin-only fields. A record of another store answers 404. Accepts fields[brand]=a,b.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Brands (gp247_admin/brand). |
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
curl "https://shop.example.com/api/v2/core/brand/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### List brands (admin) — `GET /api/v2/core/brand/list`

Returns the brands of the admin's store, page by page. Requires an admin token allowed to open the matching admin screen. Filter by status, sort by sort or name, search by name. Accepts fields[brand]=a,b to return only some fields. Supports updated_since=&lt;ISO 8601&gt; sync mode: oldest change first, paged with page[after] (meta then has per_page, next_after, has_more, sync_until; links only next).

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Brands (gp247_admin/brand). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/brand/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### Get one category (admin) — `GET /api/v2/core/category/detail/{id}`

Returns one category of the admin's store with the admin-only fields. A record of another store answers 404. Accepts fields[category]=a,b.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Categories (gp247_admin/category). |
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
curl "https://shop.example.com/api/v2/core/category/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### List categories (admin) — `GET /api/v2/core/category/list`

Returns the categories of the admin's store, page by page. Requires an admin token allowed to open the matching admin screen. Text fields in the language given by ?lang=. Filter by parent or status, sort by sort or created_at, search by name. Accepts fields[category]=a,b to return only some fields. Supports updated_since=&lt;ISO 8601&gt; sync mode: oldest change first, paged with page[after] (meta then has per_page, next_after, has_more, sync_until; links only next).

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Categories (gp247_admin/category). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/category/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### Get one product (admin) — `GET /api/v2/core/product/detail/{id}`

Returns one product of the admin's store with the admin-only fields. A record of another store answers 404. Accepts fields[product]=a,b and include=images,brand,categories.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Products (gp247_admin/product). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |
| `include` | query | `string` | Comma-separated related records to embed (one level, at most 3): images, brand, categories. Anything else = 422. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/product/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### Update product price, cost, status — `PATCH /api/v2/core/product/{id}`

Changes the selling price, cost price and/or status (0 or 1) of a product of the admin's store. Any other field, or a price field the store turned off in product settings, is refused with 422. Answers the full admin product. Idempotency-Key optional.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Write access to the admin screen Products (gp247_admin/product). |
| Since | `shop 3.6.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `Idempotency-Key` | header | `string` | Optional; 8-255 printable characters (e.g. a UUID). A retry with the same key and body answers the first result |

**Request body**: [AdminProductUpdateRequest](#adminproductupdaterequest)

**Example request**

```bash
curl -X PATCH "https://shop.example.com/api/v2/core/product/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"price":0,"cost":0,"status":0}'
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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`forbidden`](./api-troubleshooting.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting.md#idempotency_unavailable)

### List products (admin) — `GET /api/v2/core/product/list`

Returns the products of the admin's store, page by page. Requires an admin token allowed to open the matching admin screen. Includes inventory and moderation fields (cost, stock, sold, status, approve, supplier_id, view) and every product whatever its status. Filters: category_id, brand_id, kind, product_type, price_min, price_max, status, approve, supplier_id; sort by price, created_at, sort or sku; search by SKU or name; include=images,brand,categories. Accepts fields[product]=a,b to return only some fields. Supports updated_since=&lt;ISO 8601&gt; sync mode: oldest change first, paged with page[after] (meta then has per_page, next_after, has_more, sync_until; links only next).

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Products (gp247_admin/product). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |
| `include` | query | `string` | Comma-separated related records to embed (one level, at most 3): images, brand, categories. Anything else = 422. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/product/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### Get one supplier (admin) — `GET /api/v2/core/supplier/detail/{id}`

Returns one supplier of the admin's store with the admin-only fields. A record of another store answers 404. Accepts fields[supplier]=a,b.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Suppliers (gp247_admin/supplier). |
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
curl "https://shop.example.com/api/v2/core/supplier/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Response** `200` — `data`: [Supplier](#supplier)

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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

### List suppliers (admin) — `GET /api/v2/core/supplier/list`

Returns the suppliers of the admin's store, page by page. Requires an admin token allowed to open the matching admin screen. Filter by status, sort by sort or name, search by name or e-mail. Accepts fields[supplier]=a,b to return only some fields. Supports updated_since=&lt;ISO 8601&gt; sync mode: oldest change first, paged with page[after] (meta then has per_page, next_after, has_more, sync_until; links only next).

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Suppliers (gp247_admin/supplier). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/supplier/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Response** `200` — `data`: list of [Supplier](#supplier) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

## Admin · Stock

Stock changes for one product or many at once (warehouse / ERP sync).

### Move or set product stock — `POST /api/v2/core/product/{id}/stock`

Receives or issues goods with delta (positive or negative), or overwrites the stock after a count with set (administrators only). Stock never goes below zero and the sold counter is untouched; build products are refused. An Idempotency-Key header is required so a retry never moves stock twice.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Write access to the admin screen Products (gp247_admin/product). |
| Since | `shop 3.6.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `Idempotency-Key` *(required)* | header | `string` | Required; 8-255 printable characters (e.g. a UUID). A retry with the same key and body moves the stock once and answers the first result (header Idempotent-Replayed: true) |

**Request body**: [AdminProductStockRequest](#adminproductstockrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/product/123/stock" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"delta":0,"set":0}'
```

**Response** `200` — `data`: [AdminProductStockResult](#adminproductstockresult)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "sku": "TSHIRT-RED-M",
        "stock": 15
    }
}
```

**Errors**: [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting.md#idempotency_unavailable) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`not_found`](./api-troubleshooting.md#not_found) · [`forbidden`](./api-troubleshooting.md#forbidden) · [`stock_managed_by_components`](./api-troubleshooting.md#stock_managed_by_components) · [`stock_would_be_negative`](./api-troubleshooting.md#stock_would_be_negative)

### Move stock of many products — `POST /api/v2/core/stock/batch`

Applies 1 to 100 stock lines, each naming a product by id or SKU with delta or set. Each line gets its own result and a bad line never stops the others; the answer is 200 even when some lines fail. Idempotency-Key required.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Write access to the admin screen Products (gp247_admin/product). |
| Since | `shop 3.6.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `Idempotency-Key` *(required)* | header | `string` | Required; 8-255 printable characters (e.g. a UUID). A retry with the same key and body moves the stock once and answers the first result (header Idempotent-Replayed: true) |

**Request body**: [AdminStockBatchRequest](#adminstockbatchrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/stock/batch" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"items":[[]]}'
```

**Response** `200` — `data`: [AdminStockBatchResult](#adminstockbatchresult)

```json
{
    "data": {
        "results": [],
        "ok": 9,
        "failed": 1
    }
}
```

**Errors**: [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting.md#idempotency_unavailable) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`forbidden`](./api-troubleshooting.md#forbidden)

## Admin · Content

Banners and content pages as the admin screens show them.

### Get one banner (admin) — `GET /api/v2/core/banner/detail/{id}`

Returns one banner of the admin's store, including the click counter. Requires an admin token allowed to open the Banners screen; a banner of another store answers 404.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Banners (gp247_admin/banner). |
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
curl "https://shop.example.com/api/v2/core/banner/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### List banners (admin) — `GET /api/v2/core/banner/list`

Returns the banners of the admin's store, page by page, including the click counter. Requires an admin token allowed to open the Banners screen. Filter by type, sort by sort or created_at, search by name.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Banners (gp247_admin/banner). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `front 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/banner/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### Get one CMS page (admin) — `GET /api/v2/core/page/detail/{id}`

Returns one CMS page of the admin's store with its HTML body, in the language given by ?lang=. Requires access to the Pages screen.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Pages (gp247_admin/page). |
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
curl "https://shop.example.com/api/v2/core/page/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### List CMS pages (admin) — `GET /api/v2/core/page/list`

Returns the CMS pages of the admin's store, page by page, without the HTML body. Text fields are in the language given by ?lang= (site default otherwise). Requires access to the Pages screen.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Pages (gp247_admin/page). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `front 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/page/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

## Admin · Customers

Customer accounts and locking or unlocking an account.

### Get one customer (admin) — `GET /api/v2/core/customer/detail/{id}`

Returns one customer of the admin's store with the admin-only fields. A record of another store answers 404. Accepts fields[customer]=a,b.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Customers (gp247_admin/customer). |
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
curl "https://shop.example.com/api/v2/core/customer/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed)

### List customers (admin) — `GET /api/v2/core/customer/list`

Returns the customers of the admin's store, page by page. Requires an admin token allowed to open the matching admin screen. Includes the admin-only fields store_id and email_verified_at; never the password. Filter by status or group, sort by created_at or email, search by e-mail, phone or name. Accepts fields[customer]=a,b to return only some fields. Supports updated_since=&lt;ISO 8601&gt; sync mode: oldest change first, paged with page[after] (meta then has per_page, next_after, has_more, sync_until; links only next).

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Customers (gp247_admin/customer). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/customer/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Response** `200` — `data`: list of [Customer](#customer) — with `meta` (paging) and `links`.

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

**Errors**: [`validation_failed`](./api-troubleshooting.md#validation_failed)

### Lock or unlock a customer — `PATCH /api/v2/core/customer/{id}`

Sets the status of a customer of the admin's store: 0 locks the account at once (API and storefront), 1 unlocks it. Any other field is refused with 422. Answers the customer with admin-only fields. Idempotency-Key optional.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Write access to the admin screen Customers (gp247_admin/customer). |
| Since | `shop 3.6.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `Idempotency-Key` | header | `string` | Optional; 8-255 printable characters (e.g. a UUID). A retry with the same key and body answers the first result |

**Request body**: [AdminCustomerUpdateRequest](#admincustomerupdaterequest)

**Example request**

```bash
curl -X PATCH "https://shop.example.com/api/v2/core/customer/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"status":0}'
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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`forbidden`](./api-troubleshooting.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting.md#idempotency_unavailable)

## Admin · Orders

Orders, their status, delivery details and notes.

### Get one order (admin) — `GET /api/v2/core/order/detail/{id}`

Returns one order of the admin's store with the admin-only fields. A record of another store answers 404. Accepts fields[order]=a,b and include=transactions,histories.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin order detail screen (gp247_admin/order/detail/{id}). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |
| `include` | query | `string` | Comma-separated related records to embed (one level, at most 3): transactions (payment ledger), histories (processing history, plain text). Anything else = 422. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/order/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### Change order status — `POST /api/v2/core/order/{id}/status`

Sets the order status exactly as the admin order screen does: cancelling returns stock, re-opening a cancelled order takes it back (refused with 409 when stock is short) and a history line is written. Answers the order as order/detail does. An Idempotency-Key header is optional; a retry with the same key replays the first answer.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Write access to the admin screen Orders (gp247_admin/order). |
| Since | `shop 3.6.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `Idempotency-Key` | header | `string` | Optional; 8-255 printable characters (e.g. a UUID). A retry with the same key and body answers the first result |

**Request body**: [AdminOrderStatusRequest](#adminorderstatusrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"status":0}'
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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`forbidden`](./api-troubleshooting.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting.md#idempotency_unavailable) · [`admin.order.reopen_blocked_stock`](./api-troubleshooting.md#adminorderreopen_blocked_stock)

### Set shipping status or tracking — `POST /api/v2/core/order/{id}/shipping`

Sets the shipping status and/or records a tracking number (and URL) as the order's manual shipment. The payment status cannot be written here. Answers the updated order. Idempotency-Key optional.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Write access to the admin screen Orders (gp247_admin/order). |
| Since | `shop 3.6.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `Idempotency-Key` | header | `string` | Optional; 8-255 printable characters (e.g. a UUID). A retry with the same key and body answers the first result |

**Request body**: [AdminOrderShippingRequest](#adminordershippingrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/shipping" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"shipping_status":0,"tracking_number":"","tracking_url":""}'
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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`forbidden`](./api-troubleshooting.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting.md#idempotency_unavailable) · [`admin.order.shipping_status_invalid`](./api-troubleshooting.md#adminordershipping_status_invalid)

### Add an order note — `POST /api/v2/core/order/{id}/notes`

Adds a note (up to 1000 characters) to the order history, marked as written through the API. Answers the order. Idempotency-Key optional.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Write access to the admin screen Orders (gp247_admin/order). |
| Since | `shop 3.6.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `id` *(required)* | path | `string` | Id of the record (UUID). |
| `Idempotency-Key` | header | `string` | Optional; 8-255 printable characters (e.g. a UUID). A retry with the same key and body answers the first result |

**Request body**: [AdminOrderNotesRequest](#adminordernotesrequest)

**Example request**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/notes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"content":""}'
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

**Errors**: [`not_found`](./api-troubleshooting.md#not_found) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`forbidden`](./api-troubleshooting.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting.md#idempotency_unavailable)

### List orders (admin) — `GET /api/v2/core/order/list`

Returns the orders of the admin's store, page by page. Requires an admin token allowed to open the matching admin screen. Each order carries its lines (details) and totals, plus admin-only request data (ip, user_agent, device_type, domain, transaction). Filters: status, payment_status, shipping_status, customer_id; sort by created_at or total; search by order id, e-mail or phone; include=transactions,histories. Accepts fields[order]=a,b to return only some fields. Supports updated_since=&lt;ISO 8601&gt; sync mode: oldest change first, paged with page[after] (meta then has per_page, next_after, has_more, sync_until; links only next).

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen Orders (gp247_admin/order). |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `shop 3.3.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `page[number]` | query | `integer` | Page to read, from 1. |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `updated_since` | query | `string` (date-time) | Sync mode (since core 3.7.0): ISO-8601 date-time, e.g. 2026-10-31T08:00:00+07:00 (date only or no offset = site timezone). Returns rows with updated_at &gt;= updated_since, oldest change first (updated_at, then id). Cannot be combined with sort or page[number] (422) |
| `page[after]` | query | `string` | Sync mode only: cursor from meta.next_after of the previous page; any other value = 422 |
| `filter` | query | `object` | filter[&lt;name&gt;]=value — allowed names per resource, see api-introduction section 8; unknown name = 422 |
| `sort` | query | `string` | &lt;name&gt; ascending, -&lt;name&gt; descending; unknown name = 422 |
| `q` | query | `string` | Search term |
| `lang` | query | `string` | Language of names/descriptions |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |
| `fields` | query | `object` | fields[&lt;type&gt;]=a,b keeps only those fields (and id) of the record and of included records. Unknown type or field = 422. Without it the body is unchanged. Since core 3.8.0. |
| `include` | query | `string` | Comma-separated related records to embed (one level, at most 3): transactions (payment ledger), histories (processing history, plain text). Anything else = 422. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/order/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

## Admin · Sync

Event log for ERP / sync tools: every change of orders, stock, products and customers, read by cursor.

### Read the event log — `GET /api/v2/core/events`

Returns change events (orders, stock, products, customers) oldest first, for ERP and sync tools. Send ?after=&lt;last id handled&gt; to continue; page[size] default 100, max 500; filter[type]=a,b narrows the types. Only types of screens the admin may open and the admin's store are returned; events younger than a few seconds are held back. 409 when the event log is off, 410 when the cursor points to cleaned events.

| | |
|---|---|
| Sign-in | Admin token (Bearer). |
| Permission | Read access to the admin screen of each event type returned: Orders for order.*, Products for product.* and stock.*, Customers for customer.*. |
| Caching | `ETag` — send `If-None-Match` to get `304` when nothing changed. |
| Since | `core 3.7.0` |

**Parameters**

| Name | In | Type | Meaning |
|---|---|---|---|
| `after` | query | `integer` | id of the last event the reader handled; returns the events after it. Omitted = from the oldest event still kept |
| `filter[type]` | query | `string` | Comma-separated event types (&lt;resource&gt;.&lt;action&gt;); only narrows the types the admin may read. gp247/shop 3.6.0 types: order.created, order.status_changed, order.payment_status_changed, stock.changed, product.updated, product.deleted, customer.created, customer.updated |
| `page[size]` | query | `integer` | Rows per page (default 20, max 100). |
| `If-None-Match` | header | `string` | ETag of a previous answer; 304 with an empty body when nothing changed. Since core 3.8.0. |

**Example request**

```bash
curl "https://shop.example.com/api/v2/core/events" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**Response** `200` — `data`: list of [Event](#event)

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

**Errors**: [`event_log_disabled`](./api-troubleshooting.md#event_log_disabled) · [`cursor_expired`](./api-troubleshooting.md#cursor_expired) · [`validation_failed`](./api-troubleshooting.md#validation_failed) · [`forbidden`](./api-troubleshooting.md#forbidden)

## Data objects

Fields of the objects the endpoints above send and return. Dates are ISO 8601 strings; money is a number in the currency of the record.

### AdminInfo

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Admin user id. |
| `username` | `string` | Sign-in name. |
| `name` | `string \| null` | Display name. |
| `email` | `string \| null` | E-mail. |
| `avatar` | `string \| null` | Avatar path. |
| `status` | `integer` | 1 = active. |
| `roles` | list of `string` | Role slugs. |
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

### AdminLoginV2

| Field | Type | Meaning |
|---|---|---|
| `username` | `string` | Username of the administrator. *(required)* |
| `password` | `string` | Password of the administrator. *(required)* |
| `remember_me` | `boolean` | Longer-lived token. |
| `with_refresh` | `boolean` | Also issue a rotating refresh token; the access token then lives 60 minutes |
| `device_name` | `string` | Shown in the sessions list |

### AdminLogoutResult

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

### AdminSessionsDeleteResult

| Field | Type | Meaning |
|---|---|---|
| `revoked` | `boolean` | Always true. |

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

### AdminProductUpdateRequest

At least one of price / cost / status; nothing else

| Field | Type | Meaning |
|---|---|---|
| `price` | `number` | Selling price; refused (422) when the store turned the price field off |
| `cost` | `number` | Cost price; refused (422) when the store turned the cost field off |
| `status` | `integer` | 1 = on sale, 0 = hidden. |

### Supplier

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Supplier id. |
| `name` | `string` | Supplier name. |
| `alias` | `string` | URL slug. |
| `email` | `string \| null` | Contact e-mail. |
| `phone` | `string \| null` | Contact phone. |
| `image` | `string \| null` | Logo path. |
| `address` | `string \| null` | Postal address. |
| `url` | `string \| null` | Website. |
| `sort` | `integer` | Sort order. |
| `status` | `integer` | 1 = enabled. |
| `store_id` | `string \| null` | Store id. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |

### AdminProductStockRequest

Exactly one of delta / set (both or neither: 422 validation_failed)

| Field | Type | Meaning |
|---|---|---|
| `delta` | `number` | Receive (+) or issue (-) goods, not 0; whole numbers unless decimal quantities are allowed in the product settings. Atomic: an order placed at the same moment loses nothing. Never below zero (422 stock_would_be_negative, nothing moves). The sold counter is not touched |
| `set` | `number` | Set the stock outright after a stock count; administrator role only (others: 403) |

### AdminProductStockResult

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Product id. |
| `sku` | `string` | Product SKU. |
| `stock` | `number` | Stock after the move (whole number when it has no decimals). |

### AdminStockBatchRequest

| Field | Type | Meaning |
|---|---|---|
| `items` | list of `object` | More than 100 lines or a malformed line: 422, nothing applied *(required)* |

### AdminStockBatchResult

| Field | Type | Meaning |
|---|---|---|
| `results` | list of [StockBatchResult](#stockbatchresult) | One result per line, in request order. |
| `ok` | `integer` | Lines applied. |
| `failed` | `integer` | Lines refused. |

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
| `content` | `string \| null` | HTML body; detail answers only (not in lists). |
| `status` | `integer` | 1 = enabled. |
| `store_id` | `string \| null` | Store id. |
| `created_at` | `string \| null` | Creation time (ISO 8601). |
| `updated_at` | `string \| null` | Last change time (ISO 8601). |

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

### AdminCustomerUpdateRequest

Only status; nothing else

| Field | Type | Meaning |
|---|---|---|
| `status` | `integer` | 0 = lock, 1 = unlock *(required)* |

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

### AdminOrderStatusRequest

| Field | Type | Meaning |
|---|---|---|
| `status` | `integer` | Id of one of the site's order statuses. Cancelling returns stock to inventory, reopening takes it back; a finished or cancelled order can be reopened. The current status answers 200 and writes nothing *(required)* |

### AdminOrderShippingRequest

At least one of shipping_status / tracking_number. payment_status is refused (422): it follows the order's payments

| Field | Type | Meaning |
|---|---|---|
| `shipping_status` | `integer` | Id of one of the site's shipping statuses; a change adds an order history line |
| `tracking_number` | `string` | Kept as the order's manual shipment (one per order; another number updates it), shown on the admin order screen |
| `tracking_url` | `string` (uri) | http(s) address; needs tracking_number |

### AdminOrderNotesRequest

| Field | Type | Meaning |
|---|---|---|
| `content` | `string` | Saved with the author and the label [API]; shown as plain text (markup escaped) *(required)* |

### Event

| Field | Type | Meaning |
|---|---|---|
| `id` | `integer` | Event id; pass the last one handled as ?after= to continue. |
| `type` | `string` | Event type, e.g. order.created, order.status_changed, order.payment_status_changed, stock.changed, product.updated, product.deleted, customer.created, customer.updated. |
| `subject_id` | `string` | Id of the record the event is about. |
| `store_id` | `string \| null` | Store of the record. |
| `source` | `string` | Where the change came from: api, admin, web or system. |
| `actor` | `object \| null` | Who made the change: {type: admin\|customer, id}; null when unknown (system). |
| `data` | `object` | Fields declared for the type (e.g. {field, from, to} for order.status_changed); empty object when none. |
| `occurred_at` | `string` | When it happened (ISO 8601). |

### EventListMeta

| Field | Type | Meaning |
|---|---|---|
| `per_page` | `integer` | Page size. |
| `next_after` | `integer \| null` | Id of the last event of this page; send it as after= to read on. Null when the page is empty. |
| `has_more` | `boolean` | Whether more events are ready to read now. |

### ProductAttributeGroup

| Field | Type | Meaning |
|---|---|---|
| `group_id` | `string` | Attribute group id; the cart takes the choice as options[group_id]. |
| `name` | `string` | Group name, e.g. Color. |
| `required` | `boolean` | Always true: every group must be chosen. |
| `options` | list of `object` | Choices of the group: {value, add_price}; add_price is the surcharge, null when prices are hidden. |

### StockBatchResult

| Field | Type | Meaning |
|---|---|---|
| `id` | `string` | Product id (the id sent, or the id found by SKU). |
| `sku` | `string` | Only when the line named the product by SKU. |
| `ok` | `boolean` | Whether this line was applied. |
| `stock` | `number` | Stock after the move (when ok). |
| `code` | `string` | When not ok: not_found, sku_not_found, sku_ambiguous, forbidden, stock_managed_by_components, stock_would_be_negative. |
| `msg` | `string` | When not ok: human-readable reason. |

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

### OrderTotal

| Field | Type | Meaning |
|---|---|---|
| `code` | `string` | Row code, e.g. subtotal, tax, shipping, discount, total. |
| `title` | `string` | Row label. |
| `value` | `number` | Amount. |
| `text` | `string` | Amount formatted for display. |

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
