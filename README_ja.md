> 🌐 **言語:** [🇻🇳 Tiếng Việt](./README_vi.md) · [🇬🇧 English](./README.md) · 🇯🇵 日本語（現在）

# GP247 ドキュメント（gp247-docs）

## はじめに
これは GP247 の共通ドキュメントリポジトリです。このページは**目次**です。リポジトリ内のすべてのドキュメントを一覧にし、
各ドキュメントへのリンクを載せています。ドキュメントはテーマ別（API、拡張／プラグイン、リリース、システム、S-Cart）にまとめています。

## ドキュメント一覧

### API
| ドキュメント | 概要 | 最終更新 |
| --- | --- | --- |
| [GP247 API の紹介](./api/api-introduction_ja.md) | API システムの全体像、認証、試しに呼び出す方法 | 2026-10-30 |
| [標準に沿った API の作り方](./api/create-api_ja.md) | GP247 の標準に沿って新しい API エンドポイントを自作する方法 | 2026-10-28 |

### 拡張（プラグイン）
| ドキュメント | 概要 | 最終更新 |
| --- | --- | --- |
| [プラグインの作り方](./extension/create-plugin_ja.md) | 出発点：プラグインの種類に合ったガイドの選び方、コード付きの 6 ステップ（データベースに保存する設定、設定画面、インストール、公開） | 2026-10-22 |
| [決済プラグインを作る](./extension/create-payment-plugin_ja.md) | コード付きの手順：すぐに動く銀行振込プラグイン＋オンライン決済ゲートウェイ接続の枠組み（入金を一度だけ記録、Webhook） | 2026-10-22 |
| [配送プラグインを作る](./extension/create-shipping-plugin_ja.md) | コード付きの手順：すぐに動く重量別送料プラグイン＋API を持つ配送会社接続の枠組み（送り状、Webhook、代金引換） | 2026-10-22 |
| [更新に強いプラグイン](./extension/create-plugin-update-safety_ja.md) | 更新の仕組み、6 つのルール、`update()` フック、独自のデータテーブル | 2026-10-22 |
| [高度なプラグイン](./extension/create-plugin-advanced_ja.md) | ストアごとの設定、秘密キー、ストアフロントのページ／ブロック／差し込みポイント、割引コード、メニュー | 2026-10-22 |
| [テンプレート（デザイン）の作り方](./extension/create-template_ja.md) | 新しいストアフロントテンプレートの作成、gp247/shop のビューフォールバックの仕組み | 2026-10-22 |
| [プラグインとテンプレートのインストール](./extension/install-extension_ja.md) | 4 つのインストール方法：オンライン（ライブラリ）、インポート（.zip）、手動、CLI（`gp247:ext-*`） | 2026-10-22 |
| [プラグイン v1 から v2 への移行](./extension/convert-plugin-v1-to-v2_ja.md) | プラグインを Core 1.x から Core 2.0 へアップグレード | 2026-10-22 |

### リリース
| ドキュメント | 概要 | 最終更新 |
| --- | --- | --- |
| [S-Cart のリリースとセキュリティ](./release/release_ja.md) | サイトは安全か（パッケージごとの安全な最低バージョン）；注目の新機能；更新方法；S-Cart の各リリース（core／front／shop をまとめたもの）：新機能、不具合の修正、更新時の対応；セキュリティ修正；脆弱性の報告 | 2026-10-31 |

### システム
| ドキュメント | 概要 | 最終更新 |
| --- | --- | --- |
| [リリースノート v3.0 — 注文の金額](./system/release-notes-3.0_ja.md) | v2.1／v3.0 からのアップグレード：注文ごとの支払台帳、入金日基準のキャッシュフロー、一段ずれた支払ステータスの修正、**割引は税の前に差し引き**；プラグイン開発者向けの注意点 | 2026-10-22 |
| [CLI コマンド一覧](./system/command-line-reference_ja.md) | GP247 のすべての CLI コマンドのリファレンス：`--json` 出力と終了コードの約束、`gp247:ext-*` のライフサイクル、install／update／doctor／info；`core-update` はアップグレード用マイグレーションを実行（core 2.2） | 2026-10-22 |
| [GP247 の更新ガイド](./system/update-gp247_ja.md) | 稼働中サイトの安全な更新、標準化コマンド `gp247:update` を優先；公開版 v2.1 からデータを自動変換；オプション `--overwrite-lang`、`--publish`；`git pull` 後に拡張機能のデータ更新を適用 | 2026-10-22 |
| [多言語システム](./system/language-system_ja.md) | GP247 での言語／i18n の使い方 | 2026-10-22 |
| [メール送信システム](./system/mail-system_ja.md) | メール送信の流れ（図付き）、SMTP 設定、送信チャネルの選択 | 2026-10-22 |
| [機密データの暗号化](./system/data-encryption_ja.md) | 秘密情報（SMTP／OAuth／ライセンス）の保存時暗号化；専用キー `GP247_ENCRYPTION_KEY`；`gp247:encryption-key-rotate` による**安全なキー変更の手順**；他テーブルの列の暗号化 | 2026-10-22 |
| [スケジュールとキュー](./system/schedule-and-queue_ja.md) | schedule:run と queue:work の違い；環境別のメール送信用 cron の設定 | 2026-10-22 |
| [キャッシュの扱い](./system/cache-system_ja.md) | Config Cache Manager 画面；キャッシュするもの／しないもの；バージョン番号の更新；ヘルパー関数 | 2026-10-22 |
| [カスタムフィールド（Custom Field）](./system/custom-fields_ja.md) | 4 つのフックの仕組み；対象は customer／product のみ；他テーブルで有効にする開発者向け手順 | 2026-10-22 |
| [レイアウトブロック（Layout Block）](./system/layout-block-system_ja.md) | ブロック表示の仕組み：テーマ → プラグインの優先順位、更新で失われない 3 つのカスタマイズ方法、プラグインのブロック登録方法 | 2026-10-22 |
| [権限管理（権限・ロール・ユーザー）](./system/permission-and-role_ja.md) | 管理画面の RBAC：3 つのブロック、2 つの特別なロール、アドレス＋メソッドによる制御、戦略の考え方 | 2026-10-22 |

