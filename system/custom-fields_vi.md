> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./custom-fields.md)

# Trường tùy chỉnh (Custom Field) trong GP247

## Giới thiệu
Tài liệu này giải thích **tính năng Trường tùy chỉnh (Custom Field) của GP247**: nó là gì,
dữ liệu được lưu ở đâu, **cơ chế "hook" nào làm cho một trường tự hiện ra và tự lưu lại**, và
**hiện tại những bảng nào đã dùng được** tính năng này. Phần cuối là **hướng dẫn cho lập trình
viên** cách bật Custom Field cho một bảng mới (ví dụ Danh mục, Đơn hàng…), vì admin cho chọn
*mọi* bảng nhưng thực tế chỉ **Khách hàng (customer)** và **Sản phẩm (product)** mới chạy trọn vẹn.
Dành cho chủ site (hiểu tính năng + giới hạn) và lập trình viên (mở rộng thêm bảng).

## Custom Field là gì?

Custom Field cho phép bạn **gắn thêm các ô nhập liệu của riêng bạn** vào một đối tượng có sẵn của
GP247 (khách hàng, sản phẩm…) mà **không phải sửa cấu trúc bảng gốc**. Ví dụ: thêm ô "Mã số thuế"
cho khách hàng, hay ô "Chất liệu" cho sản phẩm.

GP247 lưu việc này theo mô hình **EAV** (Entity–Attribute–Value) bằng **2 bảng**:

| Bảng | Vai trò | Cột quan trọng |
| --- | --- | --- |
| `gp247_admin_custom_field` | **Định nghĩa** trường (khai báo có ô gì) | `type`, `code`, `name`, `option`, `default`, `required`, `status` |
| `gp247_admin_custom_field_detail` | **Giá trị** thực tế của từng bản ghi | `custom_field_id`, `rel_id`, `text` |

Trong đó:
- **`type`** = "trường này thuộc về bảng nào" — đây là **chìa khóa nối** quan trọng nhất (xem mục
  cơ chế bên dưới).
- **`code`** = mã kỹ thuật của trường (vd `tax-code`), duy nhất theo từng `type`.
- **`option`** = kiểu ô nhập. Có **14 kiểu**: `text`, `textarea`, `number`, `date`, `month`,
  `week`, `time`, `email`, `password`, `url`, `color`, `select`, `radio`, `checkbox`.
- **`default`** = với 3 kiểu lựa chọn (`select`/`radio`/`checkbox`), đây là danh sách lựa chọn
  lưu dạng JSON (`{"key":"Nhãn hiển thị"}`).
- **`rel_id`** = id của bản ghi sở hữu giá trị (vd id khách hàng), **`text`** = giá trị đã nhập
  (checkbox nhiều lựa chọn lưu thành chuỗi CSV ngăn cách bằng dấu phẩy).

## Cơ chế hoạt động — 4 "mắt xích" hook

Một Custom Field muốn **sống trọn vòng** (định nghĩa → nhập → lưu → hiển thị lại) phải đi qua
**4 lớp**. Điểm cốt lõi cần nhớ: **cơ chế này KHÔNG tự động** — mỗi lớp phải được "cắm tay" cho
từng bảng. Không có chỗ nào tự quét "bảng nào có field thì tự hiện".

| # | Mắt xích | Nơi ở (package core) | Nhiệm vụ |
| --- | --- | --- | --- |
| A | `HasCustomFields` (trait) | `AdminShell/Infrastructure/HasCustomFields.php` | **Nhập ở admin** — màn admin (Livewire) `use` trait này để tự có ô nhập + validate + lưu |
| B | `ModelTrait::getCustomFields()` + thuộc tính `custom_fields` | `Models/ModelTrait.php` | **Model đọc** — cho model lấy danh sách trường + giá trị đã lưu |
| C | `CustomFieldTrait` (controller) | `Controllers/CustomFieldTrait.php` | **Controller validate/lưu** — dùng cho màn kiểu controller cổ điển (không Livewire) |
| D | `render_form_custom_field.blade.php` | template storefront | **Hiển thị ngoài storefront** — vẽ các ô ra form phía khách |

### Chìa khóa nối: cột `type`

Cả 4 mắt xích trên đều tìm/ghi Custom Field theo **cùng một chuỗi `type`**. Chuỗi này **phải là
tên bảng thật của model**, tức giá trị trả về bởi `$model->getTable()` (đã kèm tiền tố DB, ví dụ
`gp247_shop_customer`). Nếu bốn nơi dùng chuỗi `type` khác nhau (nơi có tiền tố, nơi không, hoặc
một con số), trường sẽ **được lưu một kiểu nhưng lại tìm hiển thị bằng kiểu khác** → nhìn như
"mất dữ liệu".

