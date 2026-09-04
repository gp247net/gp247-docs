> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./release-notes-3.0.md)

# Ghi chú phát hành GP247 v3.0 — tiền của đơn hàng

## Giới thiệu
Bản v3.0 sửa lại toàn bộ cách GP247 ghi nhận **tiền khách đã trả cho một đơn hàng**.
Tài liệu này dành cho **chủ cửa hàng và người vận hành site** đang chạy v2.1 hoặc v2.2: nó cho
bạn biết cần làm gì để nâng cấp, và — quan trọng hơn — **những con số nào trên màn hình
sẽ thay đổi sau khi nâng**, để bạn không tưởng hệ thống tính sai. Cuối tài liệu có một
mục riêng cho lập trình viên plugin.

## Nâng cấp thế nào

1. **Sao lưu cơ sở dữ liệu trước.** Bản này có chuyển đổi dữ liệu, và dù đã được kiểm
   thử kỹ, sao lưu là cách duy nhất để quay lui nếu có sự cố. Đây là bước quan trọng nhất.

2. Mở **Terminal** (trên Windows là "Command Prompt"), vào thư mục gốc của website, gõ:

   ```
   composer update
   ```

   Lệnh này chỉ tải mã nguồn mới về, **chưa** đụng gì tới dữ liệu.

3. Gõ tiếp lệnh sau rồi nhấn Enter:

   ```
   php artisan gp247:update
   ```

   Lệnh này áp thay đổi vào site: chuyển đổi dữ liệu, cập nhật dữ liệu nền, dọn cache.
   Nếu thành công, màn hình in ra các bước đã chạy và không có dòng nào báo lỗi.

4. Xong. Không có bước thủ công nào khác — bạn **không** cần tự chạy `php artisan migrate`.

> Chạy lại lệnh ở Bước 3 nhiều lần cũng vô hại: mỗi phần chuyển đổi được thiết kế để
> chạy lần thứ hai thì không đổi gì thêm.

---

## Thay đổi 1 — Tiền của đơn nay là một cuốn sổ, không còn là một con số

**Trước đây**, mỗi đơn chỉ có một ô "Đã nhận" duy nhất. Ô đó không nói được tiền vào
**ngày nào**, bằng **cách nào**, và không ghi nổi một lần **hoàn tiền một phần**.

**Từ v3.0**, mỗi lần thu tiền hoặc hoàn tiền là **một dòng riêng** trong sổ thanh toán
của đơn, có ngày, số tiền, phương thức và mã giao dịch. Ở màn chi tiết đơn, bên dưới
phần tổng tiền bạn sẽ thấy **lịch sử thanh toán** liệt kê từng lần.

Ba điều bạn sẽ thấy khác ngay:

- **Đơn thanh toán qua cổng (PayPal) không còn hiện nợ đủ.** Trước đây cổng thanh toán
  chỉ đổi *nhãn trạng thái* mà không ghi tiền, nên đơn khách đã trả thật vẫn nằm trong
  danh sách công nợ và **không được tính vào doanh thu**. Nay tiền được ghi đúng lúc
  cổng báo về.
- **Hoàn tiền một phần được ghi đúng số.** Trước đây hoàn 10% cũng làm cả đơn chuyển
  thành "đã hoàn tiền", và số tiền hoàn không lưu ở đâu.
- **Ô "Đã nhận" đổi cách dùng.** Thay vì sửa một con số, bạn **ghi một khoản thu**
  (nhập số tiền + ngày + phương thức). Muốn hoàn tiền thì ghi một khoản hoàn.

Việc nâng cấp **tự tạo sổ** cho các đơn đang có: mỗi đơn đã có tiền sẽ có sẵn một dòng
thu tương ứng, nên số "Đã nhận" của bạn **không đổi** sau khi nâng.

---

## Thay đổi 2 — Báo cáo dòng tiền tính theo NGÀY THU (con số sẽ khác)

> ⚠️ **Đây là thay đổi dễ gây hiểu nhầm nhất. Đọc kỹ mục này.**

