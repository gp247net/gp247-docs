> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./product-stock-management.md)

# Quản lý tồn kho sản phẩm trong GP247

## Giới thiệu
Tài liệu này giải thích **nghiệp vụ quản lý tồn kho (stock)** của GP247/S-Cart cho **chủ cửa hàng không rành kỹ thuật**: tồn kho tăng/giảm khi nào, hai cấu hình quan trọng quyết định có cho bán vượt tồn hay không, và vì sao đơn tạo trong trang quản trị (admin) cũng trừ kho **giống hệt** đơn khách đặt ngoài storefront. Đọc xong bạn sẽ tự tin cấu hình và hiểu con số kho biến động thế nào.

---

## 1. Hai con số của mỗi sản phẩm

Mỗi sản phẩm có hai con số về số lượng bạn cần nhớ:

- **Tồn kho (`stock`)**: số lượng đang còn để bán.
- **Đã bán (`sold`)**: số lượng đã bán ra (chỉ để thống kê, tự cộng lên).

Ngoài ra có **Số lượng tối thiểu (`minimum`)**: số lượng ít nhất khách phải mua trong một lần (mặc định là 1).

> Khi một đơn phát sinh, hệ thống **trừ `stock`** và **cộng `sold`** cùng một lượng. Khi gỡ đơn/dòng hàng, kho được **hoàn lại**.

---

## 2. Sơ đồ vòng đời tồn kho

```mermaid
flowchart TD
    A["📦 Tồn kho sản phẩm (stock)"]
    A -->|"Tạo đơn (ngoài storefront HOẶC trong admin)"| B["stock giảm · sold tăng<br/>theo số lượng đặt"]
    A -->|"Thêm / tăng số lượng dòng hàng trong admin"| B
    B -->|"Xóa đơn · xóa dòng hàng · giảm số lượng"| A
    A -.->|"Sản phẩm loại Gói (Bundle)"| C["Trừ kho TỪNG sản phẩm con<br/>theo số lượng trong gói"]
```

> Nếu nơi bạn đọc không vẽ được sơ đồ, hãy hiểu đơn giản: **có đơn thì trừ kho, gỡ đơn thì trả kho** — bất kể đơn được tạo ở đâu.

---

## 3. Hai cấu hình quyết định việc bán theo tồn kho

Có hai công tắc trong **Cấu hình cửa hàng** ảnh hưởng trực tiếp tới tồn kho:

| Cấu hình | Nằm ở | Ý nghĩa |
| --- | --- | --- |
| **Quản lý tồn kho** (`product_stock`) | Cấu hình **Sản phẩm** | Bật thì cửa hàng theo dõi tồn kho. **Tắt** thì không quan tâm kho — luôn cho mua. |
| **Cho phép mua khi hết hàng** (`product_buy_out_of_stock`) | Cấu hình **Đơn hàng** | Bật thì cho khách mua **vượt quá** tồn kho (kho có thể về âm). Tắt thì **chặn** khi vượt tồn. |

Quy tắc "còn đủ hàng để bán" của hệ thống là:

> Cho phép đặt số lượng nếu **( cho phép mua khi hết hàng )** HOẶC **( không quản lý tồn kho )** HOẶC **( tồn kho ≥ số lượng đặt )**.

> **Quan hệ phân cấp (quan trọng):** `product_stock` là **công tắc tổng**. `product_buy_out_of_stock` **chỉ có tác dụng khi `product_stock` đang bật**. Nếu tắt quản lý tồn kho, tuỳ chọn "cho phép mua khi hết hàng" **không còn ý nghĩa** (hệ thống luôn cho mua). Hai công tắc **không mâu thuẫn** — chúng ghép bằng HOẶC.

Ngoài ra còn hai cấu hình liên quan (không bắt buộc):
- **Ẩn sản phẩm hết hàng** (`product_display_out_of_stock`): khi bật quản lý kho, sản phẩm về 0 sẽ bị ẩn khỏi storefront.
- **Đặt trước** (`product_preorder`): cho đặt trước kể cả chưa có hàng.