> 🔑 **Nguyên tắc vàng khi mở rộng:** luôn dùng **`$model->getTable()`** làm giá trị `type` ở
> **mọi** nơi (khai báo định nghĩa, nhập admin, validate, lưu, hiển thị). Đừng gõ tay chuỗi
> `'shop_customer'` ở chỗ này và `getTable()` ở chỗ kia — lệch tiền tố là lỗi phổ biến nhất.

## Độ phủ hiện tại — chỉ Khách hàng và Sản phẩm

Dù màn admin "Custom Field" cho phép chọn **bất kỳ bảng nào** (khoảng 60+ bảng `gp247_*`), nhưng
**chỉ 2 bảng thực sự có đủ hook** để trường hoạt động:

| Bảng (`type`) | Nhập ở admin (A) | Hiển thị storefront (D) | Lưu/validate | Trạng thái |
| --- | --- | --- | --- | --- |
| **`shop_customer`** (Khách hàng) | ✅ màn Quản lý khách hàng | ✅ Tài khoản → Sửa thông tin | ✅ | **Trọn vòng** |
| **`shop_product`** (Sản phẩm) | ✅ màn Quản lý sản phẩm | ❌ (chưa vẽ ra trang sản phẩm) | ✅ | **Chỉ trong admin** |
| Danh mục, Thương hiệu, Đơn hàng… (các bảng còn lại) | ❌ | ❌ | ❌ | **Chỉ nằm trong dropdown, chưa đấu dây** |

> ⚠️ **Cảnh báo cho chủ site:** nếu bạn tạo một trường cho một bảng **không phải customer/product**
> (ví dụ chọn "category"), trường đó sẽ **không hiện ra ở đâu cả** — nó thành một **định nghĩa mồ
> côi** nằm trong DB nhưng không màn nào đọc/ghi. Muốn dùng cho bảng khác, cần lập trình viên bổ
> sung hook theo hướng dẫn bên dưới.

## (Chủ site) Tạo một trường tùy chỉnh cho Khách hàng

Đây là đường dùng sẵn, không cần lập trình:

1. Đăng nhập admin, vào màn **Custom Field** (Trường tùy chỉnh).
2. Bấm **Thêm mới**. Ở ô **Type (bảng áp dụng)**, chọn **customer**.
3. Chọn **Option (kiểu ô)** — ví dụ `text` cho ô chữ, `select` cho ô chọn.
4. Nhập **Code** (mã, vd `tax-code`) và **Name** (tên hiển thị). Bật **Required** nếu bắt buộc.
5. Với kiểu `select`/`radio`/`checkbox`: nhập danh sách lựa chọn.
6. **Lưu**. Nếu thành công, trường mới sẽ xuất hiện:
   - Ở **admin → Quản lý khách hàng → sửa một khách** (thêm/sửa giá trị).
   - Ở **storefront → Tài khoản → Sửa thông tin** (khách tự nhập).

> 📌 **Lưu ý:** trang **Đăng ký** hiện **chưa** hiển thị các trường tùy chỉnh của khách (form đăng
> ký chưa có dữ liệu khách để "biết" bảng nào). Vì vậy hãy đặt các trường khách hàng ở mức **không
> bắt buộc**, hoặc để khách điền sau ở trang Sửa thông tin.

## (Lập trình viên) Bật Custom Field cho một bảng mới

Giả sử bạn muốn thêm Custom Field cho một đối tượng `X` (ví dụ Danh mục `ShopCategory`). Hãy làm
**đủ 4 việc** dưới đây; bỏ việc nào thì trường sẽ hụt ở khâu đó. Mẫu tham chiếu chuẩn nhất là màn
**Khách hàng** (`vendor/gp247/shop/src/Admin/Livewire/CustomerManager.php`) — cứ copy theo nó.

Trong toàn bộ ví dụ, ta gọi **`$type = (new XModel)->getTable()`** (vd `gp247_shop_category`) và
dùng đúng chuỗi này ở mọi nơi.

### Việc 1 — Tạo định nghĩa trường với `type` đúng

Định nghĩa (bản ghi trong `gp247_admin_custom_field`) phải có cột `type` **bằng tên bảng thật**
của `X`. Hai cách:

