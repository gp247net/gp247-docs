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
| 3 | `throttle:gp247-api` | 呼び出し頻度の制限。既定は IP ごとに **1 分あたり 1000 回**（`GP247_API_THROTTLE`） |

> ⚠️ **注意:** **この順序は固定です。変更しないでください。** config に明記された規約です。
>
> 👉 **プラグインにとって重要な結果：** core／shop／front の `Routes/Api/` に置いたルートには、上の 3 つのレイヤーが
> **自動で**付きます。しかし**プラグイン**から追加したルートは別の場所（セクション 4）で読み込まれるため、
> **自動では付きません**。自分で包むか、（3.3.0 以降は）`ApiRouteRegistrar` で登録する必要があります（セクション 4.1）。

> ℹ️ **対応バージョン:** gp247/core, gp247/front, gp247/shop 3.3.0 — 各パッケージは自分の API ルート（`Routes/Api/v2/`
> フォルダーを含む）を `GP247\Core\Api\Support\ApiRouteRegistrar` で登録します。パッケージのルートは、そのパッケージが
> データベースにインストールされている場合にだけ存在します。パスとルート名は変わりません。

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
- **失敗時／見つからない場合**：`ApiErrorResponder` を使い、**意味の正しい HTTP ステータスコード**と共通のエラー本文
  `{"error": 1, "msg": "…", "code": "…"}` を返します（コード一覧は [API 入門](./api-introduction_ja.md) の 7 章）。

  ```php
  use GP247\Core\Api\Support\ApiErrorResponder;

  if (!$product) {
      return ApiErrorResponder::notFound();          // 404, code: not_found
  }
  ```

  > ℹ️ **対応バージョン:** gp247/core 3.2.3 — `api/*` 配下の認証、権限、バリデーション、レート制限のエラーは自動でこの形式に
  > 変換されます。コントローラー自身が判断する場合だけ `notFound()`／`forbidden()` を自分で返してください。エラーに
  > `response()->json([...], 200)` を使う旧来の書き方は**もう使いません**。
- **必要なフィールドだけを返す**：公開データでは、モデルをそのまま返さず
  `GP247\Core\Api\Support\ApiFieldAllowlist::one()`／`::page()` でフィールドを絞り込みます。テーブルに新しく追加した列が
  API に自動で漏れ出すことはありません。

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

### 4.1. もっと簡単な方法：`ApiRouteRegistrar` で登録する

> ℹ️ **対応バージョン:** gp247/core 3.3.0

`Route.php` でミドルウェアグループを自分で包む代わりに、API ルートをプラグイン内の別ファイルに置き、
プラグインの `Provider.php` から登録します。

```php
// app/GP247/Plugins/MyApi/Provider.php
\GP247\Core\Api\Support\ApiRouteRegistrar::register(__DIR__ . '/Routes/api.php');
```

```php
// app/GP247/Plugins/MyApi/Routes/api.php — 接頭辞とルートだけを宣言する
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => GP247_API_FRONT_PREFIX], function () {
    $statusController = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Front\StatusController::class);
    Route::get('myapi/status', $statusController.'@getStatus');
});
```

`ApiRouteRegistrar` は `GP247_API_MIDDLEWARE`（JSON、API Connection、レート制限）で自動的に包みます。
`GP247_API_MODE` がオフのときは何も登録せず、サイトで `route:cache` 済みの場合は再登録しません。
エラーの取り決め `{error, msg, code}` は、`api/core`、`api/front`、`api/v2/core`、`api/v2/front` 配下のすべてのパスに適用されます。

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

### 5.1. 管理用エンドポイント：管理画面の宣言とストアによる絞り込み

> ℹ️ **対応バージョン:** gp247/core 3.2.3

管理者トークンがあるだけで、すべてのデータを閲覧できるわけでは**ありません**。`GP247_API_CORE_PREFIX` 配下の各ルートは、
ミドルウェア `api.admin:<管理ルート名>` で対応する**管理画面**を宣言する必要があります。GP247 は、アカウントがその画面を
開く権限を持っている場合（管理画面と同じ権限設定）にだけ呼び出しを許可し、マルチストア系プラグインの制限を適用して、
アカウントのストアを特定します。