Chỉ áp dụng nếu bạn dùng plugin **InOut** (dòng tiền & công nợ).

**Trước đây**, doanh thu bán hàng được tính theo **ngày đặt hàng**. Nghĩa là khách đặt
đơn tháng 7 nhưng trả tiền tháng 8 thì khoản tiền đó vẫn bị tính vào **tháng 7** — tức
báo cáo dòng tiền đang dùng ngày đặt hàng thay cho ngày tiền thật sự vào.

**Từ v3.0**, doanh thu được tính theo **ngày thu tiền**. Ví dụ trên: khoản tiền đó nằm ở
**tháng 8**.

Hệ quả bạn cần chuẩn bị:

- **Doanh thu của các kỳ sẽ khác con số cũ.** Đây là số **đúng hơn**, không phải lỗi.
- **Dữ liệu trước ngày nâng cấp giữ nguyên cách cũ.** Hệ thống không lưu lại ngày thu
  thật của các đơn cũ (trước đây không có chỗ để lưu), nên khi nâng cấp, tiền của đơn cũ
  được ghi theo **ngày tạo đơn** — đúng bằng cách tính trước đây. Vì vậy ranh giới nằm ở
  **ngày bạn nâng cấp**: trước đó số liệu như cũ, sau đó theo ngày thu thật.
- **Nên chốt sổ kỳ trước khi nâng cấp**, hoặc in/xuất báo cáo kỳ gần nhất để đối chiếu.

### Phiếu thu tay nay gắn được vào đơn

Trên màn Dòng tiền, form tạo phiếu thu/chi có thêm ô **"Thuộc đơn hàng"**. Nếu khoản
tiền là của một đơn, hãy chọn đơn đó — tiền sẽ vào **sổ của đơn** thay vì thành một
phiếu rời.

Lý do: tổng thu của sổ quỹ cộng cả doanh thu bán hàng lẫn phiếu thu tay. Nếu cùng một
khoản tiền vừa nằm ở đơn vừa có một phiếu thu tay, nó bị **tính hai lần**. Trước đây kế
toán gần như buộc phải lập phiếu tay (vì cổng thanh toán không ghi tiền), nên tình huống
này rất dễ xảy ra.

Nếu khoản tiền **không** thuộc đơn nào (khách đặt cọc khi chưa có đơn, thu nợ cũ…), cứ
để trống ô đó — hệ thống chỉ nhắc nhở khi khách đang có đơn còn nợ, và vẫn cho lưu.

---

## Thay đổi 3 — Trạng thái thanh toán hết lệch một bậc

**Trước đây** có lỗi khiến trạng thái thanh toán hiển thị **thấp hơn thực tế một bậc**:
đơn **trả đủ** hiện "Thanh toán 1 phần", đơn **cần hoàn tiền** hiện "Đã thanh toán", và
đơn chưa trả có khi hiện ra **số `0`** thay vì chữ. Danh sách công nợ trong InOut vì vậy
cũng lọc nhầm đối tượng.

**Từ v3.0** hiển thị đúng, và việc nâng cấp **tự sửa dữ liệu cũ**. Một điểm cần biết:

> Hệ thống chỉ sửa những đơn mà **số tiền nói rõ trạng thái là gì**. Với đơn **chưa nhập
> tiền** nhưng đã được admin **đánh dấu tay** là "Đã thanh toán", hệ thống **cố ý giữ
> nguyên** — vì đó là quyết định của con người, tự động suy lại sẽ xoá mất nó.

Nếu bạn có thói quen đánh dấu "Đã thanh toán" mà không nhập số tiền, các đơn đó vẫn giữ
nhãn cũ nhưng phần "Đã nhận" sẽ là 0. Muốn số liệu đầy đủ, hãy ghi khoản thu cho chúng.

---

## Thay đổi 4 — Giảm giá nay trừ TRƯỚC khi tính thuế

