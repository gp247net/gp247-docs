> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./currency.md)

# Tiền tệ (Currency) trong GP247

## Giới thiệu
Tài liệu này giải thích cách tính năng **tiền tệ (currency)** hoạt động trong GP247/S-Cart và hướng dẫn
chủ cửa hàng tự cấu hình các đồng tiền cho shop của mình. Dành cho người **không rành kỹ thuật**: đọc xong
bạn sẽ biết một đồng tiền gồm những gì, **đồng tiền gốc** (nơi lưu giá sản phẩm) khác gì đồng tiền hiển thị,
tỉ giá quy đổi hoạt động ra sao, khách xem giá bằng đồng tiền nào, tạo/sửa đồng tiền ở đâu, cách **đổi đồng
tiền gốc** mà không làm sai giá, và vì sao có lúc không xóa/không sửa được một đồng tiền.

> ⭐ **Áp dụng từ gp247/shop phiên bản 2.1.** Các mục có nhãn **(v2.1)** trong tài liệu này — **đồng tiền gốc
> tường minh**, **đổi đồng tiền gốc (rebase) bảo toàn giá trị**, và **ghi chú đơn vị tiền cạnh ô nhập tiền** —
> là tính năng **mới có từ gp247/shop 2.1**. Nếu shop của bạn đang chạy phiên bản cũ hơn, các phần còn lại
> vẫn đúng nhưng những mục gắn nhãn (v2.1) có thể chưa xuất hiện.

---

## 1. Tiền tệ trong GP247 hoạt động thế nào?

Có 6 điều quan trọng cần nắm:

1. **Shop có thể dùng nhiều đồng tiền.** Bạn khai báo một danh sách đồng tiền (ví dụ USD, VND). Mỗi đồng
   tiền có thể **bật** (khách dùng được) hoặc **tắt** (ẩn đi).
2. **Giá sản phẩm được lưu theo MỘT "đồng tiền gốc".** Bạn chỉ nhập giá sản phẩm một lần, theo **đồng tiền
   gốc** (base currency — GP247 gọi vui là đơn vị "gốc"/"bit" của cửa hàng). Khi hiển thị sang đồng tiền
   khác, hệ thống **tự quy đổi** bằng **tỉ giá (exchange rate)** của đồng tiền đó.
3. **Đồng tiền gốc là tường minh (v2.1).** Có đúng **một** đồng tiền được đánh dấu là **Gốc** (huy hiệu
   "Gốc" màu xanh trong danh sách). Đồng tiền gốc **luôn có tỉ giá `1`**, **luôn bật**, và **bị khóa
   sửa/xóa** — bạn chỉ đổi nó qua chức năng **Đổi đồng tiền gốc** (xem mục 4). Trước 2.1, đồng gốc chỉ được
   "ngầm hiểu" là đồng có tỉ giá `1`, dễ nhầm khi shop có 0 hoặc nhiều đồng cùng tỉ giá `1`.
4. **Mỗi đồng tiền có cách hiển thị riêng.** Gồm: **ký hiệu** (ví dụ `$`, `₫`), **vị trí ký hiệu** (đứng
   trước hay sau số tiền), **số chữ số thập phân** (precision — ví dụ USD 2 chữ số `10.00`, VND 0 chữ số
   `250.000`) và **dấu ngăn cách phần nghìn**.
5. **Có một đồng tiền hiển thị mặc định của cửa hàng.** Đây là đồng tiền khách nhìn thấy đầu tiên khi vào
   shop. **Lưu ý:** đây là khái niệm **khác** với đồng tiền gốc ở điểm 3 — đồng gốc là *nơi lưu giá*, còn
   đồng mặc định là *đồng khách thấy trước*. Thường hai đồng này trùng nhau, nhưng không bắt buộc. Nếu shop
   bật nhiều đồng tiền, khách có thể **tự đổi** đồng tiền bằng nút chuyển đổi trên giao diện.
6. **Đơn hàng ghi nhớ đồng tiền lúc đặt.** Khi khách đặt hàng, đơn sẽ **lưu lại** đồng tiền và tỉ giá tại
   thời điểm đó. Sau này bạn đổi tỉ giá thì **đơn cũ không đổi** — nó vẫn hiển thị đúng như lúc khách mua.

