> 🌐 **言語:** [🇻🇳 Tiếng Việt](./create-api_vi.md) · [🇬🇧 English](./create-api.md) · 🇯🇵 日本語（現在）

# GP247 で正しく API を作成するガイド

## はじめに

このドキュメントでは、**GP247 の標準に沿って API エンドポイントを自作（新規作成）する方法**を説明します。
対象は、独自の API で GP247 サイトを拡張したい**開発者**です（例：モバイルアプリ向けのデータ出力、
外部の管理ソフト、サードパーティ連携）。読み終えると、**コードをどこに置くか、ルートをどう宣言するか、
コントローラー／ミドルウェア／認証をどう使うか**がわかります。コピーしてすぐ動く完全な例も載せています。
既存の API を**呼び出す**だけ（自作しない）なら、先に
[GP247 API 入門](./api-introduction_ja.md) をお読みください。

> ℹ️ **GP247 の黄金ルール：** *コアのコード*（`vendor/gp247/*`）*は変更しません*。特定のサイト向けに独自の API を
> 追加するときは、**プラグイン**（`app/GP247/Plugins/` フォルダー）か、`app/GP247/` を使った**オーバーライド**の
> 仕組みで行います。`core`／`front`／`shop` パッケージに直接追加するのは、そのパッケージの主要メンテナーだけです。

---

## 1. 書く前に API のアーキテクチャを理解する

GP247 の API は、既存のいくつかの部品を組み合わせてできています。部品を理解すると、正しい場所にコードを差し込めます。

### 1.1. 2 つのグループと接頭辞（prefix）

新しいエンドポイントはすべて **v2** の接頭辞の下に置きます。

| グループ | ルートで使う定数 | 値 | 用途 |
| --- | --- | --- | --- |
| Core（管理） | `GP247_API_V2_CORE_PREFIX` | `api/v2/core` | システム管理（管理者トークンが必要） |
| Front（ストアフロント） | `GP247_API_V2_FRONT_PREFIX` | `api/v2/front` | ストアのデータと顧客アカウント |

この 2 つの定数は `vendor/gp247/core/src/Library/Const.php` で固定的に宣言されています。ルートでは**必ず定数を使ってください**
（`"api/v2/core"` という文字列を手書きしない）。将来接頭辞が変わっても、あなたのコードが自動的に追従します。

> ⚠️ **REST API v1 は凍結されています。** gp247/core、gp247/front、gp247/shop 3.3.0 以降、API v1
> （`GP247_API_CORE_PREFIX` = `api/core`、`GP247_API_FRONT_PREFIX` = `api/front`）には新機能が追加されず、セキュリティ修正のみと
> なります。gp247/core 3.3.1 以降の新規インストールでは v1 は最初からオフです。既存の v1 エンドポイントは引き続き動作しますが、
> **そこに新しいエンドポイントを追加しないでください**。[API v1](./api-v1_ja.md) を参照してください。

### 1.2. API モードを有効にする

API は **`GP247_API_MODE` フラグがオンのときだけ動作します**。サイトの `.env` ファイルを確認してください。

```dotenv
GP247_API_MODE=true
```

このフラグがオフの場合、API のルートは**一切読み込まれません**（`vendor/gp247/core/src/routes.php` を参照）。

フラグがオンでも、サイト運営者は API 管理画面でグループごとにオン／オフを切り替えられます（`api.enabled` レイヤー。
[API 入門の 5 章](./api-introduction_ja.md) を参照）。`api/v2/front` 配下のルートは **「v2 · 公開・顧客API (front)」** の
スイッチに、`api/v2/core` 配下のルートは **「v2 · 管理API (core)」** のスイッチに従います — プラグインが追加したルートも同じです。
オフのグループは `404`（`code: api_disabled`）を返します。

### 1.3. API ルートが自動で読み込まれる仕組み

`GP247_API_MODE=true` のとき、各パッケージは次のフォルダー内のすべてのファイルを自動で**走査して読み込み**、
すべてをミドルウェアグループ `GP247_API_MIDDLEWARE` で包みます。

- `vendor/gp247/core/src/Routes/Api/v2/*.php`
- `vendor/gp247/shop/src/Routes/Api/v2/*.php`
- `vendor/gp247/front/src/Routes/Api/v2/*.php`

（v1 のファイルは同じパッケージの `Routes/Api/*.php` にあり、既存のエンドポイントを残すためだけのものです。）

`GP247_API_MIDDLEWARE = ['api', 'api.request_id', 'json.response', 'api.enabled', 'api.extend']` です。`api.extend` は GP247 のミドルウェアグループで、
`config('gp247-config.api.middleware')` で定義されています。