> ⚠️ **Hai loại mã giảm giá bị ảnh hưởng khác nhau. Đọc đúng loại bạn đang dùng — nói gộp sẽ sai với một nửa.**

**Trước đây**, thuế được tính trên giá gốc rồi mới trừ giảm giá. Nghĩa là khách vẫn **chịu thuế trên phần đã
được giảm**, và con số thuế lưu trên đơn là thuế của một mức giá mà không ai trả.

**Từ v3.0**, giảm giá trừ trước, thuế tính trên phần còn lại — đúng thông lệ VAT/GST.

### Mã giảm giá theo PHẦN TRĂM → tổng tiền **không đổi**

Chỉ **con số thuế** được sửa lại cho đúng. Ví dụ đơn 100, thuế 10%, mã giảm 10%:

| | Thuế | Giảm giá | Khách trả |
|---|---|---|---|
| Trước | 10 | 11 | **99** |
| Từ v3.0 | **9** | 10 | **99** |

Khách trả y như cũ. Nhưng trước đây bạn **khai báo thừa 1 đồng tiền thuế** — đó mới là phần được sửa.

### Mã giảm giá theo SỐ TIỀN CỐ ĐỊNH → khách trả **ít hơn**

Ví dụ đơn 100, thuế 10%, mã giảm 20:

| | Thuế | Giảm giá | Khách trả |
|---|---|---|---|
| Trước | 10 | 20 | **90** |
| Từ v3.0 | **8** | 20 | **88** |

Vì thuế nay chỉ tính trên 80 còn lại. **Đây là thay đổi khách hàng nhìn thấy được** — nếu bạn dùng mã giảm giá
số tiền cố định, doanh thu mỗi đơn sẽ thấp hơn một chút so với trước.

### Mã phần trăm hết bị làm tròn thiệt cho khách

Có một lỗi làm tròn khiến mã phần trăm **giảm ít hơn mức đã quảng cáo**: giảm 10% của 236 chỉ trả lại 23 thay
vì 23,60. Sai số luôn nghiêng về phía cửa hàng, và xảy ra trên mọi đơn dùng mã phần trăm. Nay đã sửa.

### Trả hàng một phần đã có cơ sở tính đúng

Giảm giá nay được **chia về từng dòng hàng** theo tỷ lệ. Khách trả lại một sản phẩm trong đơn có mã giảm giá thì
hệ thống biết chính xác phần giảm nào thuộc về sản phẩm đó — trước đây chỉ biết "cả đơn được giảm 60.000", không
biết bao nhiêu thuộc về món hàng đang trả, nên rất dễ **hoàn thừa**.

Việc nâng cấp **chia phần giảm cho cả đơn cũ**, nhưng **không đổi một đồng nào** của chúng. Vì vậy đơn cũ sẽ có
phần giảm ở từng dòng trong khi số thuế vẫn là thuế **trước** giảm. Điều đó **không phải dữ liệu hỏng** — đó là
con số đã phát hành trên chứng từ, và sửa nó sẽ phá mọi đối chiếu kế toán đã làm trên đơn đó.

---

## Thay đổi 5 — Mã hoá dữ liệu nhạy cảm (từ 3.0.3)

**Từ `gp247/core` 3.0.3**, các thông tin nhạy cảm lưu trong cơ sở dữ liệu — **mật khẩu SMTP, secret Google
Captcha, OAuth client secret, license plugin** — được **mã hoá at-rest** (lưu dạng `enc:v2:…`, không còn thô).
Bản trước đó lưu các giá trị này dạng chữ thường.

Điểm quan trọng cho người vận hành:
- GP247 dùng một **khoá riêng `GP247_ENCRYPTION_KEY`** trong `.env`, **tách khỏi `APP_KEY`** — nhờ vậy đổi/tạo
  lại `APP_KEY` **không** làm hỏng bí mật. Nên đặt khoá riêng ngay sau khi cài; chưa đặt thì tạm dùng `APP_KEY`
  và `gp247:doctor` sẽ nhắc.