> **Thuật ngữ:** *Tỉ giá (exchange rate)* là con số quy đổi từ **giá gốc** sang đồng tiền đang xem. Đồng
> tiền gốc có tỉ giá `1`. Ví dụ giá gốc tính bằng USD (tỉ giá `1`), còn VND để tỉ giá `25000` → một món giá
> gốc `2` sẽ hiển thị `50.000 ₫` khi khách xem bằng VND.

---

## 2. Các bước cấu hình tiền tệ (làm theo lần lượt)

### Bước 1 — Mở màn quản lý Tiền tệ
1. Đăng nhập trang **quản trị (admin)** của shop.
2. Vào menu **Cấu hình → Tiền tệ (Currency)** (biểu tượng đồng tiền).

   Nếu thành công, bạn thấy màn hình chia 2 phần: **form nhập bên trái** và **danh sách đồng tiền bên phải**.
   Đồng tiền đang là **Gốc** có huy hiệu **"Gốc"** màu xanh ở cột tên (v2.1).

> Bạn cần quyền **`admin_currency`** mới vào được màn này. Nếu không thấy menu, nhờ quản trị viên cấp quyền.

### Bước 2 — Thêm một đồng tiền mới
1. Bấm **Thêm mới (Add new)**.
2. Điền các ô sau:
   - **Tên (Name):** tên dễ hiểu, ví dụ `US Dollar` hoặc `Việt Nam Đồng`.
   - **Mã (Code):** mã ngắn, **không trùng** với đồng tiền khác, ví dụ `USD`, `VND`, `EUR`.
   - **Ký hiệu (Symbol):** ký hiệu hiển thị, ví dụ `$`, `₫`, `€`.
   - **Tỉ giá (Exchange rate):** số quy đổi từ giá gốc, **phải lớn hơn 0**. (Đồng tiền gốc luôn để `1` và
     ô này bị khóa — xem mục 3.)
   - **Số chữ số thập phân (Precision):** số lẻ hiển thị, từ `0` đến `8`. USD thường `2`, VND thường `0`.
   - **Hiện ký hiệu đầu tiên (Symbol first):** chọn có/không. Có → `$10.00`; Không → `250.000 ₫`.
   - **Ngăn cách phần nghìn (Thousands separator):** dấu ngăn cách, ví dụ `,` (kiểu Anh) hoặc `.` (kiểu Việt).
   - **Trạng thái (Status):** bật để khách dùng được, tắt để ẩn.
   - **Thứ tự (Sort):** số càng nhỏ đứng càng trước trong danh sách chọn.
3. Bấm **Lưu**.

   Nếu thành công, đồng tiền mới xuất hiện trong danh sách bên phải.

> **Về dấu thập phân:** hệ thống tự suy ra dấu thập phân **ngược với** dấu ngăn cách phần nghìn. Nếu bạn
> chọn phần nghìn là `,` thì dấu thập phân là `.` (ví dụ `1,234.50`); nếu chọn phần nghìn là `.` thì dấu
> thập phân là `,` (ví dụ `1.234,50`).

### Bước 3 — Đặt đồng tiền hiển thị mặc định của cửa hàng
1. Vào **Cấu hình → Thông tin website / Cửa hàng** (màn Website Info).
2. Tìm ô chọn **Tiền tệ (Currency)** và chọn đồng tiền mặc định cho shop.
3. Lưu lại.

> Đây là đồng tiền khách **nhìn thấy đầu tiên** khi mở shop (khác với đồng tiền gốc — nơi lưu giá). Ô này
> chỉ cho chọn trong các đồng tiền **đang bật**.

### Bước 4 — (Tùy chọn) Cho khách tự đổi đồng tiền
- Nếu shop bật **từ 2 đồng tiền trở lên**, giao diện khách sẽ có **nút chuyển đổi tiền tệ** (currency
  switcher) ở đầu trang hoặc chân trang. Khách bấm chọn, giá toàn shop hiển thị lại theo đồng tiền đó.
- Lựa chọn của khách được nhớ trong phiên truy cập của họ; khách khác không bị ảnh hưởng.

### Bước 5 — Kiểm tra lại ngoài cửa hàng
1. Mở shop ở **giao diện khách (storefront)**.
2. Đổi thử sang một đồng tiền khác bằng nút chuyển đổi.
3. Xem giá sản phẩm, giỏ hàng có đổi ký hiệu và số chữ số thập phân đúng như bạn cấu hình không.

---

## 3. Đồng tiền gốc — vì sao bị khóa? (v2.1)