| 順序 | ミドルウェア | 役割 |
| --- | --- | --- |
| 1 | `json.response` | すべてのレスポンスを **JSON** にする（ヘッダー `Accept: application/json` を設定）。`GP247_API_MIDDLEWARE` にも含まれ、**ほかのすべてのミドルウェアより先に**（`auth:*` を含む）動くため、公開済みの config にこの行がなくても失われません |
| 2 | `api.connection` | 任意のアプリケーション用の鍵（`apiconnection`／`apikey`）— 設定でオン／オフ |
| 3 | `throttle:gp247-api` | 呼び出し頻度の制限。既定は IP ごとに **1 分あたり 1000 回**（`GP247_API_THROTTLE`） |

> ⚠️ **注意:** **この順序は固定です。変更しないでください。** config に明記された規約です。
>
> ℹ️ **対応バージョン:** gp247/core 3.6.0 — `json.response` は `auth:*` より先に動きます。トークンがなく
> `Accept: application/json` も送らない場合でも JSON の `401` を返します（以前は `500`）。
>
> 👉 **プラグインにとって重要な結果：** core／shop／front の `Routes/Api/v2/` に置いたルートには、上の 3 つのレイヤーが
> **自動で**付きます。しかし**プラグイン**から追加したルートは別の場所（セクション 4）で読み込まれるため、
> **自動では付きません**。自分で包むか、（3.3.0 以降は）`ApiRouteRegistrar` で登録する必要があります（セクション 4.1）。

> ℹ️ **対応バージョン:** gp247/core, gp247/front, gp247/shop 3.3.0 — 各パッケージは自分の API ルート（`Routes/Api/v2/`
> フォルダーを含む）を `GP247\Core\Api\Support\ApiRouteRegistrar` で登録します。パッケージのルートは、そのパッケージが
> データベースにインストールされている場合にだけ存在します。パスとルート名は変わりません。

### 1.4. Laravel Sanctum による認証

GP247 は **Sanctum**（Bearer 形式のトークン）を使います。2 つの独立した「ガード」（ログインの入口）があります。

| ガード | プロバイダー | 対象 | トークンを発行する場所 |
| --- | --- | --- | --- |
| `admin-api` | `admin_provider` | 管理者 | `api/v2/core/login` |
| `customer-api` | `customer_provider` | 顧客／会員 | `api/v2/front/login` |

各トークンには、できることを制限する**権限の範囲（scope／ability）**も付きます。スコープは
`config('gp247-config.api.auth')` から取得します（`.env` で変更可能）。

| 設定キー | 既定値 | 意味 |
| --- | --- | --- |
| `api_scope_admin` | `admin` | 通常の管理者 |
| `api_scope_admin_supper` | `admin-supper` | 上位の管理者（`administrator` ロール） |
| `api_scope_user` | `user` | 有効化済みの会員 |
| `api_scope_user_guest` | `user-guest` | 未有効化の会員／ゲスト |

ログインが必要なルートを保護するには、次のミドルウェアの組み合わせを使います。

```php
'middleware' => [
    'auth:admin-api',                                // or 'auth:customer-api' for front APIs
    \GP247\Core\Api\Support\ApiAbilities::admin(),   // or ApiAbilities::customer(): the allowed scopes
]
```

### 1.5. ヘルパー `gp247_namespace()` — オーバーライドを可能にする仕組み

GP247 のルートでは、コントローラーを `Controller::class` と直接書か**ず**、
`gp247_namespace()` で包みます。

```php
$catalog = gp247_namespace(\GP247\Shop\Api\V2\CatalogController::class);
Route::get('product/list', $catalog.'@productList');
```

このヘルパーは自動で確認します。`app/GP247/...` にオーバーライド用のファイルがあればアプリ側を使い、
なければ vendor 内のものを使います。これにより、エンドユーザーは **vendor を変更せずにコントローラーをオーバーライド**できます。
`GP247\...` パッケージのコントローラーをルートで指すときは、必ずこのヘルパーを使ってください。

---

## 2. 命名規則とレスポンスの形式

新しい API をシステムの他の部分と揃えるため、次の規則を守ってください。

- **リソースごとにまとめる**：データのグループごとに 1 つの `Route::group(['prefix' => 'product'], ...)` にします。
- **2 つの標準アクション**：`list`（一覧）と `detail/{id}`（1 件の詳細）。
- **コントローラーのメソッド名**：`<resource>List` と `<resource>Detail`
  （例：`productList`、`productDetail` — shop パッケージの v2 コントローラーと同じ）。