---

## 4. Khi nào tồn kho GIẢM, khi nào ĐƯỢC HOÀN?

**Giảm kho** (`stock` giảm, `sold` tăng) xảy ra khi:
1. Khách đặt hàng thành công ngoài **storefront** (thanh toán xong).
2. Bạn **tạo đơn trong admin** (màn Tạo đơn hàng).
3. Bạn **thêm** một dòng hàng, hoặc **tăng số lượng** một dòng hàng của đơn trong admin.

**Hoàn kho** (`stock` được cộng lại) xảy ra khi:
1. **Xóa cả đơn hàng** → hoàn kho tất cả dòng hàng của đơn.
2. **Xóa một dòng hàng** trong đơn.
3. **Giảm số lượng** một dòng hàng (chỉ hoàn phần chênh lệch).

> Điểm quan trọng: **đơn tạo trong admin cũng trừ/hoàn kho y như đơn khách đặt ngoài**. Trước đây đơn admin từng không trừ kho, gây lệch số liệu — nay đã thống nhất.

---

## 5. Sản phẩm Gói (Bundle) trừ kho thế nào?

Với sản phẩm loại **Gói (Bundle/Build)**, khi bán **1 gói**, hệ thống trừ kho **từng sản phẩm con** theo số lượng khai báo trong gói (đồng thời cũng trừ kho của chính gói nếu gói có quản lý kho).

Ví dụ: "Bộ quà" = 1 hộp bánh + 2 chai nước. Bán 1 bộ → trừ **1 hộp bánh** và **2 chai nước** khỏi kho.

> Chi tiết cách tạo và cấu hình Gói: xem **[Sản phẩm gói (Bundle/Combo)](./product-bundle_vi.md)** và **[Tổ chức sản phẩm](./product-structure_vi.md)**.

---

## 6. Chặn vượt tồn: áp dụng THỐNG NHẤT cho cả storefront và admin

Khi bật **Quản lý tồn kho** (`product_stock`) và **tắt** "Cho phép mua khi hết hàng" (`product_buy_out_of_stock`), nếu số lượng đặt vượt tồn:

- **Ngoài storefront (khách tự mua):** hệ thống **chặn** — không cho đặt đơn vượt tồn.
- **Trong admin (bạn tự tạo/sửa đơn):** hệ thống **cũng chặn y như storefront** — không lưu được đơn/dòng hàng vượt tồn, hiện thông báo lỗi. (Áp cho cả: tạo đơn mới, thêm dòng hàng, và tăng số lượng dòng hàng.)

> Nói ngắn gọn: **cả khách lẫn admin đều bị chặn** khi vượt tồn — cùng một quy tắc, cùng một cấu hình `product_buy_out_of_stock`.
>
> **Lưu ý thay đổi (từ 2026-08-16):** trước đây admin chỉ bị *cảnh báo* nhưng vẫn lưu được đơn vượt tồn; nay đã **thống nhất chặn** để tránh bán quá số lượng thật ở mọi kênh.
>
> **Vẫn muốn tạo đơn vượt tồn qua admin** (bán trước, nợ hàng, nhập bù sau)? → **bật** "Cho phép mua khi hết hàng" (`product_buy_out_of_stock`). Khi đó cả hai kênh đều cho phép vượt tồn (kho có thể về âm).

---

## 7. Cấu hình từng bước (dành cho người mới)

1. Đăng nhập trang quản trị, vào **Cấu hình cửa hàng**.
2. Mở nhóm **Sản phẩm**, tìm mục **Quản lý tồn kho** (`product_stock`):
   - Bật nếu bạn muốn theo dõi số lượng tồn.
   - Tắt nếu bán dịch vụ/số không giới hạn (luôn cho mua).
