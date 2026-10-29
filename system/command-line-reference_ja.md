> 🌐 **言語:** [🇻🇳 Tiếng Việt](./command-line-reference_vi.md) · [🇬🇧 English](./command-line-reference.md) · 🇯🇵 日本語（現在）

# GP247 システムのコマンドライン（CLI）一覧

## はじめに

このドキュメントでは、**GP247 システムが提供するすべての Artisan コマンド（`php artisan ...`）**を一覧にしています。
対象は `gp247/core`、`gp247/front`、`gp247/shop` の 3 つのパッケージです。各コマンドについて、機能の説明、
各オプションの意味、使用場面（他のコマンドとの組み合わせ方を含む）、対応するパッケージのバージョンを記載しています。
GP247 サイトの開発者と管理者向けのドキュメントです。読み終えると、**どのコマンドを、いつ、どのオプションで
入力すればよいか**がわかり、そのままコピーして実行できます。

> ℹ️ **コマンドの入力方法:** すべてのコマンドは**ターミナル**（Windows ではコマンドプロンプト）で、
> **プロジェクトのルートフォルダ**（`artisan` ファイルがある場所）で実行します。共通の構文: `php artisan <command-name> [options]`。
> 利用できる GP247 コマンドをすばやく一覧表示するには、`php artisan list gp247` と入力します。

**このドキュメントが対象とするバージョン**（プロジェクトの `composer.lock` から取得）:

| パッケージ | バージョン | リリース日 |
| --- | --- | --- |
| `gp247/core` | 2.0.4 | 2026-07-08 |
| `gp247/front` | 2.0.1 | 2026-07-07 |
| `gp247/shop` | 2.0.3 | 2026-07-08 |

> お使いのパッケージのバージョンは異なる場合があります。`php artisan gp247:core-info` で確認するか、
> `composer.lock` を開いてください。以下のコマンド一覧は 2.0.x 系全体で変わりません。

---

## クイックリファレンス

| コマンド | パッケージ | 機能の概要 |
| --- | --- | --- |
| `gp247:core-install` | core | GP247 をインストール（migrate、seed、アセットの publish） |
| `gp247:core-update` | core | `composer update` 後に GP247 を更新（アップグレード用マイグレーション + 安全な seed を実行） |
| `gp247:core-info` | core | システムの情報とバージョンを表示 |
| `gp247:make-plugin` | core | 新しいプラグインのひな形を生成 |
| `gp247:language-update` | core | 言語文字列をパッケージの既定値で上書き（upsert） |
| `gp247:front-install` | front | ストアフロントの画面モジュールをインストール |
| `gp247:front-uninstall` | front | front モジュールをアンインストール（front のテーブルを削除） |
| `gp247:make-template` | front | 新しい画面テンプレートのひな形を生成 |
| `gp247:template-setup` | front | ルートストアの既定テンプレートを設定 |
| `gp247:template-publish` | front | テンプレートのファイルをパッケージから `app/` に取り出して編集（ファイル単位） |
| `gp247:template-prune` | front | 公開済みで誰も編集していないテンプレートファイルをパッケージに戻す |
| `gp247:shop-install` | shop | 販売（EC）モジュールをインストール |
| `gp247:shop-update` | shop | データを壊さずに shop のスキーマ／データをアップグレード（稼働中のサイト） |
| `gp247:shop-uninstall` | shop | shop モジュールをアンインストール（shop のテーブルを削除） |
| `gp247:shop-sample` | shop | サンプルデータを作成（⚠️ 既存の shop データを削除） |
| `gp247:shop-clear-cart` | shop | 期限切れのカート／お気に入り／比較を削除 |
| `gp247:ext-list` | core | ローカルのプラグイン／テンプレート + 状態 + 更新版の有無を一覧表示 |
| `gp247:ext-install` | core | `.zip` ファイル、フォルダ、またはマーケットプレイスからプラグイン／テンプレートをインストール |
| `gp247:ext-enable` / `gp247:ext-disable` | core | インストール済みのプラグイン／テンプレートを有効化／無効化 |
| `gp247:ext-uninstall` | core | プラグイン／テンプレートをアンインストール（protected とテンプレートのガードを尊重） |
| `gp247:ext-update` | core | マーケットプレイスからプラグイン／テンプレートを更新（バックアップ／ロールバック）。`--local` はファイルを自分で置き換えた後のデータ更新だけを実行 |
| `gp247:ext-check-update` | core | マーケットプレイスの更新版を確認 |
| `gp247:ext-search` | core | マーケットプレイスのカタログを検索 |
| `gp247:ext-license` | core | 有料拡張機能のプラグインごとのライセンスを設定／表示／削除 |
| `gp247:ext-register-license` | core | 拡張機能ライブラリに接続するため、ドメインの API License（無料）を登録 |
| `gp247:ext-publish` | core | インストール済みプラグイン／テンプレートの静的ファイル（CSS/JS/画像）を `public/` に再コピー |
| `gp247:install` | core | 一括インストール（core [+front] [+shop] [+sample]） |
| `gp247:update` | core | `composer update` 後の更新（core [+shop]、稼働中のサイトでも安全） |
| `gp247:cache-rebuild` | core | route/config のキャッシュを再構築 |
| `gp247:doctor` | core | 環境を確認（PHP／拡張モジュール／書き込み権限／DB） |
| `gp247:info` | core | 状態を表示: バージョン、インストールのマーカー、拡張機能の数 |

---

## 出力の取り決め（`--json` と終了コード）

core 2.1 から、すべての `gp247:*` コマンドは共通の出力の取り決めに従います。これにより、スクリプト、CI/CD、
Docker、cron から安心して利用できます。

- **`--json`**: どのコマンドにもこのフラグを付けると、機械可読の envelope が**1 つだけ**
  **stdout** に出力されます。人間向けの行／進捗／警告はすべて **stderr** に出力されるため、
  `php artisan gp247:info --json | jq` は常にきれいな出力になります。`--json` がない場合は、
  通常どおり人間向けのテキスト／表が出力されます。
- **envelope の構造**:

  ```json
  { "ok": true, "command": "gp247:ext-install", "data": { }, "warnings": [], "error": null }
  ```

  エラー時: `"ok": false` と `"error": { "code": "...", "message": "..." }`。
- **終了コード**: 成功時は `0`、失敗時は `0` 以外です。これは**すべての**コマンドに共通です。

> ⚠️ **注意:** **互換性を壊す変更（core 2.1）:** `gp247:make-plugin` / `gp247:make-template` は、
> 以前のキー `{"error":0,"path":"...","msg":"Success"}` の代わりに、上記の envelope を出力するようになりました。
> zip のパスは `data.path` にあります。以前のキーを解析している外部ツールは、新しい envelope を
> 読むように変更する必要があります（機械向けの形式を得るには `--json` を使ってください）。

---

## CORE コマンド — `gp247/core` (2.0.4)

### 1. `gp247:core-install`

**機能:** GP247 プラットフォーム全体を初めてインストールします。このコマンドは、データベースの migrate、
既定データ（管理者、ストア、言語）の seed、core と laravel-filemanager のアセットの publish、
`storage` のシンボリックリンクの作成、インストール済みマーカー（`storage/app/gp247-installed.txt`）の書き込みを行います。

**オプション:**

| オプション | 値 | 意味 |
| --- | --- | --- |
| `--force` | `0`（既定）または `1` | `0`: 確認を求め、インストール済み（`gp247-installed.txt` ファイルがある）の場合は**ブロック**します。`1`: インストール済みのチェックと確認の質問を**両方ともスキップ**し、そのままインストールします（自動インストール／スクリプト／Docker 用）。 |