- **必ず JSON を返す**：ヘルパー `GP247\Core\Api\V2\ApiQuery`（`JsonResponse` を返す）を使います。レコードではない
  レスポンスには `response()->json(...)` を使います。
- **成功時**：データを `data` で包みます — 1 件なら `{"data": …}`（`ApiQuery::one()`）、一覧なら
  `{"data": [...], "meta": {...}, "links": {...}}`（`ApiQuery::page()`）。
- **失敗時／見つからない場合**：`ApiErrorResponder` を使い、**意味の正しい HTTP ステータスコード**と共通のエラー本文
  `{"error": 1, "msg": "…", "code": "…"}` を返します（コード一覧は [API 入門](./api-introduction_ja.md) の 6 章）。
  `ApiQuery::one()` はレコードがなければ自動で `404` を返します。

  ```php
  use GP247\Core\Api\Support\ApiErrorResponder;

  if (!$product) {
      return ApiErrorResponder::notFound();          // 404, code: not_found
  }
  ```

  > ℹ️ **対応バージョン:** gp247/core 3.2.3 — `api/*` 配下の認証、権限、バリデーション、レート制限のエラーは自動でこの形式に
  > 変換されます。コントローラー自身が判断する場合だけ `notFound()`／`forbidden()` を自分で返してください。エラーに
  > `response()->json([...], 200)` を使う旧来の書き方は**もう使いません**。
- **必要なフィールドだけを返す**：`ApiQuery` に渡す関数 `$toArray` で返すフィールドを正確に宣言し、モデルをそのまま返しません。
  テーブルに新しく追加した列が API に自動で漏れ出すことはありません。公開データでは、元のクエリを
  `GP247\Core\Api\Support\ApiVisibility::visible(...)` で作り、ウェブサイトに表示されているレコードだけを API が返すようにします。

- **ページ分割・絞り込み・並べ替え**：一覧を返すときは `->get()` の代わりに
  `ApiQuery::page($query, $request, $toArray, $filters, $sorts, $search)` を使います。クライアントは `page[number]`、
  `page[size]`（既定 20、最大 100）、`filter[<名前>]`、`sort=<名前>`／`sort=-<名前>`、`q`（検索）で制御します。受け付けるのは
  宣言した絞り込み／並べ替えの名前だけで、未知の名前は `422`（`validation_failed`）になります。クライアントの入力がクエリの列名に
  なることはありません。多言語のコンテンツでは、言語を `ApiQuery::lang($request)` で、説明のフィールドを `ApiQuery::described(...)` で取得します。

---

## 3. 例 A — 自分がメンテナンスするパッケージ（core／front／shop）にエンドポイントを追加する

あなたが**パッケージのメンテナー**（`vendor/gp247/<package>` を直接編集する）の場合に使います。
例として、公開の Front API `api/v2/front/review/list` を追加します。次の順序どおりに進めてください。

1. **コントローラーを作成**します。場所は `vendor/gp247/<package>/src/Api/V2/...` です。Front のデータなら
   `RootFrontController` を、管理のデータなら `RootAdminController` を継承します。

   ファイル `src/Api/V2/ReviewController.php` を作成します。

   ```php
   <?php

   namespace GP247\Shop\Api\V2;

   use GP247\Core\Api\V2\ApiQuery;
   use GP247\Front\Controllers\RootFrontController;
   use GP247\Shop\Models\ShopProductReview;
   use Illuminate\Database\Eloquent\Builder;
   use Illuminate\Http\Request;

   class ReviewController extends RootFrontController
   {
       /**
        * Get the public review list.
        *
        * @return \Illuminate\Http\JsonResponse
        */
       public function reviewList(Request $request)
       {
           $query = ShopProductReview::query()->where('status', 1);

           return ApiQuery::page($query, $request, fn ($r) => self::review($r),
               ['product_id' => fn (Builder $q, string $v) => $q->where('product_id', $v)],   // filter[product_id]
               ['created_at' => 'created_at']);                                                // sort=created_at | -created_at
       }

       /**
        * Get one public review (404 when it does not exist).
        *
        * @return \Illuminate\Http\JsonResponse
        */
       public function reviewDetail(Request $request, $id)
       {
           $review = ShopProductReview::query()->where('status', 1)->where('id', $id)->first();

           return ApiQuery::one($review, fn ($r) => self::review($r));
       }

       /**
        * Declared shape of a review: only these fields reach the API.
        */
       private static function review($r): array
       {
           return [
               'id'         => $r->id,
               'product_id' => $r->product_id,
               'point'      => (int) $r->point,
               'comment'    => $r->comment,
               'created_at' => optional($r->created_at)->toIso8601String(),
           ];
       }
   }
   ```

