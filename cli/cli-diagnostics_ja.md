> 🌐 **言語:** [🇻🇳 Tiếng Việt](./cli-diagnostics_vi.md) · [🇬🇧 English](./cli-diagnostics.md) · 🇯🇵 日本語（現在）

# 点検と診断のコマンド

## はじめに
このページでは、サイトがどんな状態かを教えてくれるコマンドを説明します。`gp247:doctor` はサーバー、インストール状態、
セキュリティ設定を点検し、`gp247:info` と `gp247:core-info` はバージョンを表示し、`gp247:cache-rebuild` はキャッシュを
再構築します。いずれも（キャッシュの再構築を除き）**読み取りのみ**なので、販売中のサイトでもいつでも実行できます。対象は、
サイトオーナーとサーバーを運用する方です。読み終えると、`gp247:doctor` が報告する各行の意味と直し方が分かります。

## ひと目でわかる
`gp247:doctor` は次の 3 つのタイミングで使います。

```mermaid
flowchart LR
    A["インストール前<br/>サーバーの<br/>準備はOK？"] --> B["インストール<br/>・更新後<br/>片付けは？"]
    B --> C["定期的に<br/>設定は<br/>安全？"]
```

- **3 つの状態**：**PASS** 問題なし · **WARN** 片付けたほうがよいが止めない · **FAIL** 直す必要あり。
- **FAIL が 1 つでもあると、コマンドは 0 以外で終了します** — CI やインストール前の関門として使えます。WARN は終了コードを変えません。
- **インストール済みのサイトでは、セキュリティ設定も点検します** — 「**システムアラート**」画面が管理者に表示する項目と同じです。
- **不具合の報告や修正の確認にバージョンが必要なとき** → `gp247:info`。

## `gp247:doctor` — サイトの健全性チェック
1. サイトのフォルダーで、次のように入力します。

   ```bash
   php artisan gp247:doctor
   ```

2. 結果の表を読みます。各行にはチェック名、状態、メモ（パス、件数、修正用のコマンド）があります。
3. まず **FAIL** の行をすべて直し、次に **WARN** の行を直します。FAIL がなくなるまで再実行します。

doctor は設定とデータベースを読むだけで、ネットワーク経由でサイトを開くことは**ありません**。`http://` が `https://` に
転送されるか、`.env` が外部からダウンロードできないかは、自分で確認する必要があります — [セキュリティ](../security/security-introduction_ja.md)
の [安全な本番環境の設定](../security/security-production-config_ja.md) を参照してください。

### サーバーとインストール
| チェック | 確認内容 | 不合格のとき |
| --- | --- | --- |
| `php_version` | PHP 8.3 以上 | FAIL — PHP を上げる |
| `ext_pdo`、`ext_mbstring`、`ext_openssl`、`ext_tokenizer`、`ext_ctype`、`ext_json` | 必須の PHP 拡張 | FAIL — 拡張を有効にする |
| `ext_zip`、`ext_curl`、`ext_gd`、`ext_fileinfo` | 推奨の PHP 拡張 | WARN — 一部の機能（`.zip` のインストール、画像）が動かない |
| `env_file` | `.env` ファイルがある | FAIL |
| `writable_storage`、`writable_bootstrap_cache` | `storage/`、`bootstrap/cache/` に書き込める | FAIL — フォルダーの権限を直す |
| `writable_app_gp247`、`writable_public_gp247` | `app/GP247`、`public/GP247` に書き込める | WARN — 拡張機能をインストール／更新できない |
| `db_connection` | データベースに接続できる | FAIL — `.env` の `DB_*` を確認する |
| `installed` | サイトがインストール済みか | 未インストールのとき WARN（初回インストール前は正常） |

### 暗号鍵と機密情報
| チェック | 確認内容 |
| --- | --- |
| `encryption_key_dedicated` | 機密情報用に専用の `GP247_ENCRYPTION_KEY` を設定しているか（`APP_KEY` と共用していないか） — [機密データの暗号化](../system/data-encryption_ja.md) を参照 |
| `secret_decryptable` | 設定内の暗号化された機密情報をまだ復号できるか（できない場合：古い鍵を残さずに鍵を変えた兆候） |
| `backup_post_incident_pending` | 「セキュリティ事故の後」の復元のあと、まだ終わっていない作業（鍵の変更、コードの再インストール、データベース／ホスティングのパスワード変更）がある |

### 拡張機能と見た目
| チェック | 確認内容 | 修正コマンド |
| --- | --- | --- |
| `extension_assets` | インストール済みの拡張機能の CSS／JS／画像が `public/GP247/...` にない | `gp247:ext-publish` |
| `extension_data_pending` | GP247 が記録したバージョンより新しいファイルを持つ拡張機能（`git pull` や上書きコピーの直後）で、データ更新が未実行 | `gp247:ext-update --local --all` |
| `template_source` | パッケージと同一の、公開済みテンプレートファイルの数 — 修正を受け取れない | `gp247:template-prune` |
| `file_bom` | UTF-8 の BOM で始まる `.php`／`.blade.php` ファイル | BOM なしの UTF-8 で保存し直す |

