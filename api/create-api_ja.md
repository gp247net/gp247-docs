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

| グループ | ルートで使う定数 | 値 | 用途 |
| --- | --- | --- | --- |
| Core（管理） | `GP247_API_CORE_PREFIX` | `api/core` | システム管理（管理者トークンが必要） |
| Front（ストアフロント） | `GP247_API_FRONT_PREFIX` | `api/front` | ストアのデータと顧客アカウント |

この 2 つの定数は `vendor/gp247/core/src/Library/Const.php` で固定的に宣言されています。ルートでは**必ず定数を使ってください**
（`"api/core"` という文字列を手書きしない）。将来接頭辞が変わっても、あなたのコードが自動的に追従します。

### 1.2. API モードを有効にする

API は **`GP247_API_MODE` フラグがオンのときだけ動作します**。サイトの `.env` ファイルを確認してください。

```dotenv
GP247_API_MODE=true
```

このフラグがオフの場合、API のルートは**一切読み込まれません**（`vendor/gp247/core/src/routes.php` を参照）。

### 1.3. API ルートが自動で読み込まれる仕組み

`GP247_API_MODE=true` のとき、コアは次のフォルダー内のすべてのファイルを自動で**走査して読み込み**、
すべてをミドルウェアグループ `GP247_API_MIDDLEWARE` で包みます。

- `vendor/gp247/core/src/Routes/Api/*.php`
- `vendor/gp247/shop/src/Routes/Api/*.php`
- `vendor/gp247/front/src/Routes/Api/*.php`

`GP247_API_MIDDLEWARE = ['api', 'api.extend']` です。`api.extend` は GP247 のミドルウェアグループで、
`config('gp247-config.api.middleware')` で定義されています。

| 順序 | ミドルウェア | 役割 |
| --- | --- | --- |
| 1 | `json.response` | すべてのレスポンスを **JSON** にする（ヘッダー `Accept: application/json` を設定） |
| 2 | `api.connection` | 任意のアプリケーション用の鍵（`apiconnection`／`apikey`）— 設定でオン／オフ |
| 3 | `throttle: 1000` | 1 サイクルあたり **1000 回の呼び出し**に制限（不正利用の防止） |

> ⚠️ **注意:** **この順序は固定です。変更しないでください。** config に明記された規約です。
>
> 👉 **プラグインにとって重要な結果：** core／shop／front の `Routes/Api/` に置いたルートには、上の 3 つのレイヤーが
> **自動で**付きます。しかし**プラグイン**から追加したルートは別の場所（セクション 4）で読み込まれるため、
> **自動では付きません**。自分で包む必要があります。

### 1.4. Laravel Sanctum による認証

GP247 は **Sanctum**（Bearer 形式のトークン）を使います。2 つの独立した「ガード」（ログインの入口）があります。

