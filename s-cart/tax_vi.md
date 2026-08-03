> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./tax.md)

# Thuế (Tax) trong GP247

## Giới thiệu
Tài liệu này giải thích cách tính năng **thuế** hoạt động trong GP247 (từ phiên bản 2) và hướng dẫn chủ
cửa hàng tự cấu hình thuế cho shop của mình. Dành cho người **không rành kỹ thuật**: đọc xong bạn sẽ biết
thuế được tính thế nào, tạo mức thuế ở đâu, gán thuế cho sản phẩm ra sao và thuế hiển thị ở những trang nào.

---

## 1. Thuế trong GP247 hoạt động thế nào?

Có 4 điều quan trọng cần nắm:

1. **Thuế gán theo từng sản phẩm.** Mỗi sản phẩm có thể mang một mức thuế riêng (ví dụ 8%, 10%). Bạn tạo
   sẵn các "mức thuế" rồi gán cho sản phẩm.
2. **Giá niêm yết là giá CHƯA gồm thuế** (kiểu *exclusive*). Thuế được **cộng thêm** khi tính tiền, chứ
   không nằm sẵn trong giá bán. Ví dụ: sản phẩm giá 100.000đ, thuế 8% → khách trả 108.000đ.
3. **Bật/tắt được cho toàn shop.** Bạn có thể tắt thuế hoàn toàn, hoặc đặt một mức thuế **mặc định** áp
   dụng chung.
4. **Mỗi sản phẩm chỉ dùng một mức thuế** (không cộng dồn nhiều loại thuế). GP247 (v2) tính thuế theo sản
   phẩm, **không** tính theo quốc gia/vùng/địa chỉ giao hàng.

> **Thuật ngữ:** *Mức thuế* là một dòng gồm **Tên** (ví dụ "VAT 8%") và **Giá trị** (con số phần trăm, ví
> dụ `8`). Bạn tạo mức thuế một lần, sau đó gán cho nhiều sản phẩm.

Mỗi sản phẩm có 3 lựa chọn thuế:

| Lựa chọn | Ý nghĩa |
| --- | --- |
| **Không dùng thuế** (None) | Sản phẩm này không tính thuế. |
| **Tự động** (Auto) | Dùng đúng **mức thuế mặc định** của shop (bạn đặt ở Bước 2 bên dưới). |
| **Một mức cụ thể** | Luôn dùng đúng mức thuế bạn chọn cho riêng sản phẩm này. |

---

## 2. Các bước cấu hình thuế (làm theo lần lượt)

### Bước 1 — Tạo mức thuế
1. Đăng nhập trang **quản trị (admin)** của shop.
2. Vào khu vực **Thuế (Tax)** trong menu quản trị.
3. Bấm thêm mới, điền:
   - **Tên (Name):** đặt tên dễ hiểu, ví dụ `VAT 8%` hoặc `Thuế tiêu chuẩn`.
   - **Giá trị (Value):** nhập con số phần trăm, ví dụ `8` (nghĩa là 8%). Có thể nhập số lẻ như `8.5`.
4. Lưu lại.

   Nếu thành công, mức thuế mới sẽ xuất hiện trong danh sách bên phải màn hình Thuế.

> Bạn có thể tạo nhiều mức thuế (ví dụ 5%, 8%, 10%) rồi gán cho từng nhóm sản phẩm khác nhau.

### Bước 2 — Đặt mức thuế mặc định cho shop
1. Vào **Cấu hình cửa hàng (Shop Configuration)** trong menu quản trị.
2. Tìm mục thuế (khoá cấu hình là **`product_tax`**).
3. Chọn một trong các giá trị:
   - **Một mức thuế cụ thể** (ví dụ "VAT 8%") → đây là mức mặc định áp cho các sản phẩm để **Tự động (Auto)**.
   - **Không dùng (None)** → **tắt thuế toàn shop** (mọi sản phẩm đều không tính thuế, kể cả để Auto).
4. Lưu lại.

> Đây là công tắc quan trọng nhất: nếu đặt **Không dùng**, toàn bộ shop sẽ không tính thuế dù sản phẩm
> gán mức nào đi nữa.