**Đồng tiền gốc** là đơn vị mà **toàn bộ giá sản phẩm, giá vốn (cost), phụ phí thuộc tính và giá khuyến mãi**
được lưu trong cơ sở dữ liệu. Nói cách khác, mọi con số tiền bạn nhập cho sản phẩm đều được hiểu là "bằng
đồng tiền gốc". Vì nó là "chuẩn đo" của cả kho hàng nên GP247 2.1 bảo vệ nó chặt:

- **Luôn có đúng một đồng gốc**, đánh dấu bằng huy hiệu **"Gốc"** trong danh sách.
- **Tỉ giá của đồng gốc luôn = `1`** (không sửa được) — vì mọi tỉ giá khác đo *so với* nó.
- **Đồng gốc luôn ở trạng thái bật.**
- **Không sửa, không xóa đồng gốc trực tiếp.** Nút **Sửa** và **Xóa** của dòng đồng gốc bị ẩn/khóa. Muốn
  chuyển "chuẩn đo" sang đồng khác, dùng chức năng **Đổi đồng tiền gốc** ở mục 4 — không tự tay sửa cờ gốc
  hay tỉ giá.

> **Vì sao không cho sửa tay?** Nếu ai đó đổi đồng gốc mà không đụng tới các con số giá đã lưu, cả kho hàng
> sẽ bị "đọc nhầm đơn vị" (một sản phẩm lưu `100` hiểu là "100 USD" bỗng bị đọc thành "100 VND"). Chức năng
> **Đổi đồng tiền gốc** làm việc này an toàn — xem ngay dưới.

---

## 4. Đổi đồng tiền gốc (rebase) — bảo toàn giá trị (v2.1)

Khi cần chuyển "chuẩn đo giá" của cửa hàng sang một đồng tiền khác (ví dụ shop trước nhập giá theo USD, nay
muốn nhập theo VND), dùng chức năng **Đổi đồng tiền gốc**. Điểm mấu chốt: hệ thống **tự tính lại các con số**
để **giá khách nhìn thấy ở mọi đồng tiền không đổi** — bạn không phải sửa lại giá từng sản phẩm.

### Các bước
1. Vào **Cấu hình → Tiền tệ**.
2. Bấm nút **Đổi đồng tiền gốc (Change base)** ở phía trên (biểu tượng hai mũi tên ⇄). Một hộp thoại cảnh
   báo hiện ra.
3. Xem dòng **Đồng gốc hiện tại** để chắc chắn bạn đang đổi đúng.
4. Ở ô **Đồng gốc mới**, chọn đồng tiền muốn trở thành gốc. Danh sách chỉ liệt kê các đồng **đang bật** và
   **chưa phải gốc**.
5. Xem ô **Tỉ giá mới cho đồng gốc cũ**: hệ thống **gợi ý sẵn** một giá trị (bằng `1 ÷ tỉ giá hiện tại của
   đồng mới`) để giữ giá không đổi. Cứ để nguyên gợi ý này trừ khi bạn cố ý muốn khác.
6. Tích ô xác nhận **"Tôi hiểu..."** (bắt buộc) để mở nút xác nhận.
7. Bấm **Đổi đồng tiền gốc** để thực hiện.

   Nếu thành công, màn hình báo đổi gốc thành công; huy hiệu **"Gốc"** chuyển sang đồng tiền mới, và giá trên
   storefront vẫn y như trước.

### Hệ thống làm gì bên trong (giải thích ngắn)
Trong **một giao dịch an toàn** (hoặc xong hết, hoặc không đổi gì — không có trạng thái nửa vời):
- Nhân các con số giá gốc (giá bán, giá vốn, phụ phí thuộc tính, giá khuyến mãi) với **tỉ giá của đồng mới**;
- Chia lại **tất cả** tỉ giá cho cùng con số đó (để các đồng khác vẫn quy đổi đúng);
- Đặt đồng mới thành gốc (tỉ giá `1`), đồng cũ nhận tỉ giá mới bạn vừa nhập.
- **Đơn hàng cũ không bị đụng tới** — chúng đã "đóng băng" đồng tiền và tỉ giá lúc đặt.

> Nhờ cách tính này, một sản phẩm đang bán "2 USD ↔ 50.000 ₫" sau khi đổi gốc sang VND vẫn hiển thị đúng
> "50.000 ₫ ↔ 2 USD". Số lưu trong máy đổi, nhưng **giá trị người mua thấy không đổi**.

---