2. **ルートファイルを作成**します。場所は `src/Routes/Api/v2/review.php` です（`Routes/Api/v2/` フォルダーに置くだけで
   パッケージが自動で読み込みます。ほかの場所で宣言する必要はありません）。

   ```php
   <?php

   use GP247\Shop\Api\V2\ReviewController;
   use Illuminate\Support\Facades\Route;

   Route::group([
       'prefix' => GP247_API_V2_FRONT_PREFIX,   // => api/v2/front
   ], function () {
       $reviewController = gp247_namespace(ReviewController::class);

       Route::group(['prefix' => 'review'], function () use ($reviewController) {
           Route::get('list', $reviewController.'@reviewList');
           Route::get('detail/{id}', $reviewController.'@reviewDetail');
       });
   });
   ```

3. **ルートが読み込まれたか確認**します。Artisan コマンドを使います（サイトのルートフォルダーで実行）。

   ```bash
   php artisan route:list --path=api/v2/front/review
   ```

   成功すると `GET | api/v2/front/review/list` の行が表示されます。**表示されない**場合は、
   `.env` の `GP247_API_MODE=true` を確認し、ルートキャッシュを削除してください：`php artisan route:clear`。

4. **試しに呼び出します**（公開エンドポイントなのでトークンは不要です。`-g` は curl が `[ ]` を解釈しないようにするためです）。

   ```bash
   curl -g -X GET "https://shop.example.com/api/v2/front/review/list?page[size]=10&sort=-created_at" -H "Accept: application/json"
   ```

   期待される結果：

   ```json
   {
     "data": [ { "id": 12, "product_id": 5, "point": 5, "comment": "…", "created_at": "2026-10-30T10:00:00+00:00" } ],
     "meta": { "current_page": 1, "per_page": 10, "total": 1, "last_page": 1 },
     "links": { "first": "…", "last": "…", "prev": null, "next": null }
   }
   ```

> **管理用**エンドポイント（ログインが必要）の場合は、パッケージの `src/Routes/Api/v2/` にルートを置き、
> `prefix => GP247_API_V2_CORE_PREFIX` とともにミドルウェアグループ `auth:admin-api` ＋ `ApiAbilities::admin()` で包み、
> 各ルートで `api.admin:<管理ルート名>` を宣言します（セクション 5.1。`vendor/gp247/shop/src/Routes/Api/v2/admin.php` のサンプルを参照）。

---

## 4. 例 B — プラグインから API を追加する（個別サイトにおすすめ）

**vendor を変更できない**状態で特定のサイトに API を追加するときの、**標準に沿った**方法です。プラグインは
`app/GP247/Plugins/<PluginName>/` に置かれ、その `Route.php` ファイルはコアが自動で読み込みます。

> ⚠️ **注意:** **重要な違い：** プラグインのルートは API のミドルウェアグループ（セクション 1.3）の**外側**で読み込まれます。
> そのため `GP247_API_MIDDLEWARE` で**自分で包み**、`prefix` も自分で設定する**必要があります**。忘れると、
> API は JSON を強制**せず**、`api.connection` レイヤーも通ら**ず**、API のオン／オフのスイッチにも従わ**ず**、レート制限も**かかりません**。

1. プラグイン内に**コントローラーを作成**します。例：
   `app/GP247/Plugins/MyApi/Api/Front/StatusController.php`

   ```php
   <?php

   namespace App\GP247\Plugins\MyApi\Api\Front;

   use GP247\Front\Controllers\RootFrontController;
   use Illuminate\Http\Request;

   class StatusController extends RootFrontController
   {
       /**
        * Return a simple health-check payload for external monitors.
        *
        * @return \Illuminate\Http\JsonResponse
        */
       public function status(Request $request)
       {
           return response()->json(['data' => [
               'status' => 'ok',
               'time'   => gp247_time_now(),
           ]]);
       }
   }
   ```

2. `app/GP247/Plugins/MyApi/Route.php` で**ルートを宣言**し、API のミドルウェアグループで**自分で包みます**。

   ```php
   <?php

   use App\GP247\Plugins\MyApi\Api\Front\StatusController;
   use Illuminate\Support\Facades\Route;

   // Only register when the API mode is on, matching core behaviour.
   if (config('gp247-config.env.GP247_API_MODE')) {
       Route::group([
           'middleware' => GP247_API_MIDDLEWARE,       // required: plugin routes load outside the API group
           'prefix'     => GP247_API_V2_FRONT_PREFIX,  // => api/v2/front
       ], function () {
           $statusController = gp247_namespace(StatusController::class);

           Route::group(['prefix' => 'myapi'], function () use ($statusController) {
               Route::get('status', $statusController.'@status');
           });
       });
   }
   ```

