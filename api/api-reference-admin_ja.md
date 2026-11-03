> 🌐 **言語:** [🇻🇳 Tiếng Việt](./api-reference-admin_vi.md) · [🇬🇧 English](./api-reference-admin.md) · 🇯🇵 日本語（現在）

# API v2 リファレンス — 管理（Core）

> このページは tools/openapi-to-markdown.php によって openapi.json から生成されています。手作業で編集しないでください。

`api/v2/core` 配下のすべてのエンドポイント（管理者とバックオフィスシステム（ERP、CRM、倉庫）向けのデータ）について、必要な管理者権限、パラメーター、呼び出し例、返す内容をまとめています。

API を初めて使う場合は、まず [クイックスタート](./api-quickstart_ja.md) をご覧ください。すべてのエンドポイントに共通するルール（レスポンス形式、ページ分割、`fields`、`include`、`304`）は [共通ルール](./api-conventions_ja.md) に、各エラーコードの説明は [トラブルシューティング](./api-troubleshooting_ja.md) にあります。

## 目次

- [Admin · ログインとセッション](#admin--ログインとセッション) — 管理者のログイン、トークンの更新、2 段階ログイン、自分のアカウント、ログイン中のデバイス。
- [Admin · カタログ](#admin--カタログ) — 管理画面に表示されるとおりの商品、カテゴリー、ブランド、仕入先。商品の変更。
- [Admin · 在庫](#admin--在庫) — 1 つの商品、または複数の商品の在庫を一括で変更（倉庫／ERP との同期）。
- [Admin · コンテンツ](#admin--コンテンツ) — 管理画面に表示されるとおりのバナーとコンテンツページ。
- [Admin · 顧客](#admin--顧客) — 顧客アカウントと、アカウントのロック・ロック解除。
- [Admin · 注文](#admin--注文) — 注文、そのステータス、配送情報、メモ。
- [Admin · 同期](#admin--同期) — ERP／同期ツール向けのイベントログ：注文、在庫、商品、顧客のすべての変更をカーソルで読み取ります。
- [データオブジェクト](#データオブジェクト)

## Admin · ログインとセッション

管理者のログイン、トークンの更新、2 段階ログイン、自分のアカウント、ログイン中のデバイス。

### ログイン中の管理者のプロフィール — `GET /api/v2/core/info`

トークンを所有する管理者のプロフィールとロールのスラッグを返します。有効な管理者トークンであれば呼び出せ、画面の権限は不要です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 有効な管理者トークン以外は不要。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `core 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/info" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**レスポンス** `200` — `data`: [AdminInfo](#admininfo)

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

### 管理者ログイン — `POST /api/v2/core/login`

有効な管理者をユーザー名とパスワードでログインさせ、Bearer トークンを返します。with_refresh=true の場合、レスポンスにリフレッシュトークンが追加され、アクセストークンの有効期間は短くなります。プラグインが 2 段階目（MFA）を要求する場合、レスポンスは 401 challenge_required となり、login/challenge で完了するためのチケットが付きます。失敗した試行はアカウントと IP ごとに回数が制限されます。

| | |
|---|---|
| ログイン | 不要（この呼び出しでログインします）。 |
| 権限 | 有効な管理者トークン以外は不要。 |
| 導入バージョン | `core 3.3.0` |

**リクエスト本文**: [AdminLoginV2](#adminloginv2)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"","password":"","remember_me":false,"with_refresh":false,"device_name":""}'
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

### 管理者のサインアウト — `POST /api/v2/core/logout`

現在のセッションをサインアウトします：そのアクセストークンは使えなくなり、リフレッシュトークンも使えなくなります。有効な管理者トークンであれば呼び出せます。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 有効な管理者トークン以外は不要。 |
| 導入バージョン | `core 3.3.0` |

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**レスポンス** `200` — `data`: [AdminLogoutResult](#adminlogoutresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

### 管理者の MFA ログインを完了 — `POST /api/v2/core/login/challenge`

challenge_required レスポンスのチケットを、プラグイン独自の項目（例：ワンタイムコード）とともに送ります。成功すると login と同じ形式で応答します。誤った回答はログイン回数の制限に数えられます。チケットの有効期間は 5 分で、誤った回答は 5 回まで許されます。

| | |
|---|---|
| ログイン | 不要（この呼び出しでログインします）。 |
| 権限 | 有効な管理者トークン以外は不要。 |
| 導入バージョン | `core 3.5.1` |

**リクエスト本文**: [LoginChallengeAnswer](#loginchallengeanswer)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/login/challenge" \
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

### 管理者のアクセストークンを更新 — `POST /api/v2/core/refresh`

リフレッシュトークンを、新しいアクセストークンと新しいリフレッシュトークンに交換します。各リフレッシュトークンは 1 回だけ使えます。使用済みのトークンを再度使うとセッション全体がサインアウトされます（refresh_token_reused）。ただし、使用から 10 秒以内は除きます（refresh_in_progress）。サイトでまだ gp247:update を実行していない場合は 404 を返します。

| | |
|---|---|
| ログイン | 不要（この呼び出しでログインします）。 |
| 権限 | 有効な管理者トークン以外は不要。 |
| 導入バージョン | `core 3.5.1` |

**リクエスト本文**: [RefreshRequest](#refreshrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/refresh" \
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

### 管理者のセッション一覧 — `GET /api/v2/core/sessions`

ログイン中の管理者の有効な API セッション（デバイス）を新しい順に返します。現在のセッションには印が付きます。トークンそのものは返しません。ページ分割はありません。サイトで gp247:update を実行する前は空の配列です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 有効な管理者トークン以外は不要。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `core 3.5.1` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/sessions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### 管理者のセッションを 1 つサインアウト — `DELETE /api/v2/core/sessions/{id}`

管理者自身のセッションを 1 つ（そのアクセストークンとリフレッシュトークン）サインアウトします。他の所有者のセッション id は 404 を返します。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 有効な管理者トークン以外は不要。 |
| 導入バージョン | `core 3.5.1` |

**リクエスト例**

```bash
curl -X DELETE "https://shop.example.com/api/v2/core/sessions/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**レスポンス** `200` — `data`: [AdminSessionsDeleteResult](#adminsessionsdeleteresult)

```json
{
    "data": {
        "revoked": true
    }
}
```

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found)

## Admin · カタログ

管理画面に表示されるとおりの商品、カテゴリー、ブランド、仕入先。商品の変更。

### ブランドを 1 件取得（管理） — `GET /api/v2/core/brand/detail/{id}`

管理者のストアのブランドを 1 件、管理者専用の項目付きで返します。他のストアのレコードは 404 を返します。fields[brand]=a,b に対応しています。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「ブランド」（gp247_admin/brand）の閲覧権限。 |
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
curl "https://shop.example.com/api/v2/core/brand/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### ブランド一覧（管理） — `GET /api/v2/core/brand/list`

管理者のストアのブランドをページ単位で返します。対応する管理画面を開ける管理者トークンが必要です。status で絞り込み、sort または name で並べ替え、name で検索できます。fields[brand]=a,b で一部の項目だけを返せます。updated_since=&lt;ISO 8601&gt; の同期モードに対応しています：古い変更から順に返し、page[after] でページ送りします（その場合 meta には per_page、next_after、has_more、sync_until が入り、links は next のみ）。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「ブランド」（gp247_admin/brand）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/brand/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### カテゴリーを 1 件取得（管理） — `GET /api/v2/core/category/detail/{id}`

管理者のストアのカテゴリーを 1 件、管理者専用の項目付きで返します。他のストアのレコードは 404 を返します。fields[category]=a,b に対応しています。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「カテゴリー」（gp247_admin/category）の閲覧権限。 |
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
curl "https://shop.example.com/api/v2/core/category/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### カテゴリー一覧（管理） — `GET /api/v2/core/category/list`

管理者のストアのカテゴリーをページ単位で返します。対応する管理画面を開ける管理者トークンが必要です。テキスト項目は ?lang= で指定した言語です。parent または status で絞り込み、sort または created_at で並べ替え、name で検索できます。fields[category]=a,b で一部の項目だけを返せます。updated_since=&lt;ISO 8601&gt; の同期モードに対応しています：古い変更から順に返し、page[after] でページ送りします（その場合 meta には per_page、next_after、has_more、sync_until が入り、links は next のみ）。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「カテゴリー」（gp247_admin/category）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/category/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### 商品を 1 件取得（管理） — `GET /api/v2/core/product/detail/{id}`

管理者のストアの商品を 1 件、管理者専用の項目付きで返します。他のストアのレコードは 404 を返します。fields[product]=a,b と include=images,brand,categories に対応しています。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「商品」（gp247_admin/product）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |
| `include` | クエリ | `string` | 埋め込む関連レコードをカンマ区切りで指定します（1 階層、最大 3 つ）：images、brand、categories。それ以外は 422。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/product/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### 商品の価格・原価・ステータスを更新 — `PATCH /api/v2/core/product/{id}`

管理者のストアの商品の販売価格、原価、ステータス（0 または 1）を変更します（いずれか、または複数）。それ以外の項目や、ストアが商品設定でオフにした価格項目は 422 で拒否されます。管理者向けの商品情報全体を返します。Idempotency-Key は任意です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「商品」（gp247_admin/product）の書き込み権限。 |
| 導入バージョン | `shop 3.6.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `Idempotency-Key` | ヘッダー | `string` | 任意。8-255 文字の印字可能文字（例：UUID）。同じキーと本文で再試行すると、最初の結果を返します |

**リクエスト本文**: [AdminProductUpdateRequest](#adminproductupdaterequest)

**リクエスト例**

```bash
curl -X PATCH "https://shop.example.com/api/v2/core/product/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"price":0,"cost":0,"status":0}'
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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`forbidden`](./api-troubleshooting_ja.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_ja.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_ja.md#idempotency_unavailable)

### 商品一覧（管理） — `GET /api/v2/core/product/list`

管理者のストアの商品をページ単位で返します。対応する管理画面を開ける管理者トークンが必要です。在庫と承認の項目（cost、stock、sold、status、approve、supplier_id、view）を含み、ステータスに関係なくすべての商品を返します。絞り込み：category_id、brand_id、kind、product_type、price_min、price_max、status、approve、supplier_id。並べ替え：price、created_at、sort、sku。検索：SKU または名前。include=images,brand,categories に対応しています。fields[product]=a,b で一部の項目だけを返せます。updated_since=&lt;ISO 8601&gt; の同期モードに対応しています：古い変更から順に返し、page[after] でページ送りします（その場合 meta には per_page、next_after、has_more、sync_until が入り、links は next のみ）。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「商品」（gp247_admin/product）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |
| `include` | クエリ | `string` | 埋め込む関連レコードをカンマ区切りで指定します（1 階層、最大 3 つ）：images、brand、categories。それ以外は 422。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/product/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### 仕入先を 1 件取得（管理） — `GET /api/v2/core/supplier/detail/{id}`

管理者のストアの仕入先を 1 件、管理者専用の項目付きで返します。他のストアのレコードは 404 を返します。fields[supplier]=a,b に対応しています。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「仕入先」（gp247_admin/supplier）の閲覧権限。 |
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
curl "https://shop.example.com/api/v2/core/supplier/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**レスポンス** `200` — `data`: [Supplier](#supplier)

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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 仕入先一覧（管理） — `GET /api/v2/core/supplier/list`

管理者のストアの仕入先をページ単位で返します。対応する管理画面を開ける管理者トークンが必要です。status で絞り込み、sort または name で並べ替え、name またはメールアドレスで検索できます。fields[supplier]=a,b で一部の項目だけを返せます。updated_since=&lt;ISO 8601&gt; の同期モードに対応しています：古い変更から順に返し、page[after] でページ送りします（その場合 meta には per_page、next_after、has_more、sync_until が入り、links は next のみ）。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「仕入先」（gp247_admin/supplier）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/supplier/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**レスポンス** `200` — `data`: 一覧： [Supplier](#supplier) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

## Admin · 在庫

1 つの商品、または複数の商品の在庫を一括で変更（倉庫／ERP との同期）。

### 商品の在庫を増減・設定 — `POST /api/v2/core/product/{id}/stock`

delta（正または負）で入庫・出庫するか、棚卸し後に set で在庫を上書きします（管理者のみ）。在庫がゼロ未満になることはなく、販売数のカウンターは変わりません。セット商品は拒否されます。再試行で在庫が二重に動かないよう、Idempotency-Key ヘッダーが必須です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「商品」（gp247_admin/product）の書き込み権限。 |
| 導入バージョン | `shop 3.6.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `Idempotency-Key` *(必須)* | ヘッダー | `string` | 必須。8-255 文字の印字可能文字（例：UUID）。同じキーと本文で再試行しても在庫は 1 回だけ動き、最初の結果を返します（ヘッダー Idempotent-Replayed: true） |

**リクエスト本文**: [AdminProductStockRequest](#adminproductstockrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/product/123/stock" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"delta":0,"set":0}'
```

**レスポンス** `200` — `data`: [AdminProductStockResult](#adminproductstockresult)

```json
{
    "data": {
        "id": "9f0c2a3e-6b1d-4c5e-8a7f-2d3b4c5d6e7f",
        "sku": "TSHIRT-RED-M",
        "stock": 15
    }
}
```

**エラー**: [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_ja.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_ja.md#idempotency_unavailable) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`not_found`](./api-troubleshooting_ja.md#not_found) · [`forbidden`](./api-troubleshooting_ja.md#forbidden) · [`stock_managed_by_components`](./api-troubleshooting_ja.md#stock_managed_by_components) · [`stock_would_be_negative`](./api-troubleshooting_ja.md#stock_would_be_negative)

### 複数商品の在庫を増減 — `POST /api/v2/core/stock/batch`

1〜100 行の在庫行を適用します。各行は id または SKU で商品を指定し、delta または set を持ちます。各行に個別の結果が返り、不正な行があっても他の行は止まりません。一部の行が失敗しても、レスポンスは 200 です。Idempotency-Key は必須です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「商品」（gp247_admin/product）の書き込み権限。 |
| 導入バージョン | `shop 3.6.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `Idempotency-Key` *(必須)* | ヘッダー | `string` | 必須。8-255 文字の印字可能文字（例：UUID）。同じキーと本文で再試行しても在庫は 1 回だけ動き、最初の結果を返します（ヘッダー Idempotent-Replayed: true） |

**リクエスト本文**: [AdminStockBatchRequest](#adminstockbatchrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/stock/batch" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"items":[[]]}'
```

**レスポンス** `200` — `data`: [AdminStockBatchResult](#adminstockbatchresult)

```json
{
    "data": {
        "results": [],
        "ok": 9,
        "failed": 1
    }
}
```

**エラー**: [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_ja.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_ja.md#idempotency_unavailable) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`forbidden`](./api-troubleshooting_ja.md#forbidden)

## Admin · コンテンツ

管理画面に表示されるとおりのバナーとコンテンツページ。

### バナーを 1 件取得（管理） — `GET /api/v2/core/banner/detail/{id}`

管理者のストアのバナーを 1 件、クリック数を含めて返します。「バナー」画面を開ける管理者トークンが必要です。他のストアのバナーは 404 を返します。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「バナー」（gp247_admin/banner）の閲覧権限。 |
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
curl "https://shop.example.com/api/v2/core/banner/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### バナー一覧（管理） — `GET /api/v2/core/banner/list`

管理者のストアのバナーを、クリック数を含めてページ単位で返します。「バナー」画面を開ける管理者トークンが必要です。type で絞り込み、sort または created_at で並べ替え、name で検索できます。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「バナー」（gp247_admin/banner）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `front 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/banner/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### CMS ページを 1 件取得（管理） — `GET /api/v2/core/page/detail/{id}`

管理者のストアの CMS ページを 1 件、HTML 本文付きで、?lang= で指定した言語で返します。「ページ」画面へのアクセス権限が必要です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「ページ」（gp247_admin/page）の閲覧権限。 |
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
curl "https://shop.example.com/api/v2/core/page/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### CMS ページ一覧（管理） — `GET /api/v2/core/page/list`

管理者のストアの CMS ページを、HTML 本文なしでページ単位に返します。テキスト項目は ?lang= で指定した言語です（指定しない場合はサイトのデフォルト）。「ページ」画面へのアクセス権限が必要です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「ページ」（gp247_admin/page）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `front 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/page/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

## Admin · 顧客

顧客アカウントと、アカウントのロック・ロック解除。

### 顧客を 1 件取得（管理） — `GET /api/v2/core/customer/detail/{id}`

管理者のストアの顧客を 1 件、管理者専用の項目付きで返します。他のストアのレコードは 404 を返します。fields[customer]=a,b に対応しています。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「顧客」（gp247_admin/customer）の閲覧権限。 |
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
curl "https://shop.example.com/api/v2/core/customer/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 顧客一覧（管理） — `GET /api/v2/core/customer/list`

管理者のストアの顧客をページ単位で返します。対応する管理画面を開ける管理者トークンが必要です。管理者専用の項目 store_id と email_verified_at を含みますが、パスワードは決して返しません。status または group で絞り込み、created_at または email で並べ替え、メールアドレス、電話番号、名前で検索できます。fields[customer]=a,b で一部の項目だけを返せます。updated_since=&lt;ISO 8601&gt; の同期モードに対応しています：古い変更から順に返し、page[after] でページ送りします（その場合 meta には per_page、next_after、has_more、sync_until が入り、links は next のみ）。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「顧客」（gp247_admin/customer）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/customer/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**レスポンス** `200` — `data`: 一覧： [Customer](#customer) — `meta`（ページ情報）と `links` 付き。

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

**エラー**: [`validation_failed`](./api-troubleshooting_ja.md#validation_failed)

### 顧客をロック・ロック解除 — `PATCH /api/v2/core/customer/{id}`

管理者のストアの顧客のステータスを設定します：0 はアカウントを即座にロックし（API とストアフロントの両方）、1 はロックを解除します。それ以外の項目は 422 で拒否されます。管理者専用の項目付きで顧客を返します。Idempotency-Key は任意です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「顧客」（gp247_admin/customer）の書き込み権限。 |
| 導入バージョン | `shop 3.6.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `Idempotency-Key` | ヘッダー | `string` | 任意。8-255 文字の印字可能文字（例：UUID）。同じキーと本文で再試行すると、最初の結果を返します |

**リクエスト本文**: [AdminCustomerUpdateRequest](#admincustomerupdaterequest)

**リクエスト例**

```bash
curl -X PATCH "https://shop.example.com/api/v2/core/customer/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"status":0}'
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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`forbidden`](./api-troubleshooting_ja.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_ja.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_ja.md#idempotency_unavailable)

## Admin · 注文

注文、そのステータス、配送情報、メモ。

### 注文を 1 件取得（管理） — `GET /api/v2/core/order/detail/{id}`

管理者のストアの注文を 1 件、管理者専用の項目付きで返します。他のストアのレコードは 404 を返します。fields[order]=a,b と include=transactions,histories に対応しています。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面の注文詳細画面（gp247_admin/order/detail/{id}）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |
| `include` | クエリ | `string` | 埋め込む関連レコードをカンマ区切りで指定します（1 階層、最大 3 つ）：transactions（支払い台帳）、histories（処理履歴、プレーンテキスト）。それ以外は 422。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/order/detail/123" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

### 注文ステータスを変更 — `POST /api/v2/core/order/{id}/status`

管理画面の注文画面とまったく同じ方法で注文ステータスを設定します：キャンセルすると在庫が戻り、キャンセル済みの注文を再開すると在庫が再び引き当てられ（在庫が不足する場合は 409 で拒否）、履歴行が書き込まれます。order/detail と同じ形式で注文を返します。Idempotency-Key ヘッダーは任意で、同じキーで再試行すると最初のレスポンスを再送します。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「注文」（gp247_admin/order）の書き込み権限。 |
| 導入バージョン | `shop 3.6.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `Idempotency-Key` | ヘッダー | `string` | 任意。8-255 文字の印字可能文字（例：UUID）。同じキーと本文で再試行すると、最初の結果を返します |

**リクエスト本文**: [AdminOrderStatusRequest](#adminorderstatusrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"status":0}'
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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`forbidden`](./api-troubleshooting_ja.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_ja.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_ja.md#idempotency_unavailable) · [`admin.order.reopen_blocked_stock`](./api-troubleshooting_ja.md#adminorderreopen_blocked_stock)

### 配送ステータスまたは追跡番号を設定 — `POST /api/v2/core/order/{id}/shipping`

配送ステータスを設定する、または追跡番号（と URL）を注文の手動出荷として記録します（両方も可）。支払いステータスはここでは書き込めません。更新後の注文を返します。Idempotency-Key は任意です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「注文」（gp247_admin/order）の書き込み権限。 |
| 導入バージョン | `shop 3.6.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `Idempotency-Key` | ヘッダー | `string` | 任意。8-255 文字の印字可能文字（例：UUID）。同じキーと本文で再試行すると、最初の結果を返します |

**リクエスト本文**: [AdminOrderShippingRequest](#adminordershippingrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/shipping" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"shipping_status":0,"tracking_number":"","tracking_url":""}'
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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`forbidden`](./api-troubleshooting_ja.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_ja.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_ja.md#idempotency_unavailable) · [`admin.order.shipping_status_invalid`](./api-troubleshooting_ja.md#adminordershipping_status_invalid)

### 注文にメモを追加 — `POST /api/v2/core/order/{id}/notes`

注文履歴にメモ（最大 1000 文字）を追加し、API 経由で書き込まれたことを示す印を付けます。注文を返します。Idempotency-Key は任意です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「注文」（gp247_admin/order）の書き込み権限。 |
| 導入バージョン | `shop 3.6.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `id` *(必須)* | パス | `string` | レコードの ID（UUID）。 |
| `Idempotency-Key` | ヘッダー | `string` | 任意。8-255 文字の印字可能文字（例：UUID）。同じキーと本文で再試行すると、最初の結果を返します |

**リクエスト本文**: [AdminOrderNotesRequest](#adminordernotesrequest)

**リクエスト例**

```bash
curl -X POST "https://shop.example.com/api/v2/core/order/123/notes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -H "Content-Type: application/json" \
  -d '{"content":""}'
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

**エラー**: [`not_found`](./api-troubleshooting_ja.md#not_found) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`forbidden`](./api-troubleshooting_ja.md#forbidden) · [`idempotency_key_required`](./api-troubleshooting_ja.md#idempotency_key_required) · [`idempotency_key_reused`](./api-troubleshooting_ja.md#idempotency_key_reused) · [`idempotency_in_progress`](./api-troubleshooting_ja.md#idempotency_in_progress) · [`idempotency_unavailable`](./api-troubleshooting_ja.md#idempotency_unavailable)

### 注文一覧（管理） — `GET /api/v2/core/order/list`

管理者のストアの注文をページ単位で返します。対応する管理画面を開ける管理者トークンが必要です。各注文には明細（details）と合計に加え、管理者専用のリクエスト情報（ip、user_agent、device_type、domain、transaction）が含まれます。絞り込み：status、payment_status、shipping_status、customer_id。並べ替え：created_at または total。検索：注文 ID、メールアドレス、電話番号。include=transactions,histories に対応しています。fields[order]=a,b で一部の項目だけを返せます。updated_since=&lt;ISO 8601&gt; の同期モードに対応しています：古い変更から順に返し、page[after] でページ送りします（その場合 meta には per_page、next_after、has_more、sync_until が入り、links は next のみ）。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 管理画面「注文」（gp247_admin/order）の閲覧権限。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `shop 3.3.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `page[number]` | クエリ | `integer` | 読み込むページ（1 から）。 |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `updated_since` | クエリ | `string` (date-time) | 同期モード（core 3.7.0 以降）：ISO-8601 の日時（例：2026-10-31T08:00:00+07:00。日付のみ、またはオフセットなしの場合はサイトのタイムゾーン）。updated_at &gt;= updated_since の行を、古い変更から順（updated_at、次に id）に返します。sort や page[number] とは併用できません（422） |
| `page[after]` | クエリ | `string` | 同期モードのみ：前のページの meta.next_after から得たカーソル。それ以外の値は 422 |
| `filter` | クエリ | `object` | filter[&lt;name&gt;]=value — リソースごとに使える名前は api-introduction のセクション 8 を参照。不明な名前は 422 |
| `sort` | クエリ | `string` | &lt;name&gt; で昇順、-&lt;name&gt; で降順。不明な名前は 422 |
| `q` | クエリ | `string` | 検索語 |
| `lang` | クエリ | `string` | 名前・説明の言語 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |
| `fields` | クエリ | `object` | fields[&lt;type&gt;]=a,b を指定すると、レコードと埋め込みレコードのうち指定した項目（と id）だけを返します。不明な type や項目は 422。指定しない場合、本文は変わりません。core 3.8.0 以降。 |
| `include` | クエリ | `string` | 埋め込む関連レコードをカンマ区切りで指定します（1 階層、最大 3 つ）：transactions（支払い台帳）、histories（処理履歴、プレーンテキスト）。それ以外は 422。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/order/list" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
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

## Admin · 同期

ERP／同期ツール向けのイベントログ：注文、在庫、商品、顧客のすべての変更をカーソルで読み取ります。

### イベントログの読み取り — `GET /api/v2/core/events`

ERP や同期ツール向けに、変更イベント（注文、在庫、商品、顧客）を古い順に返します。続きを読むには ?after=&lt;last id handled&gt; を送ります。page[size] はデフォルト 100、最大 500 です。filter[type]=a,b で種類を絞り込めます。管理者が開ける画面の種類と管理者のストアのイベントだけが返されます。発生から数秒以内のイベントは保留されます。イベントログがオフの場合は 409、カーソルが削除済みのイベントを指している場合は 410 です。

| | |
|---|---|
| ログイン | 管理者トークン（Bearer）。 |
| 権限 | 返される各イベント種類に対応する管理画面の閲覧権限：order.* は「注文」、product.* と stock.* は「商品」、customer.* は「顧客」。 |
| キャッシュ | `ETag` — `If-None-Match` を送ると、変更がない場合に `304` を受け取れます。 |
| 導入バージョン | `core 3.7.0` |

**パラメーター**

| 名前 | 場所 | 型 | 意味 |
|---|---|---|---|
| `after` | クエリ | `integer` | 読み手が最後に処理したイベントの id。その後のイベントを返します。省略時は、保持されている最も古いイベントから |
| `filter[type]` | クエリ | `string` | カンマ区切りのイベント種類（&lt;resource&gt;.&lt;action&gt;）。管理者が読める種類を絞り込むだけです。gp247/shop 3.6.0 の種類：order.created, order.status_changed, order.payment_status_changed, stock.changed, product.updated, product.deleted, customer.created, customer.updated |
| `page[size]` | クエリ | `integer` | 1 ページあたりの件数（デフォルト 20、最大 100）。 |
| `If-None-Match` | ヘッダー | `string` | 以前のレスポンスの ETag。変更がない場合は本文が空の 304 を返します。core 3.8.0 以降。 |

**リクエスト例**

```bash
curl "https://shop.example.com/api/v2/core/events" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

**レスポンス** `200` — `data`: 一覧： [Event](#event)

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

**エラー**: [`event_log_disabled`](./api-troubleshooting_ja.md#event_log_disabled) · [`cursor_expired`](./api-troubleshooting_ja.md#cursor_expired) · [`validation_failed`](./api-troubleshooting_ja.md#validation_failed) · [`forbidden`](./api-troubleshooting_ja.md#forbidden)

## データオブジェクト

上記のエンドポイントが送受信するオブジェクトの項目です。日付は ISO 8601 形式の文字列、金額はレコードの通貨での数値です。

### AdminInfo

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 管理者ユーザー ID。 |
| `username` | `string` | ログイン名。 |
| `name` | `string \| null` | 表示名。 |
| `email` | `string \| null` | メールアドレス。 |
| `avatar` | `string \| null` | アバターのパス。 |
| `status` | `integer` | 1 = 有効。 |
| `roles` | 一覧： `string` | ロールのスラッグ。 |
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

### AdminLoginV2

| 項目 | 型 | 意味 |
|---|---|---|
| `username` | `string` | 管理者のユーザー名。 *(必須)* |
| `password` | `string` | 管理者のパスワード。 *(必須)* |
| `remember_me` | `boolean` | 有効期間の長いトークン。 |
| `with_refresh` | `boolean` | ローテーションするリフレッシュトークンも発行します。その場合、アクセストークンの有効期間は 60 分です |
| `device_name` | `string` | セッション一覧に表示されます |

### AdminLogoutResult

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

### AdminSessionsDeleteResult

| 項目 | 型 | 意味 |
|---|---|---|
| `revoked` | `boolean` | 常に true。 |

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

### AdminProductUpdateRequest

price / cost / status のうち 1 つ以上。それ以外は不可

| 項目 | 型 | 意味 |
|---|---|---|
| `price` | `number` | 販売価格。ストアが価格項目をオフにしている場合は拒否されます（422） |
| `cost` | `number` | 原価。ストアが原価項目をオフにしている場合は拒否されます（422） |
| `status` | `integer` | 1 = 販売中、0 = 非表示。 |

### Supplier

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 仕入先 ID。 |
| `name` | `string` | 仕入先名。 |
| `alias` | `string` | URL スラッグ。 |
| `email` | `string \| null` | 連絡先メールアドレス。 |
| `phone` | `string \| null` | 連絡先電話番号。 |
| `image` | `string \| null` | ロゴのパス。 |
| `address` | `string \| null` | 住所。 |
| `url` | `string \| null` | Web サイト。 |
| `sort` | `integer` | 並び順。 |
| `status` | `integer` | 1 = 有効。 |
| `store_id` | `string \| null` | ストア ID。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |

### AdminProductStockRequest

delta / set のどちらか一方のみ（両方を指定、またはどちらも指定しない場合：422 validation_failed）

| 項目 | 型 | 意味 |
|---|---|---|
| `delta` | `number` | 入庫（+）または出庫（-）。0 は不可。商品設定で小数の数量が許可されていない限り整数。アトミックに処理されるため、同時に確定された注文が失われることはありません。ゼロ未満にはなりません（422 stock_would_be_negative、在庫は動きません）。販売数のカウンターは変わりません |
| `set` | `number` | 棚卸し後に在庫を直接設定します。administrator ロールのみ（それ以外：403） |

### AdminProductStockResult

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 商品 ID。 |
| `sku` | `string` | 商品の SKU。 |
| `stock` | `number` | 変動後の在庫（小数がない場合は整数）。 |

### AdminStockBatchRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `items` | 一覧： `object` | 100 行を超える場合、または不正な形式の行がある場合：422、何も適用されません *(必須)* |

### AdminStockBatchResult

| 項目 | 型 | 意味 |
|---|---|---|
| `results` | 一覧： [StockBatchResult](#stockbatchresult) | 1 行につき 1 つの結果（リクエストの順）。 |
| `ok` | `integer` | 適用された行数。 |
| `failed` | `integer` | 拒否された行数。 |

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
| `content` | `string \| null` | HTML 本文。詳細のレスポンスのみ（一覧には含まれません）。 *(詳細のみ)* |
| `status` | `integer` | 1 = 有効。 |
| `store_id` | `string \| null` | ストア ID。 |
| `created_at` | `string \| null` | 作成日時（ISO 8601）。 |
| `updated_at` | `string \| null` | 最終更新日時（ISO 8601）。 |

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

### AdminCustomerUpdateRequest

status のみ。それ以外は不可

| 項目 | 型 | 意味 |
|---|---|---|
| `status` | `integer` | 0 = ロック、1 = ロック解除 *(必須)* |

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

### AdminOrderStatusRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `status` | `integer` | サイトの注文ステータスのいずれかの ID。キャンセルすると在庫が戻り、再開すると再び引き当てられます。完了済みまたはキャンセル済みの注文も再開できます。現在と同じステータスの場合は 200 を返し、何も書き込みません *(必須)* |

### AdminOrderShippingRequest

shipping_status / tracking_number のうち 1 つ以上。payment_status は拒否されます（422）：注文の支払いに従って決まるためです

| 項目 | 型 | 意味 |
|---|---|---|
| `shipping_status` | `integer` | サイトの配送ステータスのいずれかの ID。変更すると注文履歴に行が追加されます |
| `tracking_number` | `string` | 注文の手動出荷として保存され（注文ごとに 1 つ。別の番号を送ると更新されます）、管理画面の注文画面に表示されます |
| `tracking_url` | `string` (uri) | http(s) のアドレス。tracking_number が必要 |

### AdminOrderNotesRequest

| 項目 | 型 | 意味 |
|---|---|---|
| `content` | `string` | 記入者とラベル [API] とともに保存され、プレーンテキストとして表示されます（マークアップはエスケープ） *(必須)* |

### Event

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `integer` | イベント ID。続きを読むには、最後に処理したものを ?after= として渡します。 |
| `type` | `string` | イベントの種類（例：order.created, order.status_changed, order.payment_status_changed, stock.changed, product.updated, product.deleted, customer.created, customer.updated）。 |
| `subject_id` | `string` | イベントの対象となるレコードの ID。 |
| `store_id` | `string \| null` | レコードのストア。 |
| `source` | `string` | 変更の発生元：api、admin、web、system のいずれか。 |
| `actor` | `object \| null` | 変更した人：{type: admin\|customer, id}。不明な場合（system）は null。 |
| `data` | `object` | 種類ごとに定義された項目（例：order.status_changed では {field, from, to}）。ない場合は空のオブジェクト。 |
| `occurred_at` | `string` | 発生日時（ISO 8601）。 |

### EventListMeta

| 項目 | 型 | 意味 |
|---|---|---|
| `per_page` | `integer` | ページサイズ。 |
| `next_after` | `integer \| null` | このページの最後のイベントの ID。続きを読むには after= として送ります。ページが空の場合は null。 |
| `has_more` | `boolean` | 今すぐ読めるイベントがさらにあるかどうか。 |

### ProductAttributeGroup

| 項目 | 型 | 意味 |
|---|---|---|
| `group_id` | `string` | 属性グループ ID。カートは選択を options[group_id] として受け取ります。 |
| `name` | `string` | グループ名（例：色）。 |
| `required` | `boolean` | 常に true：すべてのグループを選択する必要があります。 |
| `options` | 一覧： `object` | グループの選択肢：{value, add_price}。add_price は追加料金で、価格が非表示の場合は null。 |

### StockBatchResult

| 項目 | 型 | 意味 |
|---|---|---|
| `id` | `string` | 商品 ID（送られた id、または SKU から見つかった id）。 |
| `sku` | `string` | 行が SKU で商品を指定した場合のみ。 |
| `ok` | `boolean` | この行が適用されたかどうか。 |
| `stock` | `number` | 変動後の在庫（ok の場合）。 |
| `code` | `string` | ok でない場合：not_found, sku_not_found, sku_ambiguous, forbidden, stock_managed_by_components, stock_would_be_negative。 |
| `msg` | `string` | ok でない場合：人が読める理由。 |

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

### OrderTotal

| 項目 | 型 | 意味 |
|---|---|---|
| `code` | `string` | 行コード（例：subtotal、tax、shipping、discount、total）。 |
| `title` | `string` | 行のラベル。 |
| `value` | `number` | 金額。 |
| `text` | `string` | 表示用に整形した金額。 |

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