## 5. Ghi chú đơn vị tiền cạnh ô nhập tiền (admin) (v2.1)

Vì mọi ô nhập tiền của sản phẩm đều tính theo **đồng tiền gốc**, người nhập liệu dễ nhầm đang nhập theo đồng
nào. Từ 2.1, admin **tự hiện một ghi chú mã tiền** ngay cạnh mỗi ô nhập tiền:

- **Màn sản phẩm** (giá, giá vốn, phụ phí biến thể, giá khuyến mãi…): hiện mã **đồng tiền gốc**, ví dụ
  `(VND)` — nhắc bạn "ô này nhập theo đồng gốc".
- **Màn đơn hàng** (tạo/sửa đơn): hiện mã **đồng tiền của chính đơn đó** (khi sửa) hoặc đồng tiền bạn đang
  chọn (khi tạo) — để không nhầm giữa các đơn khác đồng tiền.
- Nếu vì lý do nào đó chưa xác định được đồng tiền, ghi chú lùi về một nhãn mặc định (có thể chỉnh qua hệ
  đa ngôn ngữ).

> Ghi chú này chỉ để **hiển thị nhắc nhở**, không đổi cách lưu số. Bạn vẫn nhập theo đồng tiền gốc như trước.

---

## 6. Tiền tệ hiển thị ở những đâu?

- **Giao diện khách (storefront):** giá sản phẩm, giỏ hàng, thanh toán — hiển thị theo đồng tiền khách đang
  chọn (mặc định hoặc do khách tự đổi).
- **Đơn hàng của khách:** hiển thị theo đồng tiền **lúc đặt** (đã lưu trong đơn).
- **Trang quản trị — chi tiết đơn & danh sách đơn:** mỗi đơn hiển thị theo đồng tiền **của chính đơn đó**
  (không quy đổi lại), kèm ký hiệu để phân biệt khi danh sách có nhiều đồng tiền.
- **Hóa đơn (invoice):** theo đồng tiền của đơn.

---

## 7. Một vài lưu ý quan trọng

- **Đổi tỉ giá không làm đổi đơn hàng cũ.** Đơn đã đặt giữ nguyên đồng tiền và tỉ giá lúc mua; thay đổi chỉ
  ảnh hưởng cách hiển thị giá **mới** trên storefront.
- **Giá sản phẩm luôn nhập theo đồng tiền gốc.** Đừng nhập giá đã quy đổi sẵn. Ghi chú mã tiền cạnh ô nhập
  (mục 5) sẽ nhắc bạn đang ở đồng nào.
- **Đổi đồng tiền gốc thì dùng chức năng Rebase (mục 4)**, đừng sửa tay cờ gốc/tỉ giá — sửa tay bị hệ thống
  chặn để tránh làm sai giá cả kho.
- **Số chữ số thập phân theo từng đồng tiền**, không theo giá trị. Ví dụ VND để precision `0` thì mọi số
  tiền VND đều không có phần lẻ.
- **Tỉ giá rất nhỏ vẫn lưu chính xác.** Hệ thống lưu tỉ giá tới 6 chữ số thập phân, nên đồng tiền lớn quy
  đổi từ đơn vị nhỏ (ví dụ tỉ giá `0.00004`) không bị làm tròn thành 0.

---

## 8. Điều kiện & ràng buộc (hiểu trước khi thao tác)

Màn Tiền tệ có kiểm tra dữ liệu và một vài ràng buộc nghiệp vụ. Nắm trước để không bị bất ngờ khi hệ thống
báo lỗi.

### Khi thêm / sửa một đồng tiền
- **Tên bắt buộc nhập**, tối đa 100 ký tự — để danh sách đồng tiền luôn có nhãn rõ ràng.
- **Mã bắt buộc nhập, không được trùng**, tối đa 10 ký tự — vì mã là "chìa khóa" nhận diện đồng tiền (đơn
  hàng lưu theo mã); trùng mã sẽ gây nhầm lẫn dữ liệu.
- **Ký hiệu bắt buộc nhập**, tối đa 20 ký tự — để hiển thị giá cho khách.
- **Tỉ giá bắt buộc, là số và phải lớn hơn 0** — tỉ giá `0` hoặc âm sẽ làm mọi giá quy đổi về 0 hoặc sai.
- **Số chữ số thập phân là số nguyên từ 0 đến 8** — giới hạn để hiển thị tiền hợp lý (không có đồng tiền
  thực tế nào cần quá 8 chữ số lẻ).