**使い方:**

```bash
php artisan gp247:core-install
```

確認なしでインストール（自動化）:

```bash
php artisan gp247:core-install --force=1
```

成功すると、ターミナルに GP247 のロゴと `Welcome to GP247` の行、管理画面のパス、
既定のアカウント `admin/admin` が表示されます。

**使用場面と組み合わせ:**
- ソースコードをダウンロードし、`.env` ファイルを作成してデータベース接続を設定した後の**最初の手順**です。
- `APP_KEY` がまだない場合は、コマンドが自動で `key:generate` を呼び出します。
- **再インストール**したい場合: `storage/app/gp247-installed.txt` ファイルを削除してから再実行します（または `--force=1` を使います）。
- このコマンドの後には、通常 `gp247:front-install` と／または `gp247:shop-install` を続けて実行します（末尾の
  「完全なインストールの手順」を参照）。

---

### 2. `gp247:core-update`

**機能:** `composer update` でパッケージのバージョンを上げた後に GP247 を更新します。このコマンドは
**順番に**次を実行します。(1) core の**アップグレード用マイグレーション** — インストール済みサイトの
データ構造とデータを新しいバージョンに合わせる変換処理です。(2) 既定データと言語データを**安全な**モードで
再 seed します（足りないレコードだけを追加し、自分で編集したデータは上書き**しません**）。(3) 静的ファイルを
更新し、現在の core のバージョンを表示します。

> ℹ️ **対応バージョン:** gp247/core 2.2 から。それ以前、このコマンドは再 seed だけを行っていたため、core の
> データ構造の変更をインストール済みのサイトに届ける方法がありませんでした。GP247 が **v2.1** で公開されて
> 以降、互換性を壊す変更にはすべて自動のマイグレーションが付いているため、`php artisan migrate` を
> 自分で実行する必要は**一切ありません**。

マイグレーションはパッケージの `upgrade/` フォルダからだけ実行され、インストール時のテーブル作成処理は
一切実行されません。データが削除されることはありません。コマンドを 2 回実行しても問題ありません。
実行済みのマイグレーションはスキップされます。

**オプション:** ありません。

**使い方:**

```bash
composer update
php artisan gp247:core-update
```

**使用場面と組み合わせ:**
- **`composer update` のたびに**実行し、データ構造、基本データ、アセットを新しいソースコードに合わせます。
  **先にデータベースをバックアップしてください** — 稼働中のサイトでアップデートするときの安全な習慣です。
- 日常的には **`gp247:update`**（コマンド 1）を使うことをおすすめします。このコマンドと、対応する
  front/shop のコマンドを正しい順番で呼び出します。`gp247:core-update` を単独で実行するのは、
  core の手順だけを意図的に行いたいときだけです。
- アップデートで言語文字列が追加され、パッケージの最新版に**上書き**したい場合は、
  さらに `gp247:language-update` を実行します（コマンド 5 を参照 — 安全モードと上書きモードの違い）。
- 技術的な注意: 内部で `gp247:customize static` を呼び出し、カスタマイズされた静的ファイルを更新します。
  このサブコマンドはインストール環境のカスタマイズ層が提供するものです。お使いの環境にない場合、
  この手順だけがエラーになってログに記録されますが、その前に実行されたデータの seed には影響しません。

---

### 3. `gp247:core-info`

**機能:** GP247 システムの情報を表示します: 名前、作者、**core のバージョン**とサブバージョン、
ホームページ、GitHub、Facebook、ライブラリ API のエンドポイント。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:core-info
```

**使用場面:**
- 不具合を報告する前や、アップデートの前後に、**どの core バージョンが動いているか**をすばやく確認できます。
- 正確なサブバージョンを得る最も速い方法です（`gp247:core-update` が最後に表示する行と同じです）。

---

### 4. `gp247:make-plugin`

**機能:** GP247 2.0 の標準に沿った**新しいプラグインのひな形**を生成します（フォルダ構成、
管理画面の Controller、Model、サンプルの Livewire、Seo/sitemap、vi/en の言語ファイル、Provider、Route、
`gp247.json`、`AppConfig` などが含まれます）。`gp247/front` パッケージがインストール済みの場合、生成される
プラグインには FrontController と front のルートも含まれます。インストールされていない場合、それらの front 部分は自動で省かれます。

**オプション:**

| オプション | 値 | 意味 |
| --- | --- | --- |
| `--name` | 文字列（**必須**） | プラグイン名。例: `MyBlog`。GP247 がクラス名と url key に自動で正規化します。空欄の場合は `Command error` エラーになります。 |
| `--download` | `0`（既定）または `1` | `0`: プラグインを `app/GP247/Plugins/<Name>` と `public/GP247/Plugins/<Name>` に直接コピーします。`1`: app にはコピー**せず**、`storage/tmp` 内の `.zip` ファイルにパッケージします（`--json` の場合、zip のパスは `data.path` にあります）。 |

**使い方:**

```bash
php artisan gp247:make-plugin --name=MyBlog --download=0
```

ダウンロード用の zip だけを作成（app にはインストールしない）:

```bash
php artisan gp247:make-plugin --name=MyBlog --download=1
```

既定では、コマンドは人間向けのテキストを出力します（zip を作成したときは `Success: <path>`）。標準の envelope を
得るには `--json` を追加します。例:
`{"ok":true,"command":"gp247:make-plugin","data":{"key":"MyBlog","path":"...","msg":"Success"},"warnings":[],"error":null}`
（zip のパスは `data.path`）。上記の「出力の取り決め」を参照してください。

**使用場面と組み合わせ:**
- 新しいプラグインを書き始めるとき — 生成されたひな形を使えば、ファイルを 1 つずつ手作りする必要がありません。
- `gp247:core-install` の**後に**実行します。プラグインにストアフロントの画面部分を持たせたい場合は、
  先に `gp247:front-install` をインストールしておくと、FrontController + front のルートも一緒に生成されます。
- 古い（1.x）プラグインを 2.0 にアップグレードする場合は、ドキュメント `extension/convert-plugin-v1-to-v2_ja.md` を参照してください。

---

### 5. `gp247:language-update`

**機能:** core（およびインストール済みなら front/shop）の言語行を **upsert** モードで更新します。
つまり、各文字列のテキスト／位置をパッケージの既定値で**上書き**します。これは `core-install`／`core-update`
との重要な違いです（この 2 つのコマンドは**足りないレコードを追加する**だけで、自分で編集した文字列には触れません）。

**オプション:** ありません。コマンドは core → front → shop の順に自動で実行します。インストールされていない
パッケージはスキップされ、`skipped (package not installed)` と表示されます。

**使い方:**

```bash
php artisan gp247:language-update
```

**使用場面と組み合わせ:**
- アップデートで**翻訳文字列が修正／追加**され、パッケージの標準版を取り込み直したいとき。
- ⚠️ **注意:** このコマンドは、管理画面で編集した言語文字列を**上書き**します。テキストを多く
  カスタマイズしている場合は、先にバックアップしてください。編集内容を残したまま足りない部分だけを追加したい場合は、
  このコマンドではなく `gp247:core-update` を使ってください。
- 通常は `composer update` + `gp247:core-update` の**後に**実行します。

---

## FRONT コマンド — `gp247/front` (2.0.1)

> このグループは、プロジェクトに `gp247/front` パッケージ（ストアフロントの画面モジュール）がインストールされている場合だけ利用できます。

### 6. `gp247:front-install`

**機能:** front モジュールをインストールします。このコマンドは、**まず古い front を自動でアンインストール**
（`gp247:front-uninstall` を呼び出し）し、front のデータテーブルを作り直し、front の既定設定を seed し、既定テンプレートの
アセット + view を publish してから、ルートストアのテンプレートを設定します（`gp247:template-setup` を呼び出し）。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:front-install
```

