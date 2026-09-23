> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./layout-block-system.md)

# Khối bố cục (Layout Block): cách hiển thị, cách tùy chỉnh và cơ chế dự phòng

## Giới thiệu
Tài liệu này giải thích **Layout Block** — công cụ cho phép bạn chèn thêm các khối nội dung vào trang bán hàng (dải sản phẩm, banner, đoạn HTML, khối do plugin cung cấp) mà không phải sửa mã nguồn. Phần đầu dành cho **chủ site**: thêm khối, đổi giao diện một khối. Phần sau dành cho **lập trình viên plugin/template**: cách plugin đăng ký khối của mình và thứ tự hệ thống chọn file khi hiển thị. Đọc xong, bạn biết khối của mình lấy nội dung từ đâu, sửa ở đâu thì không bị mất khi cập nhật.

## Layout Block là gì

Vào **Quản trị → Giao diện → Layout block** (đường dẫn `.../layout_block`). Mỗi dòng ở đây là **một khối** với các thông tin:

| Trường | Ý nghĩa |
| --- | --- |
| **Name** | Tên gợi nhớ, chỉ bạn nhìn thấy trong trang quản trị |
| **Position** | Vùng trên trang mà khối sẽ nằm vào (trên, dưới, cột trái…) |
| **Page** | Khối hiện ở (những) loại trang nào; `*` nghĩa là mọi trang |
| **Type** | Kiểu nội dung: `html`, `view` hoặc `page` |
| **Text** | Nội dung, hoặc **tên** của khối/trang tùy theo Type |
| **Sort** | Số càng lớn càng nằm trên, khi nhiều khối cùng một vùng |
| **Status** | Bật/tắt khối |

Ba kiểu nội dung:

1. **`html`** — bạn dán thẳng đoạn HTML vào ô Text. Đơn giản nhất, không cần lập trình.
2. **`view`** — khối là một file giao diện có sẵn (do giao diện hoặc plugin cung cấp). Ô Text là **tên khối**, ví dụ `shop_product_home`, `product_flash_sale`. Đây là loại tài liệu này nói nhiều nhất.
3. **`page`** — nhúng nội dung của một trang tĩnh (CMS) bạn đã tạo.

## Các vùng hiển thị

Giao diện quyết định có những vùng nào. Giao diện mặc định **GP247Front** vẽ ra các vùng sau:

| Vùng | Nằm ở đâu |
| --- | --- |
| `top` | Ngay dưới đầu trang, trên phần nội dung chính |
| `left` / `right` | Cột trái / cột phải (nếu có khối, trang tự chuyển sang bố cục 2 cột) |
| `center` | Giữa phần nội dung chính |
| `bottom` | Cuối phần nội dung, trên chân trang |
| `footer` | Trong chân trang |

Trang chủ mặc định dùng `bottom` cho các dải sản phẩm (sản phẩm mới, khuyến mãi, Flash Sale…).

## Cơ chế hiển thị: hệ thống chọn file nào?

Phần này là trọng tâm. Với khối kiểu **`view`**, ô Text chỉ lưu **tên khối** (ví dụ `product_flash_sale`) — **không lưu đường dẫn file**. Đến lúc hiển thị, hệ thống mới đi tìm file, theo đúng thứ tự sau:

```
Khối tên "abc" cần hiển thị
│
├─ 1. Giao diện của bạn có file riêng không?
│     app/GP247/Templates/{TÊN_GIAO_DIỆN}/blocks/abc.blade.php
│     → CÓ  ⇒ dùng file này. DỪNG.
│
├─ 2. Có plugin nào đăng ký tên khối "abc" không?
│     → CÓ  ⇒ dùng view của plugin, trong đó:
│           a) resources/views/vendor/Plugins/{TênPlugin}/…   ← bản bạn ghi đè
│           b) app/GP247/Plugins/{TênPlugin}/Views/…          ← bản gốc của plugin
│
└─ 3. Không tìm thấy ở đâu ⇒ khối im lặng không hiện gì (trang vẫn chạy bình thường).
```

Ba điều rút ra:

- **Bản của bạn luôn thắng.** Đặt file cùng tên trong giao diện là đủ để thay khối, không cần sửa gì trong trang quản trị, không cần đụng vào mã plugin.
- **Bản của plugin là dự phòng (fallback).** Xóa file tùy chỉnh đi thì khối tự quay về bản gốc của plugin — không vỡ trang.
- **Không tìm thấy thì im lặng.** Hệ thống cố ý không báo lỗi ra trang khách hàng. Nếu khối của bạn "biến mất", xem mục *Điều kiện & ràng buộc* bên dưới.