- **Ngăn cách phần nghìn bắt buộc**, tối đa 2 ký tự.
- **Thứ tự là số, không âm.**

### Với đồng tiền gốc (v2.1)
- **Không sửa, không xóa đồng gốc** — nút Sửa/Xóa bị khóa; hệ thống chặn cả khi cố truy cập thẳng bằng URL.
  *Lý do:* đồng gốc là chuẩn đo giá của cả kho, sửa tay sẽ làm sai toàn bộ giá.
- **Tỉ giá đồng gốc luôn `1` và luôn bật** — không thể đổi.
- Muốn đổi đồng gốc: **dùng chức năng Đổi đồng tiền gốc** (mục 4).

### Khi đổi đồng tiền gốc (rebase) (v2.1)
- **Phải có sẵn một đồng gốc** thì mới đổi được.
- **Đồng gốc mới phải đang bật và chưa phải gốc** — danh sách chọn đã tự lọc đúng các ứng viên hợp lệ.
- **Tỉ giá mới cho đồng gốc cũ phải là số > 0 và khác `1`** — nếu để `1` nghĩa là hai đồng ngang giá, vô lý
  với một lần đổi gốc.
- **Bắt buộc tích ô xác nhận "Tôi hiểu..."** mới bấm được nút đổi — vì đây là thao tác ảnh hưởng toàn kho.

### Khi xóa một đồng tiền
Hệ thống **chặn xóa** trong các trường hợp sau và **hiện thông báo lý do** (không xóa im lặng). Mục đích là
tránh làm hỏng cửa hàng hoặc mất dữ liệu đơn cũ:

- **Là đồng tiền gốc (v2.1)** — đồng gốc là nơi lưu mọi giá; xóa sẽ làm "mồ côi" toàn bộ giá sản phẩm. Muốn
  bỏ, hãy **đổi đồng gốc** sang đồng khác trước (mục 4).
- **Đang là đồng tiền hiển thị mặc định của cửa hàng** — nếu xóa, shop sẽ không còn đồng tiền để hiển thị
  mặc định. *Hãy đổi đồng mặc định sang đồng khác trước (Website Info), rồi mới xóa.*
- **Đã có đơn hàng dùng đồng tiền này** — nếu xóa, các đơn cũ sẽ mất thông tin đồng tiền và hiển thị sai số
  tiền. Đây là ràng buộc quan trọng để bảo vệ lịch sử đơn hàng.
- **Là đồng tiền đang bật cuối cùng** — shop luôn cần ít nhất một đồng tiền dùng được; xóa cái cuối cùng sẽ
  khiến storefront không có đồng tiền nào để hiển thị.

> Ràng buộc xóa được suy ra từ **dữ liệu thật** (cờ gốc, đồng mặc định hiện tại, đơn hàng thực tế, số đồng
> tiền đang bật), **không** dựa vào một danh sách "cứng" nào — nên nó đúng với mọi cửa hàng, kể cả shop đã
> đổi đồng tiền qua nhiều năm.

---

## 9. Logic xử lý & các hàm tiền tệ (cho lập trình viên)

> Mục này dành cho **lập trình viên**. Người dùng thông thường có thể bỏ qua.

### Mô hình dữ liệu
- Bảng `shop_currency`: `name`, `code` (unique), `symbol`, `exchange_rate` **decimal(16,6)**, `precision`
  (tinyint), `symbol_first` (0/1), `thousands`, `status` (0/1), `sort`, **`is_base` (tinyInteger, mặc định
  `0`) — cờ đồng tiền gốc, bất biến "đúng một dòng `is_base=1`" (v2.1).
- Đơn hàng snapshot đồng tiền: `shop_order.currency` (mã) + `shop_order.exchange_rate` **decimal(16,6)**;
  `shop_order_detail.exchange_rate` cũng **decimal(16,6)** (thống nhất 3 bảng để giữ tỉ giá nhỏ chính xác).
- 4 cột giá tính theo đồng gốc, bị rebase() nhân lại: `shop_product.price`, `shop_product.cost`,
  `shop_product_attribute.add_price`, `shop_product_promotion.price_promotion`.

### Xác định đồng tiền đang dùng (chỉ ở storefront)
- `CurrencyMiddleware` (chỉ đăng ký ở nhóm route **front**, **không** ở admin): lấy `session('currency')`
  → nếu trống dùng `gp247_store_info('currency')` (đồng tiền hiển thị mặc định) → nếu không nằm trong danh
  sách đang bật thì lấy đồng tiền active đầu tiên → gọi `ShopCurrency::setCode()` nạp vào static props.