```php
Route::group([
    'prefix'     => GP247_API_CORE_PREFIX,
    'middleware' => [
        'auth:admin-api',
        \GP247\Core\Api\Support\ApiAbilities::admin(),   // 3.3.0 以降。それより古い版では 'ability:admin,admin-supper'
    ],
], function () {
    $ctrl = gp247_namespace(\App\GP247\Plugins\MyApi\Api\Admin\ReportController::class);
    Route::get('report/sales', $ctrl.'@getSalesReport')
        ->middleware('api.admin:admin_report.sales');   // 対応する管理画面のルート名
});
```

コントローラーでは、必ずアカウントのストアでクエリを絞り込みます。

```php
use GP247\Core\Api\Support\ApiStoreScope;

$rows = ApiStoreScope::apply(ShopOrder::query())->jsonPaginate();
```

`api.admin` のないルートで `ApiStoreScope` を呼ぶとエラーになります（デフォルトで安全側）。これは、権限の宣言を忘れていることを
GP247 が知らせる仕組みです。

---

## 6. 「完了」とする前のチェックリスト

- [ ] **定数** `GP247_API_CORE_PREFIX`／`GP247_API_FRONT_PREFIX` を使い、文字列をハードコードしていない。
- [ ] コントローラーを `gp247_namespace(...)` 経由で指定し、オーバーライドを可能にしている。
- [ ] プラグインのルートは `GP247_API_MIDDLEWARE` ＋ `prefix` で**自分で包んでいる**（セクション 4）。
- [ ] すべての入力に `$request->validate([...])` がある。SQL を文字列連結で組み立て**ない**（Eloquent を使う）。
- [ ] 一覧は `->jsonPaginate()` を使い、すべてのレスポンスは `response()->json(...)` を通している。
- [ ] 非公開エンドポイントには `auth:admin-api`／`auth:customer-api` ＋ 正しいスコープの `ability:...` がある。
- [ ] 管理用エンドポイントに `api.admin:<管理ルート名>` があり、`ApiStoreScope::apply()` でデータを絞り込んでいる（セクション 5.1）。
- [ ] エラーは `ApiErrorResponder` で返している（意味の正しい HTTP ステータスコード）。公開データは `ApiFieldAllowlist` でフィールドを絞り込んでいる。
- [ ] `vendor/gp247/*` を**変更していない**（そのパッケージのメンテナーである場合を除く）。
- [ ] 各メソッドに英語の PHPDoc と `@aidlc-unit`／`@aidlc-story` タグがある（coding-style に従う）。
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
`gp247-config.api.login_challenge.admin` に登録します。アプリ側の呼び方：[API の紹介 — セクション 10.4](./api-introduction_ja.md)。

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

## 変更履歴
<!-- ロジック/動作が変わった場合のみ記載。最新の行を一番上に。1 日 1 行：同じ日の変更は既存の行にまとめ、同じ日付の行を追加しない。 -->

| 日付 | GP247 バージョン | 変更内容 |
| --- | --- | --- |
| 2026-10-28 | gp247/core 3.6.0 | プラグインが `LoginChallenge` で API サインインの確認ステップ（MFA）を追加（セクション 7） |
| 2026-10-26 | gp247/core 3.2.3。ルート登録の仕組み：3.3.0 | 各パッケージ／プラグインが `ApiRouteRegistrar` で API ルートを登録するように変更、`Routes/Api/v2/` フォルダーと `ApiAbilities::admin()` を追加（3.3.0）。管理用エンドポイントは管理画面の宣言（`api.admin`）とストアによる絞り込み（`ApiStoreScope`）が必須に。エラーは `ApiErrorResponder` で意味の正しい HTTP ステータスコードとともに返す。公開データのフィールドは `ApiFieldAllowlist` で絞り込む |

---

<sub>📅 **最終更新:** 2026-10-28 · ✍️ **作成者 (Author):** GP247</sub>
