> 🌐 **言語:** [🇻🇳 Tiếng Việt](./create-plugin-advanced_vi.md) · [🇬🇧 English](./create-plugin-advanced.md) · 🇯🇵 日本語（現在）

# 高度なプラグイン — 複数ストア、秘密鍵、ストアフロント、クーポン

## はじめに

このドキュメントは、GP247 プラグインを書くときの高度なトピックをまとめたものです。各セクションは単独で読めます。
ストアごとの設定、秘密鍵の保存、ストアフロントへのコンテンツ表示（専用ページ、レイアウトブロック、フック）、クーポン／ポイントプラグイン、
商品価格の変更、管理メニューの追加を扱います。[プラグイン作成ガイド](./create-plugin_ja.md) の後に読むことをおすすめします。決済プラグインと配送プラグインには
専用のガイドがあります：[決済](./create-payment-plugin_ja.md)、[配送](./create-shipping-plugin_ja.md)。

> 💡 **AI エージェントと一緒に作業しますか？** [gp247-skills](https://github.com/gp247net/gp247-skills) リポジトリのスキル **`gp247-plugin-create`** を使ってください（スキルのフォルダーを
> エージェントのスキルフォルダー、たとえば Claude Code なら `.claude/skills/` にコピーします）。スキルはサイトのバージョンを自動で調べ、以下の内容（ストアごとの設定、秘密鍵、
> ストアフロントのフックなど）を適用します。決済／配送プラグインは `gp247-payment-plugin-create` / `gp247-shipping-plugin-create` を使います。

---

## 1. ストアごとの設定

GP247 には 2 つの複数ストアの形があります。**multi-store**（ドメインごとに 1 ストア）と **marketplace**（1 つのドメインに複数の販売者）です。
「ストアごと」のプラグインはどちらでも動きます。単一ストアのサイトでは何も変わりません。

1. **スコープを宣言します。** `gp247.json` に `"storeScope": "store"`（有効／無効と設定がストアごとに異なる）または `"global"`
   （既定値。共通の 1 つの値）を書きます。
2. **有効なストアの設定を読みます。**

   ```php
   $value = gp247_config('MyPlugin_items_per_page', gp247_plugin_store_id());
   ```

   `gp247_plugin_store_id()` は正しいストアを自動で選びます（管理セッション、マーケットプレイスで決済中のストア、ドメインに対応するストア）。
   ストア固有の値がなければ、`gp247_config()` は共通の値を使います。ストアがすでに分かっている場合（たとえば注文のストア）は、直接渡します：
   `gp247_config('MyPlugin_x', $order->store_id)`。
3. **設定画面でストア選択を有効にします。** `ConfigForm` を継承した画面で、`storeScoped()` が `true` を返すようにします。コアがストア選択欄、
   「継承／個別」のラベル、「共通設定を使う」ボタンを自動で作ります。ストアが実際に変更した項目だけが個別に保存されます。
4. **ストアの管理者が自分で設定できるようにします**（任意）。`Provider.php` の `gp247_extension_check_active` ブロックに、
   プラグインの管理パスのセグメントを追加します。

   ```php
   config(['gp247-config.admin.store_scoped_segments' => array_values(array_unique(array_merge(
       (array) config('gp247-config.admin.store_scoped_segments', []), ['myplugin']
   )))]);
   ```

   追加しない場合、ストアごとの値を設定できるのはルート管理者だけです。そのため、お金の行き先を決める設定（決済アカウント）では
   **追加しないでください**。

---

## 2. 秘密鍵

すべての **認証情報**（API キー、トークン、パスワード、Webhook の署名シークレット）は暗号化する必要があります。平文で保存してはいけません。

**設定画面の場合（よくあるケース）：** `'security' => 1` で行を作り、`fieldTypes()` で項目を `password` と宣言します。
コアは保存時に暗号化し、**保存済みの値をブラウザーに送ることはありません**（項目には「値が設定済み」とだけ表示されます。空のまま保存すると
元の値を保持します）。ストアレベルでは、値を見せずに継承します。`gp247_config('MyPlugin_api_key', $storeId)` で読むと、
平文が返ります。

**プラグイン独自のテーブルの場合：** コアの `Secret` キャストと `TEXT` 型のカラムを使い、暗号鍵のローテーションツールが処理できるようにカラムを登録します。

```php
// モデル内
protected $casts = ['access_token' => \GP247\Core\Casts\Secret::class];

// Provider.php 内（gp247_extension_check_active の中）— テーブル名はプレフィックスなし
config(['gp247-config.security.encrypted_columns.my_plugin_account' => ['access_token']]);
```

暗号化したカラムは **検索／絞り込みができません**。検索が必要なら、別にハッシュ用のカラムを追加します。

**やってはいけないこと：** `Crypt` や `base64` で自前の暗号化をする。鍵を `config.php`、プラグイン付属の `.env` ファイル、JSON の行、通常のカラムに置く。
鍵をログに書き出す、または API で返す。

---

## 3. ストアフロントにコンテンツを表示する

プラグインは **テンプレートのファイルを編集したり同梱したりしません**（テンプレートは他の人のものです）。ストアフロントに組み込む方法は 3 つあり、
`gp247/front` がインストールされている必要があります。

| 方法 | 使うとき | `Provider.php` での登録 |
|---|---|---|
| **専用ページ** | プラグインが独自のページ（一覧、詳細など）を持つ | `GP247_FRONT_MIDDLEWARE` グループ内のルート + `Controllers/FrontController.php` |
| **レイアウトブロック** | ストアオーナーが好きな位置にブロックを置く | `gp247-config.front.layout_block_views` |
| **フック** | ショップページの決まった位置に表示する | `gp247-config.front.plugin_hooks.<フック名>` |

**専用ページ** — ルートは次の形にします（`Route.php` にスケルトンが生成済みです）。

```php
Route::group(['middleware' => GP247_FRONT_MIDDLEWARE, 'prefix' => 'plugin/myplugin'], function () {
    Route::get('/', [\App\GP247\Plugins\MyPlugin\Controllers\FrontController::class, 'index'])->name('myplugin.index');
});
```

ページのビューは、稼働中のテンプレートのレイアウトを使います：`@extends($GP247TemplatePath.'.layout')` と
`@section('block_main_content_center')`。ページを `sitemap.xml` に入れたい場合は、`Seo.php`（関数 `sitemapUrls($storeId)` が各 URL を
`loc` と `alias` 付きで返します）と、`Provider.php` 内の登録部分を残します。ストアオーナーがこのページにレイアウトブロックを指定できるようにするには、ページ種別を登録します。

```php
$layoutPage = config('gp247-config.front.layout_page', []);
$layoutPage['myplugin_index'] = 'Plugins/MyPlugin::lang.layout_page_index'; // 言語 KEY であり、表示済みのテキストではない
config(['gp247-config.front.layout_page' => $layoutPage]);
```

そして、ビューを返すときに `'layout_page' => 'myplugin_index'` を渡します。

**レイアウトブロック** — ブロックは、すべてのテンプレートで「レイアウトブロック」(Layout Block) 画面に表示されます。

```php
$blocks = config('gp247-config.front.layout_block_views', []);
$blocks['myplugin_banner'] = 'Plugins/MyPlugin::blocks.myplugin_banner'; // Views/blocks/myplugin_banner.blade.php
config(['gp247-config.front.layout_block_views' => $blocks]);
```

名前が重ならないよう、ブロック名にはプラグインのプレフィックスを付けます。同じ名前のファイルをテンプレートが持っている場合は、テンプレートのファイルが優先されます。

**フック** — 現在は `shop_product_detail_bottom`（`['product' => …]` を受け取る）と `shop_order_detail_bottom`（
`['order' => …]` を受け取る）があります。**配列形式の関数** を登録してください。クロージャーは使いません（クロージャーがあると `php artisan config:cache` が失敗します）。

```php
$hooks = config('gp247-config.front.plugin_hooks', []);
$hooks['shop_order_detail_bottom'][] = [
    'key' => 'MyPlugin',
    'callback' => [\App\GP247\Plugins\MyPlugin\Hooks::class, 'orderDetailBottom'],
];
config(['gp247-config.front.plugin_hooks' => $hooks]);
```

```php
namespace App\GP247\Plugins\MyPlugin;

class Hooks
{
    public static function orderDetailBottom(array $data): string
    {
        return view('Plugins/MyPlugin::hooks.order_detail_bottom', $data)->render();
    }
}
```

フック関数でエラーが起きても、失われるのはその部分だけです（ログに記録されます）。ページ全体は壊れません。

---

## 4. クーポン／ポイントプラグイン（total-method）

`configCode: "Promotion"`（古い値 `"Total"` も受け付けます）。`getInfo()` に加えて、`AppConfig` は
`GP247\Shop\Front\Contracts\CheckoutTotalMethod` を実装する必要があります。実装がないと、決済ページはプラグインを表示しません。

```php
public function checkoutApply(array $payload): array; // 検証 + 適用。['error' => 0|1, 'msg' => …] を返す
public function checkoutRemove(): void;               // checkoutApply が保存したものを消す
public function checkoutView(): ?string;              // 入力部分のビュー key、または null
```

ビューの部分は `$pluginKey`、`$plugin`、`$message` を受け取り、決済ページのアクション `applyTotal` / `removeTotal` を
`totalPayload` フィールドで呼び出します（例：`wire:model="totalPayload.{{ $pluginKey }}.code"`）。テンプレートに既にある UI クラス（`input`、
`btn-primary` など）だけを使ってください。参考プラグイン：`ShopDiscount`。

**金額のルール：** ストアオーナーは金額（割引額、最低注文額）を **基本通貨** で入力します。プラグインが返す金額は **表示通貨** でなければなりません。
`gp247_currency_value($amount)` で換算し、しきい値はカートの小計（すでに表示通貨）と比較します。

---

## 5. 商品価格を変更する

価格を変えるプラグイン（期間限定セール、会員価格）は、`Provider.php` から `gp247-config.shop.price_resolvers` に関数を 1 つ追加します。
データベースの価格は **編集しません**。関数が何を受け取り何を返すかは、`GP247\Shop\Models\ShopProduct` の resolver ループの直上にある
コメントを読んでください。関数は配列形式 `[Class::class, 'method']` で登録します。

---

## 6. 管理メニュー

既存のグループ（`key` で探します。例：`ADMIN_SHOP_PAYMENT`、`ADMIN_SHOP_SHIPPING`）の下に項目を追加します。グループがなければスキップし、
2 回追加しないようにします。`install()` と `update()` から呼び出し、`uninstall()` で削除します。

```php
use GP247\Core\Models\AdminMenu;

private const MENU_URI = 'admin::myplugin'; // GP247_ADMIN_PREFIX の後ろに付く管理ルートのプレフィックス

private function ensureMenu(): void
{
    $parent = AdminMenu::where('key', 'ADMIN_SHOP_PAYMENT')->first();
    if ($parent && !AdminMenu::where('uri', self::MENU_URI)->exists()) {
        AdminMenu::insert([
            'parent_id' => $parent->id, 'sort' => 40, 'title' => 'My plugin',
            'icon' => 'fas fa-puzzle-piece', 'uri' => self::MENU_URI, 'key' => null, 'type' => 0,
        ]);
    }
}

// uninstall() 内：
AdminMenu::where('uri', self::MENU_URI)->delete();
```

---

## よくある質問（Q&A）

**Q1: 「ストアごと」のプラグインで、単一ストアのサイトの動きは変わりますか？**

→ 変わりません。単一ストアのサイトでは、`gp247_plugin_store_id()` はルートストアを指し、すべての値は共通設定から取られます。

**Q2: 子ストアの管理者がプラグインの設定画面を開けないのはなぜですか？**

→ 管理パスのセグメントを `store_scoped_segments` に追加していません（セクション 1、手順 4）。

**Q3: 秘密鍵を保存した後、項目が空に戻るのはなぜですか？**

→ 仕様どおりです。`password` 項目は保存済みの値をブラウザーに送りません。空のまま保存すると、元の鍵が保持されます。

**Q4: テンプレートを編集せずに、商品ページに何かを表示したいのですが？**

→ フック `shop_product_detail_bottom`（セクション 3）に、配列形式の関数を登録してください。

**Q5: プラグインをインストールした後、`php artisan config:cache` が "not serializable" エラーを出すのはなぜですか？**

→ プラグインがクロージャーを config に登録しています（フック、resolver）。配列形式の関数 `[Class::class, 'method']` に変えてください。

**Q6: クーポンプラグインが決済ページに表示されないのはなぜですか？**

→ `AppConfig` が `CheckoutTotalMethod` を実装していないか、`configCode` が `"Promotion"` ではありません。

**Q7: お客様が通貨を切り替えると、割引額が正しく表示されないのはなぜですか？**

→ プラグインが基本通貨の金額を返しています。返す前に `gp247_currency_value()` で表示通貨に換算してください。

**Q8: お客様のテンプレートに、プラグインのブロックと同じ名前のファイルがある場合は？**

→ テンプレートのファイルが優先されます。重ならないよう、ブロック名にプラグインのプレフィックスを付けてください（例：`myplugin_banner`）。

---

<sub>📅 **最終更新:** 2026-10-22 · ✍️ **作成者 (Author):** GP247</sub>