- **Đổi khoá phải qua lệnh** `php artisan gp247:encryption-key-rotate` (mã hoá lại toàn bộ bằng khoá mới), **không
  sửa tay** — sửa tay sẽ làm mọi bí mật cũ không đọc được.
- Site cũ nâng lên: bí mật đang có được **tự động mã hoá** khi chạy migration nâng cấp (`gp247:update`); lười cho
  phần còn lại (lưu lại là mã).

Chi tiết đầy đủ (các bước đổi khoá, khôi phục khi mất khoá, mã hoá cột bảng khác cho lập trình viên): xem
[Mã hoá dữ liệu nhạy cảm](./data-encryption_vi.md).

---

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi ghi một khoản thu hoặc hoàn tiền**
- **Số tiền phải lớn hơn 0** — một khoản bằng 0 không phải là tiền chuyển tay. Muốn ghi
  chiều ngược lại thì dùng chức năng hoàn tiền, không nhập số âm.
- **Kỳ kế toán đã khoá thì không ghi được** vào ngày thuộc kỳ đó (chỉ khi bạn dùng
  InOut) — để số liệu kỳ đã chốt không đổi sau lưng bạn.

**Khi cổng thanh toán báo tiền về**
- **Hệ thống ghi đúng số tiền cổng trả về**, kể cả khi số đó **khác** tổng đơn. Lệch thì
  đơn ở trạng thái trả một phần và được ghi log để bạn đối chiếu — hệ thống **không** tự
  làm tròn cho khớp, vì như vậy là giấu đi một sai lệch có thật.
- **Cùng một giao dịch báo về nhiều lần chỉ được ghi một lần.** Cổng thanh toán có thể
  gửi lại thông báo; tiền không bị cộng đôi.

**Khi áp mã giảm giá**
- **Giảm giá không vượt quá tiền hàng (chưa thuế)** — giảm nhiều hơn giá trị hàng thì đơn sẽ có tổng âm, thứ
  không màn hình nào phía sau xử lý được. Nhập vượt sẽ bị cắt về đúng bằng tiền hàng.

**Khi lập phiếu thu tay trong InOut**
- **Chọn đơn thì tiền vào sổ của đơn**, không tạo thêm phiếu — để một khoản tiền không
  nằm ở hai nơi.

---

## Dành cho lập trình viên plugin

Hai thay đổi có thể ảnh hưởng plugin tự viết:

- **`ShopOrderTotal::PAID` đổi giá trị từ `2` sang `3`** (và ba hằng số cùng nhóm cũng
  dịch tương ứng). Chúng vốn **đang sai** — lệch một bậc so với bảng `shop_payment_status`
  — nên giữ nguyên còn nguy hiểm hơn. Nếu plugin của bạn so sánh `payment_status`, hãy
  dùng `ShopPaymentStatus::{UNPAID, PARTIAL, PAID, REFUND}`.
- **Plugin thanh toán nên chuyển sang `ShopOrder::recordPayment()` / `recordRefund()`.**
  `processPaymentPaid()` vẫn chạy (đã đánh dấu `@deprecated`) nhưng nó chỉ diễn tả được
  "thu đủ": không đặt cọc, không thu một phần, không mang mã giao dịch nên gọi lại **không
  chống trùng được**. Hai hàm mới nhận số tiền + phương thức + mã giao dịch + ngày thu.

- **`shop_order_detail` có thêm hai cột: `discount` và `tax_rate`.** Cột thứ hai không phải để cho đẹp: trước
  đây dòng đơn chỉ lưu **số tiền** thuế còn thuế suất nằm ở sản phẩm, nên (a) không tính lại được thuế khi giảm
  giá đổi cơ sở, và (b) một đơn cũ có thể **đổi số thuế** chỉ vì ai đó sửa thuế của sản phẩm. Nay thuế suất được
  chốt trên chứng từ.