3. **キャッシュを削除してルートを確認**します。

   ```bash
   php artisan route:clear
   php artisan route:list --path=api/v2/front/myapi
   ```

   成功すると `GET | api/v2/front/myapi/status` が表示されます。

4. **試しに呼び出します**。

   ```bash
   curl -X GET "https://shop.example.com/api/v2/front/myapi/status" -H "Accept: application/json"
   ```

   期待される結果：

   ```json
   { "data": { "status": "ok", "time": "2026-07-30 10:00:00" } }
   ```

---

### 4.1. もっと簡単な方法：`ApiRouteRegistrar` で登録する

> ℹ️ **対応バージョン:** gp247/core 3.3.0

`Route.php` でミドルウェアグループを自分で包む代わりに、API ルートをプラグイン内の別ファイルに置き、
プラグインの `Provider.php` から登録します。

```php
// app/GP247/Plugins/MyApi/Provider.php
\GP247\Core\Api\Support\ApiRouteRegistrar::register(__DIR__ . '/Routes/api.php');
```

```php
// app/GP247/Plugins/MyApi/Routes/api.php — declare only the prefix and the routes
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => GP247_API_V2_FRONT_PREFIX], function () {
    $statusController = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Front\StatusController::class);
    Route::get('myapi/status', $statusController.'@status');
});
```

`ApiRouteRegistrar` は `GP247_API_MIDDLEWARE`（JSON、API Connection、レート制限）で自動的に包みます。
`GP247_API_MODE` がオフのときは何も登録せず、サイトで `route:cache` 済みの場合は再登録しません。
エラーの取り決め `{error, msg, code}` は、`api/core`、`api/front`、`api/v2/core`、`api/v2/front` 配下のすべてのパスに適用されます。
この 4 つ以外の独自の接頭辞に置いたルートは Laravel 既定のエラー本文のままで、API 画面の全体スイッチだけに従います。

---

## 5. ログインが必要なエンドポイントを作る（トークンで保護）

API が非公開のデータ（顧客の注文、管理データなど）を返す場合は、対応する Sanctum のガードで包む必要があります。
コントローラーでは、ログイン中のユーザーを `$request->user()` で取得します。

ログインが必要な顧客用ルートの例（shop パッケージの `api.customer.active` は、トークン発行後にロックされたアカウントの
トークンを拒否します）：

```php
use GP247\Core\Api\Support\ApiAbilities;

Route::group([
    'prefix'     => GP247_API_V2_FRONT_PREFIX,   // => api/v2/front
    'middleware' => ['auth:customer-api', ApiAbilities::customer(), 'api.customer.active'],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Front\WishlistController::class);
    Route::get('member/wishlist/list', $ctrl.'@wishlistList');
});
```

管理用ルートはセクション 5.1 を参照してください。

クライアントは、`login` で取得したトークンをヘッダーに付けて送る必要があります。

```
Authorization: Bearer <access_token>
```

> **トークンの発行**（`createToken`）と**トークンの失効**（`logout`）は、core／shop のログイン用コントローラーにすでに
> 用意されています。トークンの取得には `api/v2/core/login`／`api/v2/front/login` エンドポイントを**再利用**するだけで十分です
> （v2 にはリフレッシュトークンを受け取る `with_refresh`、サインインの確認ステップ用の `login/challenge`、`refresh` もあります —
> [API 入門の 2 章](./api-introduction_ja.md) を参照）。特別な要件がない限り、ログイン部分を自作する必要はありません。

### 5.1. 管理用エンドポイント：管理画面の宣言とストアによる絞り込み

> ℹ️ **対応バージョン:** gp247/core 3.2.3

管理者トークンがあるだけで、すべてのデータを閲覧できるわけでは**ありません**。`GP247_API_V2_CORE_PREFIX` 配下の各ルートは、
ミドルウェア `api.admin:<管理ルート名>` で対応する**管理画面**を宣言する必要があります。GP247 は、アカウントがその画面を
開く権限を持っている場合（管理画面と同じ権限設定）にだけ呼び出しを許可し、マルチストア系プラグインの制限を適用して、
アカウントのストアを特定します。

```php
Route::group([
    'prefix'     => GP247_API_V2_CORE_PREFIX,   // => api/v2/core
    'middleware' => [
        'auth:admin-api',
        \GP247\Core\Api\Support\ApiAbilities::admin(),   // since 3.3.0
    ],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Admin\ReportController::class);
    Route::get('report/sales', $ctrl.'@salesList')
        ->middleware('api.admin:admin_report.sales');   // route name of the matching admin screen
});
```

