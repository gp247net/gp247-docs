> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./update-gp247.md)

# Hướng dẫn cập nhật GP247

## Giới thiệu
Tài liệu này hướng dẫn cách cập nhật một website GP247 (S-Cart) lên phiên bản mới
một cách **an toàn cho site đang chạy** — không mất dữ liệu, không mất tùy biến.
Tài liệu dành cho người quản trị site, kể cả người không rành kỹ thuật: chỉ cần
làm theo từng bước, copy đúng lệnh là được. Điểm cốt lõi: **ưu tiên bộ lệnh đã
chuẩn hóa của GP247** (`gp247:update`, `gp247:info`, `gp247:doctor`) — bạn không
cần nhớ nhiều lệnh lẻ cho từng gói (core/front/shop) nữa.

> ℹ️ **Hai loại lệnh cần phân biệt:**
> - **Lệnh chuẩn hóa (nên dùng):** `gp247:update` — một lệnh tự lo cập nhật cho
>   *mọi* gói GP247 đang có trên site.
> - **Lệnh theo từng gói (nâng cao):** `gp247:core-update`, `gp247:shop-update`…
>   — chạy riêng cho một gói. Bình thường bạn **không cần** gọi trực tiếp, vì
>   `gp247:update` đã gọi chúng giúp bạn theo đúng thứ tự.

---

## Cập nhật gồm 2 phần
Một lần cập nhật GP247 luôn có **2 phần tách biệt**, phải làm **đủ cả hai**:

1. **Cập nhật mã nguồn** — tải code mới của các gói về máy, bằng **Composer**
   (`composer update`). Bước này chỉ đổi file trong thư mục `vendor/`.
2. **Áp dụng thay đổi vào site** — cập nhật cấu trúc dữ liệu (database), làm mới
   bộ nhớ đệm (cache)… bằng lệnh artisan chuẩn hóa **`gp247:update`**.

> ⚠️ Chỉ chạy `composer update` là **chưa đủ**. Nếu bỏ qua bước 2, site có thể lỗi
> vì code mới nhưng cấu trúc dữ liệu / cache vẫn là bản cũ.

> ℹ️ **Có từ GP247 v2.1 (bản public đầu tiên):** mọi thay đổi làm đổi cấu trúc dữ liệu —
> **hoặc đổi ý nghĩa của dữ liệu đang có** — đều kèm sẵn phần chuyển đổi tự động, và
> `gp247:update` chạy giúp bạn. Đó là lý do bước 2 là bắt buộc, và là lý do bạn **không
> bao giờ** phải tự chạy `php artisan migrate`. Mỗi phần chuyển đổi đều an toàn khi chạy
> lại: chạy lệnh hai lần cũng không thay đổi gì thêm.

---

## Chuẩn bị trước khi cập nhật

1. **Sao lưu (backup) trước tiên.** Sao lưu **database** và thư mục mã nguồn.
   Đây là bước quan trọng nhất — nếu có sự cố, bạn luôn quay lại được bản cũ.

