> 🌐 **言語:** [🇻🇳 Tiếng Việt](./custom-fields_vi.md) · [🇬🇧 English](./custom-fields.md) · 🇯🇵 日本語（現在）

# GP247 のカスタムフィールド（Custom Field）

## はじめに
このドキュメントでは、**GP247 のカスタムフィールド（Custom Field）機能**について説明します。
機能の概要、データの保存場所、**フィールドが自動で表示・保存される「フック」の仕組み**、
そして**現在この機能を使えるテーブル**を解説します。最後に、**開発者向け**に新しいテーブル
（カテゴリー、注文など）でカスタムフィールドを有効にする方法を説明します。管理画面では
*すべての* テーブルを選べますが、実際に最後まで動くのは**顧客（customer）**と**商品（product）**だけだからです。
サイト運営者（機能と制限の理解）と開発者（対象テーブルの拡張）の両方を対象としています。

## カスタムフィールドとは？

カスタムフィールドを使うと、GP247 の既存のオブジェクト（顧客、商品など）に**独自の入力欄を追加**できます。
**元のテーブル構造を変更する必要はありません**。例えば、顧客に「法人番号」欄を追加したり、
商品に「素材」欄を追加したりできます。

GP247 はこれを **EAV**（Entity–Attribute–Value）モデルで、**2つのテーブル**に保存します。

| テーブル | 役割 | 主なカラム |
| --- | --- | --- |
| `gp247_admin_custom_field` | フィールドの**定義**（どんな入力欄があるかを宣言） | `type`、`code`、`name`、`option`、`default`、`required`、`status` |
| `gp247_admin_custom_field_detail` | 各レコードの実際の**値** | `custom_field_id`、`rel_id`、`text` |

各項目の意味は次のとおりです。
- **`type`** ＝「このフィールドがどのテーブルに属するか」。最も重要な**接続キー**です（下の
  仕組みの章を参照）。
- **`code`** ＝フィールドの技術的なコード（例: `tax-code`）。`type` ごとに一意です。
- **`option`** ＝入力欄の種類。**14種類**あります: `text`、`textarea`、`number`、`date`、`month`、
  `week`、`time`、`email`、`password`、`url`、`color`、`select`、`radio`、`checkbox`。
- **`default`** ＝選択式の3種類（`select`/`radio`/`checkbox`）では、選択肢の一覧を
  JSON 形式（`{"key":"表示ラベル"}`）で保存します。
- **`rel_id`** ＝値を持つレコードの id（例: 顧客の id）。**`text`** ＝入力された値です
  （複数選択の checkbox はカンマ区切りの CSV 文字列で保存されます）。

## 仕組み — 4つのフック「リンク」

カスタムフィールドが**一巡りすべて動く**（定義 → 入力 → 保存 → 再表示）には、**4つの層**を
通る必要があります。覚えておくべき要点は、**この仕組みは自動ではない**ということです。各層を
テーブルごとに「手作業でつなぐ」必要があります。「フィールドがあるテーブルを自動で探して表示する」
ような処理はどこにもありません。

| # | リンク | 場所（core パッケージ） | 役割 |
| --- | --- | --- | --- |
| A | `HasCustomFields`（trait） | `AdminShell/Infrastructure/HasCustomFields.php` | **管理画面での入力** — 管理画面（Livewire）がこの trait を `use` すると、入力欄・検証・保存が自動で使えます |
| B | `ModelTrait::getCustomFields()` ＋ `custom_fields` 属性 | `Models/ModelTrait.php` | **モデルでの読み取り** — モデルがフィールド一覧と保存済みの値を取得できるようにします |
| C | `CustomFieldTrait`（controller） | `Controllers/CustomFieldTrait.php` | **コントローラーでの検証・保存** — 従来型のコントローラー画面（Livewire 以外）で使います |
| D | `render_form_custom_field.blade.php` | ストアフロントのテンプレート | **ストアフロントでの表示** — 顧客側のフォームに入力欄を描画します |

### 接続キー：`type` カラム

上の4つのリンクはすべて、**同じ `type` 文字列**でカスタムフィールドを検索・保存します。この文字列は
**モデルの実際のテーブル名**、つまり `$model->getTable()` が返す値（DB の接頭辞付き、例:
`gp247_shop_customer`）で**なければなりません**。4か所で違う `type` 文字列を使うと（接頭辞の有無が
違う、または数値を使うなど）、フィールドは**ある形式で保存されたのに、別の形式で表示用に検索される**
ことになります。その結果、「データが消えた」ように見えます。

