> 🌐 **Language:** [🇻🇳 Tiếng Việt](./custom-fields_vi.md) · 🇬🇧 English (current)

# Custom Fields in GP247

## Introduction
This document explains **GP247's Custom Field feature**: what it is, where the data lives, **which
"hook" mechanism makes a field appear and persist**, and **which tables actually support it today**.
The last section is a **developer guide** to enabling Custom Fields on a new table (Category, Order…),
because the admin lets you pick *any* table but in practice only **Customer** and **Product** work
end-to-end. For site owners (understand the feature and its limits) and developers (extend to more
tables).

## What is a Custom Field?

Custom Fields let you **attach your own input fields** to an existing GP247 entity (customer,
product…) **without altering the base table schema**. For example: add a "Tax code" field to
customers, or a "Material" field to products.

GP247 stores this using the **EAV** (Entity–Attribute–Value) model via **2 tables**:

| Table | Role | Key columns |
| --- | --- | --- |
| `gp247_admin_custom_field` | **Definition** of the field (declares which inputs exist) | `type`, `code`, `name`, `option`, `default`, `required`, `status` |
| `gp247_admin_custom_field_detail` | **Values** for each record | `custom_field_id`, `rel_id`, `text` |

Where:
- **`type`** = "which table this field belongs to" — this is the most important **join key** (see
  the mechanism section below).
- **`code`** = technical code of the field (e.g. `tax-code`), unique per `type`.
- **`option`** = the input kind. There are **14 kinds**: `text`, `textarea`, `number`, `date`,
  `month`, `week`, `time`, `email`, `password`, `url`, `color`, `select`, `radio`, `checkbox`.
- **`default`** = for the 3 choice kinds (`select`/`radio`/`checkbox`), this is the option list
  stored as JSON (`{"key":"Display label"}`).
- **`rel_id`** = id of the record that owns the value (e.g. a customer id), **`text`** = the entered
  value (a multi-select checkbox is stored as a comma-separated CSV string).

## How it works — the 4 hook "links"

For a Custom Field to **live end-to-end** (define → input → persist → display again) it must pass
through **4 layers**. The core point to remember: **this mechanism is NOT automatic** — each layer
must be wired by hand for each table. Nothing scans "which table has fields and auto-renders them".

| # | Link | Location (core package) | Job |
| --- | --- | --- | --- |
| A | `HasCustomFields` (trait) | `AdminShell/Infrastructure/HasCustomFields.php` | **Admin input** — an admin (Livewire) screen `use`s this trait to get inputs + validation + persistence |
| B | `ModelTrait::getCustomFields()` + `custom_fields` attribute | `Models/ModelTrait.php` | **Model read** — lets a model fetch its field definitions + stored values |
| C | `CustomFieldTrait` (controller) | `Controllers/CustomFieldTrait.php` | **Controller validate/save** — for classic controller screens (non-Livewire) |
| D | `render_form_custom_field.blade.php` | storefront template | **Storefront display** — renders the fields onto the customer-facing form |

### The join key: the `type` column

All 4 links look up / write Custom Fields by the **same `type` string**. That string **must be the
model's real table name**, i.e. the value returned by `$model->getTable()` (already including the DB
prefix, e.g. `gp247_shop_customer`). If the four places use different `type` strings (one prefixed,
one not, or a number), a field will be **saved under one key but displayed by looking up another** →
it looks like "lost data".

> 🔑 **Golden rule when extending:** always use **`$model->getTable()`** as the `type` value in
> **every** place (defining, admin input, validation, saving, display). Do not hardcode
> `'shop_customer'` in one spot and `getTable()` in another — a prefix mismatch is the most common
> bug.

## Current coverage — only Customer and Product

Although the admin "Custom Field" screen lets you pick **any table** (about 60+ `gp247_*` tables),
**only 2 tables are actually fully wired**:

| Table (`type`) | Admin input (A) | Storefront display (D) | Validate/save | Status |
| --- | --- | --- | --- | --- |
| **`shop_customer`** (Customer) | ✅ Customer manager screen | ✅ Account → Edit info | ✅ | **Full loop** |
| **`shop_product`** (Product) | ✅ Product manager screen | ❌ (not rendered on the product page) | ✅ | **Admin only** |
| Category, Brand, Order… (other tables) | ❌ | ❌ | ❌ | **Only listed in the dropdown, not wired** |