2. **Kiểm tra phiên bản hiện tại.** Mở **Terminal** (trên Windows là "Command
   Prompt"), di chuyển vào thư mục gốc website, rồi gõ:

   ```bash
   php artisan gp247:info
   ```

   Nếu thành công, màn hình liệt kê phiên bản các gói đang cài (core / front /
   shop). Ghi lại con số này để so sánh sau khi cập nhật.

3. **(Khuyến nghị) Kiểm tra môi trường.** Chạy lệnh chẩn đoán để chắc chắn máy chủ
   đủ điều kiện (phiên bản PHP, extension bắt buộc, quyền ghi file, kết nối DB):

   ```bash
   php artisan gp247:doctor
   ```

   Nếu tất cả mục đều **OK**, bạn có thể cập nhật yên tâm. Nếu có mục báo **fail**,
   xử lý dứt điểm mục đó trước rồi mới cập nhật.

---

## Các bước cập nhật (cách chuẩn, nên dùng)

1. Vào thư mục gốc website trong Terminal.

2. **Tải mã nguồn mới về** bằng Composer. Chạy đúng gói bạn đang dùng — nếu chưa
   chắc, cứ chạy cả ba lệnh, gói nào không có sẽ được bỏ qua:

   ```bash
   composer update gp247/core
   composer update gp247/front
   composer update gp247/shop
   ```

   Nếu thành công, Composer in ra danh sách các gói vừa được nâng cấp.

3. **Áp dụng thay đổi vào site** bằng một lệnh chuẩn hóa duy nhất:

   ```bash
   php artisan gp247:update
   ```

   Lệnh này tự động làm tuần tự (an toàn, không phá dữ liệu):
   - Cập nhật **core**;
   - Nâng cấp cấu trúc dữ liệu **shop** — *chỉ khi* site có cài module shop;
   - Làm mới lại **cache** (route/config) để site nhận code mới.

   Nếu thành công, màn hình in ra các bước đã chạy (ví dụ `gp247:core-update`,
   `gp247:cache-rebuild`).

4. **Kiểm tra lại.** Chạy lại `php artisan gp247:info` và đối chiếu số phiên bản
   với con số bạn ghi ở phần chuẩn bị — nếu đã tăng lên bản mới là xong. Mở thử
   site (trang chủ + trang quản trị) để chắc chắn hiển thị bình thường.

> ✅ Với đa số website, **4 bước trên là đủ**. Các phần dưới đây chỉ dùng khi bạn
> có nhu cầu đặc biệt (làm mới bản dịch, làm mới giao diện đã publish).

---

## Tùy chọn nâng cao

### A. Làm mới file ngôn ngữ (`--overwrite-lang`)
Mặc định `gp247:update` **giữ nguyên** mọi bản dịch bạn đã tự sửa trong Admin.
Nếu bạn muốn kéo về bản dịch mặc định mới nhất của gói (**sẽ ghi đè** các chuỗi
ngôn ngữ bạn từng chỉnh), thêm tùy chọn:

```bash
php artisan gp247:update --overwrite-lang
```

> ⚠️ Chỉ dùng khi bạn thật sự muốn reset bản dịch về mặc định. Nếu đã sửa nhiều,
> hãy backup bảng ngôn ngữ trước. Chi tiết xem tài liệu
> [Hệ thống ngôn ngữ](./language-system_vi.md).

### B. Làm mới giao diện / asset đã publish (`--publish=<tokens>`)
`composer update` chỉ làm mới code trong `vendor/`. Các file giao diện đã **publish**
ra ngoài (CSS/JS admin trong `public/GP247`, view trong `resources/views/vendor/…`,
template storefront trong `app/GP247/…`) **không** tự động được ghi đè. Muốn làm mới
chúng, thêm tùy chọn `--publish` với danh sách "token" (ngăn cách bằng dấu phẩy):

```bash
php artisan gp247:update --publish=core-public                 # an toàn: chỉ làm mới CSS/JS admin đã build
php artisan gp247:update --publish=core-public,front-view      # đồng thời ghi đè template storefront (PHÁ DỮ LIỆU)
php artisan gp247:update --publish=all                         # làm mới TẤT CẢ (PHÁ DỮ LIỆU — backup trước)
```

Các token hợp lệ: `core-public`, `core-view`, `front-public`, `front-view`,
`shop-view-admin`, `shop-view-front`, `all`.

> 📖 Muốn hiểu từng token ảnh hưởng tới thư mục nào, xem mục "`gp247:update`" trong
> [Tài liệu tham chiếu dòng lệnh](./command-line-reference_vi.md).

---

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi chạy cập nhật thông thường (`gp247:update`)**

- **An toàn cho site đang chạy** — lệnh chỉ *nâng cấp*, không bao giờ chạy bước cài
  lại (re-install) phá dữ liệu. Vì vậy bạn dùng được ngay trên site thật.
- **Chạy được cả trên hosting hạn chế** — không đòi hỏi cron, queue worker hay
  websocket. Miễn là chạy được lệnh `php artisan`, site vẫn cập nhật đầy đủ.
- **Bước shop chỉ chạy khi đã cài shop** — nếu site chưa cài module shop, lệnh tự
  bỏ qua phần nâng cấp shop, không báo lỗi.

**Khi dùng `--overwrite-lang`**

- **Sẽ ghi đè bản dịch bạn đã sửa** về mặc định của gói — đây là lý do mặc định
  KHÔNG bật tùy chọn này. Chỉ bật khi bạn chủ đích muốn reset.

**Khi dùng `--publish=<tokens>`**

- **Chỉ `core-public` là an toàn** — đó là CSS/JS admin đã biên dịch sẵn, ghi đè
  không mất tùy biến của bạn.
- **Mọi token còn lại đều PHÁ DỮ LIỆU** — chúng ghi đè lên view/template, tức là
  **xóa các tùy chỉnh giao diện** bạn đã sửa tại chỗ. Hãy backup thư mục đích trước.
- **Không có cờ `--force`** — chính việc bạn *tự gõ* một token phá-dữ-liệu đã là sự
  đồng thuận. Khi chạy trong Terminal tương tác, lệnh vẫn **cảnh báo và hỏi xác
  nhận** (mặc định là "không"), để tránh lỡ tay.
- **Gõ sai token → dừng ngay, chưa làm gì cả** — nếu bạn gõ nhầm một token không
  hợp lệ, lệnh báo lỗi và thoát *trước khi* cập nhật, nên site không bị đụng tới.

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi chỉ chạy `composer update` được không, có cần `gp247:update` không?**

→ Bắt buộc chạy cả hai. `composer update` chỉ tải code mới về `vendor/`;
`gp247:update` mới thật sự áp dụng thay đổi (cập nhật DB, làm mới cache) vào site.

**Câu 2: Tôi phải nhớ `gp247:core-update`, `gp247:shop-update`… riêng cho từng gói?**

→ Không. Cứ dùng lệnh chuẩn hóa `gp247:update` — nó tự gọi các lệnh theo gói giúp
bạn, đúng thứ tự, và chỉ chạy phần shop khi site có cài shop.

**Câu 3: Chạy `gp247:update` có mất dữ liệu hay mất tùy biến không?**

→ Không. Mặc định lệnh chỉ nâng cấp an toàn: giữ nguyên dữ liệu, giữ nguyên bản
dịch bạn đã sửa, và không đụng tới view/template đã publish. Chỉ khi bạn *chủ động*
thêm `--overwrite-lang` hoặc `--publish` (với token khác `core-public`) thì mới có
ghi đè.

**Câu 4: Site tôi trên shared hosting không có cron/queue, cập nhật được không?**

→ Được. `gp247:update` không cần cron, queue worker hay websocket — chỉ cần chạy
được `php artisan` là đủ.

**Câu 5: Sau khi cập nhật, giao diện admin bị lỗi CSS/JS thì sao?**

→ Làm mới asset admin đã build (an toàn, không mất tùy biến):
`php artisan gp247:update --publish=core-public`.

**Câu 6: `--publish=all` khác gì `--publish=core-public`?**

→ `core-public` chỉ làm mới CSS/JS admin (an toàn). `all` làm mới **tất cả**, bao
gồm cả view/template — sẽ **ghi đè tùy chỉnh giao diện** của bạn. Hãy backup trước
khi dùng `all`.

**Câu 7: Làm sao biết cập nhật đã thành công?**

→ Chạy `php artisan gp247:info` và so số phiên bản với lúc trước khi cập nhật; nếu
đã tăng lên bản mới và site chạy bình thường là xong.

**Câu 8: Cập nhật giữa chừng báo lỗi thì có sao không?**

→ `gp247:update` chạy theo từng bước và dừng lại nếu một bước lỗi, nên site thường
không rơi vào trạng thái dở dang. Đọc thông báo lỗi để xử lý, hoặc khôi phục từ bản
backup rồi thử lại.

**Câu 9: Có cần chạy `gp247:doctor` mỗi lần cập nhật không?**

→ Không bắt buộc, nhưng nên chạy trước khi cập nhật (nhất là khi vừa đổi máy chủ /
phiên bản PHP) để phát hiện sớm vấn đề môi trường.

**Câu 10: Tôi muốn xem đầy đủ mọi lệnh GP247 và tùy chọn của chúng ở đâu?**

→ Xem [Tài liệu tham chiếu dòng lệnh](./command-line-reference_vi.md) — liệt kê tất
cả lệnh artisan của GP247 kèm tùy chọn và ví dụ.

## Lịch sử thay đổi
<!-- Chỉ ghi khi có thay đổi về logic/hành vi. Dòng mới nhất ở trên cùng. -->

| Ngày | Phiên bản GP247 | Thay đổi |
| --- | --- | --- |
| 2026-08-29 | gp247/core 2.2 | `gp247:update` nay chạy thêm **migration nâng cấp của core** (qua `gp247:core-update`), trước đây lệnh này chỉ seed lại. Ghi rõ quy tắc có từ bản public v2.1: mọi thay đổi phá vỡ đều kèm phần chuyển đổi dữ liệu tự động, nên `composer update` + `gp247:update` là toàn bộ quy trình. |

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-29 · ✍️ **Tác giả (Author):** GP247</sub>