コントローラーでは、必ずアカウントのストアでクエリを絞り込みます。

```php
use GP247\Core\Api\Support\ApiStoreScope;
use GP247\Core\Api\V2\ApiQuery;

return ApiQuery::page(ApiStoreScope::apply(ShopOrder::query()), $request,
    fn ($o) => ['id' => $o->id, 'total' => $o->total, 'status' => (int) $o->status]);
```

`api.admin` のないルートで `ApiStoreScope` を呼ぶとエラーになります（デフォルトで安全側）。これは、権限の宣言を忘れていることを
GP247 が知らせる仕組みです。

---

## 6. 「完了」とする前のチェックリスト

- [ ] 新しいエンドポイントは **v2** の下にある — **定数** `GP247_API_V2_CORE_PREFIX`／`GP247_API_V2_FRONT_PREFIX` を使い、文字列をハードコードせず、v1 に追加していない。
- [ ] コントローラーを `gp247_namespace(...)` 経由で指定し、オーバーライドを可能にしている。
- [ ] プラグインのルートは `GP247_API_MIDDLEWARE` ＋ `prefix` で**自分で包んでいる**（セクション 4）、または `ApiRouteRegistrar` で登録している（セクション 4.1）。
- [ ] すべての入力に `$request->validate([...])` がある。SQL を文字列連結で組み立て**ない**（Eloquent を使う）。
- [ ] 一覧は `ApiQuery::page()` を使い（必要な `filter`／`sort` の名前だけを宣言）、1 件は `ApiQuery::one()` を使っている。
- [ ] 非公開エンドポイントには `auth:admin-api` ＋ `ApiAbilities::admin()`、または `auth:customer-api` ＋ `ApiAbilities::customer()` ＋ `api.customer.active` がある。
- [ ] 管理用エンドポイントに `api.admin:<管理ルート名>` があり、`ApiStoreScope::apply()` でデータを絞り込んでいる（セクション 5.1）。
- [ ] エラーは `ApiErrorResponder` で返している（意味の正しい HTTP ステータスコード）。関数 `$toArray` は宣言したフィールドだけを返す。
- [ ] `vendor/gp247/*` を**変更していない**（そのパッケージのメンテナーである場合を除く）。
- [ ] 各メソッドに英語の PHPDoc がある。
- [ ] `php artisan route:clear` を実行し、`php artisan route:list` にルートが表示されることを確認した。
- [ ] サインインのステップを追加するプラグインは `LoginChallenge` を実装し、`describe()` で秘密を返さない（セクション 7）。

---

## 7. プラグインからサインインの確認ステップ（MFA）を追加する

> ℹ️ **対応バージョン:** gp247/core 3.6.0

core 自体は OTP を行いません。プラグインは `GP247\Core\Api\Contracts\LoginChallenge` インターフェースで、API サインイン
（v2 の顧客と管理者）に 2 つ目のステップを追加します：

| メソッド | 呼ばれるタイミング | 返す値 |
| --- | --- | --- |
| `required($owner, $request)` | パスワードが正しかった直後（v1 でも呼ばれる） | このアカウントが確認ステップを通る必要があれば `true` — ここではコードを**送らない** |
| `describe($owner)` | チケット発行時に 1 回（v2） | 必要ならここで SMS／メールのコードを送る。**公開**ヒント `method`、`meta` を返す — 秘密情報は含めない |
| `verify($owner, $request)` | アプリが `POST login/challenge` を呼んだとき | 回答（`code` など、プラグインが決める項目）が正しければ `true` |

```php
<?php

namespace App\GP247\Plugins\MyOtp;

use GP247\Core\Api\Contracts\LoginChallenge;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class OtpChallenge implements LoginChallenge
{
    public function required(Authenticatable $owner, Request $request): bool
    {
        return MyOtpSettings::enabledFor($owner);   // e.g. the user turned 2FA on
    }

    public function describe(Authenticatable $owner): array
    {
        return ['method' => 'totp', 'meta' => ['digits' => 6]];   // public hints only
    }

    public function verify(Authenticatable $owner, Request $request): bool
    {
        return MyOtp::check($owner, (string) $request->input('code'));
    }
}
```

```php
// In the plugin's service provider (boot)
config(['gp247-config.api.login_challenge.customer' => array_merge(
    config('gp247-config.api.login_challenge.customer', []), [OtpChallenge::class]
)]);
```