**使用場面と組み合わせ:**
- **`gp247:core-install` の後に**、ユーザー向けの画面（ストアフロント）を持つウェブサイトが必要なときに実行します。
- front モジュールを**クリーンに再インストール**する方法でもあります（必ず先に uninstall してから install するため）。
- テンプレートの publish の手順でエラーが出た場合（ネットワーク／書き込み権限）は、テンプレートのフォルダを
  手動でコピーしてから `gp247:template-setup` を再実行します。

---

### 7. `gp247:front-uninstall`

**機能:** front モジュールをアンインストールします。front のデータテーブルを削除し（front のマイグレーションの `down()` を実行）、
対応するマイグレーションのレコードを削除します。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:front-uninstall
```

**使用場面と組み合わせ:**
- ストアフロントの画面部分をシステムから**完全に取り除きたい**とき。
- 再インストール時に手動で呼び出す必要は通常ありません。`gp247:front-install` が最初にこのコマンドを自動で呼び出すからです。
- ⚠️ テーブルの削除は、front の設定データが**失われる**ことを意味します。先にバックアップを検討してください。

---

### 8. `gp247:make-template`

**機能:** GP247 2.0 の標準に沿った**新しい画面テンプレート（ストアフロント）のひな形**を生成します
（テンプレートのフォルダ、`AppConfig`、vi/en の言語ファイル、Provider、`gp247.json` など）。

**オプション:**

| オプション | 値 | 意味 |
| --- | --- | --- |
| `--name` | 文字列（**必須**） | テンプレート名。例: `MyShopTheme`。空欄の場合は `Command error` エラーになります。 |
| `--download` | `0`（既定）または `1` | `0`: テンプレートを `app/GP247/Templates/<Name>` と `public/GP247/Templates/<Name>` にコピーします。`1`: app にコピーする代わりに、`storage/tmp` 内の `.zip` にパッケージします。 |

**使い方:**

```bash
php artisan gp247:make-template --name=MyShopTheme --download=0
```

**使用場面と組み合わせ:**
- 既定の `GP247Front` を使わず、独自のストアフロント画面を作りたいとき。
- `gp247/front` をインストールした後に実行します。テンプレートを作成して既定に選んだら、
  `gp247:template-setup` でルートストアにテンプレートの設定を適用します。

---

### 9. `gp247:template-setup`

**機能:** **ルートストア（root）**の既定テンプレートを設定します。既定テンプレート（`GP247_TEMPLATE_FRONT_DEFAULT`）の
`AppConfig` クラスを読み込み、`install()` と `setupStore()` を呼び出して、ルートストアの表示設定を書き込みます。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:template-setup
```

**使用場面と組み合わせ:**
- `gp247:front-install` が最後に自動で呼び出します。手動で実行する必要があるのは、その前のテンプレートの
  publish の手順でエラーが出て、テンプレートを手動でコピーした場合だけです。
- ターミナルに `Class template Default not found` と表示された場合は、テンプレートの**拡張機能の外殻**
  （`AppConfig.php` と付属ファイル）が `app/GP247/Templates/<Name>` にまだありません。そこに必要なのは外殻だけで、
  Blade ファイルはパッケージから直接読み込まれます。
  `php artisan vendor:publish --tag=gp247:front-template --force` を実行して（または外殻を手動でコピーして）から再実行します。

### 10. `gp247:template-publish`

**機能:** テンプレートのファイルを**それを提供しているパッケージから** `app/GP247/Templates/<Name>/` に取り出し、編集できるようにします。

テンプレートの Blade は `vendor/gp247/front` と `vendor/gp247/shop` から直接読み込まれます。ファイルが
`app/` に置かれるのは、そのファイルを publish したときだけです。**ファイル単位**で publish すれば、1 つの画面を
カスタマイズしながら、**残りの部分は引き続き** `composer update` で**修正を受け取れます**。

**オプション:**

| 名前 | 値 | 意味 |
| --- | --- | --- |
| `<template>` | 例: `GP247Front` | テンプレート名（大文字と小文字を区別） |
| `--file=` | 相対パス | 1 つのファイルだけを publish。例: `--file=screen/home.blade.php` |
| `--all` | フラグ | このテンプレートについて各パッケージが提供するすべてのファイルを publish |
| `--force` | フラグ | `app/` の下にある既存のファイルを上書き（既定: 自分の版を**残す**） |
| `--json` | フラグ | 機械可読の envelope |

**使い方:**

```bash
php artisan gp247:template-publish GP247Front --file=screen/home.blade.php
php artisan gp247:template-publish GP247Front --file=blocks/shop_product_home.blade.php
php artisan gp247:template-publish GP247Front --all           # ツリー全体（vendor:publish --tag=gp247:front-view と同等）
```

**使用場面と組み合わせ:**
- テンプレートの残りの部分を固定（凍結）せずに、ストアフロントの 1 画面をカスタマイズしたいとき。
- パスが間違っている場合 → `error.code = "template_file_not_found"` のエラーになり、近いファイルの候補が示されます。
- `--force` がない場合、自分で編集したファイルは**そのまま残り**、スキップしたと報告されます。publish が
  あなたの作業を黙って壊すことは決してありません。

---

### 11. `gp247:template-prune`

**機能:** 公開済みのテンプレートファイルのうち、パッケージの版と**1 バイト単位で完全に一致する**ものを削除し、
パッケージに戻します。これにより、アップデートが再びそのファイルに届くようになります。

これは、テンプレートがパッケージ内に移る前にインストールされたサイトのためのアップグレード経路です。そうしたサイトの
`app/GP247/Templates/<Name>/` には完全なコピーがあり、パッケージの版を**永久に**覆い隠しています。

**コマンドが決して削除しないもの:** 自分で編集したファイル（名前や日時ではなく**内容のハッシュ**で比較）、
どのパッケージも提供していないファイル（プラグインが挿入したブロック、自作のファイル）、テンプレートの**拡張機能の外殻**
（`AppConfig.php`、`Provider.php`、`Route.php`、`config.php`、`function.php`、`gp247.json`、`Lang/`）。
`AppConfig.php` を削除すると、テンプレートがシステムから消えてしまいます。

**オプション:**

| 名前 | 値 | 意味 |
| --- | --- | --- |
| `<template>` | 例: `GP247Front` | テンプレート名 |
| `--dry-run` | フラグ | 削除するもの／残すものを一覧表示し、ディスクには**触れません** |
| `--json` | フラグ | 機械可読の envelope |

**使い方:**

```bash
php artisan gp247:template-prune GP247Front --dry-run   # 必ず先にプレビュー
php artisan gp247:template-prune GP247Front             # 確認を求める（既定: いいえ）
php artisan gp247:template-prune GP247Front --json      # 自動化: 確認なしで実行
```

**使用場面と組み合わせ:**
- アップグレード後に 1 回実行します。その後 `gp247:doctor`（`template_source` の項目）が
  `templates served from their package` と報告すれば完了です。
- `gp247:update` はこのコマンドを**提案する**だけで、代わりにファイルを削除することは決してありません。
- スクリプトでは `--json` を使ってください（これが同意を表す方法です）。`--no-interaction` は確認に既定値の
  **いいえ** で答えるため ⇒ 中止になります。