- **Ô nhập thuế của dòng trong admin nay lưu THUẾ SUẤT** suy ra từ số tiền bạn gõ. Bạn vẫn gõ số tiền như cũ,
  nhưng con số đó từ nay **co giãn theo cơ sở tính thuế** như một khoản thuế thật. Nguyên tắc phân định:
  **khoản nào KHÔNG được thay đổi khi có giảm giá thì đó là `other_fee`, không phải `tax`.**
- Cần phân bổ giảm giá trong code của bạn thì dùng `gp247_allocate_discount()` — đừng tự chia lại, phần dư làm
  tròn phải rơi đúng một chỗ để `Σ giảm từng dòng` luôn bằng giảm giá của đơn.

Nhãn trạng thái nên lấy từ bảng (`ShopPaymentStatus::getIdAll()`), **không** hardcode
theo số — admin có quyền đổi tên trạng thái.

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi phải làm gì trước khi nâng cấp?**

→ Sao lưu cơ sở dữ liệu. Nếu dùng InOut, nên xuất/in báo cáo dòng tiền kỳ gần nhất để
đối chiếu sau khi nâng.

**Câu 2: Nâng cấp xong doanh thu tháng trước khác con số cũ, có phải hệ thống tính sai không?**

→ Không. Doanh thu nay tính theo **ngày thu tiền** thay vì ngày đặt hàng — xem Thay đổi 2.
Đây là số đúng hơn.

**Câu 3: Số "Đã nhận" của các đơn cũ có bị mất không?**

→ Không. Việc nâng cấp tự tạo cho mỗi đơn đã có tiền một dòng thu tương ứng, nên con số
giữ nguyên.

**Câu 4: Tôi dùng mã giảm giá phần trăm — khách có phải trả khác đi không?**

→ Không. Tổng tiền y như cũ; chỉ con số thuế được sửa lại cho đúng (trước đây khai thừa). Với mã **số tiền cố
định** thì khách trả **ít hơn** — xem Thay đổi 4.

**Câu 5: Đơn tôi đánh dấu "Đã thanh toán" bằng tay có bị đổi không?**

→ Không. Hệ thống cố ý giữ nguyên những đơn đó, vì đổi tự động sẽ xoá mất quyết định
của bạn. Chỉ có phần "Đã nhận" là 0 cho tới khi bạn ghi khoản thu.

**Câu 6: Vì sao ô "Đã nhận" không cho sửa trực tiếp nữa?**

→ Vì một con số không nói được tiền vào ngày nào và bằng cách nào. Nay bạn ghi từng
khoản thu, mỗi khoản có ngày riêng — đó là điều kiện để báo cáo dòng tiền đúng kỳ.

**Câu 7: Tôi không dùng plugin InOut thì có ảnh hưởng gì không?**

→ Thay đổi 2 không ảnh hưởng tới bạn. Thay đổi 1 và 3 vẫn áp dụng.

**Câu 8: Đơn cũ hiện phần giảm ở từng dòng nhưng số thuế trông không khớp — có phải lỗi không?**

→ Không. Việc nâng cấp cố ý **không đổi một đồng nào** của đơn cũ, nên số thuế vẫn là thuế trước giảm — đó là
con số đã phát hành trên chứng từ. Phần chia về dòng chỉ để trả hàng một phần tính cho đúng.

**Câu 9: Tôi đang chạy bản cũ hơn v2.1 thì sao?**

→ Đường nâng cấp được hỗ trợ bắt đầu từ **v2.1**. Site đang chạy **v2.1 hoặc v2.2** nâng thẳng lên v3.0.
Nếu site cũ hơn v2.1, hãy nâng lên v2.1 trước rồi mới lên v3.0.

**Câu 10: Sau khi nâng, làm sao kiểm tra tiền đã ghi đúng?**

→ Mở một đơn đã thanh toán, xem mục **lịch sử thanh toán** dưới phần tổng tiền: phải có
dòng thu với số tiền và ngày đúng, và ô "Còn lại" bằng 0.

---

<sub>📅 **Cập nhật lần cuối:** 2026-09-04 · ✍️ **Tác giả (Author):** GP247</sub>
