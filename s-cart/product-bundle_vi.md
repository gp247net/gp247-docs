> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./product-bundle.md)

# Sản phẩm gói (Bundle / Combo) trong GP247

## Giới thiệu
Tài liệu này giải thích tính năng **sản phẩm gói (bundle/combo)** trong GP247 (từ phiên bản 2) và hướng
dẫn chủ cửa hàng tự tạo một sản phẩm combo gồm nhiều sản phẩm con. Dành cho người **không rành kỹ thuật**:
đọc xong bạn sẽ hiểu bundle khác gì sản phẩm thường, cách bật tính năng, cách tạo combo, và cách nó tính
giá / trừ kho.

---

## 1. Bundle là gì? Ba loại sản phẩm trong GP247

Trong GP247, mỗi sản phẩm thuộc **một trong ba loại (kind)**:

| Loại | Ý nghĩa | Bán được? | Giá | Tồn kho |
| --- | --- | --- | --- | --- |
| **Đơn lẻ (Single)** | Sản phẩm thường, đứng một mình | Có | Giá riêng của nó | Kho riêng |
| **Gói / Combo (Build)** | Một sản phẩm bán như **một gói**, bên trong gồm nhiều **sản phẩm con** kèm số lượng | Có (như sản phẩm thường) | **Giá riêng của gói** (bạn tự đặt) | Gói có kho riêng; **khi bán sẽ trừ kho từng sản phẩm con** |
| **Nhóm (Group)** | Chỉ **gom nhóm để trưng bày**, không bán trực tiếp | **Không** | Không (hiện chữ, không hiện giá) | Không quản lý |

> **Bundle chính là loại "Gói / Combo (Build)".** Ví dụ: gói "Bộ quà Tết" = 1 hộp bánh + 2 chai nước +
> 1 túi giấy. Khách mua **1 gói**, hệ thống tự trừ kho: 1 hộp bánh, 2 chai nước, 1 túi giấy.

**Điểm quan trọng nhất cần nhớ:**
- Giá của gói là **giá bạn tự đặt cho gói**, GP247 **không** tự cộng giá các sản phẩm con lại. Bạn tự
  quyết định giá combo (thường rẻ hơn mua lẻ để hấp dẫn khách).
- Khi bán 1 gói, kho của **từng sản phẩm con bị trừ** theo số lượng khai báo trong gói.

---

## 2. Bật tính năng sản phẩm gói

Tính năng phân loại sản phẩm (Single/Build/Group) được điều khiển bằng một công tắc trong cấu hình. Nếu
công tắc tắt, **mọi sản phẩm đều là Đơn lẻ** và bạn sẽ **không thấy** lựa chọn tạo gói.