- どのパッケージも提供していないテンプレート（自作のテーマ）は `template_source_missing` で拒否されます。
  戻す先がないため、削除は許可されません。

---

## SHOP コマンド — `gp247/shop` (2.0.3)

> このグループは、プロジェクトに `gp247/shop` パッケージ（販売モジュール）がインストールされている場合だけ利用できます。`gp247/shop` には
> `gp247/core` と `gp247/front` が必要です。

### 12. `gp247:shop-install`

**機能:** 販売（EC）モジュールをインストールします。このコマンドは、**まず古い shop を自動でアンインストール**
（`gp247:shop-uninstall` を呼び出し）し、shop のデータテーブルを作り直し、初期データとルートストアの既定データを
seed してから、shop の front の view を publish します。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:shop-install
```

**使用場面と組み合わせ:**
- **`gp247:core-install` と `gp247:front-install` の後に**実行し、サイトをオンラインショップにします。
- shop モジュールを**クリーンに再インストール**するときにも使います（必ず先に uninstall します）。
- インストール後、試しに見るためのデモデータが欲しい場合は、`gp247:shop-sample` を実行します（下の警告を参照）。

---

### 13. `gp247:shop-update`

> ℹ️ **対応バージョン:** gp247/shop 2.1

**機能:** インストール済みのサイトの shop モジュールを、**データを壊さずに**アップグレードします。
`gp247:shop-install`（shop のテーブルをすべて削除して作り直す → **データが失われる**）とは異なり、このコマンドは
`vendor/gp247/shop/src/Admin/Database/Migrations/upgrade` フォルダ内の**段階的で冪等な（idempotent）
アップグレード用マイグレーション**だけを実行します（元のテーブル作成用マイグレーションには意図的に触れ**ません**）。
そのため、稼働中のサイトのカテゴリー、商品、注文などは**そのまま残ります**。migrate が終わると、
新しい／名前が変わった言語ラベル（例: 住所ラベルの city/district や address1/2/3）を更新するために
`gp247:language-update` を実行するよう案内します。

**オプション:** ありません。

**使い方:**

```bash
composer update
php artisan gp247:shop-update
```

**使用場面と組み合わせ:**
- **データがある稼働中のサイト**で `gp247/shop` のバージョンを上げたとき、**`composer update` の後に**実行します。
  これは `gp247:shop-install`（新規インストール／クリーンな再インストール専用で、データを削除します）の
  安全な代替コマンドです。
- このコマンドは**冪等**です。何度実行しても害はなく、適用済みのマイグレーションはスキップされます。
- アップグレードで**言語文字列が追加／名前変更**された場合は、案内どおり `gp247:language-update` を続けて実行します
  （CORE コマンド 5 を参照 — カスタマイズした文字列が上書きされる仕組みに注意）。
- migrate でエラーが出た場合、ターミナルに `Shop upgrade failed: ...` と表示され、エラーコードが返されます。エラーは
  `gp247_report` でログに記録されます。原因を解決してから再実行してください。

---

### 14. `gp247:shop-uninstall`

**機能:** shop モジュールをアンインストールします。shop のデータテーブルを削除し（shop のマイグレーションの `down()` を実行）、
対応するマイグレーションのレコードを削除します。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:shop-uninstall
```

**使用場面と組み合わせ:**
- 販売機能を**完全に取り除きたい**とき。
- 再インストール時に手動で呼び出す必要は通常ありません。`gp247:shop-install` が最初に自動で呼び出すからです。
- ⚠️ shop のテーブルを削除する = カテゴリー、商品、注文などが**すべて失われます**。実行前にバックアップしてください。

---

### 15. `gp247:shop-sample`

