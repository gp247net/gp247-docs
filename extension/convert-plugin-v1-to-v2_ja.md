> 🌐 **言語:** [🇻🇳 Tiếng Việt](./convert-plugin-v1-to-v2_vi.md) · [🇬🇧 English](./convert-plugin-v1-to-v2.md) · 🇯🇵 日本語（現在）

# GP247 プラグインを v1（Core 1.x）から v2（Core 2.0）へ変換するガイド

## はじめに

このドキュメントでは、**旧バージョン（gp247/core 1.x）向けに書かれた GP247 プラグインを、
新しいプラグイン標準（gp247/core 2.0）にアップグレードする方法**を説明します。
対象は、GP247 1.x で動いているプラグインをすでに持っていて、GP247 2.0 でも動かしたい開発者や管理者です。
読み終えると、**どのファイルを修正し、何に変えればよいか**がわかります。
システム全体を深く理解しなくても、手順どおりに進められます。

> ⚠️ **注意:** **最初に知っておくべき最も重要なこと：** GP247 2.0 では **UI 層が丸ごと置き換わりました**。
> 1.x は **AdminLTE（Bootstrap ＋ jQuery ＋ pjax）** を使い、2.0 は **TailAdmin
> （Tailwind ＋ Alpine ＋ Livewire）** を使います。そのため、1.x のプラグインは 2.0 では**そのままでは動きません**
> — 旧レイアウトは削除され、jQuery も読み込まれなくなりました。変換作業の中心は **UI 層といくつかの設定ファイルの修正**です。
> ロジック／モデルのコードはほぼそのまま使えます。