### Bước 3 — Gán thuế cho sản phẩm
1. Vào **Sản phẩm (Product)**, mở sản phẩm cần chỉnh.
2. Tìm ô chọn **Thuế (Tax)** trong form sản phẩm, chọn:
   - **Tự động (Auto)** — dùng mức mặc định ở Bước 2 (khuyên dùng cho đa số sản phẩm).
   - **Không dùng (None)** — sản phẩm này miễn thuế.
   - **Một mức cụ thể** — ép dùng đúng mức đó cho sản phẩm này.
3. Lưu sản phẩm.

### Bước 4 — Kiểm tra lại ngoài cửa hàng
1. Mở trang sản phẩm vừa chỉnh ở **giao diện khách (storefront)**.
2. Thêm vào giỏ và vào trang **Giỏ hàng / Thanh toán**.
3. Kiểm tra dòng **Thuế (Tax)** trong bảng tổng tiền.

   Nếu đúng, tổng tiền sẽ bằng: *Tạm tính + Thuế + Phí ship − Giảm giá*.

---

## 3. Thuế hiển thị ở những đâu?

- **Trang sản phẩm (storefront):** giá hiển thị đã tính kèm thuế (khi thuế đang bật).
- **Giỏ hàng & trang thanh toán:** có một dòng **Thuế** riêng trong bảng tổng tiền.
- **Đơn hàng của khách (tài khoản):** phần tổng đơn có dòng thuế.
- **Trang quản trị — chi tiết đơn & hoá đơn (invoice):** có dòng thuế trong bảng tổng.

---

## 4. Một vài lưu ý quan trọng

- **Đổi mức thuế không làm đổi đơn hàng cũ.** Các đơn đã đặt trước đó giữ nguyên số thuế đã ghi; thay đổi
  chỉ ảnh hưởng đơn **mới**.
- **Tắt thuế toàn shop** nhanh nhất bằng cách đặt `product_tax` = **Không dùng** (Bước 2), không cần sửa
  từng sản phẩm.
- **Số tiền thuế được làm tròn tới 2 chữ số thập phân** theo từng dòng sản phẩm, nên tổng thuế của đơn luôn
  khớp với tổng các dòng.

---

## 5. Logic xử lý & các hàm thuế (cho lập trình viên)

> Mục này dành cho **lập trình viên**. Người dùng thông thường có thể bỏ qua.

### Luồng xử lý: tạo → xác định → tính → lưu

```
TẠO mức thuế (admin)          TaxManager → ShopTax (name + value%)
        │
XÁC ĐỊNH thuế của sản phẩm    config product_tax → ShopTax::checkStatus() (bật/tắt + mức mặc định)
        │                     product.tax_id (none|auto|<id>) → getTaxId() → getTaxValue() = %
        ▼
TÍNH (line-level, 1 helper)   gp247_line_tax(price+option, qty, rate) = round(...×rate/100, 2)
        │
   ├─ Giỏ hàng        ShopCurrency::sumCartCheckout() → subTotal, subTotalWithTax
   ├─ Tổng đơn        ShopOrderTotal::processDataTotal() → dòng code='tax' = subTotalWithTax − subTotal
   ├─ Lưu đơn (SF)    ShopOrder::createOrder() → shop_order_detail.tax per-line + shop_order.tax
   └─ Sửa đơn (admin) AdminOrder::updateSubTotal() → shop_order.tax = Σ detail.tax
```

### Các hàm / method xử lý thuế

**Tạo & quản lý mức thuế**
- `TaxManager` (Livewire) — màn CRUD mức thuế; validate `value: numeric|min:0`; ghi vào `ShopTax`.
- `ShopTax` — model bảng `shop_tax` (`name`, `value` kiểu `decimal(8,4)`, cast `float`):
  - `getListAll()` — danh sách mức thuế kèm `none`/`auto` (dùng cho dropdown).
  - `getArrayId()` / `getArrayValue()` — map `id → value%` để tra nhanh.
  - `checkStatus()` — đọc `gp247_config('product_tax')`; trả `0` khi tắt/`none`/không hợp lệ, ngược lại trả `id` mức mặc định.

**Xác định thuế của một sản phẩm** (`ShopProduct`)
- `getTaxId()` — chọn `id` mức thuế: tắt → `0`; `auto` → `checkStatus()`; `id` cụ thể → trả về.
- `getTaxValue()` — trả `%` từ `getArrayValue()[$taxId]`.
- `getFinalPriceTax()` — giá **một đơn vị** đã gồm thuế (dùng để hiển thị).