Một khối lỗi (ví dụ plugin viết sai) chỉ làm **mất chính khối đó**; các khối khác và cả trang vẫn hiển thị bình thường.

## Tùy chỉnh một khối (dành cho chủ site)

Ví dụ dùng khối `product_flash_sale` của plugin *Product Flash Sale*. Thay `{TÊN_GIAO_DIỆN}` bằng tên giao diện site bạn đang dùng (mặc định là `GP247Front`), và `{TênPlugin}` bằng tên thư mục plugin (ví dụ `ProductFlashSale`).

> ⚠️ **Đừng sửa thẳng file trong thư mục plugin** (`app/GP247/Plugins/...`). Lần cập nhật plugin tiếp theo sẽ ghi đè lên, và bạn mất hết công sửa.

### Cách 1 — Thêm thắt xung quanh, giữ nguyên ruột (nhẹ nhất, khuyên dùng)

1. Tạo file mới tại đường dẫn sau (nếu chưa có thư mục `blocks`, cứ tạo thêm):

   ```
   app/GP247/Templates/{TÊN_GIAO_DIỆN}/blocks/product_flash_sale.blade.php
   ```

2. Dán nội dung sau vào file, sửa phần chữ theo ý bạn:

   ```blade
   <div class="container-x pt-6">
       <h2 class="section-title">Giờ vàng hôm nay</h2>
   </div>

   @includeIf('Plugins/ProductFlashSale::blocks.product_flash_sale')
   ```

3. Tải lại trang chủ. Nếu thành công, bạn thấy tiêu đề mới của mình nằm ngay trên dải sản phẩm cũ.

Dùng `@includeIf` chứ **không** dùng `@include`: nếu sau này bạn gỡ plugin, `@includeIf` chỉ đơn giản không hiện gì, còn `@include` sẽ làm trang báo lỗi "View not found".

Ưu điểm của cách này: phần ruột vẫn là của plugin, nên plugin cập nhật giao diện thì bạn hưởng theo.

### Cách 2 — Viết lại toàn bộ khối

1. Vẫn là file ở đường dẫn của Bước 1 phía trên.
2. Tự viết toàn bộ giao diện, lấy dữ liệu bằng hàm hỗ trợ của plugin:

   ```blade
   @php
       $products = gp247_product_flash(10);
   @endphp

   @foreach ($products as $product)
       <div>{{ $product->getName() }}</div>
   @endforeach
   ```

3. Tải lại trang. Khối của plugin biến mất hoàn toàn, chỉ còn phần bạn viết.

### Cách 3 — Chỉ sửa một mảnh nhỏ bên trong khối

Dùng khi bạn hài lòng với khối, chỉ muốn đổi một phần (ví dụ riêng cái thẻ sản phẩm).

1. Tạo file **cùng tên, cùng đường dẫn con** với file của plugin, nhưng đặt dưới thư mục ghi đè của Laravel:

   ```
   resources/views/vendor/Plugins/ProductFlashSale/partials/flash_card.blade.php
   ```

   (bản gốc là `app/GP247/Plugins/ProductFlashSale/Views/partials/flash_card.blade.php`)

2. Viết giao diện thẻ sản phẩm theo ý bạn.
3. Tải lại trang — không cần chạy lệnh xóa cache. Thẻ sản phẩm mới được dùng ở **mọi** nơi plugin hiển thị nó.

### Quay về bản gốc

Xóa file bạn đã tạo đi là xong. Hệ thống tự quay về bản của plugin ngay lần tải trang kế tiếp.

### So sánh nhanh

| | Cách 1 (bọc) | Cách 2 (viết lại) | Cách 3 (sửa một mảnh) |
| --- | --- | --- | --- |
| Công sức | Thấp | Cao | Trung bình |
| Nhận cập nhật giao diện từ plugin | ✅ Có | ❌ Không | Một phần |
| Phù hợp khi | Thêm tiêu đề, khung, nền | Thiết kế hoàn toàn khác | Chỉnh thẻ sản phẩm, nút bấm |

## Dành cho lập trình viên: plugin đăng ký khối của mình

Nếu bạn viết plugin và muốn nó cung cấp một khối cho màn Layout block, **hãy đăng ký**, đừng tạo thư mục giao diện trong plugin và cũng đừng chép file vào `app/GP247/Templates`.