> ⚠️ **Warning for site owners:** if you create a field for a table **other than customer/product**
> (e.g. picking "category"), that field will **not appear anywhere** — it becomes an **orphan
> definition** sitting in the DB that no screen reads/writes. To use it on another table, a developer
> must add the hooks per the guide below.

## (Site owner) Create a custom field for Customers

This is the ready-made path, no coding required:

1. Log into the admin, open the **Custom Field** screen.
2. Click **Add new**. In the **Type (target table)** field, choose **customer**.
3. Choose the **Option (input kind)** — e.g. `text` for a text box, `select` for a dropdown.
4. Enter a **Code** (e.g. `tax-code`) and a **Name** (display label). Toggle **Required** if needed.
5. For `select`/`radio`/`checkbox`: enter the list of choices.
6. **Save**. On success, the new field appears:
   - In **admin → Customer manager → edit a customer** (add/edit the value).
   - In **storefront → Account → Edit info** (the customer fills it in).

> 📌 **Note:** the **Register** page does **not** currently show customer custom fields (the register
> form has no customer record yet to "know" which table applies). So keep customer fields **optional**,
> or let customers fill them later on the Edit info page.

## (Developer) Enable Custom Fields on a new table

Suppose you want Custom Fields on an entity `X` (e.g. Category `ShopCategory`). Do **all 4 tasks**
below; skip one and the field breaks at that step. The best reference implementation is the
**Customer** screen (`vendor/gp247/shop/src/Admin/Livewire/CustomerManager.php`) — just copy it.

Throughout, let **`$type = (new XModel)->getTable()`** (e.g. `gp247_shop_category`) and use that same
string everywhere.

### Task 1 — Create the definition with the correct `type`

The definition (a row in `gp247_admin_custom_field`) must have its `type` column **equal to the real
table name** of `X`. Two ways:

- **Via admin:** open the Custom Field screen, pick the correct table `X` in the Type field.
- **Or seed via code** (most reliable, UI-independent):

  ```php
  use GP247\Core\Models\AdminCustomField;
  use GP247\Shop\Models\ShopCategory;

  AdminCustomField::create([
      'type'     => (new ShopCategory)->getTable(), // e.g. "gp247_shop_category"
      'code'     => 'banner-note',
      'name'     => 'Banner note',
      'option'   => 'text',
      'required' => 0,
      'status'   => 1,
  ]);
  ```

> ✅ **Check:** `gp247_custom_field_list((new ShopCategory)->getTable())` must return the definition
> you just created. If empty → the `type` column in the DB is out of sync with `getTable()` (wrong
> prefix).

### Task 2 — Let model X read values (link B)

Ensure `XModel` **uses `ModelTrait`** so it has `->getCustomFields()` and the `->custom_fields`
attribute. Most GP247 models already do. If not:

```php
use GP247\Core\Models\ModelTrait;

class ShopCategory extends Model
{
    use ModelTrait;
    // ...
}
```

After this: `$category->getCustomFields()` returns `[code => ['text' => 'value']]`.

### Task 3 — Add inputs to the admin screen (link A)

If `X` has a **Livewire manager** admin screen (like Customer/Product), do 4 things in the component:

1. `use` the trait and declare `customFieldType()` returning **`getTable()`** (never a hardcoded
   string):

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

2. Load values into state when opening the form:
   - On **create**: call `$this->initCustomFields();`
   - On **edit**: call `$this->loadCustomFields($category->id);`

3. Merge the required rules into the component's `rules()`:

   ```php
   protected function rules(): array
   {
       return array_merge($otherRules, $this->customFieldRules());
   }
   ```

4. Save on submit:

   ```php
   gp247_custom_field_update($this->customFieldsPayload(), (string) $category->id, $this->customFieldType());
   ```

5. Render the inputs in the screen's Blade. The trait keeps its helpers `protected`, so **expose a
   public accessor** for the view (like `CustomerManager::customFieldList()`):

   ```php
   /** @return iterable<mixed> */
   public function customFieldList(): iterable
   {
       return $this->customFieldDefs();
   }
   ```

   Then in Blade, loop `$this->customFieldList()` and bind to
   `wire:model="customFields.{{ $field->code }}"` (copy the whole markup block from
   `vendor/gp247/shop/src/Views/admin/customer-manager.blade.php` — fastest and TailAdmin-correct).