**機能:** ショップの**サンプルデータ**を作成します: 多階層のカテゴリー、ブランド、仕入先、
単品商品、セット商品（bundle）、グループ商品（group）、サンプルのプロモーション。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:shop-sample
```

**使用場面と組み合わせ:**
- **デモ／開発環境**で、画面を確認したり機能を試したりするための商品を用意するときに使います。
- ⚠️ **非常に重要:** このコマンドは、サンプルを作成する前に**既存の shop テーブルを完全に削除（truncate）**します
  （カテゴリー、商品、ブランド、仕入先、属性など）。**データのある本番サイトでは
  絶対に実行しないでください。**
- 通常は、開発マシンで `gp247:shop-install` の直後に実行します。

---

### 16. `gp247:shop-clear-cart`

**機能:** **期限切れ**のカートを削除します: カート（`default`）、お気に入り
（`wishlist`）、比較（`compare`）。期限は
`config('gp247-config.shop.cart_expire.*')` に設定した日数に基づきます。

**オプション:** ありません。

**使い方:**

```bash
php artisan gp247:shop-clear-cart
```

**使用場面と組み合わせ:**
- これは**定期メンテナンス**用のコマンドです。スケジューラー（scheduler/cron）に登録して毎日実行し、
  カートのテーブルが肥大化しないようにすることをおすすめします。
- 毎日午前 2 時に実行する cron の例:

  ```bash
  0 2 * * * cd /path-to-project && php artisan gp247:shop-clear-cart >> /dev/null 2>&1
  ```

- 期限の日数は、設定 `gp247-config.shop.cart_expire`（`cart`、`wishlist`、
  `compare`）で調整します。

---

## 拡張機能のライフサイクルコマンド — `gp247:ext-*` (core 2.1)

プラグイン／テンプレートのライフサイクル全体（以前は管理画面 UI だけで可能）を CLI でも扱えるようにしました。プラグインと
テンプレートは同じコマンド群を使い、`--type=plugin|template` で選びます（既定は `plugin`）。すべて
`--json` に対応しています。

| コマンド | 主なオプション | 機能 |
| --- | --- | --- |
| `gp247:ext-list` | `--type` | ローカルの拡張機能を installed/active/version と更新版の有無とともに一覧表示します（キャッシュのみ、API は呼び出しません）。 |
| `gp247:ext-install` | `--type`, `--file=<zip>`, `--dir=<folder>`, `--key=<key>`, `--paid`, `--license=` | `.zip` ファイル（`--file`）、展開済みのフォルダ（`--dir`）、またはキー（`--key`）でインストールします。キーの場合: 拡張機能が**インストール済み** → 拒否します。ファイルが**ディスク上にあるが未インストール**（例: `News` のような同梱プラグイン、または自分でアップロードしたフォルダ） → その場でインストールします（管理画面の「インストール」(Install) ボタンと同じ）。その際、**拡張機能の `public/` フォルダを `public/GP247/...` にコピーします**。それ以外 → マーケットプレイスからダウンロードします（有料アイテムには `--paid --license=...` を追加）。 |
| `gp247:ext-enable` | `--type`, `--key` | **インストール済み**の拡張機能を有効にします（未インストールの場合は明確なエラーで拒否）。 |
| `gp247:ext-disable` | `--type`, `--key` | インストール済みの拡張機能を無効にします（未インストールの場合、または使用中のテンプレートの場合は拒否）。 |
| `gp247:ext-uninstall` | `--type`, `--key`, `--only-data`, `--purge` | アンインストールします（`extension_protected` と、使用中／既定のテンプレートのガードを尊重）。**インストール済み**: DB の設定を削除**し**、ファイルも削除します。`--only-data` ならファイルは残します。**未インストールだがディスク上にある**: `--purge` がない限り拒否します（その場合はファイルだけを削除）。`--only-data` と `--purge` は同時に使えません。 |
| `gp247:ext-update` | `--type`, `--key`, `--all`, `--local`, `--dry-run` | 1 つの拡張機能、または更新版のあるすべての拡張機能を更新します（バックアップ + ロールバック）。**`--local`**（2026-09-29 の更新から）: **何もダウンロードせず**、`git pull`／Composer／手動コピーでファイルが置き換えられたインストール済みの拡張機能に対して、データ更新（`AppConfig::update()`）だけを実行します。つまり、`gp247.json` がインストール時に GP247 が記録したバージョンより新しい場合です。完了すると新しいバージョンを記録します。`--dry-run` は一覧表示だけを行います。再実行しても安全です。**いずれか**の拡張機能でエラーが出ると 0 以外で終了します（新しいファイルはそのまま残り、再実行できるよう古いバージョンの記録も残ります）。 |
| `gp247:ext-check-update` | `--type`, `--force` | 更新版を報告します（`--force` がない限りキャッシュを使用）。 |
| `gp247:ext-search` | `--type`, `--keyword=`, `--free`, `--page=` | マーケットプレイスのカタログを閲覧／検索します。 |
| `gp247:ext-license` | `--type`, `--key`, `--license=`, `--delete` | 有料拡張機能のプラグインごとのライセンスを設定／表示／削除します（`admin_config` に保存し、`.env` には触れません）。 |
| `gp247:ext-register-license` | （なし） | ドメイン `APP_URL` の **API License**（無料）を GP247 ライブラリに登録し、`.env` の `GP247_API_LICENSE` に書き込みます。管理画面の「Click here」ボタンと同じです。`ext-install`／`ext-update`／`ext-search` がライブラリを呼び出す前に必要です。`.env` に書き込めない場合 → 0 以外で終了し（`env_write_failed`）、手動で貼り付けられるようキーを表示します。 |
| `gp247:ext-publish` | `--type`, `--key`, `--all` | **インストール済み**の拡張機能の `public/` フォルダ（CSS/JS/画像）を `public/GP247/<Plugins\|Templates>/<Key>/` に再コピーし、古い版を上書きします。`--all` = `public/` フォルダを持つすべてのインストール済み拡張機能。`gp247:doctor` が `extension_assets` の項目を報告したときに使います。未インストールの拡張機能 → エラー。 |

例:

```bash
php artisan gp247:ext-register-license
php artisan gp247:ext-list --type=plugin --json
php artisan gp247:ext-install --type=plugin --file=storage/tmp/MyBlog.zip
php artisan gp247:ext-install --type=plugin --key=News
php artisan gp247:ext-enable --type=plugin --key=News
php artisan gp247:ext-update --type=plugin --all
php artisan gp247:ext-update --local --all --type=plugin --dry-run
php artisan gp247:ext-update --local --type=plugin --key=InOut --json
php artisan gp247:ext-uninstall --type=plugin --key=News
php artisan gp247:ext-publish --type=template --key=MKP
php artisan gp247:ext-publish --type=plugin --all
```

> **複数アイテム（バッチ）。** `ext-install`、`ext-enable`、`ext-disable`、`ext-uninstall` は
> **複数のキー**を受け付けます。オプションを繰り返す（`--key=A --key=B`）か、カンマで区切ります（`--key=A,B`）。
> `ext-install` は複数の `--file`／`--dir` も受け付けます。アイテムは**1 つずつ、独立して**処理されます
> （複数の拡張機能にまたがるアトミックなトランザクションはありません。それぞれがファイル + マイグレーション + 設定の
> 独立した単位です）。結果は**アイテムごとに**報告され、route/config のキャッシュは最後に**1 回だけ**再構築されます。
> **いずれか**のアイテムが失敗すると、コマンドは 0 以外で終了します。**有料の拡張機能は
> 1 つずつインストールする必要があります** — `--paid` に複数の `--key` を付けると、**最初の段階で拒否されます**
> （`error.code: paid_multi_not_allowed`）。1 つの `--license` が他のプラグインに誤って適用されてしまうためです。
> 有料のインストールでは、毎回 `--key` を 1 つだけ使います。

> **再実行／インストール済みの場合。** 「インストール済み」= `admin_config` の行があること（ディスク上にファイルが
> あるだけではありません）。**インストール済み**の拡張機能に `ext-install` を実行すると、「既に存在します」というエラーで拒否されます
> （リモートの場合はダウンロードの**前に**確認するため、帯域を消費しません）。また、重複した `admin_config`
> レコードは作成**されません**。インストール済みの拡張機能を更新するには `gp247:ext-update` を使い、再インストールするには
> 先に `gp247:ext-uninstall` を実行します。
> バッチでは、インストール済みのキーは `failed` に記録されるだけで、他のアイテムは引き続き実行されます。反対に、
> `ext-enable`／`ext-disable`／`ext-uninstall` は**未インストール**の拡張機能をエラーとして扱います（enable/disable は
> 拒否、uninstall は `--purge` がない限り拒否）。そのため、ディスク上の同梱プラグインが何もしないまま「有効化」されたり、
> 予期せず削除されたりすることはありません。

> **拡張機能の静的ファイル（CSS/JS/画像）。** ブラウザが読めるのは `public/` フォルダだけです。そのため、インストール時に
> `app/GP247/<Plugins|Templates>/<Key>/public/` フォルダが `public/GP247/<Plugins|Templates>/<Key>/` にコピーされます。どの
> インストール方法でもこれが行われます: `.zip` ファイル、オンラインライブラリ、ディスク上に既にあるフォルダ（FTP でアップロード、`git clone`、
> Docker イメージにコピー）を置いてから「インストール」(Install) を押す、または `ext-install --key` を実行する方法です。2026-09-27 の更新より前は、最後の方法では
> `public/` がコピー**されず**、拡張機能はインストール成功と報告されるのに画面が崩れていました（CSS/JS/画像が 404 エラー）。その
> 方法でインストールしたサイトは、`gp247:doctor` を実行して不足している拡張機能を確認し、`gp247:ext-publish --type=... --key=...`（または `--all`）で修正してください。

> ℹ️ **対応バージョン:** gp247/core 2.1.1（`gp247:ext-register-license`）、2026-09-27 の更新（`gp247:ext-publish`）

> **ライブラリ（マーケットプレイス）からのインストール — `--key` でダウンロード。** `ext-install --key` が GP247 ライブラリから
> ダウンロードする必要がある場合（拡張機能がまだディスク上にない場合）、ウェブサイトには **API License**（無料）が必要です。登録は
> `gp247:ext-register-license` で**1 回だけ**行います。
>
> ```bash
> php artisan gp247:ext-register-license
> php artisan gp247:ext-install --type=plugin --key=News
> php artisan gp247:ext-install --type=plugin --key=ProPlugin --paid --license=<extension-license>
> ```
>
> - 登録の前に、`.env` の `APP_URL` を**実際のドメイン**に設定してください。ライセンスは `url('/')` に紐づきます。
>   `http://localhost` のままだと、その後のライブラリ呼び出しはすべて拒否されます（`domain_not_authorized`）。
> - コマンドはキーを `.env` の `GP247_API_LICENSE` に書き込みます。`.env` に書き込めない場合、コマンドは 0 以外で終了し
>   （`error.code: env_write_failed`）、手動で貼り付けられるようキーを表示します。秘密にして、コミットしないでください。
> - ライセンスには 2 種類あります: **API License**（`.env` の `GP247_API_LICENSE`、無料、ライブラリへの
>   接続用）と、**有料拡張機能のライセンス**（`--license=`、`admin_config` に保存）です。
> - ダウンロード時のライセンス／ドメインのエラー（`api_license_required`、`domain_not_authorized`）は、
>   `gp247:ext-register-license` を再実行するよう提案とともに報告されます。キーがライブラリにあっても有料版の場合 →
>   `--paid --license=` が不足しています。キーがライブラリにない場合 → 「not found in the marketplace」となります。
> - `ext-install` は `requireComposerPackages`（`composer require` は自動で行いません）と
>   `requireGp247Extensions`（依存関係は自動でインストールしません）を**確認する**だけです。これらは先にインストールしてください。Pro 版は Free 版の後に、
>   別のコマンドでインストールします（`--paid` は 1 つの `--key` としか使えないため）。
> - インストールが完了したプラグインは**有効な状態**になり、route/config のキャッシュも自動で更新されます。テンプレートは引き続き、管理画面
>   → **「サイト情報」**で有効化する必要があります（この手順の CLI コマンドはありません。`gp247:template-setup` はルートストアに
>   既定テンプレートを適用するだけです）。