| ガード | プロバイダー | 対象 | トークンを発行する場所 |
| --- | --- | --- | --- |
| `admin-api` | `admin_provider` | 管理者 | `api/core/login` |
| `customer-api` | `customer_provider` | 顧客／会員 | `api/front/login` |

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
    'auth:admin-api',                    // Front API の場合は 'auth:customer-api'
    'ability:admin,admin-supper',        // 許可するスコープ
]
```

### 1.5. ヘルパー `gp247_namespace()` — オーバーライドを可能にする仕組み

GP247 のルートでは、コントローラーを `Controller::class` と直接書か**ず**、
`gp247_namespace()` で包みます。

```php
$productController = gp247_namespace(\GP247\Shop\Api\Admin\AdminProductController::class);
Route::get('list', $productController.'@getProductList');
```

このヘルパーは自動で確認します。`app/GP247/...` にオーバーライド用のファイルがあればアプリ側を使い、
なければ vendor 内のものを使います。これにより、エンドユーザーは **vendor を変更せずにコントローラーをオーバーライド**できます。
`GP247\...` パッケージのコントローラーをルートで指すときは、必ずこのヘルパーを使ってください。

---

## 2. 命名規則とレスポンスの形式

新しい API をシステムの他の部分と揃えるため、次の規則を守ってください。

- **リソースごとにまとめる**：データのグループごとに 1 つの `Route::group(['prefix' => 'product'], ...)` にします。
- **2 つの標準アクション**：`list`（一覧）と `detail/{id}`（1 件の詳細）。
- **コントローラーのメソッド名**：`get<Resource>List` と `get<Resource>Detail`
  （例：`getProductList`、`getProductDetail`）。
- **必ず JSON を返す**：`response()->json(...)` を使います。
- **成功時**：データをそのまま返します（オブジェクトまたはページ分割された一覧）。
- **失敗時／見つからない場合**：共通の形式でエラーオブジェクトを返します。

  ```php
  return response()->json([
      'error' => 1,
      'msg'   => 'Not found',
      'detail'=> 'Product not found or no permission!',
  ], 200);
  ```

- **ページ分割**：一覧を返すときは `->get()` の代わりにマクロ `->jsonPaginate()`
  （導入済みのパッケージ `spatie/laravel-json-api-paginate`）を使います。標準のページ分割構造
  （`data`、`current_page`、`total` など）を返し、ページサイズはリクエストで制御できます。

---

## 3. 例 A — 自分がメンテナンスするパッケージ（core／front／shop）にエンドポイントを追加する

あなたが**パッケージのメンテナー**（`vendor/gp247/<package>` を直接編集する）の場合に使います。
例として、公開の Front API `api/front/review/list` を追加します。次の順序どおりに進めてください。

1. **コントローラーを作成**します。場所は `vendor/gp247/<package>/src/Api/...` です。Front のデータなら
   `RootFrontController` を、管理のデータなら `RootAdminController` を継承します。

   ファイル `src/Api/Front/ReviewController.php` を作成します。

   ```php
   <?php

   namespace GP247\Shop\Api\Front;

   use GP247\Front\Controllers\RootFrontController;
   use Illuminate\Http\Request;
   use GP247\Shop\Models\ShopProductReview;

   class ReviewController extends RootFrontController
   {
       /**
        * Get the public review list.
        *
        * @return \Illuminate\Http\JsonResponse
        *
        * @aidlc-unit shop-api
        * @aidlc-story US-shop-api-review-list
        */
       public function getReviewList(Request $request)
       {
           $reviews = (new ShopProductReview)
               ->where('status', 1)
               ->jsonPaginate();

           return response()->json($reviews, 200);
       }
   }
   ```

2. **ルートファイルを作成**します。場所は `src/Routes/Api/review.php` です（`Routes/Api/` フォルダーに置くだけで
   コアが自動で読み込みます。ほかの場所で宣言する必要はありません）。

   ```php
   <?php

   use GP247\Shop\Api\Front\ReviewController;
   use Illuminate\Support\Facades\Route;

   Route::group([
       'prefix' => GP247_API_FRONT_PREFIX,   // => api/front
   ], function () {
       $reviewController = gp247_namespace(ReviewController::class);

       Route::group(['prefix' => 'review'], function () use ($reviewController) {
           Route::get('list', $reviewController.'@getReviewList');
       });
   });
   ```

3. **ルートが読み込まれたか確認**します。Artisan コマンドを使います（サイトのルートフォルダーで実行）。

   ```bash
   php artisan route:list --path=api/front/review
   ```

   成功すると `GET | api/front/review/list` の行が表示されます。**表示されない**場合は、
   `.env` の `GP247_API_MODE=true` を確認し、ルートキャッシュを削除してください：`php artisan route:clear`。

4. **試しに呼び出します**（公開エンドポイントなのでトークンは不要です）。

   ```bash
   curl -X GET "https://shop.example.com/api/front/review/list" -H "Accept: application/json"
   ```

> **管理用**エンドポイント（ログインが必要）の場合は、パッケージの `src/Routes/Api/` にルートを置き、
> `prefix => GP247_API_CORE_PREFIX` とともにミドルウェアグループ `auth:admin-api` ＋ `ability:...` で包みます
> （`vendor/gp247/shop/src/Routes/Api/admin.php` のサンプルを参照）。

---

## 4. 例 B — プラグインから API を追加する（個別サイトにおすすめ）

**vendor を変更できない**状態で特定のサイトに API を追加するときの、**標準に沿った**方法です。プラグインは
`app/GP247/Plugins/<PluginName>/` に置かれ、その `Route.php` ファイルはコアが自動で読み込みます。

> ⚠️ **注意:** **重要な違い：** プラグインのルートは API のミドルウェアグループ（セクション 1.3）の**外側**で読み込まれます。
> そのため `GP247_API_MIDDLEWARE` で**自分で包み**、`prefix` も自分で設定する**必要があります**。忘れると、
> API は JSON を強制**せず**、`api.connection` レイヤーも通ら**ず**、レート制限も**かかりません**。

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
        *
        * @aidlc-unit myapi-plugin
        * @aidlc-story US-myapi-plugin-status
        */
       public function getStatus(Request $request)
       {
           return response()->json([
               'error'  => 0,
               'status' => 'ok',
               'time'   => gp247_time_now(),
           ], 200);
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
           'middleware' => GP247_API_MIDDLEWARE,   // ['api','api.extend'] — 必ず自分で追加する
           'prefix'     => GP247_API_FRONT_PREFIX,  // => api/front
       ], function () {
           $statusController = gp247_namespace(StatusController::class);

           Route::group(['prefix' => 'myapi'], function () use ($statusController) {
               Route::get('status', $statusController.'@getStatus');
           });
       });
   }
   ```

3. **キャッシュを削除してルートを確認**します。

   ```bash
   php artisan route:clear
   php artisan route:list --path=api/front/myapi
   ```

   成功すると `GET | api/front/myapi/status` が表示されます。

4. **試しに呼び出します**。

   ```bash
   curl -X GET "https://shop.example.com/api/front/myapi/status" -H "Accept: application/json"
   ```

   期待される結果：

   ```json
   { "error": 0, "status": "ok", "time": "2026-07-30 10:00:00" }
   ```