1. Đăng nhập trang **quản trị (admin)**.
2. Vào **Cấu hình cửa hàng (Shop Configuration)**.
3. Tìm tuỳ chọn kiểu cấu trúc sản phẩm (khoá cấu hình là **`product_kind`**, nhãn kiểu "Use structure
   type" / dùng loại cấu trúc).
4. **Bật** tuỳ chọn này, rồi lưu.

   Nếu thành công, khi mở form thêm/sửa sản phẩm bạn sẽ thấy phần chọn **loại sản phẩm** với 3 thẻ:
   Đơn lẻ, Gói (Build), Nhóm (Group).

> Nếu tắt công tắc này về sau, các sản phẩm sẽ bị coi là Đơn lẻ và phần chọn loại bị ẩn (dữ liệu gói cũ
> vẫn còn trong cơ sở dữ liệu, chỉ là không hiển thị lựa chọn).

---

## 3. Các bước tạo một sản phẩm gói (Bundle)

> Chuẩn bị: các **sản phẩm con** (ví dụ hộp bánh, chai nước…) nên đã được tạo sẵn dưới dạng sản phẩm Đơn
> lẻ, vì bạn sẽ tìm chúng theo tên/SKU khi lắp vào gói.

1. Vào **Sản phẩm (Product)** → **Thêm mới**.
2. Ở phần **Loại sản phẩm**, chọn thẻ **Gói (Build)**.
3. Điền thông tin cơ bản như sản phẩm thường: **Tên**, **SKU**, **Giá** (giá bán của cả gói), **Tồn kho**
   của gói, mô tả…
4. Mở tab **Cấu thành / Composition** (tab này chỉ hiện khi loại là Gói hoặc Nhóm).
5. Trong ô tìm kiếm, gõ **tên hoặc mã (SKU)** của sản phẩm con, chọn nó trong danh sách gợi ý rồi bấm thêm.
6. Với mỗi sản phẩm con vừa thêm, nhập **Số lượng** có trong gói (ví dụ `2` chai nước). Lặp lại cho tất cả
   sản phẩm con.
7. Bấm **Lưu**.

   Nếu thành công, sản phẩm gói xuất hiện trong danh sách sản phẩm; mở lại tab Cấu thành sẽ thấy đủ các
   sản phẩm con kèm số lượng.

> Mẹo: bạn có thể sửa gói bất cứ lúc nào — thêm/bớt sản phẩm con hoặc đổi số lượng, rồi Lưu lại. Danh sách
> con cũ sẽ được ghi đè bằng danh sách mới.

---

## 4. Giá và tồn kho của sản phẩm gói

- **Giá:** là **giá bạn nhập cho gói** (có thể đặt giá khuyến mãi như sản phẩm thường). GP247 **không** tự
  tính tổng giá các con — bạn toàn quyền định giá combo.
- **Tồn kho khi bán:** khi khách mua **N gói**, hệ thống trừ:
  - Kho của **gói** đi N, và
  - Kho của **từng sản phẩm con** đi `N × (số lượng con trong gói)`.
  - Ví dụ: gói chứa 2 chai nước, khách mua 3 gói → trừ 6 chai nước khỏi kho.
- **Khuyến mãi:** áp dụng được cho sản phẩm gói (giống sản phẩm Đơn lẻ).
- **Thuộc tính/biến thể (size, màu…):** **không** áp dụng cho sản phẩm gói — biến thể chỉ dành cho sản
  phẩm Đơn lẻ.

> Lưu ý về kho: vì bán 1 gói sẽ rút kho các con, hãy đảm bảo các sản phẩm con còn đủ hàng. Nếu một con hết
> kho, hãy kiểm tra lại tồn kho của gói cho hợp lý.

---

## 5. Sản phẩm gói hiển thị thế nào với khách?

- Ở **trang chi tiết sản phẩm** (storefront), sản phẩm gói hiển thị một **khối "Bundle"** liệt kê các sản
  phẩm con: ảnh, **số lượng** mỗi con, tên con, và dấu **`+`** xen giữa để thể hiện "gói gồm A + B + C".
- Khách **thêm vào giỏ và mua như sản phẩm thường** (không cần chọn từng con).
- Giá hiển thị là **giá của gói**.

> Khác với loại **Nhóm (Group)**: Nhóm không có nút mua, chỉ hiện các sản phẩm thành viên để khách bấm
> chuyển sang xem — không phải combo bán được.

---

## 6. Chi tiết kỹ thuật (cho lập trình viên)

> Mục này dành cho **lập trình viên**. Người dùng thông thường có thể bỏ qua.

**Hằng số loại sản phẩm** — `vendor/gp247/shop/src/Library/Const.php`:
```php
define('GP247_PRODUCT_SINGLE', 0);
define('GP247_PRODUCT_BUILD', 1);   // bundle/combo
define('GP247_PRODUCT_GROUP', 2);
```
Lưu ở cột `shop_product.kind`.

**Bảng dữ liệu**
- `shop_product_build` — quan hệ gói→con: `build_id` (id gói cha), `product_id` (id con), `quantity`
  (số lượng con, `decimal(15,2)`), khoá chính kép `(build_id, product_id)`.
- `shop_product_group` — quan hệ nhóm→thành viên: `group_id`, `product_id` (khoá chính kép). **Không có
  cột `quantity`** (khác Build).

**Model & quan hệ** (`ShopProduct`)
- `builds()` → `hasMany(ShopProductBuild, 'build_id', 'id')`; mỗi phần tử có `->product` (con) và `->quantity`.
- `groups()` → `hasMany(ShopProductGroup, 'group_id', 'id')`.
- `ShopProductBuild::product()` / `ShopProductGroup::product()` → `belongsTo(ShopProduct, 'product_id')`.

**Giá & tồn kho** (`ShopProduct`)
- `getFinalPrice()` — trả **giá riêng của sản phẩm cha** (hoặc giá khuyến mãi), **không** cộng dồn giá con.
- `updateStock($productId, $qtyChange)` — trừ kho cha; nếu `kind == GP247_PRODUCT_BUILD` thì lặp `builds`
  trừ kho từng con theo `qtyChange × build->quantity`.
- `allowSale()` — loại **Group** khỏi bán (`kind != GP247_PRODUCT_GROUP`).

**Admin** (Livewire `ProductManager` + trait `HasProductComposition`)
- Bộ chọn kind gated bởi `gp247_config('product_kind')`: khi `= 0` ẩn selector và ép `kind = SINGLE`.
- `persistComposition()` — **xoá hết rồi tạo lại** các dòng `builds()->create(['product_id','quantity'])`
  (hoặc `groups()->create([...])`).
- Tab "composition" chỉ bật khi kind là Build/Group; tab "variants" chỉ khi Single.

**Storefront** — `shop_product_detail.blade.php`: khối Bundle lặp `$product->builds`, badge số lượng dùng
`gp247_qty_format($build->quantity)`.

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi không thấy chỗ chọn loại "Gói (Build)" khi tạo sản phẩm?**
Bạn cần bật tuỳ chọn `product_kind` trong Cấu hình cửa hàng trước (xem Mục 2). Nếu tắt, mọi sản phẩm mặc
định là Đơn lẻ và phần chọn loại bị ẩn.

**Câu 2: Giá gói có tự cộng từ giá các sản phẩm con không?**
Không. Bạn **tự đặt giá** cho gói. GP247 không tự tính tổng — bạn toàn quyền định giá combo.

**Câu 3: Khách mua 1 gói thì kho trừ thế nào?**
Trừ 1 ở kho gói, và trừ ở kho **từng sản phẩm con** theo số lượng khai báo (ví dụ gói có 2 chai → trừ 2 chai).

**Câu 4: Bundle (Build) khác Nhóm (Group) ở chỗ nào?**
Build là **combo bán được**, có giá và trừ kho con. Group chỉ **gom nhóm trưng bày**, không bán, không giá,
không thêm vào giỏ.

**Câu 5: Tôi gán được thuộc tính (size, màu) cho sản phẩm gói không?**
Không. Thuộc tính/biến thể chỉ dành cho sản phẩm Đơn lẻ. Gói không dùng biến thể.

**Câu 6: Sản phẩm con phải tạo trước không?**
Nên có trước, vì bạn tìm sản phẩm con theo tên/SKU khi lắp vào gói. Hãy tạo các sản phẩm con (Đơn lẻ) trước.

**Câu 7: Tôi đặt khuyến mãi cho gói được không?**
Được. Sản phẩm gói dùng khuyến mãi giống sản phẩm Đơn lẻ.

**Câu 8: Sửa gói (thêm/bớt con, đổi số lượng) có được không?**
Được. Mở sản phẩm gói, vào tab Cấu thành, chỉnh rồi Lưu. Danh sách con cũ sẽ được thay bằng danh sách mới.

**Câu 9: Một sản phẩm con hết kho thì gói còn bán được không?**
Hệ thống vẫn trừ kho con khi bán; bạn nên kiểm tra tồn kho các con và tồn kho gói cho khớp thực tế để tránh
bán quá số hàng có.

**Câu 10: Tôi tắt `product_kind` sau khi đã tạo gói thì sao?**
Các sản phẩm sẽ bị coi là Đơn lẻ và phần chọn loại bị ẩn. Dữ liệu gói cũ vẫn nằm trong cơ sở dữ liệu, chỉ
là không còn hiển thị lựa chọn loại trên form.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-04 · ✍️ **Tác giả (Author):** GP247</sub>