> CLI と管理画面 UI は、内部で**同じ**エンジン（`ExtensionInstaller` /
> `LibraryClient`）を使うようになったため、どちらの方法でも動作はまったく同じです。保護された拡張機能と、
> 使用中／既定のテンプレートは、どちらでもアンインストールが拒否されます。

---

## オーケストレーションと診断（core 2.1）

| コマンド | 主なオプション | 機能 |
| --- | --- | --- |
| `gp247:install` | `--sample`, `--force=1` | システム全体の共通インストールコマンドです。存在するパッケージを**自動で検出**し、次の順にインストールします: `core-install` → （`front-install`） → （`shop-install`） → （`--sample` がある場合は `shop-sample`）。1 つの手順でエラーが出ると、0 以外の終了コードで停止します。**既定では確認が必須**です（下の安全上の注意を参照）。非対話でインストールするには `--force=1` を使います。`composer require` の直後から利用できます（プラットフォームがまだインストールされていない場合も含む）。パッケージの選択は完全に自動です — `--with-front` / `--with-shop` フラグは**ありません**。 |
| `gp247:update` | `--overwrite-lang`, `--publish=<tokens>` | 稼働中のサイト向けの、`composer update` 後の安全な更新です: `core-update`、次に `shop-update`（shop がインストール済みの場合のみ）、オプションで `language-update`（`--overwrite-lang`）、**拡張機能のデータ更新を適用する**手順（プラグイン、次にテンプレートに対して `ext-update --local --all`。エラーは警告だけで、後続の手順は止めません。2026-09-29 の更新から）、**任意の** アセット／view の再 publish 手順（`--publish=`、既定はオフ）、最後に `cache-rebuild`。データを壊す（再）インストールの手順は一切実行しません。各 `--publish` トークンの影響の大きさは、下の再 publish に関する注意を参照してください。 |
| `gp247:cache-rebuild` | — | route/config のキャッシュを再構築します（拡張機能の有効化／更新の後）。 |
| `gp247:doctor` | `--json` | 環境を確認します: PHP ≥ 8.3、必須拡張モジュール、書き込み権限、DB 接続、インストールのマーカー、およびソースコードの衛生チェックの一部（下記参照）。**fail** の項目があると 0 以外で終了するため、CI／インストール前のゲートとして使えます。**warn** の項目では 0 以外で終了しません。 |
| `gp247:info` | `--json` | 状態を表示します: インストール済みパッケージ（core/front/shop）のバージョン、インストールのマーカー、プラグイン／テンプレートの数、マーケットプレイス API のエンドポイント。読み取り専用です。 |

### `gp247:doctor` が確認する項目

結果の各行は、次の 3 つの状態のいずれかになります: **PASS**（問題なし）、**WARN**（整理すべきだがブロックしない）、**FAIL**
（修正が必要 — コマンドは 0 以外で終了し、CI やインストールをブロックできます）。

| 項目 | 意味 |
| --- | --- |
| `php_version`, `ext_*` | PHP のバージョンと必須／推奨の拡張モジュール |
| `env_file`, `writable_*` | `.env` の有無。`storage`、`bootstrap/cache`、`app/GP247`、`public/GP247` の書き込み権限 |
| `db_connection`, `installed` | データベースに接続できるか。サイトがインストール済みかどうか |
| `encryption_key_dedicated` | シークレット専用の `GP247_ENCRYPTION_KEY` を設定済みか、`APP_KEY` を共用しているか |
| `secret_decryptable` | 設定内の暗号化済みシークレットがまだ復号できるか（`APP_KEY` が変更された兆候） |
| `template_source` | 公開済みでパッケージの版と完全に同じ画面ファイルがいくつあるか — つまり、アップデートを決して受け取れないファイル |
| `file_bom` | **UTF-8 の BOM** で始まる `.php` / `.blade.php` ファイル |
| `extension_assets` | `public/GP247/...` に静的ファイルが不足している**インストール済み**のプラグイン／テンプレート（CSS/JS/画像が 404 になる）。**WARN**。修正用の `gp247:ext-publish` コマンドを併記 |
| `extension_data_pending` | GP247 が記録したバージョンより新しいファイルを持つ（`git pull`／上書きコピーした直後の）、データ更新がまだ実行されていない**インストール済み**のプラグイン／テンプレート。**WARN**。一覧と `gp247:ext-update --local --all` コマンドを併記 |

> ℹ️ **対応バージョン:** 2026-09-23 の更新（`file_bom` 項目）、2026-09-27 の更新（`extension_assets` 項目）、2026-09-29 の更新（`extension_data_pending` 項目）

**`file_bom` を確認する理由。** BOM（Byte Order Mark）は、Windows の多くのエディターが「UTF-8」で保存するときに
ファイルの先頭に自動で付ける**目に見えない 3 バイト**（`EF BB BF`）です。この 3 バイトは `<?php ?>` タグの**外側**に
あるため、PHP はそれを**他の何よりも先に**出力します。その結果:

- `header()` / `redirect()` を使うファイルで **「headers already sent」** エラーが発生します。
- 返される JSON / XML / CSV が、最初の 1 文字目から壊れます。
- view が、目に見えない文字で始まる HTML を出力します。

ファイルを開いても**まったく普通に見え**、diff にも何も表示されません。機械でしか見つけられない種類の不具合なので、
`doctor` が代わりに確認します。この項目は FAIL ではなく **WARN** です。整理は必要ですが、インストールや
CI をブロックするほどではありません（特に、すぐには修正できないサードパーティのプラグインに BOM がある場合）。

コマンドはこのサイトが実際に実行するコード（`app/GP247/**` と `vendor/gp247/{core,front,shop}/src/**`）をスキャンし、
各ファイルの先頭 3 バイトだけを読むので、とても高速です（約 1,200 ファイルで約 1.2 秒。Laravel の起動時間を含む）。

BOM の付いたファイルがある場合の結果:

```
| file_bom | WARN | 1 file(s) start with a UTF-8 BOM, which is echoed before any output:
                    app/GP247/Plugins/MyPlugin/Views/index.blade.php — re-save each as UTF-8 WITHOUT BOM |
```

**修正方法:** エディターでファイルを開き、**UTF-8 without BOM** で保存し直します（VS Code: 右下のステータスバーの
`UTF-8 with BOM` をクリック → *Save with Encoding* → *UTF-8*）。`gp247:doctor` を再実行し、
この項目が **PASS** に戻ったことを確認します。

例:

```bash
php artisan gp247:install            # 対話型: 計画 + データ消失の警告を表示してから確認を求める
php artisan gp247:install --force=1  # 非対話型（CI/Docker）: 確認をスキップ — 安全上の注意を参照
php artisan gp247:install --sample   # shop のデモデータも seed（既定では対話型のまま）
php artisan gp247:update                       # 既定: 更新だけを行い、何も publish しない（稼働中のサイトでも安全）
php artisan gp247:update --publish=core-public  # ビルド済みの管理画面アセットも再 publish（安全）
php artisan gp247:update --publish=front-view   # GP247Front の Blade ツリー全体も app/ にコピー（データを破壊 — 注意を参照）
php artisan gp247:update --publish=all          # すべての対象を再 publish（データを破壊 — 先にバックアップ）
php artisan gp247:doctor --json
php artisan gp247:info --json
```

> **再 publish（`--publish=<tokens>`）— 任意。影響の大きさで段階分けされています。** `composer update`
> は vendor 内のコードを更新しますが、`public/GP247`、`app/GP247`、`resources/views/vendor/*` の公開済みの版は
> 更新**しません**。`--publish=` は、指定に応じてそれらを再 publish します。**既定（`--publish` なし）では
> 何も publish しません** — 以前の動作のままで、稼働中のサイトでも安全です。各トークンは publish タグの名前（パッケージ名を
> 明記）で、カンマで区切ります（例: `--publish=core-public,front-view`）。`all` はすべてのトークンを表します。
>
> | トークン | publish 先 | 影響の大きさ |
> | --- | --- | --- |
> | `core-public` | `public/GP247`（ビルド済みの管理画面 CSS/JS） | **安全** — 再生成される成果物で、手で編集しない |
> | `core-view` | `resources/views/vendor/gp247-admin` | **データを破壊** — 管理画面 view のオーバーライドを上書き |
> | `front-public` | `public/GP247/Templates/GP247Front` | **安全** — パッケージ内のビルドから再生成できるコピー。通常は入力不要: パッケージが新しいビルドを含むと、ストアフロントのアセットは**自動で更新**される |
> | `front-template` | `app/GP247/Templates/GP247Front` | **データを破壊** — テンプレートの**拡張機能の外殻**（`AppConfig`、`Provider`、`Route`、`config.php`、`function.php`、`gp247.json`、`Lang/`）を上書き |
> | `front-view` | `app/GP247/Templates/GP247Front` | **データを破壊** — GP247Front の Blade ツリー**全体**を app にコピー。以後、これらのファイルはパッケージの版を**永久に**覆い隠し、`composer update` では二度と修正できなくなる。`gp247:template-publish --file=` の使用を推奨 |
> | `shop-view-admin` | `resources/views/vendor/gp247-shop-admin` | **データを破壊** — shop の管理画面 view のオーバーライドを上書き |
> | `shop-view-front` | `app/GP247/Templates/GP247Front` | **データを破壊** — `front-view` と同様。テンプレートの shop 部分の view が対象 |
>
> **`all` は、サイトが使っていないテンプレートを作り直すことはありません。** サイトの既定テンプレートが
> `GP247Front` でない場合（どのストアも選んでおらず、`app/GP247/Templates/GP247Front` が削除済み）、`all` は
> `front-public`、`front-template`、`front-view`、`shop-view-front` を通知付きで**自動的にスキップ**します。
> 独自テンプレートを使うサイトで、アップデート後に GP247Front が復活することはありません。これらのトークンを
> **名指しで**入力した場合は publish されます。それはあなたの判断だからです。
>
> **データを破壊するトークンを publish する前に、対象フォルダをバックアップしてください** — 上書きは元に戻せません。
> `gp247:update` には `--force` フラグが**ありません**。データを破壊するトークンの名前を自分で入力したこと自体が同意です。
> 非対話モード／`--json` では、データを破壊するトークンはそのまま publish されます（影響についての警告を stderr に出力）。
> **対話型のターミナル**では、コマンドが警告し、バックアップを促してから確認を求めます（既定は**いいえ**）。拒否した場合は
> データを破壊する publish だけがスキップされ、update とキャッシュの手順は実行されます。1 つの対象でファイル書き込みエラーが
> 起きても（例: 読み取り専用の共有ホスト）、警告してスキップするだけで、コマンド全体は失敗しません。（内部の
> `vendor:publish --tag=… --force` の `--force` フラグは、そのコマンド自身の上書きフラグであり、`gp247:update` のオプションではありません。）

> **安全 — 既定では確認が必須です。** `front-install` / `shop-install` は、それぞれのテーブルを**削除して作り直します**
> （先に対応する `*-uninstall` を呼び出す）。そのため、稼働中のサイトでインストーラーを再実行すると、front/shop の
> データが**破壊されます**。このため、`--force` が**ない**場合の `gp247:install` は:
> - 非対話のコンテキストや `--json` では実行を**拒否します**（`error.code = "confirmation_required"` を返し、
>   0 以外の終了コードで、どの手順も**実行しません**）。これにより、自動モードで誤ってデータを失うことはありえません。
> - **対話型のターミナル**では、検出した計画とデータ消失の警告を表示してから、
>   `Proceed with installation?` と確認を求めます（既定は**いいえ**）。拒否すると、何も実行せずに 0 で終了します。
>
> `--force=1` は、非対話でのインストール／再インストールを本当に行いたいときだけ使ってください。`sc:install` は独自に確認を取ってから、
> `gp247:install --force=1` に処理を委ねます。

---

## よく一緒に使う `vendor:publish` コマンド

これらは Laravel の標準コマンドです（GP247 独自のコマンドではありません）が、インストール時や
**画面デザインをカスタマイズ**したいときによく一緒に使います。`--tag` フラグで、公開するファイルのグループを選びます。

| コマンド | 公開するもの |
| --- | --- |
| `php artisan vendor:publish --tag=gp247:core-public --force` | core の管理画面アセット（CSS/JS）を `public/GP247` へ |
| `php artisan vendor:publish --tag=gp247:front-public --force` | ビルド済みのストアフロントのアセット → `public/GP247/Templates/GP247Front`（通常は自動 — 新しいビルドがあるとパッケージが自動で更新） |
| `php artisan vendor:publish --tag=gp247:front-template --force` | 既定テンプレートの**拡張機能の外殻** → `app/GP247/Templates/GP247Front`（インストール時に実行される手順） |
| `php artisan vendor:publish --tag=gp247:front-view --force` | 既定テンプレートの **Blade ツリー全体** → `app/...`（全体をオーバーライドしたい場合のみ。`gp247:template-publish --file=` を参照） |
| `php artisan vendor:publish --tag=gp247:shop-view-admin` | shop の管理画面 view（オーバーライド用） → `resources/views/vendor/gp247-shop-admin` |
| `php artisan vendor:publish --tag=gp247:shop-view-front` | shop の front の view ツリー全体 → `app/GP247/Templates/GP247Front`（全体をオーバーライドしたい場合のみ） |

> `composer update` で**パッケージを更新**した後、管理画面の見た目が変わらない場合は、
> `vendor:publish --tag=gp247:core-public --force` を再実行して、公開済みのアセットを更新してください。

---

## 完全なインストールの手順（推奨の順番）

完全な GP247 EC サイトを作るには、次の手順を順番に実行します。

1. `.env`（データベース接続）を準備し、Composer でソースコードをインストールします。
2. core プラットフォームをインストールします。

   ```bash
   php artisan gp247:core-install
   ```

3. ストアフロントの画面モジュールをインストールします。

   ```bash
   php artisan gp247:front-install
   ```

4. 販売モジュールをインストールします。

   ```bash
   php artisan gp247:shop-install
   ```

5. （開発／デモマシンのみ）サンプルデータを作成します。

   ```bash
   php artisan gp247:shop-sample
   ```