3. Mở nhóm **Đơn hàng**, tìm mục **Cho phép mua khi hết hàng** (`product_buy_out_of_stock`):
   - **Tắt** nếu muốn chặn bán vượt tồn (khuyến nghị cho hàng vật lý).
   - Bật nếu chấp nhận cho mua kể cả khi hết (kho có thể về âm).
4. Vào từng **Sản phẩm**, nhập **Tồn kho (`stock`)** và **Số lượng tối thiểu (`minimum`)** nếu cần.
5. Lưu lại. Nếu thành công, hệ thống báo cập nhật thành công và con số tồn kho sẽ áp dụng ngay ở storefront.

> Mẹo kiểm tra: tạo thử một đơn trong admin cho một sản phẩm có tồn kho, rồi mở lại sản phẩm đó xem `stock` đã giảm và `sold` đã tăng đúng chưa.

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi muốn cửa hàng không quan tâm tồn kho (luôn cho mua) thì làm sao?**

→ Tắt cấu hình **Quản lý tồn kho** (`product_stock`). Khi đó hệ thống luôn cho mua, không kiểm tra số lượng tồn.

**Câu 2: Tôi muốn chặn khách mua khi hết hàng?**

→ Bật **Quản lý tồn kho** và **tắt** "Cho phép mua khi hết hàng" (`product_buy_out_of_stock`). Khi tồn về 0, khách không đặt được nữa.

**Câu 3: Vì sao tôi tạo đơn trong admin mà tồn kho không đổi (ở bản cũ)?**

→ Đó là lỗi cũ: đơn admin không trừ kho. Bản hiện tại đã sửa — đơn admin trừ/hoàn kho giống đơn khách đặt ngoài.

**Câu 4: Trong admin tôi tạo đơn vượt tồn, hệ thống có cho lưu không?**

→ **Không** (khi đã bật "Quản lý tồn kho" và **tắt** "Cho phép mua khi hết hàng"). Từ **2026-08-16**, admin **bị chặn giống hệt khách** ngoài storefront — không tạo được đơn/dòng hàng vượt tồn. Nếu thực sự cần vượt tồn (bán trước, nợ hàng), hãy **bật** "Cho phép mua khi hết hàng".

**Câu 5: Tồn kho có thể về số âm không?**

→ Có, **chỉ khi bật** "Cho phép mua khi hết hàng" (`product_buy_out_of_stock`). Khi đó cả khách lẫn admin đều đặt được vượt tồn và kho về âm (bạn đang nợ hàng). Nếu **tắt** cấu hình này thì **không kênh nào** (kể cả admin) tạo được đơn vượt tồn.

**Câu 6: Xóa đơn thì tồn kho có tự trả lại không?**

→ Có. Xóa cả đơn hoặc xóa từng dòng hàng đều hoàn kho tương ứng. Giảm số lượng dòng hàng thì hoàn phần chênh lệch.

**Câu 7: Sản phẩm Gói (Bundle) trừ kho ra sao?**

→ Bán 1 gói sẽ trừ kho **từng sản phẩm con** theo số lượng trong gói. Xem thêm tài liệu [Sản phẩm gói](./product-bundle_vi.md).

**Câu 8: "Đã bán" (`sold`) khác "Tồn kho" (`stock`) thế nào?**

→ `stock` là số còn lại để bán; `sold` là số đã bán ra, chỉ dùng để thống kê và tự cộng khi có đơn.

**Câu 9: Tôi muốn ẩn sản phẩm khi hết hàng trên storefront?**

→ Bật cấu hình **Ẩn sản phẩm hết hàng** (`product_display_out_of_stock`); khi có quản lý kho, sản phẩm về 0 sẽ không hiện.

**Câu 10: Số lượng tối thiểu (`minimum`) để làm gì?**

→ Bắt khách mua tối thiểu một lượng nhất định mỗi lần (ví dụ bán sỉ tối thiểu 10). Mặc định là 1.

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-16 · ✍️ **Tác giả (Author):** GP247</sub>