- Route đổi tiền tệ storefront: `front.currency` (`currency/{code}`) → `session(['currency' => $code])`.
- **Lưu ý admin:** admin **không** chạy `CurrencyMiddleware`, nên để hiển thị đúng phải dùng đồng tiền
  **snapshot của đơn** (`onlyRender`), không dựa vào đồng tiền active.

### Đồng tiền gốc & rebase (v2.1) — `Models/ShopCurrency.php`
- `getBaseCode(): ?string` — **nguồn sự thật duy nhất** cho đồng gốc: `where('is_base', 1)->value('code')`.
  Thay cho cách cũ "quét đồng active có tỉ giá `1`" (dễ sai). Trả `null` nếu site nâng cấp chưa chọn gốc.
- **Boot guards (defense-in-depth):**
  - `deleting`: chặn xóa nếu `deleteBlockReason() !== null` (áp dụng cho MỌI đường xóa: Livewire, seeder,
    tinker…).
  - `saving`: chặn lưu nếu `is_base=1` mà `exchange_rate != 1` hoặc `status != 1` (giữ bất biến gốc). Rebase
    và seeder dùng query builder (bỏ qua model event) nên không bị guard này cản.
- `rebase(string $newBaseCode, float $newRateForOldBase): void` — đổi gốc **bảo toàn giá trị**, chạy trong
  **một transaction** (NFR-AVAIL-rebase-atomicity). Gọi `r = tỉ giá hiện tại của đồng mới`; nhân 4 cột giá
  gốc × r (bulk UPDATE ở tầng DB, **không** nạp catalog vào PHP — RISK-TECH-rebase-bulk-price-update); chia
  mọi tỉ giá ÷ r; ghim đồng mới `rate=1, is_base=1`, đồng cũ `rate=$newRateForOldBase, is_base=0`. Chứng
  minh bảo toàn: `(price×r)×(oldRate(C)/r) = price×oldRate(C)` với mọi đồng C. Ném `InvalidArgumentException`
  khi thiếu gốc / target sai / rate không hợp lệ. Cuối cùng gọi `resetStaticCache()`.
- `sqlNumericLiteral(float): string` (private) — `sprintf('%.6F', $r)` tạo literal decimal an toàn (không mũ,
  dấu chấm thập phân, cắt theo scale 6) để nội suy vào `col = col * r`. Chỉ dùng cho giá trị caller kiểm soát.
- `deleteBlockReason(): ?string` — trả `base` / `default` / `in_use` / `last_active` / `null` theo bất biến
  xóa (mục 8). Guard theo **`code`**; kiểm tra `is_base` trước (v2.1); dùng `gp247_store_info('currency')`
  (không `getCode()` vì admin không nạp middleware); đếm trực tiếp `where('status',1)->count()`.

### Livewire admin — `Admin/Livewire/CurrencyManager.php`
- Ẩn/khóa Sửa & Xóa cho dòng gốc; `edit()`/`save()` từ chối nếu `is_base=1` (chống truy cập thẳng bằng URL).
- Modal **Đổi đồng tiền gốc**: `rebaseTarget`, `rebaseOldRate`, `rebaseConfirmed`. `updatedRebaseTarget()`
  tự gợi ý `rebaseOldRate = 1 / rate(target)` (`sprintf('%.6F')` rồi trim). `rebase()` validate
  (`rebaseTarget` phải tồn tại, `status=1`, `is_base=0`; `rebaseOldRate` `numeric|gt:0|not_in:1`;
  `rebaseConfirmed` `accepted`) rồi gọi `ShopCurrency::rebase()`, báo lỗi domain qua toast, thành công thì
  flash + redirect.

### Các hàm helper (`Library/Helpers/currency.php`)
- `gp247_currency_render($money, $currency=null, $rate=null, $space=false, $useSymbol=true)` — **quy đổi**
  (nhân tỉ giá) + format + ký hiệu.
- `gp247_currency_render_symbol($money, $currency=null, $space=false, $includeSymbol=true)` — **chỉ format +
  ký hiệu, KHÔNG quy đổi** (số tiền đã ở đúng đồng tiền, ví dụ tiền snapshot của đơn).