> ℹ️ If `X`'s admin screen is a **classic controller** (not Livewire), use **link C**
> (`CustomFieldTrait`) instead of trait A: call `getCustomFieldValidation()` on validate and
> `handleCustomField()` on save — both already derive `type` via `getTable()`.

### Task 4 — Display on the storefront (link D, only if needed)

If `X` has a customer-facing form (rare for category, needed for customer), add to the form's Blade:

```blade
@include($GP247TemplatePath.'.common.render_form_custom_field', ['object' => $category])
```

And in the controller handling that form's POST:

```php
// Validate
$rules = gp247_custom_field_validate($rules, $category->getTable());

// Save after creating/updating the record
gp247_custom_field_update($request->input('fields', []), $category->id, $category->getTable());
```

> ⚠️ The `render_form_custom_field` Blade must **exist in the active template**
> (`app/GP247/Templates/<Template>/common/`). GP247Front already ships a Tailwind copy; other
> templates must ship their own, otherwise the request fails with a missing-view error.

### Invariants to keep

| Task | Function / touchpoint | The `type` value must be |
| --- | --- | --- |
| Create definition | `AdminCustomField.type` | `(new XModel)->getTable()` |
| Admin input (Livewire) | `customFieldType()` | `(new XModel)->getTable()` |
| Admin input (controller) | `CustomFieldTrait` | derives `getTable()` |
| Validate/save | `gp247_custom_field_validate/update(..., $type)` | `$x->getTable()` |
| Storefront display | `render_form_custom_field` (`$object`) | `$object->getTable()` (auto) |

A single place with a mismatched `type` string breaks the round-trip. Using `getTable()` throughout
is the safest approach.

## Q&A

**Q1: I created a field for "category" in the admin but can't find it anywhere?**
That's by current design: only **customer** and **product** are wired. Fields for other tables are
"orphan definitions" until a developer adds the hooks (see "Enable Custom Fields on a new table").

**Q2: Why does the admin let me pick every table if only 2 work?**
The table list is pulled automatically from the DB (`SHOW TABLES`), not filtered by "already hooked
or not". This is a common gotcha; only create fields for customer/product unless you have added hooks
for another table yourself.

**Q3: Do customer fields show on the Register page?**
**Not yet.** The register page has no customer record to determine the table, so the form doesn't
render custom fields. Customers fill them in at **Account → Edit info**. So don't make customer fields
"required" if you expect to collect them at registration.

**Q4: I entered a value and saved, but it's empty when I reopen — why?**
Almost always a **mismatched `type` string** between saving and displaying (one place has the
`gp247_` prefix, the other doesn't). Verify every touchpoint uses `$model->getTable()`. See the
"Golden rule when extending".

**Q5: What does `option` (input kind) support?**
14 kinds: `text`, `textarea`, `number`, `date`, `month`, `week`, `time`, `email`, `password`, `url`,
`color`, `select`, `radio`, `checkbox`. The last three (`select`/`radio`/`checkbox`) need a choice
list stored in the `default` column as JSON.

**Q6: How is a multi-select checkbox value stored?**
As **a single CSV string** (keys joined by commas) in the `text` column. So a choice key **must not
contain a comma** — the system blocks this via validation, but avoid it when seeding by hand too.

**Q7: Do I have to write validation for each kind myself?**
No. `gp247_custom_field_rules()` already generates per-kind rules (email must be a valid email,
number must be numeric, select/radio/checkbox must be within the choice list…). Just call
`gp247_custom_field_validate()` (controller) or `customFieldRules()` (Livewire).

**Q8: If I delete a field definition, are the entered values deleted too?**
Yes. The `AdminCustomField` model has a `deleting` event that cascades and deletes the corresponding
value rows (`admin_custom_field_detail`), avoiding orphaned data.

**Q9: Does adding a hook for a new table require editing the core?**
It shouldn't. Per GP247 conventions you **plug into** the core (using the existing traits/functions),
not edit it. The new table's component/blade lives in its package (shop/front/plugin), reusing
`HasCustomFields`, `gp247_custom_field_*` and `render_form_custom_field`.

**Q10: How do I quickly check whether a table is fully wired?**
Grep for any `use HasCustomFields` (or `CustomFieldTrait`) declaring that table's `type`, a
`gp247_custom_field_update(...)` call on save, and (if customer-facing display is needed) an
`@include` of the `render_form_custom_field` blade. Whichever link is missing, add that link.

---

<sub>📅 **Last updated:** 2026-08-14 · ✍️ **Author:** GP247</sub>