### S-Cart（販売）
| ドキュメント | 概要 | 最終更新 |
| --- | --- | --- |
| [注文のライフサイクル](./s-cart/order-processing/order-lifecycle_ja.md) | 注文の各段階 — 注文、編集、入金、返金、キャンセル、再開、削除 — と各段階での在庫／金額／履歴、フロー図 | 2026-10-22 |
| [支払リクエスト（Payment Request）](./s-cart/order-processing/payment-request_ja.md) | カート外での入金／出金：オンライン支払リンク、手動記録、返金；4 つのお金の流れ、注文／InOut／MultiVendor の用途、出金の権限、フロー図 | 2026-10-22 |
| [GP247 の決済](./s-cart/payment/payment_ja.md) | 設定と運用：決済ゲートウェイの導入（テスト → 本番、Webhook）、注文の支払台帳、代金引換、支払時にお客様に表示する案内；開発者は[決済プラグインを作る](./extension/create-payment-plugin_ja.md)を参照 | 2026-10-22 |
| [GP247 の配送](./s-cart/shipping/shipping_ja.md) | 設定と運用：配送タブ、集荷倉庫、送り状、追跡、代金引換（shop 3.1.4 から）；開発者は[配送プラグインを作る](./extension/create-shipping-plugin_ja.md)を参照 | 2026-10-22 |
| [通貨（Currency）](./s-cart/tax-and-currency/currency_ja.md) | 明示的な基準通貨、価値を保ったままの基準通貨の変更、金額入力欄の注記（shop 2.1 から） | 2026-10-22 |
| [GP247 の税](./s-cart/tax-and-currency/tax_ja.md) | 商品ごとの税の仕組みと設定方法 | 2026-10-22 |
| [セット商品（Bundle/Combo）](./s-cart/product/product-bundle_ja.md) | 複数の子商品からなるセット商品の作成；価格と在庫の差し引き | 2026-10-22 |
| [商品の構成（Single/Bundle/Group）](./s-cart/product/product-structure_ja.md) | 3 種類の商品を図で比較；どれを選ぶか | 2026-10-22 |
| [商品の在庫管理](./s-cart/product/product-stock-management_ja.md) | 在庫が増減するタイミング；在庫超過の設定；**注文キャンセルでは在庫が戻り、注文削除では戻らない**（v3.0 から変更） | 2026-10-22 |
| [商品属性（Color/Size）](./s-cart/product/product-attribute_ja.md) | 属性グループと値＋追加料金；管理画面での設定；価格／カート／注文の流れ；価格の安全性 | 2026-10-22 |
| [商品タグ（Product Tag）](./s-cart/product/product-tag_ja.md) | 商品へのタグの作成と割り当て；配送タイプとの違い；無効化と削除の違い（shop 2.1.6 から） | 2026-10-22 |

### 複数店舗とマーケットプレイス
> 2 つのモデルは**互いに排他的**です。どちらか一方を選びます：自社の複数店舗（Multi-Store）か、複数の販売者が出店する 1 つのマーケットプレイス（Multi-Vendor）か。

| ドキュメント | 概要 | 最終更新 |
| --- | --- | --- |
| [Multi-Store — 複数サイト、1 つの管理画面](./multi-store/multi-store_ja.md) | 同じ企業の複数店舗。店舗ごとにドメイン／デザイン／言語／通貨を持てます：モデル、機能、Free／Pro の比較；インストール、運用、カスタマイズへの目次 | 2026-10-22 |
| [Multi-Vendor — 複数販売者のマーケットプレイス](./multi-vendor/multi-vendor_ja.md) | S-Cart を 1 つのドメイン上のマーケットプレイスにします：モデル、機能、Free／Pro の比較、Multi-Store との比較；インストール、運用、カスタマイズへの目次 | 2026-10-22 |

### 仕入れ・返品・入出金（InOut）
| ドキュメント | 概要 | 最終更新 |
| --- | --- | --- |
| [InOut — 仕入れ、返品、入出金と債権債務](./in-out/in-out_ja.md) | S-Cart に仕入れの流れを加える有料プラグイン：仕入伝票、返品伝票、入出金台帳、双方向の債権債務、支払リクエストによる回収／支払、締め処理、Excel／PDF 出力；5 つの業務パートへの目次 | 2026-10-22 |

---

<sub>📅 **最終更新:** 2026-10-31 · ✍️ **作成者 (Author):** GP247</sub>
