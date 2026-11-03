> 🌐 **言語:** [🇻🇳 Tiếng Việt](./api-reference-front_vi.md) · [🇬🇧 English](./api-reference-front.md) · 🇯🇵 日本語（現在）

# API v2 リファレンス — ストアフロント（Front）

> このページは tools/openapi-to-markdown.php によって openapi.json から生成されています。手作業で編集しないでください。

`api/v2/front` 配下のすべてのエンドポイント（ストアフロント、顧客アカウント、カート、チェックアウト）について、呼び出せる人、パラメーター、呼び出し例、返す内容をまとめています。

API を初めて使う場合は、まず [クイックスタート](./api-quickstart_ja.md) をご覧ください。すべてのエンドポイントに共通するルール（レスポンス形式、ページ分割、`fields`、`include`、`304`）は [共通ルール](./api-conventions_ja.md) に、各エラーコードの説明は [トラブルシューティング](./api-troubleshooting_ja.md) にあります。

## 目次

- [Front · サイト情報](#front--サイト情報) — ストアフロントが提供する情報：API バージョン、パッケージのバージョン、言語、通貨。
- [Front · カタログ](#front--カタログ) — 呼び出し元ドメインのストアフロントに表示される商品、カテゴリー、ブランド。
- [Front · コンテンツ](#front--コンテンツ) — ストアフロントのバナーとコンテンツページ。
- [Front · ログインとセッション](#front--ログインとセッション) — 顧客のログイン、トークンの更新、2 段階ログイン、ログイン中のデバイス一覧。
- [Front · 顧客アカウント](#front--顧客アカウント) — ログイン中の顧客の会員登録、パスワード再設定、プロフィール、アドレス帳、メールアドレスの確認。
- [Front · カート](#front--カート) — ログイン中の顧客またはゲスト（カートトークン）のカート：明細とプロモーションコード。
- [Front · チェックアウト](#front--チェックアウト) — 住所、配送方法と支払い方法の選択、合計金額、注文の確定。
- [Front · 注文](#front--注文) — 顧客の注文（または注文アクセストークンを持つゲストの注文）：一覧、詳細、支払い、キャンセル。
- [データオブジェクト](#データオブジェクト)

## Front · サイト情報

ストアフロントが提供する情報：API バージョン、パッケージのバージョン、言語、通貨。

### API の説明 — `GET /api/v2/front/meta`

API バージョン、インストール済みの GP247 パッケージ（名前のみ）、有効な言語、デフォルト言語、呼び出し元ドメインに応答するストアを返します。公開エンドポイントです。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `front 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/meta" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [Meta](#meta)

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

## Front · カタログ

呼び出し元ドメインのストアフロントに表示される商品、カテゴリー、ブランド。

### ブランドを 1 件取得 — `GET /api/v2/front/brand/detail/{id}`

有効なブランドを 1 件返します。公開エンドポイントです。無効なブランドは 404 を返します。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/brand/detail/123" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [Brand](#brand)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### ブランド一覧 — `GET /api/v2/front/brand/list`

有効なブランドをページ単位で返します。sort または name で並べ替え、name で検索できます。公開エンドポイントです。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/brand/list" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: 一覧： [Brand](#brand) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### カテゴリーを 1 件取得 — `GET /api/v2/front/category/detail/{id}`

ストアフロントに表示されるカテゴリーを 1 件返します。公開エンドポイントです。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/category/detail/123" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [Category](#category)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### カテゴリー一覧 — `GET /api/v2/front/category/list`

ストアフロントに表示されるカテゴリーを、?lang= で指定した言語でページ単位に返します。parent または top で絞り込み、sort または created_at で並べ替え、name で検索できます。公開エンドポイントです。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/category/list" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: 一覧： [Category](#category) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 商品を 1 件取得 — `GET /api/v2/front/product/detail/{id}`

ストアフロントで販売中の商品を 1 件、HTML 本文、追加画像、属性の選択肢、在庫状況とともに返します。公開エンドポイントです。非表示の商品は 404 を返します。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |
| `include` | クエリ | `string` | 埋め込む関連レコードをカンマ区切りで指定します（1 階層、最大 3 つ）：images、brand（有効なブランドのみ）、categories（表示中のカテゴリーのみ）。それ以外は 422。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/product/detail/123" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [Product](#product)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 商品一覧 — `GET /api/v2/front/product/list`

呼び出し元ドメインのストアフロントで販売中の商品を、請求価格（price_final）、属性の選択肢、在庫状況（在庫数は返しません）とともにページ単位で返します。ストアが価格を非表示にしている場合、価格は null です。絞り込み：category_id、brand_id、kind、product_type、price_min、price_max。並べ替え：price、created_at、sort、sku。検索：SKU または名前。fields[product] と include=images,brand,categories に対応しています。公開エンドポイントで、キャッシュできます。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |
| `include` | クエリ | `string` | 埋め込む関連レコードをカンマ区切りで指定します（1 階層、最大 3 つ）：images、brand（有効なブランドのみ）、categories（表示中のカテゴリーのみ）。それ以外は 422。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/product/list" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: 一覧： [Product](#product) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

## Front · コンテンツ

ストアフロントのバナーとコンテンツページ。

### バナーを 1 件取得 — `GET /api/v2/front/banner/detail/{id}`

呼び出し元ドメインのストアフロントに表示されるバナーを 1 件返します。公開エンドポイントです。非表示のバナーは 404 を返します。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `front 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/banner/detail/123" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [Banner](#banner)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found)

### バナー一覧 — `GET /api/v2/front/banner/list`

呼び出し元ドメインのストアフロントに表示されるバナーをページ単位で返します。公開エンドポイントです。type で絞り込み、sort または created_at で並べ替え、name で検索できます。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `front 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/banner/list" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: 一覧： [Banner](#banner) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### CMS ページを 1 件取得 — `GET /api/v2/front/page/detail/{id}`

表示中の CMS ページを 1 件、HTML 本文付きで、?lang= で指定した言語で返します。公開エンドポイントです。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `front 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/page/detail/123" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [Page](#page)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found)

### CMS ページ一覧 — `GET /api/v2/front/page/list`

ストアフロントに表示される CMS ページを、HTML 本文なしでページ単位に返します。テキストは ?lang= で指定した言語です。公開エンドポイントです。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `front 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/page/list" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: 一覧： [Page](#page) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

## Front · ログインとセッション

顧客のログイン、トークンの更新、2 段階ログイン、ログイン中のデバイス一覧。

### 顧客ログイン — `POST /api/v2/front/login`

有効な顧客をメールアドレスとパスワードでログインさせ、Bearer トークン（with_refresh=true の場合はリフレッシュトークンも）を返します。アプリがゲストカートの X-Cart-Token を送ると、そのカートはアカウントに統合され、レスポンスに cart_merge が含まれます。プラグインによる 2 段階目がある場合は 401 challenge_required を返します。失敗した試行の回数には制限があります。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| 導入バージョン | `shop 3.3.0` |

**リクエスト本文**: [CustomerLoginV2](#customerloginv2)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"","password":"","remember_me":false,"with_refresh":false,"device_name":""}'
```

**レスポンス** `200` — `data`: [SessionToken](#sessiontoken)

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`unauthenticated`](./api-troubleshooting_ja.md#unauthenticated) · [`too_many_attempts`](./api-troubleshooting_ja.md#too_many_attempts) · [`challenge_required`](./api-troubleshooting_ja.md#challenge_required) · [`challenge_invalid`](./api-troubleshooting_ja.md#challenge_invalid)

### 顧客のサインアウト — `POST /api/v2/front/logout`

現在のセッション（アクセストークンとリフレッシュトークン）をサインアウトします。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.3.0` |

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontLogoutResult](#frontlogoutresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

### 顧客の MFA ログインを完了 — `POST /api/v2/front/login/challenge`

challenge_required レスポンスのチケットを、プラグインの項目とともに送ります。成功すると login と同じ形式で応答します（ゲストカートの統合を含む）。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [LoginChallengeAnswer](#loginchallengeanswer)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/login/challenge" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"ticket":""}'
```

**レスポンス** `200` — `data`: [SessionToken](#sessiontoken)

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`challenge_invalid`](./api-troubleshooting_ja.md#challenge_invalid) · [`too_many_attempts`](./api-troubleshooting_ja.md#too_many_attempts)

### 顧客のアクセストークンを更新 — `POST /api/v2/front/refresh`

リフレッシュトークンを、新しいアクセストークンとリフレッシュトークンの組と交換します。各リフレッシュトークンは 1 回だけ使え、再使用するとセッションがサインアウトされます。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [RefreshRequest](#refreshrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/refresh" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":""}'
```

**レスポンス** `200` — `data`: [SessionToken](#sessiontoken)

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`not_found`](./api-troubleshooting_ja.md#not_found) · [`refresh_token_invalid`](./api-troubleshooting_ja.md#refresh_token_invalid) · [`refresh_token_reused`](./api-troubleshooting_ja.md#refresh_token_reused) · [`refresh_in_progress`](./api-troubleshooting_ja.md#refresh_in_progress) · [`unauthenticated`](./api-troubleshooting_ja.md#unauthenticated)

### 自分のセッション一覧 — `GET /api/v2/front/member/sessions`

ログイン中の顧客の有効な API セッション（デバイス）を返します。現在のセッションには印が付きます。ページ分割はありません。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.4.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/member/sessions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: 一覧： [Session](#session)

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

### 自分のセッションを 1 つサインアウト — `DELETE /api/v2/front/member/sessions/{id}`

顧客自身のセッションを 1 つサインアウトします。他の所有者の id は 404 を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト例**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/member/sessions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontMemberSessionsDeleteResult](#frontmembersessionsdeleteresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found)

## Front · 顧客アカウント

ログイン中の顧客の会員登録、パスワード再設定、プロフィール、アドレス帳、メールアドレスの確認。

### ログイン中の顧客（基本） — `GET /api/v2/front/info`

ログイン中の顧客のプロフィールを返します（ストアや確認状態の項目は含みません）。member/profile は同じ内容に加えて、確認状態とデフォルトの住所を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/info" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [Customer](#customer)

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

### 会員登録してログイン — `POST /api/v2/front/register`

ストア独自の項目ルール（有効な場合は captcha も）に従って顧客アカウントを作成し、ログインさせます。プロフィールとトークン情報を付けて 201 を返します。X-Cart-Token で送られたゲストカートは統合されます。IP ごとに回数制限があります。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [Register](#register)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","email":"","password":"","password_confirmation":"","with_refresh":false,"device_name":""}'
```

**レスポンス** `201` — `data`: [FrontRegisterResult](#frontregisterresult)

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`challenge_required`](./api-troubleshooting_ja.md#challenge_required) · [`challenge_invalid`](./api-troubleshooting_ja.md#challenge_invalid)

### パスワード再設定メールを送信 — `POST /api/v2/front/password/forgot`

パスワード再設定メールを送信します。メールアドレスにアカウントがあるかどうかに関係なく同じレスポンスを返すため、アカウントの探索には使えません。IP ごとに回数制限があります。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [ForgotPassword](#forgotpassword)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/password/forgot" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":""}'
```

**レスポンス** `200` — `data`: [FrontPasswordForgotResult](#frontpasswordforgotresult)

```json
{
    "data": {
        "sent": true
    }
}
```

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### トークンでパスワードを再設定 — `POST /api/v2/front/password/reset`

再設定メールのトークンで新しいパスワードを設定し、アカウントのすべての API セッションをサインアウトします。ログインは行いません。新しいパスワードで login を呼び出してください。誤ったトークンや期限切れのトークンは、token 項目の 422 になります。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [ResetPassword](#resetpassword)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/password/reset" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"","token":"","password":"","password_confirmation":""}'
```

**レスポンス** `200` — `data`: [FrontPasswordResetResult](#frontpasswordresetresult)

```json
{
    "data": {
        "reset": true
    }
}
```

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`too_many_attempts`](./api-troubleshooting_ja.md#too_many_attempts)

### 自分のプロフィールを取得 — `GET /api/v2/front/member/profile`

ログイン中の顧客のプロフィールを、メールアドレスの確認状態とデフォルトの住所とともに返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.4.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/member/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [CustomerProfile](#customerprofile)

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

### 自分のプロフィールを更新 — `PUT /api/v2/front/member/profile`

ストアが有効にしているプロフィール項目を更新し、更新後のプロフィールを返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [Profile](#profile)

**リクエスト例**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","email":""}'
```

**レスポンス** `200` — `data`: [CustomerProfile](#customerprofile)

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 自分のパスワードを変更 — `PUT /api/v2/front/member/password`

現在のパスワードを確認したうえでパスワードを変更します（現在のパスワードが誤っている場合は 422）。このセッションはログインしたままで、他のすべてのセッションはサインアウトされます。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [ChangePassword](#changepassword)

**リクエスト例**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/password" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"password_old":"","password":"","password_confirmation":""}'
```

**レスポンス** `200` — `data`: [FrontMemberPasswordUpdateResult](#frontmemberpasswordupdateresult)

```json
{
    "data": {
        "changed": true
    }
}
```

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`too_many_attempts`](./api-troubleshooting_ja.md#too_many_attempts)

### 確認メールを再送信 — `POST /api/v2/front/member/email/verify`

ストアがメールアドレスの確認を必須にしている場合、確認リンクを再送信します。必須でない場合、sent は false です。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/email/verify" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontMemberEmailVerifyResult](#frontmemberemailverifyresult)

```json
{
    "data": {
        "sent": true
    }
}
```

### 自分の住所一覧 — `GET /api/v2/front/member/address/list`

ログイン中の顧客のアドレス帳を古い順に返します。デフォルトの住所には印が付きます。ページ分割はありません。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.4.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/member/address/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: 一覧： [CustomerAddress](#customeraddress)

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

### 住所を追加 — `POST /api/v2/front/member/address`

ストアの住所項目ルールに従って住所を追加します。default: true を指定するとデフォルトの住所になります。住所を付けて 201 を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [Address](#address)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/address" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","address1":"","default":false}'
```

**レスポンス** `201` — `data`: [CustomerAddress](#customeraddress)

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 住所を更新 — `PUT /api/v2/front/member/address/{id}`

顧客自身の住所を 1 件更新します。他の顧客の id は 404 を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト本文**: [Address](#address)

**リクエスト例**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/member/address/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","address1":"","default":false}'
```

**レスポンス** `200` — `data`: [CustomerAddress](#customeraddress)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 住所を削除 — `DELETE /api/v2/front/member/address/{id}`

顧客自身の住所を 1 件削除します。デフォルトの住所は削除できません（422）。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.4.0` |

**リクエスト例**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/member/address/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontMemberAddressDeleteResult](#frontmemberaddressdeleteresult)

```json
{
    "data": {
        "deleted": true
    }
}
```

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

## Front · カート

ログイン中の顧客またはゲスト（カートトークン）のカート：明細とプロモーションコード。

### カートを取得 — `GET /api/v2/front/cart`

カートをストアごとにまとめて返します。価格はサーバーで計算され、購入できなくなった明細には印が付きます。顧客の Bearer トークン、またはストアがゲスト購入を許可している場合はゲストの X-Cart-Token ヘッダーで利用できます。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/cart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [Cart](#cart)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict)

### カートに商品を追加 — `POST /api/v2/front/cart/items`

属性の選択肢とともに商品を追加します（すべての属性グループの選択が必要です）。同じ商品を同じ選択肢で追加すると、その明細の数量が増えます。カート全体を付けて 201 を返します。カートを持たないゲストには、保存しておくための新しい X-Cart-Token レスポンスヘッダーが返されます。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**リクエスト本文**: [FrontCartItemsRequest](#frontcartitemsrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/cart/items" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"product_id":"","qty":0,"options":{}}'
```

**レスポンス** `201` — `data`: [Cart](#cart)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`not_found`](./api-troubleshooting_ja.md#not_found) · [`product_not_for_sale`](./api-troubleshooting_ja.md#product_not_for_sale) · [`cart_line_limit`](./api-troubleshooting_ja.md#cart_line_limit) · [`insufficient_stock`](./api-troubleshooting_ja.md#insufficient_stock)

### 明細の数量を設定 — `PATCH /api/v2/front/cart/items/{rowId}`

カート明細の数量を設定します。0 以下にすると明細が削除されます。カート全体を返します。顧客の Bearer トークン、またはストアがゲスト購入を許可している場合はゲストの X-Cart-Token ヘッダーで利用できます。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `rowId` *(必須)* | パス | `string` | カート明細の ID（カートから取得）。 |

**リクエスト本文**: [FrontCartItemsUpdateRequest](#frontcartitemsupdaterequest)

**リクエスト例**

```bash
curl -X PATCH "https://shop.example.com/api/v2/front/cart/items/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"qty":0}'
```

**レスポンス** `200` — `data`: [Cart](#cart)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`not_found`](./api-troubleshooting_ja.md#not_found) · [`insufficient_stock`](./api-troubleshooting_ja.md#insufficient_stock)

### カート明細を削除 — `DELETE /api/v2/front/cart/items/{rowId}`

明細を 1 件削除し、カート全体を返します。顧客の Bearer トークン、またはストアがゲスト購入を許可している場合はゲストの X-Cart-Token ヘッダーで利用できます。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `rowId` *(必須)* | パス | `string` | カート明細の ID（カートから取得）。 |

**リクエスト例**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/cart/items/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [Cart](#cart)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`not_found`](./api-troubleshooting_ja.md#not_found)

### プロモーションを適用 — `POST /api/v2/front/cart/promotions/{pluginKey}`

パスで指定したプロモーションプラグインを通じて、ペイロードとともにプロモーション（クーポン、ポイントなど）を適用します。プラグインのメッセージと新しい合計金額を返します。拒否された場合は 422 promotion_refused です。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `pluginKey` *(必須)* | パス | `string` | プロモーションプラグインのキー（カートのレスポンスから取得）。 |
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |

**リクエスト本文**: [FrontCartPromotionsRequest](#frontcartpromotionsrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/cart/promotions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"payload":{}}'
```

**レスポンス** `200` — `data`: [FrontCartPromotionsResult](#frontcartpromotionsresult)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity) · [`not_found`](./api-troubleshooting_ja.md#not_found) · [`promotion_refused`](./api-troubleshooting_ja.md#promotion_refused)

### プロモーションを解除 — `DELETE /api/v2/front/cart/promotions/{pluginKey}`

指定したプラグインのプロモーションを解除し、新しい合計金額を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `pluginKey` *(必須)* | パス | `string` | プロモーションプラグインのキー（カートのレスポンスから取得）。 |
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |

**リクエスト例**

```bash
curl -X DELETE "https://shop.example.com/api/v2/front/cart/promotions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontCartPromotionsDeleteResult](#frontcartpromotionsdeleteresult)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity) · [`not_found`](./api-troubleshooting_ja.md#not_found)

## Front · チェックアウト

住所、配送方法と支払い方法の選択、合計金額、注文の確定。

### チェックアウトの状態を取得 — `GET /api/v2/front/checkout`

カート内の 1 つのストアについて、これまでに選んだチェックアウトの内容（住所、配送方法、支払い方法、プロモーション）を返します。カートに複数のストアがある場合は store_id を送ってください。別のストアを選ぶと、そのストアのチェックアウトの選択は最初からになります。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/checkout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [CheckoutState](#checkoutstate)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity)

### 配送先住所を設定 — `PUT /api/v2/front/checkout/address`

入力した項目、または保存済みの住所（address_id、ログイン中の顧客のみ）から配送先住所を設定します。住所を変更すると、選択済みの配送方法はクリアされます。チェックアウトの状態を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |

**リクエスト本文**: [FrontCheckoutAddressUpdateRequest](#frontcheckoutaddressupdaterequest)

**リクエスト例**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/address" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"","last_name":"","first_name_kana":"","last_name_kana":"","email":"","phone":"","city":"","district":"","ward":"","address1":"","address2":"","address3":"","postcode":"","country":"","company":"","comment":"","address_id":"","save_address":false}'
```

**レスポンス** `200` — `data`: [CheckoutState](#checkoutstate)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`not_found`](./api-troubleshooting_ja.md#not_found)

### 配送方法の見積もり — `GET /api/v2/front/checkout/shipping-methods`

チェックアウトの住所とカートに対して見積もられた配送方法を返します。ストアが配送を使わない場合、required は false、rates は空です。先に住所を設定する必要があります。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/checkout/shipping-methods" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontCheckoutShippingMethodsResult](#frontcheckoutshippingmethodsresult)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity) · [`address_required`](./api-troubleshooting_ja.md#address_required)

### 配送方法を選択 — `PUT /api/v2/front/checkout/shipping-method`

見積もられた配送方法を rate_id で 1 つ選びます。見積もり価格は注文用に保持されます。チェックアウトの状態を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |

**リクエスト本文**: [FrontCheckoutShippingMethodUpdateRequest](#frontcheckoutshippingmethodupdaterequest)

**リクエスト例**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/shipping-method" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"rate_id":""}'
```

**レスポンス** `200` — `data`: [CheckoutState](#checkoutstate)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`method_unavailable`](./api-troubleshooting_ja.md#method_unavailable)

### 支払い方法一覧 — `GET /api/v2/front/checkout/payment-methods`

API で使える支払い方法を返します：オンラインの手続きがない方法（type none）と、ホスト型の支払いページを持つゲートウェイ（type hosted）です。Web サイト上でしか使えない方法は含まれません。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/checkout/payment-methods" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontCheckoutPaymentMethodsResult](#frontcheckoutpaymentmethodsresult)

```json
{
    "data": {
        "required": true,
        "methods": [],
        "selected": null
    }
}
```

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity)

### 支払い方法を選択 — `PUT /api/v2/front/checkout/payment-method`

使える支払い方法の中から key で 1 つ選びます。チェックアウトの状態を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |

**リクエスト本文**: [FrontCheckoutPaymentMethodUpdateRequest](#frontcheckoutpaymentmethodupdaterequest)

**リクエスト例**

```bash
curl -X PUT "https://shop.example.com/api/v2/front/checkout/payment-method" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"key":""}'
```

**レスポンス** `200` — `data`: [CheckoutState](#checkoutstate)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`method_unavailable`](./api-troubleshooting_ja.md#method_unavailable)

### チェックアウトの合計金額を取得 — `GET /api/v2/front/checkout/totals`

請求される合計行（小計、送料、税、割引、合計）を返します。これらの金額は保持され、注文確定時に再度照合されます。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `store_id` | クエリ | `string` | カートに複数のストアがある場合に、チェックアウトするストア |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/checkout/totals" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [CheckoutTotals](#checkouttotals)

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

**エラー**: [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity)

### 注文を確定 — `POST /api/v2/front/checkout/place`

チェックアウト中のストアの注文を、Idempotency-Key ヘッダーごとに 1 回だけ確定します。同じキーで再送すると、確定済みの注文を返します（201 ではなく 200）。API による注文は、サイトのオーナーが有効にするまでオフです（403 api_checkout_disabled）。合計金額を表示した後に価格が変わった場合は、409 price_changed で新しい合計金額が返されます。それを表示してから、再度確定してください。ゲストには、後で注文を参照・キャンセルするための access_token が返されます。ホスト型の支払い方法では、支払いページの URL が返されます。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）、またはゲストカートのカートトークン。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `Idempotency-Key` *(必須)* | ヘッダー | `string` | 変更ごとに選ぶ一意のキー（例：UUID）。同じキーで同じリクエストを再送しても、二重には適用されません。 |

**リクエスト本文**: [FrontCheckoutPlaceRequest](#frontcheckoutplacerequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/checkout/place" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>" \
  -H "Content-Type: application/json" \
  -d '{"store_id":"","return_url":""}'
```

**レスポンス** `201` — `data`: [FrontCheckoutPlaceResult](#frontcheckoutplaceresult)

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

**エラー**: [`api_checkout_disabled`](./api-troubleshooting_ja.md#api_checkout_disabled) · [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`login_required`](./api-troubleshooting_ja.md#login_required) · [`cart_conflict`](./api-troubleshooting_ja.md#cart_conflict) · [`cart_empty`](./api-troubleshooting_ja.md#cart_empty) · [`store_required`](./api-troubleshooting_ja.md#store_required) · [`invalid_quantity`](./api-troubleshooting_ja.md#invalid_quantity) · [`address_required`](./api-troubleshooting_ja.md#address_required) · [`shipping_method_required`](./api-troubleshooting_ja.md#shipping_method_required) · [`payment_method_required`](./api-troubleshooting_ja.md#payment_method_required) · [`method_unavailable`](./api-troubleshooting_ja.md#method_unavailable) · [`minimum_not_met`](./api-troubleshooting_ja.md#minimum_not_met) · [`price_changed`](./api-troubleshooting_ja.md#price_changed) · [`create_failed`](./api-troubleshooting_ja.md#create_failed) · [`token_missing`](./api-troubleshooting_ja.md#token_missing) · [`input_empty`](./api-troubleshooting_ja.md#input_empty) · [`shipping_unquotable`](./api-troubleshooting_ja.md#shipping_unquotable)

## Front · 注文

顧客の注文（または注文アクセストークンを持つゲストの注文）：一覧、詳細、支払い、キャンセル。

### 自分の注文を 1 件取得 — `GET /api/v2/front/member/order/detail/{id}`

ログイン中の顧客の注文を 1 件、明細と合計とともに返します。他の顧客の注文は 404 を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/member/order/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [Order](#order)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 自分の注文一覧 — `GET /api/v2/front/member/order/list`

ログイン中の顧客自身の注文を、明細と合計付き（管理者向けのリクエスト情報は除く）でページ単位に返します。status で絞り込み、created_at または total で並べ替えできます。fields[order] に対応しています。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/member/order/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: 一覧： [Order](#order) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 自分の注文の支払い状況 — `GET /api/v2/front/member/orders/{id}/payment`

ログイン中の顧客の注文 1 件について、決済ゲートウェイで確認された支払い状況と金額を返します。他の顧客の注文は 404 を返します。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/member/orders/123/payment" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontMemberOrdersPaymentResult](#frontmemberorderspaymentresult)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found)

### ゲスト注文の支払い状況 — `GET /api/v2/front/orders/{id}/payment`

X-Order-Access ヘッダーの注文アクセストークン（checkout/place で取得）で開いたゲスト注文の支払い状況を返します。誤ったトークンは、存在しない注文と同じ 404 を返します。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.5.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `X-Order-Access` *(必須)* | ヘッダー | `string` | ゲストが注文を確定したときに返された access_token |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/front/orders/123/payment" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [FrontOrdersPaymentResult](#frontorderspaymentresult)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found)

### 自分の未払い注文をキャンセル — `POST /api/v2/front/member/orders/{id}/cancel`

ログイン中の顧客の注文のうち、API で確定され、まだ処理されていない未払いの注文を 1 件キャンセルします。その在庫は戻されます。新しい支払い状況を返します。それ以外の注文は 409 order_not_cancellable です。

| | |
|---|---|
| ログイン | 顧客トークン（Bearer）。 |
| 導入バージョン | `shop 3.5.1` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/member/orders/123/cancel" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <customer-token>"
```

**レスポンス** `200` — `data`: [FrontMemberOrdersCancelResult](#frontmemberorderscancelresult)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`order_not_cancellable`](./api-troubleshooting_ja.md#order_not_cancellable)

### 未払いのゲスト注文をキャンセル — `POST /api/v2/front/orders/{id}/cancel`

API で確定された未払いのゲスト注文を、X-Order-Access トークンで開いてキャンセルします。在庫は戻されます。新しい支払い状況を返します。

| | |
|---|---|
| ログイン | 不要（公開）。 |
| 導入バージョン | `shop 3.5.1` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `X-Order-Access` *(必須)* | ヘッダー | `string` | ゲストが注文を確定したときに返された access_token |

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/front/orders/123/cancel" \
  -H "Accept: application/json"
```

**レスポンス** `200` — `data`: [FrontOrdersCancelResult](#frontorderscancelresult)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`order_not_cancellable`](./api-troubleshooting_ja.md#order_not_cancellable)

## データオブジェクト

上記のエンドポイントが送受信するオブジェクトの項目です。日付は ISO 8601 形式の文字列、金額はレコードの通貨での数値です。

### Meta

| 項目 | 型 | 意味 |
|---|---|---|
| `api_version` | `string` | 常に '2'。 |
| `packages` | 一覧： `string` | インストール済みの GP247 パッケージ（名前のみ、バージョンなし）。 |
| `languages` | 一覧： `string` | 有効な言語のコード。 |
| `locale` | `string` | サイトのデフォルト言語。 |
| `store_id` | `string` | 呼び出し元ドメインに応答するストア。 |

### Brand

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | ブランド ID。 |
| `name` | `string` | ブランド名。 |
| `alias` | `string` | URL スラッグ。 |
| `image` | `string \| null` | ロゴのパス。 |
| `url` | `string \| null` | ブランドの Web サイト。 |
| `sort` | `integer` | 並び順。 |
| `status` | `integer` | 1 = 有効。 |
| `store_id` | `string \| null` | ストア ID。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |

### Category

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | カテゴリー ID。 |
| `alias` | `string` | URL スラッグ。 |
| `name` | `string \| null` | 指定した言語での名前。 |
| `keyword` | `string \| null` | 指定した言語での SEO キーワード。 |
| `description` | `string \| null` | 指定した言語での説明。 |
| `image` | `string \| null` | 画像のパス。 |
| `parent` | `string \| null` | 親カテゴリーの ID。 |
| `top` | `integer` | 1 = トップメニューに表示。 |
| `sort` | `integer` | 並び順。 |
| `status` | `integer` | 1 = 有効。 |
| `store_id` | `string \| null` | ストア ID。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |

### Product

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 商品 ID（UUID）。 |
| `sku` | `string` | 在庫管理単位（SKU）コード。 |
| `alias` | `string` | 商品の URL スラッグ。 |
| `name` | `string \| null` | 指定した言語での商品名（ない場合は最初の言語）。 |
| `keyword` | `string \| null` | 指定した言語での SEO キーワード。 |
| `description` | `string \| null` | 指定した言語での短い説明。 |
| `content` | `string \| null` | 指定した言語での HTML 本文。詳細のレスポンスのみ（一覧には含まれません）。 *(詳細のみ)* |
| `image` | `string \| null` | 保存されているメイン画像のパス。 |
| `images` | 一覧： `string` | 追加画像のパス。詳細のレスポンスまたは include=images 指定時に値が入り、それ以外の一覧では空の配列です。 |
| `price` | `number \| null` | 定価。ストアがストアフロントで価格を非表示にしている場合は null（管理者には常に表示されます）。 |
| `price_final` | `number \| null` | ストアフロントで請求される価格（プロモーション適用後）。ストアが価格を非表示にしている場合は null。 |
| `tax_id` | `string \| null` | 税ルールの ID。'0' = 非課税、'auto' = ストアのデフォルトの税。 |
| `kind` | `integer` | 商品の種類：0 単品、1 セット（構成品から成る）、2 グループ。 |
| `product_type` | `string \| null` | 商品タイプ（例：物理商品、デジタル商品）。 |
| `brand_id` | `string \| null` | ブランド ID。 |
| `category_ids` | 一覧： `string` | 商品のカテゴリー ID（ストアフロント：ストアフロントに表示されるカテゴリーのみ）。 |
| `minimum` | `number` | 1 回の注文あたりの最小数量。 |
| `weight` | `number` | 重量。 |
| `weight_class` | `string \| null` | 重量の単位。 |
| `length` | `number` | 長さ。 |
| `width` | `number` | 幅。 |
| `height` | `number` | 高さ。 |
| `length_class` | `string \| null` | 長さの単位。 |
| `date_available` | `string \| null` | 商品の販売開始日（Y-m-d、カラムの生の値）。 |
| `store_id` | `string \| null` | 商品を所有するストア。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |
| `image_sizes` | `object` | メイン画像の絶対 URL：{original, thumb}（画像がない場合はサイトのプレースホルダー）。 |
| `stock_status` | `string` | in_stock または out_of_stock。ショップが在庫を管理していない場合や、在庫切れでも販売する場合は常に in_stock。 |
| `attributes` | 一覧： [ProductAttributeGroup](#productattributegroup) | 商品をカートに追加するために購入者が選ぶ必要のある属性グループ。 |
| `cost` | `number` | 管理者のみ。原価。 *(管理者のみ)* |
| `stock` | `number` | 管理者のみ。在庫数。 *(管理者のみ)* |
| `sold` | `number` | 管理者のみ。販売数。 *(管理者のみ)* |
| `status` | `integer` | 管理者のみ。1 = 有効、0 = 無効。 *(管理者のみ)* |
| `approve` | `integer` | 管理者のみ。承認フラグ：1 = 承認済み。 *(管理者のみ)* |
| `supplier_id` | `string \| null` | 管理者のみ。仕入先 ID。 *(管理者のみ)* |
| `view` | `integer` | 管理者のみ。閲覧数。 *(管理者のみ)* |
| `brand` | [Brand](#brand) | include=brand 指定時のみ：ブランド（ストアフロント：有効なブランドのみ）。 *(include 指定時のみ `brand`)* |
| `categories` | 一覧： [Category](#category) | include=categories 指定時のみ：カテゴリーの全情報。 *(include 指定時のみ `categories`)* |

### Banner

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | バナー ID。 |
| `name` | `string \| null` | バナー名。 |
| `image` | `string \| null` | 画像のパス。 |
| `url` | `string \| null` | リンク先。 |
| `target` | `string \| null` | リンク先のウィンドウ（例：_self、_blank）。 |
| `html` | `string \| null` | 任意の HTML コンテンツ。 |
| `type` | `string` | バナーの位置・種類のコード。 |
| `sort` | `integer` | 並び順。 |
| `status` | `integer` | 1 = 有効。 |
| `store_id` | `string \| null` | ストア ID。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |
| `click` | `integer` | 管理者のみ。クリック数。 *(管理者のみ)* |

### Page

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | ページ ID。 |
| `alias` | `string` | URL スラッグ。 |
| `image` | `string \| null` | 画像のパス。 |
| `name` | `string \| null` | 指定した言語でのタイトル。 |
| `keyword` | `string \| null` | SEO キーワード。 |
| `description` | `string \| null` | 短い説明。 |
| `content` | `string \| null` | HTML 本文。詳細のレスポンスのみ（一覧には含まれません）。 |
| `status` | `integer` | 1 = 有効。 |
| `store_id` | `string \| null` | ストア ID。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |

### SessionToken

| 項目 | 型 | 意味 |
|---|---|---|
| `access_token` | `string` | Authorization: Bearer &lt;token&gt; として送る Bearer トークン。 |
| `token_type` | `string` | 常に Bearer。 |
| `scopes` | 一覧： `string` | トークンの権限。 |
| `expires_at` | `string \| null` | アクセストークンの有効期限。サイトのタイムゾーンでの 'Y-m-d H:i:s'（ISO 8601 ではありません）。 |
| `expires_in` | `integer \| null` | アクセストークンの有効期限までの秒数。 |
| `session_id` | `string` | API セッション ID。サイトで gp247:update を実行済み（セッションのテーブルが存在する）の場合に含まれます。 |
| `refresh_token` | `string` | with_refresh=true の場合のみ：1 回限りのリフレッシュトークン。表示されるのは 1 回だけです。 |
| `refresh_expires_at` | `string` | with_refresh=true の場合のみ：リフレッシュトークンの有効期限（'Y-m-d H:i:s'）。 |
| `cart_merge` | `object` | 顧客ログインで X-Cart-Token のゲストカートが送られた場合のみ：明細数 {merged, dropped}。 |

### CustomerLoginV2

| 項目 | 型 | 意味 |
|---|---|---|
| `email` | `string` (email) | 顧客アカウントのメールアドレス。 *(必須)* |
| `password` | `string` | アカウントのパスワード。 *(必須)* |
| `remember_me` | `boolean` | 有効期間の長いトークン（Web サイトの「ログイン状態を保持」と同じ）。 |
| `with_refresh` | `boolean` | ローテーションするリフレッシュトークンも発行します。その場合、アクセストークンの有効期間は 60 分です |
| `device_name` | `string` | セッション一覧に表示されます |

### FrontLogoutResult

| 項目 | 型 | 意味 |
|---|---|---|
| `revoked` | `boolean` | 常に true。 |

### LoginChallengeAnswer

さらに、チャレンジプラグインが定義する項目（例：code）

| 項目 | 型 | 意味 |
|---|---|---|
| `ticket` | `string` | login の challenge_required レスポンスで受け取ったチケット。 *(必須)* |

### RefreshRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `refresh_token` | `string` | login または前回の更新で受け取ったリフレッシュトークン。各トークンは 1 回だけ使えます。 *(必須)* |

### Session

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | セッション ID。 |
| `name` | `string \| null` | ログイン時に送られたデバイス名。ない場合はユーザーエージェント。 |
| `ip` | `string \| null` | ログイン時の IP アドレス。 |
| `created_at` | `string \| null` | ログイン日時（ISO 8601）。 |
| `last_used_at` | `string \| null` | 最終利用日時（ISO 8601）。 |
| `refreshable` | `boolean` | セッションにリフレッシュトークンがあるかどうか。 |
| `refresh_expires_at` | `string \| null` | リフレッシュ可能な期間の終了日時（ISO 8601）。 |
| `current` | `boolean` | 呼び出し元のトークンのセッションでは true。 |

### FrontMemberSessionsDeleteResult

| 項目 | 型 | 意味 |
|---|---|---|
| `revoked` | `boolean` | 常に true。 |

### Customer

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 顧客 ID。 |
| `first_name` | `string \| null` | 名。 |
| `last_name` | `string \| null` | 姓。 |
| `first_name_kana` | `string \| null` | 名のカナ（日本のストア向け）。 |
| `last_name_kana` | `string \| null` | 姓のカナ（日本のストア向け）。 |
| `email` | `string` | メールアドレス（ログイン名）。 |
| `phone` | `string \| null` | 電話番号。 |
| `sex` | `integer \| null` | 0 = 女性、1 = 男性（カラムの生の値）。 |
| `birthday` | `string \| null` | 生年月日（Y-m-d、カラムの生の値）。 |
| `company` | `string \| null` | 会社。 |
| `country` | `string \| null` | 国コード。 |
| `postcode` | `string \| null` | 郵便番号。 |
| `city` | `string \| null` | 都市。 |
| `district` | `string \| null` | 地区。 |
| `ward` | `string \| null` | 区・町。 |
| `address1` | `string \| null` | 住所 1 行目。 |
| `address2` | `string \| null` | 住所 2 行目。 |
| `address3` | `string \| null` | 住所 3 行目。 |
| `status` | `integer` | 1 = 有効、0 = ロック中。 |
| `group` | `integer` | 顧客グループ（カラムの生の値）。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |
| `store_id` | `string \| null` | 管理者のみ。アカウントのストア。 *(管理者のみ)* |
| `email_verified_at` | `string \| null` | 管理者のみ。メールアドレスが確認された日時。'Y-m-d H:i:s' の生の値（ISO 8601 ではありません）。未確認の場合は null。 *(管理者のみ)* |

### FrontRegisterResult

| 項目 | 型 | 意味 |
|---|---|---|
| `customer` | [CustomerProfile](#customerprofile) | 新しいアカウント。 |
| `access_token` | `string` | Authorization: Bearer &lt;token&gt; として送る Bearer トークン。 |
| `token_type` | `string` | 常に Bearer。 |
| `scopes` | 一覧： `string` | トークンの権限。 |
| `expires_at` | `string \| null` | アクセストークンの有効期限。サイトのタイムゾーンでの 'Y-m-d H:i:s'（ISO 8601 ではありません）。 |
| `expires_in` | `integer \| null` | アクセストークンの有効期限までの秒数。 |
| `session_id` | `string` | API セッション ID。サイトで gp247:update を実行済み（セッションのテーブルが存在する）の場合に含まれます。 |
| `refresh_token` | `string` | with_refresh=true の場合のみ：1 回限りのリフレッシュトークン。表示されるのは 1 回だけです。 |
| `refresh_expires_at` | `string` | with_refresh=true の場合のみ：リフレッシュトークンの有効期限（'Y-m-d H:i:s'）。 |
| `cart_merge` | `object` | 顧客ログインで X-Cart-Token のゲストカートが送られた場合のみ：明細数 {merged, dropped}。 |

### Register

ストアの設定に応じたその他の顧客項目（last_name、phone、address1…、カスタム項目、有効な場合は captcha 項目）。with_refresh、device_name は login と同じ

| 項目 | 型 | 意味 |
|---|---|---|
| `first_name` | `string` | 名。 *(必須)* |
| `email` | `string` (email) | メールアドレス。ログイン名も兼ねます。 |
| `password` | `string` | パスワード（サイトのパスワードルールが適用されます）。 *(必須)* |
| `password_confirmation` | `string` | 同じパスワードをもう一度。 *(必須)* |
| `with_refresh` | `boolean` | リフレッシュトークンも返します。 |
| `device_name` | `string` | ログイン中のデバイス一覧に表示される名前。 |

### FrontPasswordForgotResult

| 項目 | 型 | 意味 |
|---|---|---|
| `sent` | `boolean` | 常に true。 |

### ForgotPassword

| 項目 | 型 | 意味 |
|---|---|---|
| `email` | `string` (email) | アカウントのメールアドレス。存在するかどうかに関係なく、同じレスポンスを返します。 *(必須)* |

### FrontPasswordResetResult

| 項目 | 型 | 意味 |
|---|---|---|
| `reset` | `boolean` | 常に true。 |

### ResetPassword

| 項目 | 型 | 意味 |
|---|---|---|
| `email` | `string` (email) | アカウントのメールアドレス。 *(必須)* |
| `token` | `string` | 再設定メールのトークン。 *(必須)* |
| `password` | `string` | 新しいパスワード。 *(必須)* |
| `password_confirmation` | `string` | 同じパスワードをもう一度。 *(必須)* |

### CustomerProfile

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 顧客 ID。 |
| `first_name` | `string \| null` | 名。 |
| `last_name` | `string \| null` | 姓。 |
| `first_name_kana` | `string \| null` | 名のカナ（日本のストア向け）。 |
| `last_name_kana` | `string \| null` | 姓のカナ（日本のストア向け）。 |
| `email` | `string` | メールアドレス（ログイン名）。 |
| `phone` | `string \| null` | 電話番号。 |
| `sex` | `integer \| null` | 0 = 女性、1 = 男性（カラムの生の値）。 |
| `birthday` | `string \| null` | 生年月日（Y-m-d、カラムの生の値）。 |
| `company` | `string \| null` | 会社。 |
| `country` | `string \| null` | 国コード。 |
| `postcode` | `string \| null` | 郵便番号。 |
| `city` | `string \| null` | 都市。 |
| `district` | `string \| null` | 地区。 |
| `ward` | `string \| null` | 区・町。 |
| `address1` | `string \| null` | 住所 1 行目。 |
| `address2` | `string \| null` | 住所 2 行目。 |
| `address3` | `string \| null` | 住所 3 行目。 |
| `status` | `integer` | 1 = 有効、0 = ロック中。 |
| `group` | `integer` | 顧客グループ（カラムの生の値）。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |
| `email_verified` | `boolean` | メールアドレスが確認済みかどうか。 |
| `email_verification_required` | `boolean` | ストアがメールアドレスの確認を必須にしているかどうか。 |
| `default_address_id` | `string \| null` | デフォルトの住所の ID。 |
| `default_address` | [CustomerAddress](#customeraddress) | デフォルトの住所（CustomerAddress の形式）、または null。 |

### Profile

ストアで設定された顧客項目。status と password は無視されます

| 項目 | 型 | 意味 |
|---|---|---|
| `first_name` | `string` | 名。 |
| `email` | `string` (email) | メールアドレス。変更すると、新しいアドレスの確認が必要になる場合があります。 |

### FrontMemberPasswordUpdateResult

| 項目 | 型 | 意味 |
|---|---|---|
| `changed` | `boolean` | 常に true。 |

### ChangePassword

| 項目 | 型 | 意味 |
|---|---|---|
| `password_old` | `string` | 現在のパスワード。 *(必須)* |
| `password` | `string` | 新しいパスワード。 *(必須)* |
| `password_confirmation` | `string` | 同じパスワードをもう一度。 *(必須)* |

### FrontMemberEmailVerifyResult

| 項目 | 型 | 意味 |
|---|---|---|
| `sent` | `boolean` | メールが送信された場合は true（ストアが確認を必須にしている場合）。 |

### CustomerAddress

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 住所 ID。 |
| `first_name` | `string \| null` | 名。 |
| `last_name` | `string \| null` | 姓。 |
| `first_name_kana` | `string \| null` | 名のカナ。 |
| `last_name_kana` | `string \| null` | 姓のカナ。 |
| `phone` | `string \| null` | 電話番号。 |
| `country` | `string \| null` | 国コード。 |
| `postcode` | `string \| null` | 郵便番号。 |
| `city` | `string \| null` | 都市。 |
| `district` | `string \| null` | 地区。 |
| `ward` | `string \| null` | 区・町。 |
| `address1` | `string \| null` | 住所 1 行目。 |
| `address2` | `string \| null` | 住所 2 行目。 |
| `address3` | `string \| null` | 住所 3 行目。 |
| `is_default` | `boolean` | 顧客のデフォルトの住所では true。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |

### Address

ストアの設定に応じたその他の住所項目。"default": true を指定するとデフォルトの住所になります

| 項目 | 型 | 意味 |
|---|---|---|
| `first_name` | `string` | 受取人の名。 *(必須)* |
| `address1` | `string` | 住所の 1 行目。 *(必須)* |
| `default` | `boolean` | デフォルトの住所にします。 |

### FrontMemberAddressDeleteResult

| 項目 | 型 | 意味 |
|---|---|---|
| `deleted` | `boolean` | 常に true。 |

### Cart

| 項目 | 型 | 意味 |
|---|---|---|
| `stores` | 一覧： `object` | ストアごとにまとめたカート明細：{store_id, lines: [CartLine], subtotal}。subtotal は購入可能な明細だけを合計します。 |
| `line_count` | `integer` | 明細の数。 |
| `item_count` | `number` | すべての明細の合計数量。 |
| `currency` | `string` | 価格の表示通貨。 |

### FrontCartItemsRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `product_id` | `string` | 商品の ID。 *(必須)* |
| `qty` | `number` | 追加する数量。 *(必須)* |
| `options` | `object` | 属性グループ ID =&gt; 選択肢名 |

### FrontCartItemsUpdateRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `qty` | `number` | 明細の新しい数量。 *(必須)* |

### FrontCartPromotionsResult

| 項目 | 型 | 意味 |
|---|---|---|
| `result` | `object` | {msg}：プロモーションプラグインのメッセージ。 |
| `totals` | [CheckoutTotals](#checkouttotals) | 新しい合計金額（CheckoutTotals の形式）。 |

### FrontCartPromotionsRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `payload` | `object` | プラグインが求める形式（例：{"code": "SUMMER"}） |

### FrontCartPromotionsDeleteResult

| 項目 | 型 | 意味 |
|---|---|---|
| `totals` | [CheckoutTotals](#checkouttotals) | 新しい合計金額（CheckoutTotals の形式）。 |

### CheckoutState

| 項目 | 型 | 意味 |
|---|---|---|
| `store_id` | `string` | チェックアウト中のストア。 |
| `address` | `object \| null` | これまでに設定された配送先住所：first_name, last_name, first_name_kana, last_name_kana, email, phone, city, district, ward, address1, address2, address3, postcode, country, company, comment（すべて文字列）。 |
| `save_address` | `boolean` | 住所を顧客のアドレス帳に保存するかどうか。 |
| `shipping_method` | `object \| null` | 選択された配送方法（ShippingRate の形式、見積もりが保持されていない場合は {plugin_key}）。未選択の場合は null。 |
| `payment_method` | `string \| null` | 選択された支払いプラグインのキー。 |
| `promotions` | 一覧： `string` | 適用されたプロモーションプラグインのキー。 |

### FrontCheckoutAddressUpdateRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `first_name` | `string` | 受取人の名。 |
| `last_name` | `string` | 受取人の姓。 |
| `first_name_kana` | `string` | 名のカナ（日本のストア向け）。 |
| `last_name_kana` | `string` | 姓のカナ（日本のストア向け）。 |
| `email` | `string` | 注文確認用のメールアドレス。 |
| `phone` | `string` | 電話番号。 |
| `city` | `string` | 都市または省・県。 |
| `district` | `string` | 地区。 |
| `ward` | `string` | 区・町。 |
| `address1` | `string` | 住所の 1 行目。 |
| `address2` | `string` | 住所の 2 行目。 |
| `address3` | `string` | 住所の 3 行目。 |
| `postcode` | `string` | 郵便番号。 |
| `country` | `string` | 国コード（ISO 3166-1 alpha-2、例：VN）。 |
| `company` | `string` | 会社名。 |
| `comment` | `string` | ショップへの備考。 |
| `address_id` | `string` | 顧客の保存済み住所のいずれか |
| `save_address` | `boolean` | ログイン中の顧客：アドレス帳にも保存します。 |

### FrontCheckoutShippingMethodsResult

| 項目 | 型 | 意味 |
|---|---|---|
| `required` | `boolean` | ストアが配送方法の選択を必須にしているかどうか。 |
| `rates` | 一覧： [ShippingRate](#shippingrate) | 見積もられた選択肢。 |
| `unquotable` | 一覧： `string` | この荷物の見積もりができなかった配送プラグインのキー。 |
| `selected` | `string \| null` | すでに選択された配送方法の ID（この住所とカートに対してまだ有効な場合）。 |

### FrontCheckoutShippingMethodUpdateRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `rate_id` | `string` | checkout/shipping-methods で取得した配送方法の ID。 *(必須)* |

### FrontCheckoutPaymentMethodsResult

| 項目 | 型 | 意味 |
|---|---|---|
| `required` | `boolean` | ストアが支払い方法の選択を必須にしているかどうか。 |
| `methods` | 一覧： [PaymentMethod](#paymentmethod) | 使える支払い方法。 |
| `selected` | `string \| null` | すでに選択された支払い方法のキー。 |

### FrontCheckoutPaymentMethodUpdateRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `key` | `string` | checkout/payment-methods で取得した支払い方法のキー。 *(必須)* |

### CheckoutTotals

| 項目 | 型 | 意味 |
|---|---|---|
| `store_id` | `string` | チェックアウト中のストア。 |
| `lines` | 一覧： [OrderTotal](#ordertotal) | 合計行（OrderTotal と同じ形式）：code、title、value、text。 |
| `currency` | `string` | 表示通貨。 |

### FrontCheckoutPlaceRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `store_id` | `string` | 注文する明細のストア（カートには複数のストアの明細が入ることがあります）。 |
| `return_url` | `string` | ゲートウェイでの手続き後に支払者が戻る先。サイトの許可リスト（GP247_PAYMENT_RETURN_URLS）にある場合のみ使われます |

### FrontCheckoutPlaceResult

| 項目 | 型 | 意味 |
|---|---|---|
| `order` | `object` | 確定した注文の {id, store_id, status, payment_status, total, balance, currency}。 |
| `access_token` | `string` | ゲストのみ：注文アクセストークン。orders/{id}/payment と orders/{id}/cancel で X-Order-Access として送ります。 |
| `payment` | `object` | {type: none} または {type: hosted, url}：url はゲートウェイのページの URL です。ゲートウェイを開始できなかった場合（同じキーで再試行してください）や、支払う金額がない場合は null。 |

### Order

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 注文 ID。 |
| `customer_id` | `string \| null` | 顧客 ID（ゲスト注文では '0' または空）。 |
| `status` | `integer` | 注文ステータスの ID（管理画面の注文画面で設定したステータス）。 |
| `payment_status` | `integer` | 支払いステータスの ID。 |
| `shipping_status` | `integer` | 配送ステータスの ID。 |
| `currency` | `string` | 注文の通貨。 |
| `exchange_rate` | `number` | 注文に使われた為替レート。 |
| `subtotal` | `number` | 明細の合計。 |
| `tax` | `number` | 税。 |
| `shipping` | `number` | 送料。 |
| `discount` | `number` | 割引。 |
| `other_fee` | `number` | その他の手数料。 |
| `total` | `number` | 支払金額。 |
| `received` | `number` | これまでに受け取った金額。 |
| `balance` | `number` | 未払いの残額。 |
| `first_name` | `string \| null` | 配送先の名。 |
| `last_name` | `string \| null` | 配送先の姓。 |
| `email` | `string \| null` | 購入者のメールアドレス。 |
| `phone` | `string \| null` | 購入者の電話番号。 |
| `company` | `string \| null` | 会社。 |
| `country` | `string \| null` | 国コード。 |
| `postcode` | `string \| null` | 郵便番号。 |
| `city` | `string \| null` | 都市。 |
| `district` | `string \| null` | 地区。 |
| `ward` | `string \| null` | 区・町。 |
| `address1` | `string \| null` | 住所 1 行目。 |
| `address2` | `string \| null` | 住所 2 行目。 |
| `address3` | `string \| null` | 住所 3 行目。 |
| `comment` | `string \| null` | 購入者の備考。 |
| `payment_method` | `string \| null` | 支払いプラグインのキー。 |
| `shipping_method` | `string \| null` | 配送プラグインのキー。 |
| `store_id` | `string \| null` | 注文のストア。 |
| `details` | 一覧： [OrderLine](#orderline) | 注文明細。 |
| `totals` | 一覧： [OrderTotal](#ordertotal) | 表示順の合計行。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |
| `ip` | `string \| null` | 管理者のみ。注文元の IP アドレス。 *(管理者のみ)* |
| `user_agent` | `string \| null` | 管理者のみ。ブラウザー／アプリのユーザーエージェント。 *(管理者のみ)* |
| `device_type` | `string \| null` | 管理者のみ。デバイスの種類（desktop、phone、tablet、other）。 *(管理者のみ)* |
| `domain` | `string \| null` | 管理者のみ。注文が確定されたサイトのドメイン。 *(管理者のみ)* |
| `transaction` | `string \| null` | 管理者のみ。従来の取引参照番号。 *(管理者のみ)* |
| `transactions` | 一覧： [OrderTransaction](#ordertransaction) | 管理者のみ、include=transactions 指定時：注文の入出金。 *(管理者のみ, include 指定時のみ `transactions`)* |
| `histories` | 一覧： [OrderHistory](#orderhistory) | 管理者のみ、include=histories 指定時：注文履歴の行（プレーンテキスト）。 *(管理者のみ, include 指定時のみ `histories`)* |

### FrontMemberOrdersPaymentResult

| 項目 | 型 | 意味 |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}。住所と明細は含みません。 |
| `payment` | `object` | ゲートウェイから記録された {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid}。 |

### FrontOrdersPaymentResult

| 項目 | 型 | 意味 |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}。住所と明細は含みません。 |
| `payment` | `object` | ゲートウェイから記録された {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid}。 |

### FrontMemberOrdersCancelResult

| 項目 | 型 | 意味 |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}。住所と明細は含みません。 |
| `payment` | `object` | ゲートウェイから記録された {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid}。 |

### FrontOrdersCancelResult

| 項目 | 型 | 意味 |
|---|---|---|
| `order` | `object` | {id, store_id, status, payment_status, total, received, balance, currency}。住所と明細は含みません。 |
| `payment` | `object` | ゲートウェイから記録された {type: none\|hosted, status: paid\|partially_paid\|refunded\|unpaid}。 |

### ProductAttributeGroup

| 項目 | 型 | 意味 |
|---|---|---|
| `group_id` | `string` | 属性グループ ID。カートは選択を options[group_id] として受け取ります。 |
| `name` | `string` | グループ名（例：色）。 |
| `required` | `boolean` | 常に true：すべてのグループを選択する必要があります。 |
| `options` | 一覧： `object` | グループの選択肢：{value, add_price}。add_price は追加料金で、価格が非表示の場合は null。 |

### ShippingRate

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | rate_id として送るレート ID。 |
| `plugin_key` | `string` | 配送プラグインのキー。 |
| `service_code` | `string` | プラグイン内のサービスコード。 |
| `title` | `string` | 表示するラベル。 |
| `amount` | `number` | 表示通貨での価格。 |
| `currency` | `string` | 表示通貨。 |
| `eta_min_days` | `integer \| null` | 最短配送日数。 |
| `eta_max_days` | `integer \| null` | 最長配送日数。 |

### PaymentMethod

| 項目 | 型 | 意味 |
|---|---|---|
| `key` | `string` | key として送る支払いプラグインのキー。 |
| `title` | `string` | ラベル。 |
| `note` | `string` | 支払い方法の補足説明。 |
| `type` | `string` | none = オンライン決済の手続きなし。hosted = 支払者をゲートウェイのページへ移動させます。 |

### OrderTotal

| 項目 | 型 | 意味 |
|---|---|---|
| `code` | `string` | 行コード（例：subtotal、tax、shipping、discount、total）。 |
| `title` | `string` | 行のラベル。 |
| `value` | `number` | 金額。 |
| `text` | `string` | 表示用に整形した金額。 |

### OrderLine

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 明細 ID。 |
| `product_id` | `string` | 商品 ID。 |
| `sku` | `string \| null` | 注文時の SKU。 |
| `name` | `string \| null` | 注文時の商品名。 |
| `price` | `number` | 単価。 |
| `qty` | `number` | 数量。 |
| `discount` | `number` | 明細の割引。 |
| `tax` | `number` | 明細の税。 |
| `tax_rate` | `number` | 税率（パーセント）。 |
| `total_price` | `number` | 明細の合計。 |
| `attribute` | `string \| null` | 選択された属性。JSON エンコードされた文字列 {group_id: 'name__price[__slug]'}、カラムの生の値。 |

### OrderTransaction

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 取引 ID。 |
| `type` | `string` | 入出金の種類（例：支払い、返金）。 |
| `amount` | `number` | 金額。 |
| `currency` | `string` | 通貨。 |
| `method` | `string \| null` | 支払い方法のキー。 |
| `gateway_transaction_id` | `string \| null` | 決済ゲートウェイ側の参照番号。 |
| `paid_at` | `string \| null` | 支払日時（ISO 8601）。 |
| `created_at` | `string \| null` | 記録日時（ISO 8601）。 |

### OrderHistory

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 履歴行の ID。 |
| `content` | `string` | 履歴行のテキスト（HTML は除去済み）。 |
| `order_status_id` | `integer` | その行の時点での注文ステータス。 |
| `admin_id` | `string \| null` | 記入した管理者。 |
| `customer_id` | `string \| null` | 記入した顧客。 |
| `created_at` | `string \| null` | 記入日時（ISO 8601）。 |

---

<sub>📅 **最終更新:** 2026-11-03 · ✍️ **作成者 (Author):** GP247</sub>