運用を始めた後、アップグレードのたびに:

```bash
composer update
php artisan gp247:core-update
php artisan gp247:shop-update   # shop モジュールを使っている場合（安全なアップグレードで、データは失われない）
```

また、期限切れのカートを削除する cron を設定します: `php artisan gp247:shop-clear-cart`（毎日実行）。

---

## よくある質問（Q&A）

**Q1: 自分のマシンにある GP247 コマンドをすばやく全部確認するには？**

→ `php artisan list gp247` と入力します。ターミナルに、`gp247:` で始まるすべてのコマンドが短い説明付きで一覧表示されます。

**Q2: `gp247:core-install` を実行すると「GP247 has been installed」と表示されました。どうすればよいですか？**

→ すでにインストール済みという意味です（`storage/app/gp247-installed.txt` ファイルがあります）。再インストールしたい場合は、そのファイルを削除して
再実行するか、`php artisan gp247:core-install --force=1` を使います。

**Q3: `gp247:core-update` と `gp247:language-update` の違いは何ですか？**

→ `core-update` は**安全に** seed します（足りないレコードだけを追加し、自分で編集した内容はそのまま残します）。
`language-update` は言語文字列をパッケージの既定版で**上書き**します。カスタマイズしたテキストを残したい場合は
`core-update` を、最新の標準版を取り込み直したい場合は `language-update` を使います。

**Q4: `gp247:shop-sample` は本番サイトで実行しても安全ですか？**

→ **いいえ。** このコマンドは、サンプルを作成する前に既存の shop データを**完全に削除**します。デモ／開発環境でだけ
実行してください。

**Q5: `gp247:shop-install` でテーブルやクラスが不足しているというエラーが出たら？**

→ `gp247/shop` には、先に `gp247/core` と `gp247/front` がインストールされている必要があります。正しい順番で実行してください:
`core-install` → `front-install` → `shop-install`。

**Q6: 販売機能は使わず、紹介用のウェブサイトだけが必要な場合は、何をインストールしますか？**

→ `gp247:core-install` と `gp247:front-install` だけで十分です。shop のコマンドグループは飛ばしてください。

**Q7: `make-plugin`/`make-template` の `--download=1` と `--download=0` の違いは何ですか？**

→ `--download=0` は、プラグイン／テンプレートを `app/GP247/...` フォルダに直接コピーし、すぐに使えるようにします。
`--download=1` は app にはコピー**せず**、`storage/tmp` 内の `.zip` ファイルにパッケージします。ダウンロードしたり、
別の場所に持ち出したりするためのものです。

**Q8: `composer update` の後、管理画面の見た目が変わりません。**

→ `php artisan gp247:core-update` を実行します。それでも変わらない場合は、ビルド済みの管理画面アセットを publish し直します。
`php artisan vendor:publish --tag=gp247:core-public --force` を実行するか、更新と同時に
`php artisan gp247:update --publish=core-public` で行います（安全で、管理画面の CSS/JS だけを publish します）。データを破壊する
view／テンプレートのトークンは、カスタマイズした view を本当にリセットする必要があるときだけ使ってください（先にバックアップ）。
*オーケストレーションと診断* の項にある再 publish の注意を参照してください。

**Q9: `core-update` が呼び出す `gp247:customize static` とは何ですか？インストールが必要ですか？**

→ カスタマイズされた静的ファイルを更新する内部コマンドで、インストール環境のカスタマイズ層が提供しています。直接
呼び出すことはありません。お使いの環境にない場合、その手順だけがエラーを報告してログに記録されますが、
データの更新部分は壊れません。

**Q10: 動いている core/front/shop の正確なバージョンを知るには？**

→ `php artisan gp247:core-info` を実行するか（core のバージョンを表示）、`composer.lock` ファイルを開いて
`gp247/core`、`gp247/front`、`gp247/shop` を探します。

---

## 変更履歴
<!-- ロジック／動作に変更があった場合のみ記録します。最新の行を一番上にします。 -->

| 日付 | GP247 バージョン | 変更内容 |
| --- | --- | --- |
| 2026-09-29 |  | `gp247:ext-update` に `--local` / `--dry-run` を追加: `git pull`／Composer／手動コピーでファイルが置き換えられた拡張機能のデータ更新を実行します（GP247 がインストール済みのバージョンを記録）。`gp247:update` がプラグインとテンプレートに対してこの手順を実行します。`gp247:doctor` に `extension_data_pending` 項目を追加。 |
| 2026-09-23 |  | `gp247:doctor` に `file_bom` 項目を追加: UTF-8 の BOM で始まる `.php`/`.blade.php` ファイルを報告します（WARN レベルで、コマンドは 0 以外で終了しません） |
| 2026-08-29 | gp247/core 2.2 | `gp247:core-update` が、再 seed の**前に** **core のアップグレード用マイグレーション**（`Migrations/upgrade/`）を実行するようになりました — 以前は seed だけだったため、core のデータ構造の変更がインストール済みのサイトに届きませんでした。これは次のルールの一部です: 公開版 **v2.1** 以降、互換性を壊す変更にはすべて自動のマイグレーションが付き、`gp247:update` を通じて適用されます。 |
| 2026-08-24 | gp247/core 2.1 | • `gp247:update` に、`composer update` 後にアセット／view を再 publish する**任意の**オプション `--publish=<tokens>` を追加（既定では何も publish しない）。トークンは publish タグの名前を明記し（`core-public`/`core-view`/`front-public`/`front-view`/`shop-view-admin`/`shop-view-front`/`all`）、**影響の大きさで段階分け**: 安全なのは `core-public` だけで、view／テンプレートのトークンはカスタマイズを上書きします。**`--force` フラグはありません** — データを破壊するトークンを自分で入力すること自体が同意です。対話型で実行した場合は、警告 + 確認があります（既定はいいえ）。<br>• `gp247:install` と `gp247:doctor` を **bootstrap tier** に登録（`composer require` の直後、プラットフォームのインストール前から利用可能 — 「未インストール時には `gp247:core-install` しか存在しない」問題を修正）。`gp247:install` は存在するパッケージを**自動で検出**します。`--with-front`/`--with-shop` フラグは**完全に廃止**しました（安定版で公開されたことはありません）。**安全:** `gp247:install` は**既定で確認が必須**になりました — `--force=1` がない場合、非対話／`--json` での実行を拒否し、対話型では確認を求めます（既定はいいえ）。`sc:install` は `gp247:install` に処理を委ねます。<br>• `ext-install --key` は、同梱／ディスク上にあるプラグインをその場でインストールします（インストール済みなら拒否）。`ext-enable`/`ext-disable` は未インストールの拡張機能を拒否します。`ext-uninstall` は、ディスク上にある未インストールの拡張機能を `--purge` がない限り拒否します（`--only-data`/`--purge` は排他）。 |
| 2026-08-23 | gp247/core 2.1 | CLI の出力の取り決めを標準化（すべてのコマンドに `--json` + 終了コード）。拡張機能のライフサイクルコマンド群 `gp247:ext-*` と、`gp247:install` / `gp247:update` / `gp247:cache-rebuild` / `gp247:doctor` / `gp247:info` を追加。**互換性を壊す変更:** `make-plugin`/`make-template` が JSON の envelope を出力するようになりました（パスは `data.path`）。 |
| 2026-08-22 | gp247/shop 2.1 | `gp247:shop-update` コマンドを追加 — 稼働中のサイト向けに、データを壊さず shop をアップグレード |

---

<sub>📅 **最終更新:** 2026-10-22 · ✍️ **作成者 (Author):** GP247</sub>
