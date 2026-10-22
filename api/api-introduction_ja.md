> 🌐 **言語:** [🇻🇳 Tiếng Việt](./api-introduction_vi.md) · [🇬🇧 English](./api-introduction.md) · 🇯🇵 日本語（現在）

# GP247 システムの API 入門

## はじめに

このドキュメントでは、**GP247 の API（アプリケーション・プログラミング・インターフェース）システム**を紹介します。
外部のアプリケーション（モバイルアプリ、別の Web サイト、管理ソフト、サードパーティ連携など）が、
あなたの GP247 サイトと「会話」してデータを取得したり操作を実行したりする方法を説明します。
対象読者は、API の全体像を知りたい**連携開発者**と**サイト管理者**です。
API にどんな機能があるか、いくつのグループに分かれるか、認証（ログイン）の仕組み、試し方を扱います。
読み終えると、**どの API が何をするのか、どのアドレスで呼ぶのか、どんな鍵が必要か**がわかります。
コピーしてすぐ動かせる例も載せています。

> ℹ️ **API とは（身近なたとえ）：** API はサイトの「窓口」のようなものです。
> 外部アプリはデータの保管庫に直接入りません。窓口（URL アドレス）にリクエストを送ります。
> 窓口は書類（トークン／キー）を確認し、結果を **JSON**（コンピューターが読めるテキスト形式のデータ）で返します。
> やり取りはすべて、通常の Web の通信方式（HTTP）で行われます。

---

## 1. 2 つの主要 API グループ

GP247 システムは API を **2 つのグループ**に分けています。それぞれ利用者が異なります。

| グループ | アドレスの接頭辞（prefix） | 用途 | ログインする人 |
| --- | --- | --- | --- |
| **Core API（管理）** | `api/core` | リモートからのシステム管理（管理アプリ、独自ダッシュボード） | **管理者**（admin）アカウント |
| **Front API（ストアフロント）** | `api/front` | ストアのデータ表示、顧客アカウント | 顧客／会員。公開データは**ログイン不要** |

- **`api/core`** は `gp247/core` パッケージが提供します。管理者ログインや管理者情報の取得など、
  「裏側」（バックエンド）の操作です。
- **`api/front`** は `gp247/front` と `gp247/shop` パッケージが提供します。商品一覧、カテゴリー、
  ブランド、コンテンツページ、バナー、顧客のアカウント／注文など、ストアの「表側」のデータです。

> **完全なアドレス** ＝ サイトのドメイン ＋ 接頭辞 ＋ パス です。たとえばサイトが
> `https://shop.example.com` の場合、商品一覧を取得するエンドポイントは次のとおりです。
> `https://shop.example.com/api/front/product/list`

---

## 2. 認証（API へのログイン）

GP247 は **Laravel Sanctum** を使って**トークン**（「入場券」の役割をする長い文字列）を発行します。
仕組みは両グループで共通です。

1. アプリが **ユーザー名／メールアドレス ＋ パスワード** を `login` エンドポイントに送ります。
2. 正しければ、システムは **`access_token`**（Bearer 形式のトークン）と有効期限（`expires_at`）を返します。
3. 以降、ログインが必要なリクエストには、このトークンを **ヘッダー** に付けて送ります。

   ```
   Authorization: Bearer <access_token>
   ```

4. 終了するときは `logout` エンドポイントを呼び、**トークンを失効**させます（古いトークンはすぐに使えなくなります）。

### 権限の範囲（scope / ability）

各トークンには、できることを制限する**権限の範囲**が付きます。

| Scope | 意味 |
| --- | --- |
| `admin` | 通常の管理者（Core API） |
| `admin-supper` | 上位の管理者 — `administrator` ロール（Core API） |
| `user` | 有効化済みの会員（Front API） |
| `user-guest` | 未有効化の会員／ゲスト（Front API） |

### ログイン状態の保持（`remember_me`）

`login` を呼ぶときに `remember_me = true` を付けると、トークンの有効期限が長くなります
（既定は 30 日。環境変数 `GP247_API_RECOMMEMBER` で設定）。
付けない場合は既定の期限（7 日。`GP247_API_TOKEN_EXPIRE_DEFAULT` で設定）で失効します。

---

## 3. 接続保護レイヤー（API Connection）— 任意

トークンに加えて、GP247 にはすべての API の前に立つ**追加の鍵**があります。これを **API Connection** と呼びます。
これは 2 つ目の「門番」です。許可されたアプリケーションだけが API を呼べるようにします。
公開エンドポイントも対象です。