> 💡 **ヒント:** **AI エージェントと一緒に作業しますか？** [gp247-skills](https://github.com/gp247net/gp247-skills) リポジトリのスキル **`gp247-plugin-create`** は、
> 現行のプラグイン標準（Livewire ＋ TailAdmin、更新に安全）を把握しています。UI 層を書き直すときに使ってください。
> 決済／配送プラグインには `gp247-payment-plugin-create`／`gp247-shipping-plugin-create` を使います。

---

## 1. 2 つのプラグイン形式はどこが違うのか？

GP247 はコアの中に「プラグインの雛形」（plugin format）を持っています。v1 と v2 の雛形を簡単に比較します。

| 項目 | プラグイン v1（Core 1.x） | プラグイン v2（Core 2.0） |
|---|---|---|
| 管理画面の UI フレームワーク | AdminLTE（Bootstrap ＋ jQuery） | TailAdmin（Tailwind ＋ Alpine） |
| 管理画面のページ遷移 | pjax | pjax なし（Livewire の再描画） |
| 管理画面ビューのマスターレイアウト | `@extends('gp247-core::layout')` | `@extends('gp247-admin::layouts.admin')` |
| 動的な操作 | jQuery ＋ jQuery プラグイン（select2、daterangepicker など） | Livewire 4 ＋ Alpine |
| 新方式の管理画面 | *（なし）* | Livewire コンポーネント `Livewire/AdminLivewire.php` |
| SEO サイトマップ連携 | *（なし）* | `Seo.php` ファイル ＋ `Provider.php` での登録 |
| LayoutBlock の page-type 登録 | *（なし）* | `Provider.php` で `token => lang-key` を登録（プラグインが独自の公開ページを持つ場合） |
| `gp247.json` → `requireCore` | `["1.2"]` | 稼働中のコアのバージョン。例：`["3.1"]`（`php artisan gp247:info` を参照） |
| `gp247.json` → 依存関係のキー | `requirePackages`／`requireExtensions` | `requireComposerPackages`／`requireGp247Extensions`（core 2.1 で名称変更） |
| `gp247.json` → `requireUpdateFrom` | *（なし）* | `"1.0"`（ワンクリック更新の条件） |

**結論：** ビジネスロジック（モデル、データ処理、インストール／アンインストール）はほとんど変わりません。
変わるのは **UI** と **いくつかの設定の宣言**です。

---

## 2. 手を入れるファイル

変換時に見直すプラグインのファイルです。「必須？」の列は、プラグインを動かすための最低限の作業か、
新しい標準に正しく沿うための推奨作業かを示します。

| ファイル | 作業内容 | 必須？ |
|---|---|---|
| `gp247.json` | `requireCore` を稼働中のコアに合わせる（例：`["3.1"]`）、`requireUpdateFrom` を追加、`requirePackages`／`requireExtensions` → `requireComposerPackages`／`requireGp247Extensions` に名称変更 | ✅ 必須 |
| `Views/Admin.blade.php` | マスターレイアウトを TailAdmin に変更し、jQuery／AdminLTE を除去 | ✅ 必須 |
| `AppConfig.php` | `disable()` のエラーメッセージを多言語対応に変更 | ✅ 必須 |
| `Livewire/AdminLivewire.php` | **新規作成** — Livewire 方式の管理画面 | 🟡 推奨 |
| `Views/livewire.blade.php` | **新規作成** — Livewire コンポーネント用のビュー | 🟡 推奨 |
| `Route.php` | Livewire のルートを追加（`class_exists` で保護） | 🟡 推奨 |
| `Seo.php` | **新規作成** — プラグインにサイトマップへ載せたい公開ページがある場合 | ⚪ 任意 |
| `Provider.php` | サイトマップ登録ブロックを追加（`Seo.php` を使う場合） | ⚪ 任意 |
| `Provider.php` | LayoutBlock の page-type 登録ブロックを追加（プラグインが独自の公開ページを持つ場合） | ⚪ 任意 |

> 用語メモ：**Livewire** は、**JavaScript／jQuery を書かずに**動的な UI（ボタン操作、ページの一部の再読み込み）を作れる
> Laravel の技術です。GP247 2.0 は旧バージョンの jQuery ＋ pjax の代わりに Livewire を使います。

---

## 3. 変換の手順（上から順に進めます）

### ステップ 1 — バックアップと作業用コピーの作成

1. プラグインのフォルダー全体を安全な場所にコピーします（例：`.zip` ファイルに圧縮）。
   失敗しても元に戻せるようにするためです。
2. プラグインで Git を使っている場合は、作業用の新しいブランチを作ります。

   ```bash
   git checkout -b upgrade-to-v2
   ```

   成功すると、ターミナルに `Switched to a new branch 'upgrade-to-v2'` という行が表示されます。

### ステップ 2 — `gp247.json` を更新する

プラグインのフォルダーにある `gp247.json` ファイルを開きます。`requireCore` を修正し、`requireUpdateFrom` を**追加**し、
core 2.1 の標準に従って依存関係の 2 つのキーを**名称変更**します：`requirePackages` → `requireComposerPackages`、
`requireExtensions` → `requireGp247Extensions`。

**変更前（v1）：**

```json
{
    "version": "1.0",
    "requireCore": ["1.2"],
    "requirePackages": [],
    "requireExtensions": []
}
```

**変更後（v2 — コア 3.1 の例）：**

```json
{
    "version": "1.0",
    "requireCore": ["3.1"],
    "requireUpdateFrom": "1.0",
    "requireComposerPackages": [],
    "requireGp247Extensions": [],
    "requireLivewire": false
}
```

各行の意味：
- `requireCore`：このプラグインが対応する GP247 コアのバージョン。`php artisan gp247:info` の `core` の行を正しく書きます（例：`["3.1"]`）。各値 `"X.Y"` は「X.x 系の中で X.Y 以上」という範囲です — `"2.1"` と書くと 3.x のコアは**インストールを拒否します**。
- `requireLivewire`：プラグインが Livewire を必要とするかどうか（`true`／`false`）。ステップ 5 で Livewire の画面を導入するときに追加します（`false` が安全な既定値です — Livewire はコアに同梱されています）。
- `requireComposerPackages`／`requireGp247Extensions`：依存元（Composer パッケージか GP247 拡張機能か）を明確にするため、`requirePackages`／`requireExtensions` から名称変更されました。Core 2.1 は旧キーも読みます（後方互換）が、非推奨（deprecated）です — 新しいキーを使ってください。
- `requireUpdateFrom`：このバージョンへワンクリック更新できる、インストール済みの**最低**バージョン。
  `"1.0"` にしておけば安全です（ほぼ制限なし）。旧系列から自動移行できないメジャーリリースを出すときだけ、
  この値を上げてください。

### ステップ 3 — 管理画面ビューのレイアウトを修正する（最重要）

これを省くと旧プラグインが「壊れる」変更です。レイアウト `gp247-core::layout` は **2.0 で削除された**ためです。
ファイル `Views/Admin.blade.php` を開きます。

1. 最初の `@extends(...)` の行を探し、レイアウト名を変更します。

   **変更前（v1）：**
   ```blade
   @extends('gp247-core::layout')
   ```

   **変更後（v2）：**
   ```blade
   @extends('gp247-admin::layouts.admin')
   ```

2. `@section('main') ... @endsection` の構造はそのまま残します。中身はひとまず元のままで構いません。
   ステップ 4 で後から調整します。

最小限の完成形は次のようになります。

```blade
@extends('gp247-admin::layouts.admin')

@section('main')
<div class="row">
      <div class="col-md-12">
            Your-content!
      </div>
</div>
@endsection

@push('styles')
      {{-- style css --}}
@endpush

@push('scripts')
      {{-- script --}}
@endpush
```

### ステップ 4 — jQuery と AdminLTE のウィジェットを取り除く

GP247 2.0 は **jQuery を読み込みません**。プラグインのビュー／アセットにある次のコードは**動作しない**ため、
置き換える必要があります。

- `$(...)`、`$.pjax(...)`、`$.ajax(...)` — jQuery の構文。
- `select2`、`daterangepicker`、`datetimepicker`、`bootstrap modal` — 旧来の jQuery プラグイン。
- `x-pjax` に関係するヘッダーやスクリプト。

置き換え方：
- **日付／時刻の選択** → **flatpickr**（TailAdmin 一式に同梱済み）か、該当する
  `<x-gp247::*>` コンポーネントがあればそれを使います。
- **テーブル／ボタン／動的な操作** → **Livewire ＋ Alpine** を使います（ステップ 5 を参照）。
- **表示テキスト** → 必ず `gp247_language_render('...')` で表示し、ハードコードしません。

> プラグインが静的なデータを表示するだけ（動的なボタンも jQuery もない）なら、
> ステップ 3 の後で動作します — ステップ 4 と 5 は、プラグインに動的な操作がある場合だけ必要です。

### ステップ 5 —（推奨）Livewire 方式の管理画面を追加する

これが v2 の新しい UI 標準です。プラグインを標準どおりにしたい場合（2 層の RBAC、統一されたレイアウト）は、
**新しいファイルを 2 つ**作ります。

1. ファイル `Livewire/AdminLivewire.php` を次の内容で作成します（`Extension_Key` を自分のプラグイン名に置き換えます
   — `gp247.json` の `configKey` とまったく同じにします）。

   ```php
   <?php
   #App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire.php

   namespace App\GP247\Plugins\Extension_Key\Livewire;

   use GP247\Core\AdminShell\Infrastructure\GP247AdminComponent;

   class AdminLivewire extends GP247AdminComponent
   {
       protected ?string $permission = null;

       public function render()
       {
           return view('Plugins/Extension_Key::livewire')
               ->layout('gp247-admin::layouts.admin', [
                   'title' => trans('Plugins/Extension_Key::lang.title'),
               ]);
       }
   }
   ```

   - `GP247AdminComponent` を継承すると、プラグインは第 2 層の RBAC（画面を開くときの権限チェック）、
     トースト通知、共通の管理画面レイアウトを**自動で**持ちます — コアの画面とまったく同じです。
   - `$permission = null` は、権限がコンポーネント名から自動で推定されることを意味します。

2. そのコンポーネントのビューとして、ファイル `Views/livewire.blade.php` を作成します。

   ```blade
   <div class="space-y-5">
       <x-gp247::card :title="trans('Plugins/Extension_Key::lang.title')">
           <p class="text-sm text-gray-600 dark:text-gray-300">
               {{ trans('Plugins/Extension_Key::lang.title') }} — Your content here!
           </p>
       </x-gp247::card>
   </div>
   ```

   - `<x-gp247::card>` は TailAdmin の共通コンポーネントです。生の HTML を書き直すより、
     既存の `<x-gp247::*>` コンポーネントを優先して使ってください。
   - クラス `dark:text-gray-300` により、ダークモードでもきれいに表示されます。

3. `Provider.php` で、プラグインの Livewire クラスの名前空間を登録します（Livewire のない環境でも起動できるよう保護します）。
   これにより、コンポーネントは `<livewire:Extension_Key::admin-livewire>` の形で解決されます。

   ```php
   if (class_exists(\Livewire\Livewire::class)) {
       \Livewire\Livewire::addNamespace('Extension_Key', classNamespace: 'App\\GP247\\Plugins\\Extension_Key\\Livewire');
   }
   ```

### ステップ 6 — `Route.php` に Livewire のルートを追加する

`Route.php` を開きます。管理画面用の `Route::group([...], function () { ... })` ブロックの中に、
Livewire のルート登録を**追加**します。このコードは `class_exists` で保護されているため、
プラグインにまだ Livewire のファイルがなくても**安全**です（エラーになりません）。

**変更前（v1）：**

```php
function () {
    Route::get('/', 'AdminController@index')
    ->name('admin_ExtensionUrlKey.index');
}
```

**変更後（v2）：**

```php
function () {
    Route::get('/', 'AdminController@index')
    ->name('admin_ExtensionUrlKey.index');

    // Livewire のルート。旧プラグインが動き続けるよう、旧コントローラーと並べて登録する
    if (class_exists(\App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire::class)) {
        Route::get('/livewire', \App\GP247\Plugins\Extension_Key\Livewire\AdminLivewire::class)
        ->name('admin_ExtensionUrlKey.livewire');
    }
}
```

> 補足：旧コントローラーのルート（`AdminController@index`）は**そのまま残します**。これにより
> プラグインは後方互換になり、旧画面と新しい Livewire 画面の両方を持てます。

### ステップ 7 — `AppConfig.php` を修正する（多言語のエラーメッセージ）

`AppConfig.php` で `disable()` メソッドを探します。v1 は英語の文字列 `'Error disable'` をハードコードしています。
v2 は多言語対応の関数を使います。

**変更前（v1）：**

```php
if (!$process) {
    $return = ['error' => 1, 'msg' => 'Error disable'];
}
```

**変更後（v2）：**

```php
if (!$process) {
    $return = ['error' => 1, 'msg' => gp247_language_render('admin.extension.action_error', ['action' => 'Disable'])];
}
```

また、わかりやすくするため、ファイル先頭のコメントを `Plugin format 1.0` から
`Plugin format 2.0` に変更してもかまいません。その他のメソッド（`install`、`uninstall`、`enable`、
`getInfo` など）はそのままです。

### ステップ 8 —（任意）SEO サイトマップ連携を追加する

このステップは、プラグインに Web サイトの `sitemap.xml` に載せたい**公開ページ**がある場合だけ行います。

1. プラグインのフォルダーにファイル `Seo.php` を作成します。

   ```php
   <?php

   namespace App\GP247\Plugins\Extension_Key;

   class Seo
   {
       public static function sitemapUrls($storeId): array
       {
           // サイトマップ用に、プラグインの公開 URL の一覧を返す。
           // まだない場合は、空の配列を返せば安全。
           return [];
       }
   }
   ```

2. `Provider.php` を開き、`if (gp247_extension_check_active(...))` の中に次のブロックを**追加**します。

   ```php
   if (class_exists('GP247\Front\Controllers\RootFrontController')) {
       $sitemapProviders = config('gp247-config.front.seo_sitemap_providers', []);
       $sitemapProviders[] = [
           'key' => $config['configKey'],
           'label' => $config['name'],
           'callback' => [\App\GP247\Plugins\Extension_Key\Seo::class, 'sitemapUrls'],
       ];
       config(['gp247-config.front.seo_sitemap_providers' => $sitemapProviders]);
   }
   ```

   このブロックは `class_exists` で保護されているため、Web サイトに `gp247/front` がインストールされて**いなく**ても、
   プラグインは通常どおりインストールできます（エラーになりません）。

### ステップ 8b —（任意）LayoutBlock 用の page-type を登録する

このステップは、プラグインが**独自のストアフロント（公開）ページ**（例：プラグインの一覧／詳細ページ）を持ち、
管理者がそのページに表示される **LayoutBlock のブロック**（バナー、HTML、ビューなど）を**差し込めるようにしたい**場合だけ行います。

背景：ストアフロントの各ページは、描画時に変数 `$layout_page` を通して「page-type」を発行します（コントローラーが
`'layout_page' => 'myplugin_index'` を `view()` に渡します）。管理画面の「レイアウトブロック」(Layout block) 画面は、
レジストリ `config('gp247-config.front.layout_page')` に**登録済み**の page-type しか一覧に出せません。
そのため、独自のページを持つプラグインは自分の page-type を登録する必要があります — ステップ 8 でサイトマップを登録するのと同じ方法です。

1. プラグインのコントローラーが、公開ページを描画するときに `layout_page` を渡すようにします。例：

   ```php
   return view($view, [
       // ... その他のデータ ...
       'layout_page' => 'myplugin_index',   // このページの page-type トークン
   ]);
   ```

2. `Provider.php` を開き、`if (gp247_extension_check_active(...))` の中に次のブロックを**追加**します。

   ```php
   if (class_exists('GP247\Front\Controllers\RootFrontController')) {
       $layoutPage = config('gp247-config.front.layout_page', []);
       // 多言語の KEY を保存する（翻訳済みの文字列ではない）— 管理画面が閲覧中の言語で表示する。
       $layoutPage['myplugin_index'] = $extensionPath.'::lang.layout_block_page.myplugin_index';
       config(['gp247-config.front.layout_page' => $layoutPage]);
   }
   ```

   - `token`（`myplugin_index`）は、コントローラーが発行する `$layout_page` の値と**一致しなければなりません**。
     一致しないと、管理者がブロックを選んでも表示されません。
   - 値は（プラグインの `Lang` ファイルを指す）**多言語のキー**であり、翻訳済みの文字列ではありません —
     管理画面のドロップダウンが、閲覧中のロケールで正しく表示されるようにするためです。
   - このブロックは `class_exists` で保護されているため、`gp247/front` がない Web サイトでは単にスキップされ、
     プラグインは通常どおりインストールできます。

3. プラグインの `Lang/vi/lang.php` と `Lang/en/lang.php` に、対応する言語の行を追加します
   （配列 `layout_block_page`）。例：`'myplugin_index' => 'プラグインの一覧ページ'`。

> プラグイン `News`（`app/GP247/Plugins/News/Provider.php`）が参考例です。
> `news_index`／`news_category`／`news_detail` をこの方法で登録しています。

> **テンプレート**との違いに注意：プラグインではなく*テンプレート*（見た目）を作る場合、page-type は登録**しません**
> — テンプレートはコントローラーが発行した `$layout_page` に従って表示するだけで、新しい page-type を定義しません。

---

### ステップ 8c —（任意）チェックアウトに表示する total-method プラグイン（クーポン／ポイント）

プラグインが **total-method**（`configCode: "Promotion"` — 例：クーポンやポイントのプラグイン。後方互換のため旧値 `"Total"` も受け付けます）で、
決済ページに入力欄を表示する必要がある場合だけ行います。v1 では、チェックアウトのテンプレートがプラグインの
`Views/render.blade.php` ＋ `Views/script.blade.php`（jQuery）を直接 include していました。GP247 2.0 はこれを**コントラクト**に置き換え、
どのテンプレート（既定でもカスタムでも）でも、互いに依存せずにどの total-method プラグインとも連携できるようにしました。
旧来の jQuery の render／script の代わりに、次のようにします。

1. **`AppConfig.php` にコントラクトを実装します。** `implements CheckoutTotalMethod`
   （`use GP247\Shop\Front\Contracts\CheckoutTotalMethod;`）と 3 つのメソッドを追加します — 既存の
   検証／セッションのロジックを再利用します。

   ```php
   public function checkoutApply(array $payload): array   // 検証 + session('totalMethod')[key] を設定。['error'=>0|1,'msg'=>...] を返す
   public function checkoutRemove(): void                 // session('totalMethod')[key] を unset
   public function checkoutView(): ?string                // 例：return $this->appPath.'::checkout'
   ```

2. `checkoutView()` の名前で**フラグメントビューを作成**します（例：`Views/checkout.blade.php`）。これはチェックアウトの
   Livewire コンポーネントの**内側**で描画されるため、`wire:` を使います — jQuery も fetch も使いません。

   ```blade
   <input type="text" class="input" wire:model="totalPayload.{{ $pluginKey }}.code">
   <button type="button" wire:click="applyTotal('{{ $pluginKey }}')">{{ gp247_language_render('cart.apply') }}</button>
   ```

   **テンプレートにすでにある UI トークン**（`input`、`btn-primary`、`btn-ghost`、`card`、
   `text-ink-*` など）だけを使ってください。まったく新しい Tailwind のクラスは、テンプレートのビルド済み CSS に存在せず、
   **警告もなく、スタイルが当たらないままになります**。

3. データ層は変わりません：`session('totalMethod')` → `getInfo()` → `ShopOrderTotal` →
   `addOrder()`。`getInfo()` はそのまま残します。

チェックアウトはプラグインを**自動で検出**し（configCode `Promotion`、旧値 `Total` ＋ インターフェースの実装）、フラグメントを
total-method の領域に描画します。インターフェースを実装して**いない** total プラグインは、アップグレードするまで 2.0 のチェックアウトに
表示されません（`Log::warning` が記録されます）。新しいコントラクトは旧来の jQuery AJAX の経路
（`render.blade.php` ＋ `script.blade.php` ＋ エンドポイント `discount.process`／`discount.remove` ＋ DOM `#gp247_showTotal` の差し替え）を
**完全に置き換えます** — 参考プラグイン `ShopDiscount` は、2.0 の UI からどこからも呼ばれないため、これらを**すべて削除**しました。
このような HTTP エンドポイントは、独自のヘッドレス／API の入口が本当に必要な場合だけ残してください。チェックアウトの UI には含まれません。

> テンプレート作成者へ：total-method プラグインに対応するには、カスタムのチェックアウトビューの確認ステップに
> 2 つの include を置くだけです：`@include('gp247-shop-front::partials.checkout_total_methods')` と
> `@include('gp247-shop-front::partials.order_totals')`。

---

### ステップ 8d —（任意）決済／配送プラグイン

v1 の決済（`configCode: "Payment"`）と配送（`configCode: "Shipping"`）プラグインは、2.0 のチェックアウトでも
`configCode` と `getInfo()` によって見つかります。アップグレード時には、さらに次のことを行うことをおすすめします。

1. **決済プラグイン：** 注文の状態や受領金額を自分で書き込んでいる箇所をすべて取り除き、
   `$order->recordPayment()`／`recordRefund()`（金額を正確に 1 回だけ記録）と `$order->changeStatus()` に切り替えます。
   Webhook のルートはストアフロントのミドルウェアグループの**外**に出し、データベースに触れる前に署名を検証します。
2. **配送プラグイン：** 旧来の `getInfo()` はそのまま動きます（サービスは 1 つ、料金は表示通貨）。複数のサービスを
   配達予定日つきで表示したり、送り状の作成、ラベル印刷、追跡、代金引換を行いたい場合は、`gp247/shop` の配送の
   コントラクトに従って `ShippingProvider` ドライバーを追加します。

完全なコード付きの手順ガイド：[決済プラグインの作成](create-payment-plugin_ja.md)、[配送プラグインの作成](create-shipping-plugin_ja.md)。
AI エージェントと作業する場合は、[gp247-skills](https://github.com/gp247net/gp247-skills) リポジトリのスキル
`gp247-payment-plugin-create`／`gp247-shipping-plugin-create` を使います。

---

### ステップ 9 — 動作確認

1. 新しいルート／ビュー／設定を読み込み直すため、Laravel のキャッシュを削除します。

   ```bash
   php artisan optimize:clear
   ```

   成功すると、ターミナルに `cache ... cleared successfully` のような行が表示されます。

2. 管理画面に入り、プラグインの画面を開きます。Livewire を追加した場合（ステップ 5～6）は、
   末尾が `/livewire` のパスも開いてみます。画面が正しく表示され、「view not found」や「class not found」の
   エラーが出なければ合格です。
3. 管理画面でプラグインの有効化／無効化（enable／disable）を試し、`AppConfig.php` が動作することを確認します。

---

## 4. 「完了」とする前のチェックリスト

- [ ] `gp247.json`：`requireCore` が `gp247:info` に従っている（例：`["3.1"]`）、`requireUpdateFrom` を追加済み、`requireComposerPackages`／`requireGp247Extensions` に変更済み。
- [ ] `Views/Admin.blade.php`：レイアウトを `gp247-admin::layouts.admin` に変更済み。
- [ ] ビュー／アセットに `$(...)`、pjax、jQuery のウィジェット（select2／daterangepicker など）が残っていない。
- [ ] 表示テキストは `gp247_language_render(...)` で表示し、ハードコードしていない。
- [ ] `AppConfig.php`：`disable()` が `'Error disable'` の代わりに `gp247_language_render(...)` を使っている。
- [ ] （Livewire を使う場合）`Livewire/AdminLivewire.php` ＋ `Views/livewire.blade.php` があり、ルートを追加済み。
- [ ] （公開ページがある場合）`Seo.php` ＋ `Provider.php` の登録ブロックがある。
- [ ] （LayoutBlock を差し込む独自の公開ページがある場合）`Provider.php` で page-type（`token => lang-key`）を `config('gp247-config.front.layout_page')` に登録済みで、トークンがコントローラーの発行する `$layout_page` と一致している。
- [ ] （total-method プラグイン — クーポン／ポイントの場合）`AppConfig` が `CheckoutTotalMethod` を実装し、`checkoutView()` のフラグメントがあり、テンプレートに既存の UI トークンだけを使っている。
- [ ] （決済／配送プラグインの場合）ステップ 8d を確認済み：金額は決済台帳 ＋ `changeStatus()` を通す、Webhook はストアフロントのグループの外。配送は `ShippingProvider` ドライバーを検討した。
- [ ] `php artisan optimize:clear` を実行し、管理画面を開けることを確認した。
- [ ] UI がライトモードとダークモードの両方で正しく表示される。

---

## 5. よくある質問（Q&A）

**Q1: 1.x のプラグインは、何も修正せずに 2.0 で動きますか？**

→ 非常に単純なものを除き、動きません。レイアウト `gp247-core::layout` が削除され、jQuery も読み込まれなくなったため、
旧来の管理画面 UI を使うプラグインは壊れます。最低でもステップ 2、3、7 を行う必要があります。

**Q2: Livewire への移行は必須ですか？**

→ 必須ではありません。プラグインの管理画面が静的な内容を表示するだけなら、レイアウトを変更する（ステップ 3）だけで動きます。
Livewire（ステップ 5～6）は、プラグインに動的な操作（以前は jQuery で実現していたもの）がある場合だけ必要です。

**Q3: ロジックやモデルを書き直す必要がありますか？**

→ ありません。GP247 2.0 のデータベーススキーマとロジック層は 1.x と同じです。変換で手を入れるのは、
UI 層といくつかの設定宣言ファイルだけです。

**Q4: 管理画面を開くと「View [gp247-core::layout] not found」と表示されます。なぜですか？**

→ ステップ 3 を行っていないためです。そのレイアウトは 2.0 で削除されました。`@extends('gp247-core::layout')` を
`@extends('gp247-admin::layouts.admin')` に変更してください。

**Q5: ルート／ビューを修正したのに、管理画面に古い表示が出ます。どうすればよいですか？**

→ `php artisan optimize:clear` を実行してルート／ビュー／設定のキャッシュを削除し、ページを再読み込みしてください。
Laravel が古いキャッシュを保持していることによる、最もよくある問題です。

**Q6: `requireUpdateFrom` にはどの値を設定すべきですか？**

→ `"1.0"` が最も安全です（更新をほとんど妨げません）。`update()` で旧系列から移行できない
メジャーリリースを出すときだけ、値を上げてください（例：`"2.0"`）。

**Q7: すべてのプラグインに `Seo.php` を作るべきですか？**

→ いいえ。プラグインに `sitemap.xml` へ URL を提供したい公開ページがある場合だけ作ります。
管理画面だけで使うプラグインなら、ステップ 8 は省略します。

**Q8: Web サイトに gp247/front がない場合、`Provider.php` のサイトマップ登録ブロックはエラーになりますか？**

→ なりません。このブロックは `class_exists('GP247\Front\Controllers\RootFrontController')` で包まれているため、
`gp247/front` がインストールされていなければ自動でスキップされ、プラグインは通常どおりインストールできます。

**Q9: Livewire のルートと並べて、旧コントローラーのルートを残すのはなぜですか？**

→ 後方互換のためです。両方を残すことで、旧画面を使っているプラグインが突然壊れることを防ぎ、
新しい Livewire の画面へ少しずつ移行できます。

**Q10: （v1 を修正するのではなく）まったく新しい v2 プラグインを作る最も速い方法は何ですか？**

→ artisan コマンドで v2 の雛形からプラグインを生成し、旧来のロジックをそこへコピーします。

```bash
php artisan gp247:make-plugin --name=YourPluginName --download=0
```

このコマンドは v2 標準の構造一式（Livewire、Seo、新しいレイアウトを含む）を生成するため、ファイルを 1 つずつ手作業で作る必要がありません。

---

## 変更履歴
<!-- ロジック・動作に変更があった場合のみ記録します。最新の行を一番上に。1 日 1 行：同じ日の変更は既存の行にまとめ、新しい行を追加しません。 -->

| 日付 | GP247 バージョン | 変更内容 |
| --- | --- | --- |
| 2026-10-18 | gp247/core 3.1 | **修正：** `requireCore` を稼働中のコア（`php artisan gp247:info`）に従って書く — 古い値 `"2.1"` は 3.x のコアに拒否される。ステップ 8d から決済／配送プラグインのガイドへリンク |

---

<sub>📅 **最終更新:** 2026-10-22 · ✍️ **作成者 (Author):** GP247</sub>