- **Qua admin:** vào màn Custom Field, chọn đúng bảng `X` ở ô Type.
- **Hoặc seed bằng code** (chắc chắn nhất, không lệ thuộc UI):

  ```php
  use GP247\Core\Models\AdminCustomField;
  use GP247\Shop\Models\ShopCategory;

  AdminCustomField::create([
      'type'     => (new ShopCategory)->getTable(), // vd "gp247_shop_category"
      'code'     => 'banner-note',
      'name'     => 'Ghi chú banner',
      'option'   => 'text',
      'required' => 0,
      'status'   => 1,
  ]);
  ```

> ✅ **Kiểm tra:** chạy `gp247_custom_field_list((new ShopCategory)->getTable())` phải trả về đúng
> định nghĩa vừa tạo. Nếu rỗng → cột `type` trong DB đang lệch với `getTable()` (sai tiền tố).

### Việc 2 — Cho model X đọc được giá trị (mắt xích B)

Đảm bảo `XModel` **dùng `ModelTrait`** để có sẵn `->getCustomFields()` và thuộc tính
`->custom_fields`. Đa số model GP247 đã có sẵn. Nếu chưa:

```php
use GP247\Core\Models\ModelTrait;

class ShopCategory extends Model
{
    use ModelTrait;
    // ...
}
```

Sau bước này: `$category->getCustomFields()` trả về `[code => ['text' => 'giá trị']]`.

### Việc 3 — Thêm ô nhập ở màn admin (mắt xích A)

Nếu `X` có màn admin dạng **Livewire manager** (giống Customer/Product), làm 4 thao tác trong
component:

1. `use` trait và khai báo `customFieldType()` trả về **`getTable()`** (không gõ tay chuỗi):

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

2. Nạp giá trị vào state khi mở form:
   - Khi **thêm mới**: gọi `$this->initCustomFields();`
   - Khi **sửa**: gọi `$this->loadCustomFields($category->id);`

3. Gộp luật bắt buộc vào `rules()` của component:

   ```php
   protected function rules(): array
   {
       return array_merge($otherRules, $this->customFieldRules());
   }
   ```

4. Lưu khi submit:

   ```php
   gp247_custom_field_update($this->customFieldsPayload(), (string) $category->id, $this->customFieldType());
   ```

5. Vẽ các ô ra Blade của màn. Trait để các helper ở mức `protected`, nên **mở một accessor public**
   cho view dùng (giống `CustomerManager::customFieldList()`):

   ```php
   /** @return iterable<mixed> */
   public function customFieldList(): iterable
   {
       return $this->customFieldDefs();
   }
   ```

   Rồi trong Blade, lặp `$this->customFieldList()` và bind vào `wire:model="customFields.{{ $field->code }}"`
   (copy nguyên khối markup từ `vendor/gp247/shop/src/Views/admin/customer-manager.blade.php` cho
   nhanh và đúng chuẩn TailAdmin).

> ℹ️ Nếu màn admin của `X` là **controller cổ điển** (không Livewire), dùng **mắt xích C**
> (`CustomFieldTrait`) thay cho trait A: gọi `getCustomFieldValidation()` khi validate và
> `handleCustomField()` khi lưu — cả hai đã tự lấy `type` qua `getTable()`.

### Việc 4 — Hiển thị ngoài storefront (mắt xích D, chỉ khi cần)

Nếu `X` có form phía khách (hiếm với category, nhưng cần với customer), thêm vào Blade của form:

```blade
@include($GP247TemplatePath.'.common.render_form_custom_field', ['object' => $category])
```

Và ở controller nhận POST của form đó:

```php
// Validate
$rules = gp247_custom_field_validate($rules, $category->getTable());

// Lưu sau khi tạo/cập nhật bản ghi
gp247_custom_field_update($request->input('fields', []), $category->id, $category->getTable());
```

> ⚠️ Blade `render_form_custom_field` phải **tồn tại trong template đang chạy**
> (`app/GP247/Templates/<Template>/common/`). GP247Front đã có sẵn bản Tailwind; template khác
> phải tự ship bản của mình, nếu không request sẽ báo thiếu view.

### Tóm tắt bất biến cần giữ

| Việc | Hàm/điểm chạm | Giá trị `type` phải là |
| --- | --- | --- |
| Tạo định nghĩa | `AdminCustomField.type` | `(new XModel)->getTable()` |
| Nhập admin (Livewire) | `customFieldType()` | `(new XModel)->getTable()` |
| Nhập admin (controller) | `CustomFieldTrait` | tự lấy `getTable()` |
| Validate/lưu | `gp247_custom_field_validate/update(..., $type)` | `$x->getTable()` |
| Hiển thị storefront | `render_form_custom_field` (`$object`) | `$object->getTable()` (tự tính) |