### REST API
| チェック | 確認内容 |
| --- | --- |
| `api_config_legacy_names` | `.env` に、名前が変わった古い API 変数名が残っている（設定がキャッシュされている間は省略） |
| `api_route_gate` | ルートがキャッシュされているとき：すべての REST API ルートが API 共通の保護を通っている |
| `api_event_retention` | API 同期イベントのテーブルに保存期間を過ぎたイベントがある（API リクエスト時に自動で削除。必要なら `GP247_API_EVENT_DAYS` を小さくする） |

### セキュリティ設定（インストール済みのサイト）
これらのチェックは **PASS** か **WARN** だけです（管理画面で重大と表示される場合でも）。そのため終了コードは変わりません。

| チェック | WARN になる条件 |
| --- | --- |
| `app_debug` | `APP_DEBUG` が有効 |
| `https` | `APP_URL` が `https://` で始まらない、または `SESSION_SECURE_COOKIE` が無効（`APP_ENV=production` のときだけ WARN） |
| `trusted_proxies` | `TRUSTED_PROXIES` がすべての接続元を信頼している（`*`、`REMOTE_ADDR` など） — 誰でも自分の IP を偽れるので、ログイン制限と IP ルールが効かなくなる |
| `default_admin_password` | 有効な `admin` アカウントが既定のパスワードのまま |
| `password_policy` | 管理者パスワードの最小文字数が 12 未満 |
| `mfa_enabled` | 二要素認証プラグイン MFA が未インストールまたは無効 |
| `api_exposure` | （情報のみ、PASS）REST API／MCP が有効 |
| `support_line` | 使用中のバージョン系列のセキュリティサポートが残り 6 か月未満、または終了 |

### `file_bom` を確認する理由
BOM とは、Windows の多くのエディターが「UTF-8」で保存するときにファイルの先頭へ付け加える**目に見えない 3 バイト**です。
PHP はこれを**ほかの何よりも先に**出力するため、「headers already sent」エラー、先頭の文字から壊れた JSON／XML／CSV、
不明な文字で始まる HTML などの原因になります。ファイルを開いてもまったく普通に見えるので、機械でしか見つけられません。
doctor は `app/GP247/**` と `gp247/*` パッケージのソースを調べます。

**直し方:** エディターでファイルを開き、**BOM なしの UTF-8**（UTF-8 without BOM）で保存し直します（VS Code：右下のステータス
バーの `UTF-8 with BOM` をクリック → *Save with Encoding* → *UTF-8*）。`gp247:doctor` を再実行し、PASS に戻ったことを確認します。

## `gp247:info` と `gp247:core-info` — バージョンの確認
| コマンド | 表示内容 |
| --- | --- |
| `gp247:info` | インストール済みの各パッケージ（core、front、shop、mcp）のバージョン、インストール済みか、プラグイン／テンプレートの数、拡張機能ライブラリのアドレス |
| `gp247:core-info` | 名前、core のバージョンとサブバージョン、ホームページ、ソースリポジトリ |

```bash
php artisan gp247:info
```

> 💡 **ヒント:** サイトにセキュリティ修正が入っているかを知るには、`gp247:info` が表示するパッケージのバージョンを
> [リリースとセキュリティ](../release/release_ja.md) の表と比べてください。管理画面のフッターに表示される S-Cart のバージョンは、
> 最初の `composer update` の後は当てになりません。必ずパッケージのバージョンで判断してください。

## `gp247:cache-rebuild` — キャッシュの再構築
ルートと設定のキャッシュを消して再構築します。GP247 は、拡張機能のインストール、有効化、更新の後と、`gp247:update` の最後に
自動でこのコマンドを呼び出します。`.env` やルートを変えたのにサイトに反映されないときに、手動で実行します。

```bash
php artisan gp247:cache-rebuild
```

## よくある質問（Q&A）
**Q1: doctor に WARN の行があります。サイトは動きますか？**

→ はい。WARN は片付けたほうがよい項目（セキュリティ、不要なファイル）で、サイトを止めません。すぐに直す必要があるのは FAIL だけです。

**Q2: インストール前に doctor を実行する意味はありますか？**

→ あります。それが主な用途の 1 つです。doctor は `composer require` の直後から、インストール前でも使えます。インストール済みの
データベースが必要なチェックは省略されます。

**Q3: doctor が `secret_decryptable` を不合格と報告します。**

→ 暗号鍵が変わり、古い鍵が失われています。古い鍵を `GP247_ENCRYPTION_PREVIOUS_KEYS` に入れて `gp247:encryption-key-rotate` を
実行してください — [機密データの暗号化](../system/data-encryption_ja.md) を参照してください。

**Q4: 開発用マシンで `https` が WARN になります。**

→ WARN になるのは `APP_ENV=production` のときだけで、ほかの環境ではメモつきの PASS です。WARN が出る場合は `APP_ENV` を
確認してください。

**Q5: CI で doctor を使うには？**

→ `php artisan gp247:doctor --json` を使います。FAIL があると 0 以外で終了するので、CI のステップが自動で止まります。
[スクリプト・CI・Docker](./cli-automation_ja.md) を参照してください。

**Q6: doctor のセキュリティチェックで、セキュリティの見直しは不要になりますか？**

→ いいえ。doctor は設定を読むだけです。[セキュリティチェックリスト](../security/security-checklist_ja.md) で全体を確認してください。

---

<sub>📅 **最終更新:** 2026-11-08 · ✍️ **作成者 (Author):** GP247</sub>