> 🔑 **拡張するときの黄金ルール:** `type` の値には、**すべての**場所（定義の宣言、管理画面での入力、
> 検証、保存、表示）で常に **`$model->getTable()`** を使ってください。ある場所では `'shop_customer'`
> と手入力し、別の場所では `getTable()` を使う、ということは避けてください。接頭辞のずれは最もよくある不具合です。

## 現在の対応範囲 — 顧客と商品のみ

管理画面の「カスタムフィールド」画面では**どのテーブルでも**（約60以上の `gp247_*` テーブル）選べます。
しかし、フィールドが実際に動くための**フックが揃っているのは2つのテーブルだけ**です。

| テーブル（`type`） | 管理画面での入力（A） | ストアフロントでの表示（D） | 保存・検証 | 状態 |
| --- | --- | --- | --- | --- |
| **`shop_customer`**（顧客） | ✅ 顧客管理画面 | ✅ マイページ → 会員情報の変更 | ✅ | **一巡り完結** |
| **`shop_product`**（商品） | ✅ 商品管理画面 | ❌（商品ページにはまだ描画されません） | ✅ | **管理画面のみ** |
| カテゴリー、ブランド、注文…（その他のテーブル） | ❌ | ❌ | ❌ | **ドロップダウンにあるだけで、未接続** |

> ⚠️ **サイト運営者への注意:** **customer/product 以外**のテーブル（例えば「category」を選択）用に
> フィールドを作成すると、そのフィールドは**どこにも表示されません**。DB には残りますが、どの画面も
> 読み書きしない**孤立した定義**になります。別のテーブルで使いたい場合は、下の手順に従って開発者が
> フックを追加する必要があります。

## （サイト運営者向け）顧客用のカスタムフィールドを作成する

これはすぐに使える方法で、プログラミングは不要です。

1. 管理画面にログインし、**「カスタムフィールド」**（Custom Field）画面を開きます。
2. **「新規追加」**（Add new）をクリックします。**「種類」**（Type、対象テーブル）欄で **customer** を選びます。
3. **「オプション」**（Option、入力欄の種類）を選びます。例えば、文字入力欄なら `text`、選択欄なら `select` です。
4. **「コード」**（Code、例: `tax-code`）と**「名前」**（Name、表示名）を入力します。必須にする場合は**「必須」**（Required）をオンにします。
5. `select`/`radio`/`checkbox` の場合は、選択肢の一覧を入力します。
6. **「保存」**します。成功すると、新しいフィールドが次の場所に表示されます。
   - **管理画面 → 顧客管理 → 顧客の編集**（値の追加・編集）。
   - **ストアフロント → マイページ → 会員情報の変更**（顧客が自分で入力）。

> 📌 **注意:** **「会員登録」**ページには、顧客のカスタムフィールドは現在**まだ表示されません**
> （登録フォームには、どのテーブルかを「知る」ための顧客データがまだないためです）。そのため、
> 顧客のフィールドは**必須にしない**か、後から会員情報の変更ページで入力してもらうようにしてください。

## （開発者向け）新しいテーブルでカスタムフィールドを有効にする

オブジェクト `X`（例: カテゴリー `ShopCategory`）にカスタムフィールドを追加したいとします。
下の**4つの作業をすべて**行ってください。どれかを省くと、その段階でフィールドが欠けます。最も標準的な
参考例は**顧客**画面（`vendor/gp247/shop/src/Admin/Livewire/CustomerManager.php`）です。これを真似してください。

すべての例で、**`$type = (new XModel)->getTable()`**（例: `gp247_shop_category`）とし、
この文字列をすべての場所で使います。

### 作業1 — 正しい `type` でフィールド定義を作成する

定義（`gp247_admin_custom_field` のレコード）の `type` カラムは、`X` の**実際のテーブル名と同じ**
でなければなりません。方法は2つです。

- **管理画面から:** カスタムフィールド画面を開き、「種類」欄で正しいテーブル `X` を選びます。
- **またはコードで seed する**（最も確実で、UI に依存しません）:

  ```php
  use GP247\Core\Models\AdminCustomField;
  use GP247\Shop\Models\ShopCategory;

  AdminCustomField::create([
      'type'     => (new ShopCategory)->getTable(), // 例: "gp247_shop_category"
      'code'     => 'banner-note',
      'name'     => 'バナーのメモ',
      'option'   => 'text',
      'required' => 0,
      'status'   => 1,
  ]);
  ```