### Cách làm

Trong `Provider.php` của plugin, bên trong khối `gp247_extension_check_active()` đã có sẵn, thêm:

```php
$blockViews = config('gp247-config.front.layout_block_views', []);
$blockViews['product_flash_sale'] = $extensionPath.'::blocks.product_flash_sale';
config(['gp247-config.front.layout_block_views' => $blockViews]);
```

- **Khóa** (`product_flash_sale`) là tên khối mà quản trị viên nhìn thấy trong ô Text.
- **Giá trị** là khóa view của plugin, trỏ tới file thật, ví dụ `app/GP247/Plugins/ProductFlashSale/Views/blocks/product_flash_sale.blade.php`.

Chỉ vậy. Khối sẽ xuất hiện trong danh sách chọn của màn Layout block và hiển thị được — với **mọi** giao diện.

### Vì sao không chép file vào thư mục giao diện

Cách cũ (chép file lúc cài) hỏng ở bốn chỗ, và đều hỏng **âm thầm**:

1. Cần thư mục giao diện **ghi được**. Máy chủ triển khai ở chế độ chỉ-đọc thì bước chép bị bỏ qua, chủ site không có khối mà cũng không thấy báo lỗi.
2. Chỉ chép cho giao diện **đang dùng lúc cài**. Đổi giao diện hoặc thêm cửa hàng mới là mất khối.
3. Cập nhật plugin **không làm mới** bản đã chép — bản chép cũ tiếp tục chạy mãi.
4. Gỡ plugin để lại **file mồ côi** trong thư mục giao diện.

Cách đăng ký không dính lỗi nào trong số đó: chạy trên máy chủ chỉ-đọc, phục vụ mọi giao diện, đi theo bản cập nhật của plugin, và biến mất sạch khi gỡ plugin.

### Quy ước đặt tên khối

- **Đặt tiền tố theo plugin** (`product_flash_sale`, `vendor_new`) vì tên khối nằm chung một không gian phẳng với khối của giao diện. Hai plugin đặt trùng tên sẽ đè nhau.
- **Không đổi tên khối sau khi phát hành**: tên này được lưu vào dữ liệu site (ô Text của từng dòng Layout block), đổi tên là các site đang dùng mất khối.

### Ghi chú kỹ thuật

- Danh sách chọn ở màn quản trị và phần hiển thị ngoài trang bán hàng đọc **cùng một nguồn**, theo **cùng thứ tự ưu tiên** — nên danh sách không bao giờ mời bạn một thứ khác với thứ sẽ hiển thị.
- Khối chỉ được đăng ký khi plugin đang **bật**. Tắt plugin thì dòng Layout block vẫn còn trong dữ liệu nhưng không hiển thị gì; bật lại là khối trở lại.

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

### Khi tạo một dòng Layout block

- **Vị trí (Position) phải là vùng giao diện thực sự vẽ ra.** Danh sách vị trí và các vùng mà giao diện gọi là hai thứ khác nhau; chọn một vị trí mà giao diện của bạn không dùng thì khối **lưu thành công, hiện trong danh sách, nhưng không sinh ra gì trên trang**. Trường hợp đã biết ở GP247Front: vị trí `top_site` không được giao diện nào vẽ — muốn chèn mã vào vùng `<head>` thì hiện chưa chọn được qua màn này.
- **Page phải là loại trang, không phải đường dẫn.** Ô Page nhận các mã loại trang (`front_home`, `shop_product_detail`, `shop_cart`…) hoặc `*` cho mọi trang. Điền một đường dẫn hay một tên trang CMS vào đây thì không bao giờ khớp và khối không hiện.
- **Tên khối (Text) phải chọn từ danh sách.** Với `Type = view`, gõ một cái tên không có thật thì hệ thống không tìm ra file và bỏ qua khối — không có thông báo lỗi.

### Khi khối không hiển thị

Kiểm tra theo thứ tự này, đây là bốn nguyên nhân thường gặp nhất:

- **Khối đang tắt** — cột Status phải bật.
- **Khối thuộc cửa hàng và giao diện khác.** Mỗi dòng Layout block gắn với **một cửa hàng** và **một giao diện**. Đổi giao diện của cửa hàng thì các dòng tạo cho giao diện cũ ngừng hiển thị (chúng không bị xóa — đổi về giao diện cũ là chúng trở lại).
- **Plugin cung cấp khối đang tắt hoặc đã gỡ** — khối biến mất, dòng Layout block vẫn còn.
- **Sai vị trí hoặc sai loại trang** — xem mục ngay trên.