残りは core が行います：1 回限りのチケット（5 分、既定のキャッシュにハッシュで保存 — 共有ホスティングでも動作）、試行回数の
制限（ログインのカウンターと共通。チケットはどの IP からでも 5 回誤ると破棄）、`verify()` が `true` を返したときのトークン／セッションの発行。プラグインが例外を投げたり、
クラスの登録が誤っていたりすると、サインインは**拒否**されます（確認ステップが飛ばされることはありません）。管理者向けには
`gp247-config.api.login_challenge.admin` に登録します。アプリ側の呼び方：[API の紹介 — セクション 9.4](./api-introduction_ja.md)。

## 8. 注文を処理するプラグイン: アプリからの注文も同じフックを通る

> ℹ️ **対応バージョン:** gp247/shop 3.5.0

API からの注文（`POST api/v2/front/checkout/place`）は、**ウェブサイトと同じチェックアウトのコード**で処理されます。ウェブの注文をすでに
処理しているプラグイン（出品者ごとの注文分割、手数料の計上、プロモーション枠の消費、通知の送信など）は、**API 用に何も書かずに**
アプリからの注文も受け取ります:

| フック | 実行されるタイミング | 備考 |
| --- | --- | --- |
| モデルイベント `ShopOrder::created` | 注文を書き込むとき | 注文明細は同じトランザクション内で後から書き込まれる — `DB::afterCommit()` の中で読む |
| `gp247_listen_order_created($order)` | 注文作成の直後 | ヘルパー関数。サイト側で独自に定義し直せる |
| 決済 / 配送 / 合計プラグインの `AppConfig` の `endApp()` | 注文を完了するとき | 注文 ID はウェブと同じく `session('orderID')` から読む |
| `gp247_listen_order_success($order)` | 注文を完了するとき | API の注文では **注文した時点で** 実行される（ゲートウェイで支払う注文でも、まだ入金前） |
| `gp247_order_process_after_success($orderId)` | 成功イベントの後 | 注文確認メールを送る |

表のヘルパー関数を置き換えるには、関数名を `config/gp247_functions_except.php` に追加し、同じシグネチャで自分で定義します。

```php
// In the plugin's service provider (boot)
\GP247\Shop\Models\ShopOrder::created(function ($order) {
    \Illuminate\Support\Facades\DB::afterCommit(function () use ($order) {
        // The order lines exist here, whether the order came from the website or the app.
        MyCommission::book($order->fresh('details'));
    });
});
```

プラグインが API リクエストの中で動くときに覚えておくこと:

- **注文の出どころ**: 注文の `channel` 列は、アプリからの注文なら `api`、ウェブサイトからの注文なら `web` です（管理画面で作成した注文と
  古い注文は空）。本当に区別が必要なときだけ使ってください — ほとんどのプラグインには不要です。
- **ブラウザーのセッションはない**: API リクエストの中の `session()` は、そのリクエストだけのセッションです（Cookie なし）。ウェブと同じく
  チェックアウトのキーは読み書きできますが、リダイレクト、フラッシュメッセージ、Cookie の読み取りはしないでください — アプリには届きません。
- **「注文成功」は「支払い済み」ではない**: API の注文では、このイベントは注文した時点で実行されます。入金を待つべき処理（デジタル商品の
  配信、サービスの有効化など）は、注文の支払い状況にもとづいて行ってください。

アプリ側の呼び出し方: [API の紹介 — 10 章](./api-introduction_ja.md)。

---

## よくある質問（Q&A）

**Q1: API はどこに追加すべきですか — vendor ですか、プラグインですか？**

→ ほとんどの場合は**プラグイン**（`app/GP247/Plugins/`）か、`app/GP247/` によるオーバーライドです。
`vendor/gp247/*` を直接編集するのは、そのパッケージの主要メンテナーの場合だけです。
`vendor` 内の変更は `composer update` で失われるためです。

**Q2: 新しいルートが表示されないのはなぜですか？**

→ よくある原因は 3 つです。(1) `.env` で `GP247_API_MODE=true` を設定していない。(2) キャッシュを削除していない —
`php artisan route:clear` を実行してください。(3) プラグインの場合、ファイル名が正確に `Route.php` で、
プラグインが有効になっている必要があります。

**Q3: プラグインの API が JSON を返さない／レート制限がかからないのはなぜですか？**

→ プラグインのルートは API のミドルウェアグループの外側で読み込まれるためです。セクション 4 のように
`'middleware' => GP247_API_MIDDLEWARE` で**自分で包む**必要があります。プラグインから API を追加するときに最も多い間違いです。

**Q4: `ApiQuery::page()` は Laravel の `paginate()` と何が違いますか？**