> ✅ **確認:** `gp247_custom_field_list((new ShopCategory)->getTable())` を実行すると、作成した定義が
> 返るはずです。空の場合は、DB の `type` カラムが `getTable()` とずれています（接頭辞の誤り）。

### 作業2 — モデル X が値を読めるようにする（リンク B）

`XModel` が **`ModelTrait` を使っている**ことを確認してください。これで `->getCustomFields()` と
`->custom_fields` 属性が使えます。ほとんどの GP247 モデルには最初から入っています。入っていない場合は次のようにします。

```php
use GP247\Core\Models\ModelTrait;

class ShopCategory extends Model
{
    use ModelTrait;
    // ...
}
```

この作業の後、`$category->getCustomFields()` は `[code => ['text' => '値']]` を返します。

### 作業3 — 管理画面に入力欄を追加する（リンク A）

`X` の管理画面が **Livewire manager** 形式（顧客・商品と同じ）の場合は、コンポーネント内で
4つの操作を行います。

1. trait を `use` し、**`getTable()`** を返す `customFieldType()` を宣言します（文字列を手入力しないでください）。

   ```php
   use GP247\Core\AdminShell\Infrastructure\HasCustomFields;
   use GP247\Shop\Models\ShopCategory;

   class CategoryManager extends Component
   {
       use HasCustomFields;

       /**
        * @return string The prefixed table name used as the custom-field `type` key.
        */
       protected function customFieldType(): string
       {
           return (new ShopCategory)->getTable();
       }
   ```

2. フォームを開くときに、値を state に読み込みます。
   - **新規追加**のとき: `$this->initCustomFields();` を呼びます。
   - **編集**のとき: `$this->loadCustomFields($category->id);` を呼びます。

3. 必須ルールをコンポーネントの `rules()` に統合します。

   ```php
   protected function rules(): array
   {
       return array_merge($otherRules, $this->customFieldRules());
   }
   ```

4. 送信時に保存します。

   ```php
   gp247_custom_field_update($this->customFieldsPayload(), (string) $category->id, $this->customFieldType());
   ```

5. 画面の Blade に入力欄を描画します。trait のヘルパーは `protected` なので、view から使えるように
   **public のアクセサーを用意**します（`CustomerManager::customFieldList()` と同じ方法です）。

   ```php
   /** @return iterable<mixed> */
   public function customFieldList(): iterable
   {
       return $this->customFieldDefs();
   }
   ```

   そして Blade で `$this->customFieldList()` をループし、`wire:model="customFields.{{ $field->code }}"` に
   バインドします（`vendor/gp247/shop/src/Views/admin/customer-manager.blade.php` のマークアップを
   そのままコピーすると、早くて TailAdmin の標準どおりになります）。

> ℹ️ `X` の管理画面が**従来型のコントローラー**（Livewire 以外）の場合は、trait A の代わりに
> **リンク C**（`CustomFieldTrait`）を使います。検証時に `getCustomFieldValidation()`、保存時に
> `handleCustomField()` を呼びます。どちらも `getTable()` で `type` を自動取得します。

### 作業4 — ストアフロントに表示する（リンク D、必要な場合のみ）

`X` に顧客側のフォームがある場合（category ではまれですが、customer では必要です）、フォームの Blade に次を追加します。

```blade
@include($GP247TemplatePath.'.common.render_form_custom_field', ['object' => $category])
```

そして、そのフォームの POST を受け取るコントローラーで次のようにします。

```php
// 検証
$rules = gp247_custom_field_validate($rules, $category->getTable());

// レコードの作成・更新後に保存
gp247_custom_field_update($request->input('fields', []), $category->id, $category->getTable());
```

> ⚠️ Blade の `render_form_custom_field` は、**稼働中のテンプレートに存在**している必要があります
> （`app/GP247/Templates/<Template>/common/`）。GP247Front には Tailwind 版が最初から入っています。
> 他のテンプレートは自前の版を同梱する必要があり、ない場合はリクエスト時に view が見つからないエラーになります。

### 守るべき不変条件のまとめ

| 作業 | 関数・接点 | `type` の値 |
| --- | --- | --- |
| 定義の作成 | `AdminCustomField.type` | `(new XModel)->getTable()` |
| 管理画面での入力（Livewire） | `customFieldType()` | `(new XModel)->getTable()` |
| 管理画面での入力（controller） | `CustomFieldTrait` | `getTable()` を自動取得 |
| 検証・保存 | `gp247_custom_field_validate/update(..., $type)` | `$x->getTable()` |
| ストアフロントでの表示 | `render_form_custom_field`（`$object`） | `$object->getTable()`（自動計算） |