### Khi tùy chỉnh giao diện của khối

- **Tên file phải trùng khít tên khối**: `blocks/{tên khối}.blade.php`. Sai một ký tự là hệ thống bỏ qua file của bạn và dùng bản plugin — dễ tưởng nhầm là "sửa không ăn".
- **Tên giao diện phân biệt hoa thường** và phải đúng tên thư mục trong `app/GP247/Templates/`.
- **Sửa thẳng file trong thư mục plugin sẽ mất khi cập nhật plugin.** Luôn dùng một trong ba cách ở trên.
- **Cách 2 và Cách 3 tạo ra bản sao đóng băng**: plugin cải tiến giao diện về sau thì phần bạn đã ghi đè không nhận thay đổi đó. Cách 1 không có nhược điểm này.

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi sửa file trong thư mục plugin, cập nhật plugin xong thì mất hết. Cứu được không?**

→ Không khôi phục tự động được. Hãy chép lại nội dung bạn muốn giữ sang `app/GP247/Templates/{TÊN_GIAO_DIỆN}/blocks/{tên khối}.blade.php` — từ đó về sau bản cập nhật plugin không đụng tới nó nữa.

**Câu 2: Tôi tạo file tùy chỉnh rồi mà trang vẫn hiện bản cũ?**

→ Kiểm tra ba thứ: tên file có đúng `{tên khối}.blade.php` không, tên thư mục giao diện có đúng tên giao diện cửa hàng đang dùng không, và file có nằm trong thư mục `blocks` không. Nếu vẫn chưa được, chạy `php artisan optimize:clear` rồi tải lại.

**Câu 3: Gỡ plugin thì khối tôi đã bọc có làm vỡ trang không?**

→ Không, nếu bạn dùng `@includeIf`. Trang sẽ chỉ hiện phần bạn viết, bỏ qua phần của plugin. Nếu bạn lỡ dùng `@include`, hãy sửa thành `@includeIf`.

**Câu 4: Site tôi dùng giao diện tự viết, khối của plugin có chạy không?**

→ Có. Khối do plugin đăng ký chạy với mọi giao diện, bạn không phải tạo thêm file nào.

**Câu 5: Tôi có nhiều cửa hàng (multi-store), phải tạo khối mấy lần?**

→ Mỗi cửa hàng một dòng riêng, vì mỗi dòng gắn với một cửa hàng và một giao diện. Khối của plugin thì chỉ cần plugin bật là dùng được ở mọi cửa hàng.

**Câu 6: Hai plugin cùng đặt tên khối giống nhau thì sao?**

→ Cái đăng ký sau sẽ đè cái trước, và trong danh sách chọn chỉ còn một mục. Vì vậy lập trình viên nên đặt tên khối có tiền tố theo plugin.

**Câu 7: Khối của tôi bị lỗi thì có làm sập cả trang không?**

→ Không. Lỗi được cô lập trong phạm vi khối đó: khối không hiện, phần còn lại của trang vẫn chạy bình thường, và lỗi được ghi vào nhật ký hệ thống.

**Câu 8: Làm sao chèn mã theo dõi (Google Analytics, Facebook Pixel) vào trang?**

→ Tạo khối `Type = html`, dán đoạn mã vào ô Text, chọn `Page = *` và một vị trí mà giao diện của bạn thực sự vẽ ra (ví dụ `footer`). Lưu ý phần *Điều kiện & ràng buộc*: vị trí `top_site` hiện không hiển thị.

**Câu 9: Tôi muốn ghim cứng một khối vào giao diện, không qua Layout block, được không?**

→ Được. Trong file giao diện của bạn, thêm một dòng `@includeIf('Plugins/{TênPlugin}::blocks.{tên khối}')` vào đúng chỗ muốn hiện.

**Câu 10: Xóa dòng Layout block có xóa mất nội dung không?**

→ Với `Type = html`, nội dung nằm ngay trong dòng đó nên sẽ mất — hãy chép ra nơi khác trước. Với `Type = view` và `Type = page`, dòng chỉ trỏ tới file/trang nên xóa dòng không làm mất gì.

---

<sub>📅 **Cập nhật lần cuối:** 2026-09-22 · ✍️ **Tác giả (Author):** GP247</sub>