Chỉ cần **một** nơi lệch chuỗi `type` là trường sẽ không round-trip. Dùng `getTable()` xuyên suốt
là cách an toàn nhất.

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi tạo trường cho "category" trong admin nhưng không thấy nó đâu?**
Đúng như thiết kế hiện tại: chỉ **customer** và **product** đã được đấu dây. Trường cho các bảng
khác là "định nghĩa mồ côi" cho tới khi lập trình viên bổ sung hook (xem mục "Bật Custom Field cho
một bảng mới").

**Câu 2: Vì sao admin lại cho chọn tất cả các bảng nếu chỉ 2 bảng chạy?**
Danh sách bảng được lấy tự động từ DB (`SHOW TABLES`), không lọc theo "đã có hook hay chưa". Đây là
điểm dễ gây nhầm; hãy chỉ tạo trường cho customer/product trừ khi bạn đã tự thêm hook cho bảng khác.

**Câu 3: Trường của khách hàng có hiện ở trang Đăng ký không?**
**Chưa.** Trang đăng ký chưa có dữ liệu khách để xác định bảng, nên form không vẽ các trường tùy
chỉnh. Khách điền chúng ở **Tài khoản → Sửa thông tin**. Vì vậy đừng đặt trường khách hàng ở mức
"bắt buộc" nếu bạn kỳ vọng thu tại bước đăng ký.

**Câu 4: Tôi nhập giá trị rồi lưu nhưng mở lại thấy trống — vì sao?**
Gần như luôn là do **chuỗi `type` bị lệch** giữa lúc lưu và lúc hiển thị (một nơi có tiền tố
`gp247_`, nơi kia không). Kiểm tra tất cả điểm chạm đều dùng `$model->getTable()`. Xem "Nguyên tắc
vàng khi mở rộng".

**Câu 5: `option` (kiểu ô) hỗ trợ những gì?**
14 kiểu: `text`, `textarea`, `number`, `date`, `month`, `week`, `time`, `email`, `password`, `url`,
`color`, `select`, `radio`, `checkbox`. Ba kiểu cuối (`select`/`radio`/`checkbox`) cần danh sách
lựa chọn lưu ở cột `default` dạng JSON.

**Câu 6: Giá trị checkbox nhiều lựa chọn lưu thế nào?**
Lưu thành **một chuỗi CSV** (các key ngăn cách bằng dấu phẩy) trong cột `text`. Vì vậy key của
lựa chọn **không được chứa dấu phẩy** — hệ thống đã chặn bằng validation, nhưng khi seed tay bạn
cũng nên tránh.

**Câu 7: Tôi có phải tự viết validation cho từng kiểu không?**
Không. `gp247_custom_field_rules()` đã sinh luật theo kiểu (email đúng định dạng email, số phải là
số, select/radio/checkbox phải nằm trong danh sách lựa chọn…). Bạn chỉ cần gọi
`gp247_custom_field_validate()` (controller) hoặc `customFieldRules()` (Livewire).

**Câu 8: Xóa một định nghĩa trường thì giá trị đã nhập có bị xóa theo không?**
Có. Model `AdminCustomField` có sự kiện `deleting` cascade xóa các bản ghi giá trị
(`admin_custom_field_detail`) tương ứng, tránh dữ liệu rác.

**Câu 9: Thêm hook cho bảng mới có phải sửa code lõi (core) không?**
Không nên. Theo quy ước GP247, bạn **cắm** vào core (dùng trait/hàm có sẵn) chứ không sửa core.
Component/blade của bảng mới đặt ở package tương ứng (shop/front/plugin), tái dùng
`HasCustomFields`, `gp247_custom_field_*` và `render_form_custom_field`.

**Câu 10: Làm sao kiểm tra nhanh một bảng đã đấu dây đủ chưa?**
Grep xem có nơi nào `use HasCustomFields` (hoặc `CustomFieldTrait`) khai báo `type` của bảng đó,
có gọi `gp247_custom_field_update(...)` khi lưu, và (nếu cần hiển thị khách) có `@include` blade
`render_form_custom_field`. Thiếu mắt xích nào thì bổ sung mắt xích đó.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-14 · ✍️ **Tác giả (Author):** GP247</sub>