**Công thức làm tròn (helper dùng chung)** — `Library/Helpers/cart_store.php`
- `gp247_line_tax($unitPriceWithOption, $qty, $rate)` — làm tròn **theo dòng** (line-level), là **nguồn công thức chuẩn duy nhất**.
- `gp247_tax_price($price, $tax)` — giá một đơn vị đã gồm thuế = `round(price×(100+tax)/100, 2)` (chỉ dùng hiển thị).

**Tính & tổng hợp**
- `ShopCurrency::sumCartCheckout()` — duyệt giỏ; trả `subTotal` (chưa thuế) và `subTotalWithTax` (đã cộng `gp247_line_tax` từng dòng).
- `ShopOrderTotal::processDataTotal()` — dựng các dòng tổng; `tax = subTotalWithTax − subTotal`.
- `ShopOrderTotal::sumValueTotal($code, $dataTotal)` — lấy giá trị một dòng tổng (ví dụ `'tax'`).

**Ghi vào đơn hàng**
- `ShopOrder::createOrder()` — ghi `shop_order_detail.tax` **theo dòng** qua `gp247_line_tax` (áp cho mọi đường tạo đơn, gồm storefront).
- `AdminOrder::updateSubTotal()` — tính lại `shop_order.tax = Σ detail.tax` khi admin thêm/sửa dòng đơn.

**Cấu hình**
- `gp247_config('product_tax')` — khoá bật/tắt + mức thuế mặc định (nhóm `gp247_cart`), chỉnh ở màn Cấu hình cửa hàng.

### Bất biến (invariant)
Với đơn hàng mới:

```
shop_order.tax  ==  Σ shop_order_detail.tax  ==  (subTotalWithTax − subTotal)
```

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi muốn tắt thuế cho cả shop thì làm sao?**
Vào Cấu hình cửa hàng, đặt `product_tax` = **Không dùng (None)**. Cả shop sẽ ngừng tính thuế ngay, không
cần sửa từng sản phẩm.

**Câu 2: "Tự động (Auto)" ở ô thuế của sản phẩm nghĩa là gì?**
Nghĩa là sản phẩm đó dùng **mức thuế mặc định** của shop (mức bạn chọn ở Bước 2). Đổi mặc định thì mọi sản
phẩm để Auto tự đổi theo.

**Câu 3: Giá tôi nhập cho sản phẩm đã bao gồm thuế chưa?**
Chưa. GP247 tính thuế kiểu **cộng thêm** (exclusive): giá bạn nhập là giá **chưa thuế**, thuế được cộng
vào khi tính tiền.

**Câu 4: Thuế của tôi là 8,5% (có số lẻ) thì nhập được không?**
Được. Ô **Giá trị** nhận cả số lẻ, ví dụ nhập `8.5` cho 8,5%.

**Câu 5: Một sản phẩm có gán được nhiều loại thuế cùng lúc không?**
Không. Mỗi sản phẩm chỉ dùng **một** mức thuế.

**Câu 6: GP247 có tính thuế khác nhau theo quốc gia/vùng của khách không?**
Không. Ở v2, thuế tính **theo sản phẩm**, không theo địa chỉ giao hàng hay quốc gia.

**Câu 7: Tôi đổi mức thuế thì các đơn hàng cũ có bị tính lại không?**
Không. Đơn cũ giữ nguyên số thuế lúc đặt; thay đổi chỉ áp cho đơn mới.

**Câu 8: Sản phẩm tôi để "Không dùng (None)" nhưng shop vẫn có thuế mặc định thì sao?**
Sản phẩm để **None** sẽ **không** bị tính thuế, kể cả khi shop có mức mặc định.

**Câu 9: Tôi tạo mức thuế nhưng chưa thấy áp vào sản phẩm nào?**
Tạo mức thuế mới **chưa** tự áp cho sản phẩm. Bạn cần đặt nó làm mặc định (Bước 2) hoặc gán trực tiếp cho
sản phẩm (Bước 3).

**Câu 10: Tổng thuế trên đơn có khớp với từng dòng sản phẩm không?**
Có. Thuế được tính và làm tròn theo **từng dòng**, nên tổng thuế của đơn luôn bằng tổng thuế các dòng.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-04 · ✍️ **Tác giả (Author):** GP247</sub>