- このレイヤーは、管理画面の設定 `api_connection_required` で**オン／オフを切り替えられます**。
- **オンの場合：** すべてのリクエストに、さらに**2 つのヘッダー**を付ける必要があります。

  ```
  apiconnection: <接続コード>
  apikey: <秘密キー>
  ```

- `apiconnection`／`apikey` のペアは **GP247 の管理画面**（API Connection の管理項目）で作成・管理します。
  ペアごとに有効期限（`expire`）とオン／オフの状態（`status`）を設定できます。
- 不足または誤りがある場合、API は `{"error": 1, "msg": "Connection not correct"}` を返します。

> チェックレイヤー（ミドルウェア）の順序は **JSON を返す → API Connection を確認 → 1000 回のレート制限** です。
> この順序は固定で、変更しないでください。

---

## 4. Core API（管理）の処理の流れ

下の図は、管理アプリが Core API を呼ぶ順序を示しています。
接続を確認（オンの場合）→ ログインしてトークンを取得 → トークンを付けて管理用エンドポイントを呼び出します。

![Core API の処理の流れ](https://static.gp247.net/file/api/gp247-api-sequence-core.png)

**Core API のエンドポイント**（接頭辞 `api/core`）：

| メソッド | パス | ログイン必要 | 機能 |
| --- | --- | --- | --- |
| `POST` | `api/core/login` | 不要 | 管理者ログイン、`access_token` を受け取る |
| `GET` | `api/core/logout` | 必要（Bearer） | ログアウト、現在のトークンを失効 |
| `GET` | `api/core/info` | 必要（Bearer） | ログイン中の管理者情報を取得 |

管理者ログインの例（ターミナルで `curl` を使用）：

```bash
curl -X POST "https://shop.example.com/api/core/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin@example.com","password":"your-password","remember_me":true}'
```

成功すると、次のような JSON が返ります。

```json
{
  "access_token": "1|aBcD...very-long...XyZ",
  "token_type": "Bearer",
  "scopes": ["admin-supper"],
  "expires_at": "2026-08-29 10:00:00"
}
```

続いて、受け取ったトークンで管理者情報を取得します。

```bash
curl -X GET "https://shop.example.com/api/core/info" \
  -H "Authorization: Bearer 1|aBcD...very-long...XyZ"
```

---

## 5. Front API（ストアフロント）の処理の流れ

Front API は 2 種類のデータを扱います。**公開データ**（ログイン不要 — 商品一覧、カテゴリーなど）と、
**顧客個人のデータ**（ログイン必要 — 注文、個人情報）です。

![Front API の処理の流れ](https://static.gp247.net/file/api/gp247-api-sequence-front.png)

**公開エンドポイント**（接頭辞 `api/front`、トークン不要）：

| メソッド | パス | 機能 |
| --- | --- | --- |
| `GET` | `api/front/product/list` | 商品一覧 |
| `GET` | `api/front/product/detail/{id}` | 商品 1 件の詳細 |
| `GET` | `api/front/category/list` | カテゴリー一覧 |
| `GET` | `api/front/category/detail/{id}` | カテゴリー 1 件の詳細（商品を含む） |
| `GET` | `api/front/brand/list` | ブランド一覧 |
| `GET` | `api/front/brand/detail/{id}` | ブランド 1 件の詳細 |
| `GET` | `api/front/banner/list` | バナー一覧 |
| `GET` | `api/front/banner/detail/{id}` | バナー 1 件の詳細 |
| `GET` | `api/front/page/list` | コンテンツページ一覧 |
| `GET` | `api/front/page/detail/{id}` | コンテンツページ 1 件の詳細 |

**顧客アカウントのエンドポイント**（接頭辞 `api/front`、顧客のトークンが必要）：

| メソッド | パス | ログイン必要 | 機能 |
| --- | --- | --- | --- |
| `POST` | `api/front/login` | 不要 | 顧客のメールアドレス／パスワードでログインし、トークンを受け取る |
| `GET` | `api/front/logout` | 必要（Bearer） | 顧客のログアウト |
| `GET` | `api/front/info` | 必要（Bearer） | 顧客アカウントの情報 |
| `GET` | `api/front/member/order/list` | 必要（Bearer） | 顧客の注文一覧 |
| `GET` | `api/front/member/order/detail/{id}` | 必要（Bearer） | 注文 1 件の詳細 |

商品一覧を取得する例（公開データ、トークン不要）：

```bash
curl -X GET "https://shop.example.com/api/front/product/list" \
  -H "Accept: application/json"
```

顧客としてログインし、注文を確認する例：

```bash
# 1) 顧客ログイン
curl -X POST "https://shop.example.com/api/front/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"customer@example.com","password":"your-password"}'

# 2) 受け取ったトークンで注文一覧を取得
curl -X GET "https://shop.example.com/api/front/member/order/list" \
  -H "Authorization: Bearer 2|zzz...customer-token...zzz"
```

---

## 6. レスポンスの形式とエラーコード

- **レスポンスはすべて JSON です**（ミドルウェア `json.response` によるもの）。
- ページ分割のあるエンドポイント（商品一覧など）は、Laravel 標準のページ分割形式で返ります
  （`data`、`current_page`、`total` などを含みます）。
- よくあるエラー：

| ケース | レスポンス |
| --- | --- |
| アカウント／パスワードの誤り | HTTP `401` — `{"error": 1, "msg": "Unauthorized"}` |
| API Connection の不足／誤り | `{"error": 1, "msg": "apiconnection or apikey not found"}` または `"Connection not correct"` |
| データが見つからない（詳細） | HTTP `404`、本文は空 |
| 呼び出し回数が多すぎる | HTTP `429`（レート制限を超過） |

---

## よくある質問（Q&A）

**Q1: どちらの API グループを使えばよいですか？**

→ ストア向けのアプリ／顧客クライアント（商品の閲覧、注文、注文の確認）を作るなら **Front API**
（`api/front`）を使います。管理者向けのリモート管理ツールを作るなら **Core API**（`api/core`）を使います。

**Q2: トークンはどこで取得し、どのくらい有効ですか？**

→ トークンは `login` エンドポイントが発行します。既定では 7 日後に失効します。
`remember_me = true` を送ると延長されます（既定 30 日）。
環境変数 `GP247_API_TOKEN_EXPIRE_DEFAULT` と `GP247_API_RECOMMEMBER` で変更できます。

**Q3: `apiconnection`／`apikey` と `access_token` の違いは何ですか？**

→ `access_token` は**ユーザー**（あなたが誰か）を認証します。`apiconnection`／`apikey` は**アプリケーション**
（どのアプリが API を呼べるか）を認証します。こちらは**任意のレイヤー**で、
管理者が `api_connection_required` をオンにした場合だけ必須です。

**Q4: 公開エンドポイントを呼ぶのにトークンは必要ですか？**

→ 不要です。商品、カテゴリー、ブランド、バナー、コンテンツページの一覧／詳細エンドポイントは公開されています。
ただし API Connection レイヤーがオンの場合は、`apiconnection`／`apikey` ヘッダーを送る必要があります。

**Q5: パスワードが正しいのに `Unauthorized`（401）エラーになるのはなぜですか？**

→ Core API では、管理者アカウントが `status = 1`（有効）である必要があります。Front API では、
メールアドレスが正しく登録されているか確認してください。また、正しい項目名で送っているか確認してください。
Core は `username`、Front は `email` を使います。

**Q6: `Connection not correct` エラーになるのはなぜですか？**

→ API Connection レイヤーがオンで、`apiconnection`／`apikey` のペアが誤っている、期限切れ（`expire`）、
または無効（`status`）になっています。GP247 の管理画面の API Connection 管理項目で確認するか、作り直してください。

**Q7: 呼び出し回数に制限はありますか？**

→ あります。ミドルウェアは既定で 1 サイクルあたり **1000 回** に制限します。超えると HTTP `429` が返ります。
呼び出し間隔を空けるか、アプリ側で結果をキャッシュしてください。

**Q8: エンドポイントの完全なアドレスはどう書きますか？**

→ `https://<site-domain>/<prefix>/<path>` です。例：`https://shop.example.com/api/front/product/list`。
接頭辞はグループに応じて `api/core` または `api/front` です。

**Q9: 安全にログアウトするには？**

→ 現在のトークンで `logout`（`api/core/logout` または `api/front/logout`）を呼びます。
システムはそのトークンをデータベースから**削除**するため、古いトークンは再利用できなくなります。

**Q10: 一覧にないエンドポイントが必要な場合は？**

→ 上の一覧は `core`／`front`／`shop` に標準で用意されたエンドポイントです。さらに必要な場合、開発者は
独自のプラグインでルートを宣言して拡張できます。コアのコードは変更しません
（GP247 の「プラグインはコアに差し込む」という原則に従います）。

---

<sub>📅 **最終更新:** 2026-10-22 · ✍️ **作成者 (Author):** GP247</sub>