`type` 文字列が**1か所**でもずれると、フィールドは一巡りしません。最初から最後まで `getTable()` を
使うのが最も安全な方法です。

## よくある質問（Q&A）

**Q1: 管理画面で「category」用のフィールドを作成しましたが、どこにも表示されません。なぜですか？**

→ 現在の設計どおりの動作です。接続済みなのは **customer** と **product** だけです。他のテーブル用の
フィールドは、開発者がフックを追加するまで「孤立した定義」のままです（「新しいテーブルでカスタムフィールドを
有効にする」の章を参照）。

**Q2: 2つのテーブルしか動かないのに、なぜ管理画面ではすべてのテーブルを選べるのですか？**

→ テーブル一覧は DB から自動で取得され（`SHOW TABLES`）、「フックがあるかどうか」で絞り込まれていないためです。
誤解しやすい点です。他のテーブルに自分でフックを追加していない限り、フィールドは customer/product 用だけに作成してください。

**Q3: 顧客のフィールドは会員登録ページに表示されますか？**

→ **まだ表示されません。** 会員登録ページにはテーブルを判別するための顧客データがまだないため、フォームに
カスタムフィールドが描画されません。顧客は **マイページ → 会員情報の変更** で入力します。そのため、
会員登録の段階で集めたい場合でも、顧客のフィールドを「必須」にしないでください。

**Q4: 値を入力して保存したのに、開き直すと空になっています。なぜですか？**

→ ほとんどの場合、保存時と表示時で **`type` 文字列がずれている**ことが原因です（一方は `gp247_` 接頭辞付き、
もう一方は接頭辞なし）。すべての接点で `$model->getTable()` を使っているか確認してください。
「拡張するときの黄金ルール」も参照してください。

**Q5: `option`（入力欄の種類）は何に対応していますか？**

→ 14種類です: `text`、`textarea`、`number`、`date`、`month`、`week`、`time`、`email`、`password`、`url`、
`color`、`select`、`radio`、`checkbox`。最後の3種類（`select`/`radio`/`checkbox`）には、`default` カラムに
JSON 形式で保存する選択肢の一覧が必要です。

**Q6: 複数選択の checkbox の値はどのように保存されますか？**

→ `text` カラムに**1つの CSV 文字列**（カンマ区切りのキー）として保存されます。そのため、選択肢のキーには
**カンマを含めてはいけません**。システムは検証でこれを防いでいますが、手作業で seed する場合も避けてください。

**Q7: 種類ごとに検証ルールを自分で書く必要がありますか？**

→ いいえ。`gp247_custom_field_rules()` が種類に応じたルールを生成します（email は正しいメール形式、数値は
数値であること、select/radio/checkbox は選択肢の中にあること など）。`gp247_custom_field_validate()`
（controller）または `customFieldRules()`（Livewire）を呼ぶだけで済みます。

**Q8: フィールドの定義を削除すると、入力済みの値も削除されますか？**

→ はい。`AdminCustomField` モデルには `deleting` イベントがあり、対応する値のレコード
（`admin_custom_field_detail`）をカスケード削除して、不要なデータが残らないようにしています。

**Q9: 新しいテーブルにフックを追加するには、core のコードを変更する必要がありますか？**

→ 変更すべきではありません。GP247 の規約では、core を変更せずに core へ**差し込み**ます（既存の trait・関数を
使います）。新しいテーブルのコンポーネントや Blade は対応するパッケージ（shop/front/plugin）に置き、
`HasCustomFields`、`gp247_custom_field_*`、`render_form_custom_field` を再利用してください。

**Q10: あるテーブルが完全に接続済みかどうかを、すばやく確認するにはどうすればよいですか？**

→ grep で次を確認します。そのテーブルの `type` を宣言している `use HasCustomFields`（または `CustomFieldTrait`）が
あるか、保存時に `gp247_custom_field_update(...)` を呼んでいるか、そして（顧客側に表示する必要がある場合）
Blade の `render_form_custom_field` を `@include` しているか。欠けているリンクがあれば、そのリンクを追加してください。

---

<sub>📅 **最終更新:** 2026-10-22 · ✍️ **作成者 (Author):** GP247</sub>