---

## 5. ログインが必要なエンドポイントを作る（トークンで保護）

API が非公開のデータ（顧客の注文、管理データなど）を返す場合は、対応する Sanctum のガードで包む必要があります。
コントローラーでは、ログイン中のユーザーを `$request->user()` で取得します。

ログインが必要な管理用ルートの例：

```php
Route::group([
    'prefix'     => GP247_API_CORE_PREFIX,   // => api/core
    'middleware' => [
        'auth:admin-api',
        'ability:'.config('gp247-config.api.auth.api_scope_admin')
                 .','.config('gp247-config.api.auth.api_scope_admin_supper'),
    ],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Admin\ReportController::class);
    Route::get('report/sales', $ctrl.'@getSalesReport');
});
```

クライアントは、`login` で取得したトークンをヘッダーに付けて送る必要があります。

```
Authorization: Bearer <access_token>
```

> **トークンの発行**（`createToken`）と**トークンの失効**（`logout`）は、core／shop のログイン用コントローラーにすでに
> 用意されています。トークンの取得には `api/core/login`／`api/front/login` エンドポイントを**再利用**するだけで十分です。
> 特別な要件がない限り、ログイン部分を自作する必要はありません。

---

## 6. 「完了」とする前のチェックリスト

- [ ] **定数** `GP247_API_CORE_PREFIX`／`GP247_API_FRONT_PREFIX` を使い、文字列をハードコードしていない。
- [ ] コントローラーを `gp247_namespace(...)` 経由で指定し、オーバーライドを可能にしている。
- [ ] プラグインのルートは `GP247_API_MIDDLEWARE` ＋ `prefix` で**自分で包んでいる**（セクション 4）。
- [ ] すべての入力に `$request->validate([...])` がある。SQL を文字列連結で組み立て**ない**（Eloquent を使う）。
- [ ] 一覧は `->jsonPaginate()` を使い、すべてのレスポンスは `response()->json(...)` を通している。
- [ ] 非公開エンドポイントには `auth:admin-api`／`auth:customer-api` ＋ 正しいスコープの `ability:...` がある。
- [ ] `vendor/gp247/*` を**変更していない**（そのパッケージのメンテナーである場合を除く）。
- [ ] 各メソッドに英語の PHPDoc と `@aidlc-unit`／`@aidlc-story` タグがある（coding-style に従う）。
- [ ] `php artisan route:clear` を実行し、`php artisan route:list` にルートが表示されることを確認した。

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

**Q4: `jsonPaginate()` は Laravel の `paginate()` と何が違いますか？**

→ `jsonPaginate()`（パッケージ `spatie/laravel-json-api-paginate` のもの）は、JSON:API 標準に適したページ分割構造を返し、
クライアントがパラメーターでページサイズを制御できます。GP247 の既存 API と揃えるため、一覧エンドポイントではこれを使ってください。

**Q5: 標準に沿ってエラーを返すには？**

→ `response()->json(...)` でオブジェクト `{'error': 1, 'msg': '...', 'detail': '...'}` を返します。
認証エラーは Sanctum が自動で `401` を返すのに任せます。HTML や素の文字列は返さないでください。

**Q6: 自分の API 用に独自のスコープ／権限が必要ですか？**

→ 既存のスコープ（`admin`、`admin-supper`、`user`、`user-guest`）を再利用できます。新しいスコープが必要な場合は、
トークン作成時に付与し、ミドルウェア `ability:<your-scope>` で確認します。
Sanctum と並行する独自の権限の仕組みを作るのは避けてください。

**Q7: `apiconnection`／`apikey` レイヤーは新しい API に必須ですか？**

→ コードで決めるものではありません。管理画面の設定 `api_connection_required` で全体としてオン／オフします。
ルートが `GP247_API_MIDDLEWARE` グループの中にあれば、管理者がオンにしたときにこのレイヤーが自動で適用されます。

**Q8: ログイン／トークン発行のエンドポイントを自作する必要がありますか？**

→ ありません。`api/core/login`（管理者）または `api/front/login`（顧客）を再利用してトークンを取得し、
新しいエンドポイントを `auth:admin-api`／`auth:customer-api` で保護してください。
独自のログインを書くのは、特殊な認証フローがある場合だけです。

**Q9: API のコントローラーはどのクラスを継承すべきですか？**

→ **管理**データなら `GP247\Core\Controllers\RootAdminController` を継承します。**Front／ストア**のデータなら
`GP247\Front\Controllers\RootFrontController` を継承します。どちらも、それぞれの側に適したコンテキストが設定済みです。

**Q10: 新しい API にテストを書く必要がありますか？**

→ あります。`testing.md` に従い、エンドポイントの feature test を書きます（`tests/` リポジトリに置きます）。
少なくとも、ステータスコード、JSON の構造、非公開エンドポイントでトークンがないときにアクセスが拒否されることを確認してください。

---

<sub>📅 **最終更新:** 2026-10-22 · ✍️ **作成者 (Author):** GP247</sub>