→ `ApiQuery::page()` は内部で `paginate()` を使いますが、API v2 の標準を加えます。宣言した `filter[...]`／`sort` の名前だけを
受け付け（未知の名前は `422`）、`page[size]` を最大 100 に制限し、ページ間でレコードが重複・欠落しないよう常に主キーでの並べ替えを
追加し、`{data, meta, links}` の形をそのまま返します。GP247 の既存の v2 API と揃えるため、一覧エンドポイントではこれを使ってください。

**Q5: 標準に沿ってエラーを返すには？**

→ `ApiErrorResponder`（`notFound()`、`forbidden()`、`respond($status, $code, $msg)`）を使います。
意味の正しい HTTP ステータスコードとともに、エラー本文 `{error, msg, code}` を返します。認証、バリデーション、
レート制限のエラーは GP247 が自動でこの形式に変換します。HTML や素の文字列は返さないでください。

**Q6: 自分の API 用に独自のスコープ／権限が必要ですか？**

→ スコープはアカウントの種類（`admin`、`admin-supper`、`user`）を区別するだけです。管理用エンドポイントでデータを閲覧する権限は、
`api.admin:<管理ルート名>`（セクション 5.1）を通じて管理画面の権限設定から取得されます。管理者は一度権限を付与するだけで、
管理画面と API の両方に適用されます。並行する独自の権限の仕組みを作るのは避けてください。

**Q7: `apiconnection`／`apikey` レイヤーは新しい API に必須ですか？**

→ コードで決めるものではありません。管理画面の設定 `api_connection_required` で全体としてオン／オフします。
ルートが `GP247_API_MIDDLEWARE` グループの中にあれば、管理者がオンにしたときにこのレイヤーが自動で適用されます。

**Q8: ログイン／トークン発行のエンドポイントを自作する必要がありますか？**

→ ありません。`api/v2/core/login`（管理者）または `api/v2/front/login`（顧客）を再利用してトークンを取得し、
新しいエンドポイントを `auth:admin-api`／`auth:customer-api` で保護してください。
独自のログインを書くのは、特殊な認証フローがある場合だけです。

**Q9: API のコントローラーはどのクラスを継承すべきですか？**

→ **管理**データなら `GP247\Core\Controllers\RootAdminController` を継承します。**Front／ストア**のデータなら
`GP247\Front\Controllers\RootFrontController` を継承します。どちらも、それぞれの側に適したコンテキストが設定済みです。

**Q10: 新しい API にテストを書く必要がありますか？**

→ 書くことをおすすめします。エンドポイントの feature test（PHPUnit）を、プロジェクトまたはプラグインのテストフォルダーに書きます。
少なくとも、ステータスコード、JSON の構造、非公開エンドポイントでトークンがないときにアクセスが拒否されることを確認してください。

---

## 変更履歴
<!-- ロジック/動作が変わった場合のみ記載。最新の行を一番上に。1 日 1 行：同じ日の変更は既存の行にまとめ、同じ日付の行を追加しない。 -->

| 日付 | GP247 バージョン | 変更内容 |
| --- | --- | --- |
| 2026-10-31 | gp247/core 3.6.0, gp247/shop 3.5.0。API v2：core/front/shop 3.3.0 | 新しいエンドポイントは v2 の接頭辞（`GP247_API_V2_CORE_PREFIX`、`GP247_API_V2_FRONT_PREFIX`）の下に置き、`{data, meta, links}` のレスポンスに `ApiQuery` を使う。v1 は凍結（1〜6 章）。`json.response` は `GP247_API_MIDDLEWARE` に含まれ `auth:*` より先に動く — `Accept` がなくても `500` にならない（1 章）。API v2 からの注文がウェブの注文と同じフック（`ShopOrder::created`、`endApp()`、`gp247_listen_order_success`、確認メール）を通る。注文の `channel` 列（`api` / `web`）（8 章） |
| 2026-10-28 | gp247/core 3.6.0 | プラグインが `LoginChallenge` で API サインインの確認ステップ（MFA）を追加（セクション 7） |
| 2026-10-26 | gp247/core 3.2.3。ルート登録の仕組み：3.3.0 | 各パッケージ／プラグインが `ApiRouteRegistrar` で API ルートを登録するように変更、`Routes/Api/v2/` フォルダーと `ApiAbilities::admin()` を追加（3.3.0）。管理用エンドポイントは管理画面の宣言（`api.admin`）とストアによる絞り込み（`ApiStoreScope`）が必須に。エラーは `ApiErrorResponder` で意味の正しい HTTP ステータスコードとともに返す。公開データのフィールドは `ApiFieldAllowlist` で絞り込む |

---

<sub>📅 **最終更新:** 2026-10-31 · ✍️ **作成者 (Author):** GP247</sub>