- `gp247_currency_value($money, $rate=null)` — quy đổi giá trị theo tỉ giá.
- `gp247_currency_code()` / `gp247_currency_rate()` / `gp247_currency_info()` — đồng tiền đang active.
- `gp247_currency_format($money)` — format số theo precision đồng tiền active (không ký hiệu).
- `gp247_currency_all()` — danh sách đồng tiền đang bật (đã sort); `gp247_currency_all_active()` — map
  `code → name` của đồng tiền đang bật.
- **`gp247_base_currency_code(): ?string` (v2.1)** — wrapper của `ShopCurrency::getBaseCode()`.
- **`gp247_money_hint($code = null): string` (v2.1)** — ghi chú cạnh ô nhập tiền: `null` → dùng đồng gốc,
  trả `"(CODE)"`; nếu không xác định được thì lùi về nhãn i18n `product.base_unit_hint`.

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi nhập giá sản phẩm bằng đồng tiền nào?**

→ Bằng **đồng tiền gốc** — đồng có huy hiệu "Gốc" (tỉ giá `1`). Các đồng khác hệ thống tự quy đổi khi hiển
thị. Từ 2.1, cạnh mỗi ô nhập tiền có ghi chú mã tiền (ví dụ `(VND)`) nhắc bạn đang nhập theo đồng nào.

**Câu 2: "Đồng tiền gốc" khác gì "đồng tiền mặc định" của shop?**

→ **Đồng gốc** là *nơi lưu giá sản phẩm* (chuẩn đo, luôn tỉ giá `1`, bị khóa). **Đồng mặc định** (đặt ở
Website Info) là *đồng khách thấy đầu tiên* khi vào shop. Chúng thường trùng nhau nhưng là hai khái niệm khác.

**Câu 3: Tôi muốn đổi đồng gốc từ USD sang VND thì làm sao?**

→ Vào Cấu hình → Tiền tệ, bấm **Đổi đồng tiền gốc**, chọn đồng mới, giữ tỉ giá gợi ý, tích ô xác nhận rồi
bấm đổi. Hệ thống tự tính lại số để **giá khách thấy không đổi** — bạn không phải sửa giá từng sản phẩm.

**Câu 4: Đổi đồng gốc có làm sai giá sản phẩm không?**

→ Không. Chức năng đổi gốc **bảo toàn giá trị**: nó nhân/chia lại các con số một cách khớp nhau nên giá hiển
thị ở mọi đồng tiền giữ nguyên. Đơn hàng cũ cũng không bị đụng tới.

**Câu 5: Vì sao tôi không sửa/không xóa được đồng tiền gốc?**

→ Vì đồng gốc là chuẩn đo giá của cả kho; sửa tay sẽ làm sai toàn bộ giá. Muốn thay, hãy dùng **Đổi đồng
tiền gốc** thay vì sửa trực tiếp.

**Câu 6: Ghi chú `(VND)` cạnh ô nhập tiền có ý nghĩa gì?**

→ Đó là nhắc nhở ô đó đang nhập theo đồng tiền nào (ở màn sản phẩm là **đồng gốc**; ở màn đơn là đồng của
đơn). Chỉ để hiển thị, không đổi cách lưu số.

**Câu 7: Vì sao VND của tôi hiển thị `.00` ở cuối (ví dụ `250.000,00`)?**

→ Do đồng tiền đó đang để **số chữ số thập phân (precision)** khác 0. Đặt precision của VND về `0` để không
hiện phần lẻ.

**Câu 8: Tôi đổi tỉ giá thì đơn hàng cũ có bị tính lại không?**

→ Không. Mỗi đơn đã lưu đồng tiền và tỉ giá lúc đặt; đổi tỉ giá chỉ ảnh hưởng giá hiển thị **mới** trên
storefront.

**Câu 9: Vì sao tôi không xóa được một đồng tiền?**

→ Vì nó rơi vào một trong các trường hợp bị chặn: là **đồng gốc**, đang là **mặc định** của shop, **đã có
đơn** dùng nó, hoặc là **đồng tiền bật cuối cùng**. Hệ thống sẽ hiện thông báo lý do cụ thể.

**Câu 10: Đổi tỉ giá tự động theo thị trường được không?**

→ Bản hiện tại nhập tỉ giá **thủ công** ở màn Tiền tệ. Không có cập nhật tỉ giá tự động sẵn.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-22 · ✍️ **Tác giả (Author):** GP247</sub>
