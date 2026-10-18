> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./shipping.md)

# Vận chuyển trong GP247 — thiết lập cho cửa hàng và hướng dẫn cho lập trình viên

## Giới thiệu

Tài liệu này giải thích cách GP247 tính phí giao hàng, tạo vận đơn với hãng vận chuyển và theo dõi đơn tới
tay khách. **Phần A** dành cho người quản lý cửa hàng: cài đặt, thiết lập từng ô, xử lý vận đơn hằng ngày,
có sơ đồ để hình dung toàn bộ luồng. **Phần B** dành cho lập trình viên: GP247 đã có sẵn những gì, nằm ở
đâu, và cần làm gì khi tích hợp một hãng mới hay chỉnh sửa. Đọc xong, chủ cửa hàng tự bật được vận chuyển
cho đúng; lập trình viên biết chính xác phần nào chỉ cần "cắm vào", phần nào không được viết lại.

> ℹ️ **Có từ:** gp247/shop 3.1.4 (lớp vận chuyển chuẩn: nhiều dịch vụ mỗi hãng, vận đơn, theo dõi, thu hộ).
> Trên phiên bản cũ hơn, mỗi plugin vận chuyển chỉ trả về một mức phí.

---

## Phần A — Dành cho người quản lý cửa hàng

### A1. Các khái niệm cần biết

| Khái niệm | Nghĩa đời thường |
|---|---|
| **Hãng vận chuyển** | Một **plugin vận chuyển** cài vào cửa hàng. Có plugin chỉ tính phí theo quy tắc bạn đặt (ví dụ `ShippingStandard`: phí cố định, miễn phí từ một mức tiền); có plugin nối thẳng API của hãng giao hàng. |
| **Dịch vụ** | Một gói giao của hãng: đường bộ, nhanh, hoả tốc… Mỗi dịch vụ hiện thành **một lựa chọn riêng** ở trang thanh toán, kèm giá và số ngày giao dự kiến. |
| **Vận đơn** | Phiếu gửi hàng tạo với hãng cho một đơn — có **mã vận đơn** để tra cứu. Chỉ có với hãng hỗ trợ tạo vận đơn. |
| **Kho lấy hàng** | Địa chỉ người gửi — hãng đến đây lấy hàng và tính phí từ đây. |
| **Thu hộ (COD)** | Shipper thu tiền mặt của khách khi giao rồi hãng chuyển lại cho bạn. |
| **Webhook** | Hãng tự "gọi về" website mỗi khi đơn đổi trạng thái — bạn không phải bấm cập nhật. |

### A2. Toàn cảnh: hệ thống chạy thế nào

```mermaid
flowchart TD
    subgraph KH["🛒 Khách đặt hàng"]
        A1["Nhập địa chỉ giao<br/>(Tỉnh/Thành, Phường/Xã…)"] --> A2["Bước Vận chuyển:<br/>hệ thống hỏi giá các hãng đang bật"]
        A2 --> A3["Khách chọn một dịch vụ<br/>(giá + số ngày dự kiến)"]
        A3 --> A4["Chọn thanh toán, đặt đơn<br/>→ phí đã chọn được lưu vào đơn"]
    end
    subgraph AD["🧑‍💼 Bạn xử lý đơn"]
        B1["Mở chi tiết đơn → khối Vận chuyển"] --> B2["Tạo vận đơn<br/>(sửa khối lượng, kích thước kiện, COD nếu cần)"]
        B2 --> B3["Tải nhãn, dán lên kiện hàng"]
    end
    subgraph H["🚚 Hãng vận chuyển"]
        C1["Lấy hàng tại kho"] --> C2["Vận chuyển → giao cho khách"]
        C2 --> C3["Thu hộ (nếu có) → đối soát tiền về bạn"]
    end
    A4 --> B1
    B3 --> C1
    C2 -- "webhook / Đồng bộ" --> D1["Trạng thái vận đơn cập nhật<br/>→ Trạng thái giao hàng của đơn tự đổi"]
    C3 -- "nếu bật tự ghi COD" --> D2["Tiền thu hộ ghi vào sổ thanh toán của đơn"]
```

Điểm đáng nhớ: **giá khách thấy là giá hãng báo cho đúng địa chỉ và giỏ hàng đó**. Khách đổi địa chỉ hay giỏ
hàng thì hệ thống hỏi giá lại; đơn luôn lưu đúng mức phí khách đã chọn.

### A3. Cài đặt từng bước

1. **Cài plugin hãng vận chuyển.** Vào **Admin → Plugins**, tìm plugin vận chuyển (nhóm *Shipping*), bấm
   **Cài đặt** rồi **Bật**. Có nhiều cửa hàng thì bật cho đúng cửa hàng cần dùng.
   Kết quả mong đợi: plugin hiện trạng thái đang bật trong danh sách.
2. **Bật các ô địa chỉ khách cần cho hãng.** Vào **Admin → Cấu hình cửa hàng → tab Khách hàng**, bật
   **Tỉnh/Thành phố** và **Phường/Xã** (hoặc Quận/Huyện). Hãng giao hàng tại Việt Nam bắt buộc các thông tin này.
3. **Mở tab Vận chuyển** (**Admin → Cấu hình cửa hàng → tab Vận chuyển**). Tab luôn hiện, kể cả khi vận chuyển
   đang tắt, để bạn chuẩn bị trước. Phía trên có khung **Trạng thái vận chuyển** liệt kê hãng đang bật và
   các cảnh báo còn thiếu (thiếu ô địa chỉ, thiếu kho lấy hàng, chưa có hãng nào).
4. **Điền khối Kho lấy hàng**: tên người gửi, số điện thoại, quốc gia (mã 2 chữ, ví dụ `VN`), Tỉnh/Thành phố,
   Phường/Xã, địa chỉ chi tiết. Quận/Huyện bỏ trống nếu địa phương đã bỏ cấp huyện (từ 01/07/2025).
5. **Điền khối Gói hàng**: **Khối lượng mặc định mỗi sản phẩm (gram)** — dùng cho sản phẩm chưa nhập khối lượng.
6. **Điền khối Theo dõi và đối soát** (chỉ cần khi hãng hỗ trợ vận đơn): số phút tự cập nhật trạng thái, và có
   tự ghi tiền thu hộ vào đơn hay không (xem bảng A4).
7. **Bật "Bật vận chuyển khi đặt hàng"** ở đầu tab, rồi bấm **Lưu**.
   Kết quả mong đợi: khung trạng thái hiện **ĐANG BẬT** và không còn cảnh báo.
8. **Cấu hình plugin hãng**: trong khung trạng thái, bấm vào tên hãng để mở màn cấu hình của plugin (tài khoản,
   mã bí mật, dịch vụ muốn bán, môi trường thử/thật). Nếu hãng tính giá bằng một đồng tiền khác tiền gốc của
   cửa hàng (ví dụ VND), đồng tiền đó phải có trong **Admin → Tiền tệ**.
9. **Nhập khối lượng (và kích thước) cho sản phẩm.** Khối lượng sai làm phí hãng thu khác phí khách đã trả.
   Sản phẩm tải về / kỹ thuật số không tính vào kiện hàng.
10. **Đặt thử một đơn** ở trang cửa hàng. Kết quả mong đợi: bước Vận chuyển hiện từng dịch vụ kèm giá và số ngày
    dự kiến; chọn xong, tổng tiền cộng đúng phí đó.

### A4. Ý nghĩa từng ô ở tab Vận chuyển

| Ô | Dùng để làm gì | Ví dụ |
|---|---|---|
| Bật vận chuyển khi đặt hàng | Hiện bước chọn hãng/dịch vụ ở trang thanh toán. Tắt: khách không thấy bước này và đơn không tính phí ship. | Bật |
| Tên người gửi / tên cửa hàng | In trên nhãn vận đơn | Cửa hàng ABC |
| Số điện thoại kho | Shipper gọi số này khi đến lấy hàng | 0901234567 |
| Quốc gia (mã 2 chữ) | Mã ISO của nước đặt kho | VN |
| Tỉnh/Thành phố · Phường/Xã · Quận/Huyện | Địa chỉ kho theo địa giới hiện hành | TP. Hồ Chí Minh · Phường Sài Gòn · (trống) |
| Địa chỉ chi tiết | Số nhà, tên đường | 12 Lê Lợi |
| Khối lượng mặc định mỗi sản phẩm (gram) | Dùng khi sản phẩm chưa có khối lượng | 500 |
| Tự cập nhật trạng thái vận đơn sau (phút) | Khi mở đơn mà lần cập nhật trước đã quá số phút này, hệ thống tự hỏi lại hãng. Dùng khi webhook của hãng không về được. Không cần cron. | 30 |
| Tự ghi tiền thu hộ (COD) vào đơn khi hãng báo đã đối soát | Bật nếu bạn đối chiếu tiền COD theo báo cáo của hãng. Mỗi vận đơn chỉ ghi một lần. Mặc định tắt. | Tắt |

> Mỗi cửa hàng (khi dùng nhiều cửa hàng) có kho lấy hàng và thiết lập riêng; ô nào để trống thì kế thừa giá trị chung.

### A5. Xử lý vận đơn hằng ngày

Mở **Admin → Đơn hàng → chi tiết đơn**, khối **Vận chuyển**. Khối luôn hiện phí và dịch vụ khách đã chọn; các nút
bên dưới chỉ có khi đang bật một hãng hỗ trợ việc đó (tạo vận đơn, nhãn, tra cứu, huỷ):

1. Bấm **Tạo vận đơn**. Biểu mẫu điền sẵn từ đơn: hãng, dịch vụ khách đã chọn, người nhận, giá trị khai báo,
   tiền thu hộ, khối lượng. Bạn sửa được trước khi gửi, ví dụ nhập **kích thước kiện** sau khi đóng gói.
2. Bấm gửi. Kết quả mong đợi: thông báo *Đã tạo vận đơn …* kèm **mã vận đơn**; nếu phí hãng thu khác phí trên đơn,
   hệ thống ghi rõ để bạn biết.
3. Bấm **Tải nhãn** để in nhãn dán kiện (nếu hãng có).
4. Trạng thái tự cập nhật qua webhook; muốn hỏi ngay thì bấm **Đồng bộ**. Mọi sự kiện nằm ở **Lịch sử vận chuyển**.
5. Cần huỷ: bấm **Huỷ vận đơn** — hãng có thể từ chối nếu đã lấy hàng. Huỷ xong mới tạo được vận đơn mới.

Trạng thái vận đơn tự quy ra **trạng thái giao hàng** của đơn:

| Trạng thái vận đơn | Trạng thái giao hàng của đơn |
|---|---|
| Chờ lấy hàng · Đã huỷ | Chưa gửi |
| Đã lấy hàng · Đang vận chuyển · Đang giao · Giao thất bại · Đang hoàn hàng · Ngoại lệ | Đang gửi |
| Đã giao | Đã gửi xong |
| Đã hoàn hàng | Đã hoàn |

### A6. Thu hộ (COD) đi qua những bước nào

```mermaid
sequenceDiagram
    participant K as Khách
    participant S as Cửa hàng (GP247)
    participant H as Hãng vận chuyển
    K->>S: Đặt đơn, chọn thanh toán khi nhận hàng
    S->>S: Tạo vận đơn: tiền thu hộ = số còn phải trả của đơn
    S->>H: Gửi vận đơn kèm số tiền thu hộ
    H->>K: Giao hàng, thu tiền mặt
    H-->>S: Báo đã đối soát tiền thu hộ (webhook)
    alt Bật "Tự ghi tiền thu hộ"
        S->>S: Ghi khoản thu vào sổ thanh toán của đơn (một lần)
    else Tắt
        S->>S: Chỉ lưu sự kiện; bạn tự ghi tiền khi đối chiếu xong
    end
```

Tiền thu hộ chỉ áp dụng cho đơn chọn phương thức **thanh toán khi nhận hàng** (mặc định là plugin `CashPayment`).

---

## Phần B — Dành cho lập trình viên

### B1. GP247 đã có sẵn những gì, nằm ở đâu

Toàn bộ lớp vận chuyển nằm trong package `gp247/shop`, thư mục `vendor/gp247/shop/src/Shipping/`
(namespace `GP247\Shop\Shipping`). **Không sửa trong `vendor/`** — mọi tuỳ biến đi qua plugin.

| Thành phần | Vị trí | Việc nó làm (bạn không phải viết lại) |
|---|---|---|
| Hợp đồng hãng | `Contracts/ShippingProvider.php` + 5 interface năng lực | Định nghĩa thứ một plugin phải cung cấp |
| Sổ đăng ký hãng | `ShippingProviderRegistry.php`, khoá config `gp247-config.shop.shipping_providers` | Tìm driver của từng plugin, kiểm năng lực khai báo có thật không |
| Báo giá | `RateService.php`, `RateRequestFactory.php` | Hỏi giá mọi hãng, cache 10 phút theo địa chỉ + giỏ, tạm dừng hãng lỗi 1 phút, quy đổi tiền tệ hãng → gốc → hiển thị |
| Giữ giá đã chọn | `ShippingCheckout.php`, `Data/RateSnapshot.php` | Phí do server quyết, lưu vào `shop_order.shipping_service` / `shipping_rate`; giá không còn hợp lệ thì từ chối đặt đơn thay vì giao miễn phí |
| Dữ liệu hàng | `ShippingItemFactory.php`, `Data/ShippingItem.php`, `WeightConverter.php`, `LengthConverter.php` | Khối lượng (gram), kích thước (cm), mã vạch, thương hiệu… từ sản phẩm; bỏ hàng kỹ thuật số; combo tính theo thành phần |
| Vận đơn | `ShipmentService.php`, `Models/ShopShipment.php`, `Models/ShopShipmentEvent.php` | Tạo/huỷ/nhãn/đồng bộ, lưu sự kiện chống trùng, quy ra trạng thái giao hàng, tự hỏi lại hãng khi mở đơn |
| Webhook chung | `Routes/Webhook/shipping.php`, `Http/ShippingWebhookController.php` | Route `POST /shipping/webhook/<PluginKey>` ngoài middleware storefront, có throttle |
| Thu hộ | `CodResolver.php`, khoá config `gp247-config.shop.cod_payment_methods` | Số tiền thu hộ = số còn phải trả với phương thức COD; ghi tiền một lần khi hãng đối soát |
| Màn admin | `Admin/Livewire/OrderShipmentPanel.php`, tab Vận chuyển của `ShopConfigForm` | Khối Vận chuyển ở chi tiết đơn, tab cấu hình |
| Hợp đồng cũ | `LegacyGetInfoAdapter.php` | Plugin viết theo `getInfo()` cũ vẫn hiện ở checkout (một dịch vụ) |

### B2. Hợp đồng mà một plugin vận chuyển cung cấp

Bắt buộc một method; các năng lực khác **chỉ khai báo khi hãng thật sự có**:

| Năng lực (khai báo) | Interface | Method |
|---|---|---|
| `rates` (bắt buộc) | `ShippingProvider` | `rates(RateRequest): RateResult` — mỗi dịch vụ một `ShippingRate`, giá theo **tiền tệ của hãng** |
| `shipments` | `CreatesShipments` | `createShipment(ShipmentRequest): ShipmentResult`, `shipmentFormFields()` |
| `cancel` | `CancelsShipments` | `cancelShipment($tracking, $storeId)` |
| `documents` | `ProvidesShippingDocuments` | `document($tracking, $storeId, $options)` — nhãn in |
| `tracking` | `TracksShipments` | `track($tracking, $storeId)` — danh sách `TrackingEvent` |
| `webhook` | `HandlesShippingWebhooks` | `handleWebhook(Request, Closure $storeOf): WebhookResult` |
| `cod` | `CreatesShipments` | Gửi `ShipmentRequest::$codAmount` (tiền theo **đơn hàng** — quy đổi sang tiền hãng) |

Đăng ký trong `Provider.php` của plugin:

```php
if (interface_exists(\GP247\Shop\Shipping\Contracts\ShippingProvider::class)) {
    config(['gp247-config.shop.shipping_providers.MyCarrier' => [
        'driver' => \App\GP247\Plugins\MyCarrier\Services\MyCarrierProvider::class,
        'capabilities' => ['rates', 'shipments', 'cancel', 'documents', 'tracking', 'webhook', 'cod'],
        'settings_route' => 'admin_my-carrier.index', // tab Vận chuyển sẽ link tới màn cấu hình này
    ]]);
}
```

Plugin vẫn có `configCode: "Shipping"` trong `gp247.json` — đó là cách checkout biết plugin là hãng vận chuyển
và cách chủ cửa hàng bật/tắt nó theo từng cửa hàng.

### B3. Luồng dữ liệu trong code

```mermaid
sequenceDiagram
    participant W as CheckoutWizard
    participant R as RateService
    participant P as Driver của plugin
    participant O as Đơn hàng
    participant SS as ShipmentService
    participant C as API hãng
    W->>R: quote(RateRequest từ địa chỉ + giỏ)
    R->>P: rates(RateRequest)
    P->>C: hỏi giá (timeout ngắn)
    C-->>P: các dịch vụ + giá
    P-->>R: RateResult (tiền của hãng)
    R-->>W: RateSnapshot đã quy đổi (cache 10 phút)
    W->>O: đặt đơn — phí lấy lại từ snapshot trên server
    Note over SS,C: Admin bấm Tạo vận đơn
    SS->>P: createShipment(ShipmentRequest)
    P->>C: tạo vận đơn
    C-->>SS: mã vận đơn, trạng thái
    C->>SS: webhook → handleWebhook() xác minh → TrackingEvent
    SS->>O: trạng thái giao hàng, tiền COD (nếu bật)
```

### B4. Việc của bạn theo từng tình huống

**Tích hợp một hãng vận chuyển mới**

1. Tạo plugin bằng `php artisan gp247:make-plugin --name=MyCarrier --download=0`, đặt `configCode: "Shipping"`,
   `requireComposerPackages: ["gp247/shop"]`.
2. Lưu thông tin tài khoản hãng trong `admin_config` (màn cấu hình `ConfigForm`), mã bí mật dạng `password`
   (được mã hoá). Đọc theo cửa hàng của yêu cầu: `gp247_config('MyCarrier_token', $request->storeId)` — không đọc từ session.
3. Viết driver `rates()`: lỗi thường gặp (thiếu địa chỉ, chưa cấu hình, hãng từ chối) trả `RateResult::error()`;
   hãng sập (timeout, lỗi 5xx) thì ném exception để hệ thống tạm dừng hãng đó.
4. Thêm các năng lực hãng có; ánh xạ mã trạng thái của hãng về `ShipmentStatus` ở **một** chỗ.
5. Webhook: xác minh chữ ký/mã bí mật **trước** khi tin dữ liệu; dùng thời gian sự kiện của hãng để chống trùng;
   hiển thị URL `route('shop.shipping.webhook', ['pluginKey' => 'MyCarrier'])` trên màn cấu hình.
6. Test không gọi hãng thật: dùng `Http::fake()`.

**Bổ sung dữ liệu hàng cho hãng** (mã HS, hàng dễ vỡ…): đăng ký một callable dạng mảng vào
`gp247-config.shop.shipping_item_resolvers` — không dùng closure (vỡ `config:cache`).

**Tự viết template giao diện**: nếu override bước thanh toán (`livewire/shop_checkout-wizard.blade.php`), giữ
cách hiển thị của bản gốc trong `vendor/gp247/shop/src/Views/templates/GP247Front/`: mỗi dịch vụ một lựa chọn
(khoá là mã dịch vụ), giá đã định dạng, số ngày dự kiến, thông báo khi không có dịch vụ, và ô Phường/Xã.

**Plugin vận chuyển cũ viết theo `getInfo()`**: vẫn chạy (một dịch vụ, phí theo tiền hiển thị). Muốn nhiều dịch
vụ, vận đơn, theo dõi, thu hộ thì thêm driver như trên — plugin mẫu `ShippingStandard` hỗ trợ cả hai cách.

### B5. Làm việc cùng AI agent

> 💡 Dùng skill **`gp247-shipping-plugin-create`** từ repo [gp247-skills](https://github.com/gp247net/gp247-skills)
> (chép thư mục skill vào thư mục skill của agent, ví dụ `.claude/skills/` với Claude Code). Skill tự dò phiên bản
> và năng lực của site trước khi viết code, dựng plugin đúng hợp đồng ở trên, và được cập nhật cùng `gp247/shop`
> mỗi khi hợp đồng vận chuyển thay đổi. Plugin không phải vận chuyển: dùng `gp247-plugin-create`; plugin thanh
> toán: `gp247-payment-plugin-create`. Hướng dẫn chung về tạo plugin: [create-plugin_vi.md](../../extension/create-plugin_vi.md).

---

## Điều kiện & ràng buộc (hiểu trước khi thao tác)

**Khi khách đặt hàng**
- **Vận chuyển đang bật mà không hãng nào báo giá được cho địa chỉ đó → không đặt được đơn**, khách thấy thông báo
  kiểm tra lại địa chỉ. Hệ thống cố ý chặn để không giao hàng mà không thu phí.
- **Địa chỉ hoặc giỏ hàng đổi sau khi đã chọn dịch vụ → khách phải chọn lại** ("Phí vận chuyển đã thay đổi"), vì
  giá cũ không còn đúng cho kiện hàng mới.
- **Đồng tiền hãng tính giá phải có trong danh sách Tiền tệ** — nếu thiếu, dịch vụ của hãng đó bị bỏ, vì hệ thống
  không quy đổi được sang tiền khách đang xem.

**Khi lưu tab Vận chuyển**
- Khối lượng mặc định: số nguyên **từ 1 đến 100.000 gram**.
- Tự cập nhật trạng thái: **từ 5 đến 1.440 phút** — đủ dày để theo kịp, đủ thưa để không bị hãng chặn vì hỏi quá nhiều.
- Quốc gia: đúng **2 chữ cái** (mã ISO); số điện thoại kho tối đa 30 ký tự.

**Khi xử lý vận đơn**
- **Mỗi đơn chỉ một vận đơn đang hiệu lực** — huỷ vận đơn cũ trước khi tạo mới, tránh gửi trùng hai kiện.
- **Đơn đã Hoàn thành, Đã huỷ hoặc Đã hoàn tiền thì không thao tác vận đơn được** — đơn đã chốt.
- **Huỷ vận đơn có thể bị hãng từ chối** khi hàng đã được lấy; khi đó liên hệ hãng.

**Tiền thu hộ**
- Chỉ ghi tự động khi bạn bật ô tương ứng, **mỗi vận đơn một lần**, và **không vượt số còn phải trả** của đơn. Hãng
  báo số khác số còn phải trả thì hệ thống ghi chú vào lịch sử đơn để bạn đối chiếu.

---

## Hỏi & Đáp (Q&A)

**Câu 1: Tôi đã cài plugin nhưng trang thanh toán không hiện bước vận chuyển?**

→ Kiểm tra ô **Bật vận chuyển khi đặt hàng** ở tab Vận chuyển đã bật và đã Lưu, và plugin đã **Bật** cho đúng cửa hàng.

**Câu 2: Bước vận chuyển báo "Chưa có dịch vụ giao hàng cho địa chỉ này"?**

→ Thường do thiếu thông tin: địa chỉ khách chưa có Tỉnh/Thành hoặc Phường/Xã, kho lấy hàng chưa đủ, plugin hãng
chưa nhập tài khoản, hoặc đồng tiền của hãng chưa có trong Tiền tệ. Khung **Trạng thái vận chuyển** ở đầu tab chỉ
ra phần còn thiếu.

**Câu 3: Phí hãng thu khác phí khách đã trả?**

→ Hầu hết do khối lượng sản phẩm sai hoặc chưa nhập (đang dùng khối lượng mặc định). Nhập khối lượng thật cho sản
phẩm; khi tạo vận đơn, sửa khối lượng và nhập kích thước kiện sau khi đóng gói.

**Câu 4: Tôi có cần cài cron để cập nhật trạng thái vận đơn không?**

→ Không. Trạng thái về qua webhook; khi webhook không về được, hệ thống tự hỏi lại hãng lúc bạn mở đơn (theo số phút
đã đặt), hoặc bạn bấm **Đồng bộ**.

**Câu 5: Trạng thái giao hàng của đơn có tự đổi không?**

→ Có, với đơn có vận đơn: đổi theo trạng thái hãng báo (bảng ở mục A5). Đơn không có vận đơn thì bạn chọn tay như trước.

**Câu 6: Tại sao không thấy nút Tạo vận đơn?**

→ Hãng đang bật chỉ tính phí, không hỗ trợ tạo vận đơn (ví dụ `ShippingStandard`), hoặc đơn đã chốt (Hoàn thành /
Đã huỷ / Đã hoàn tiền), hoặc đơn đã có một vận đơn đang hiệu lực.

**Câu 7: Gỡ plugin hãng có mất các vận đơn đã tạo không?**

→ Không. Vận đơn và lịch sử của chúng do cửa hàng lưu; gỡ plugin chỉ xoá thiết lập của plugin.

**Câu 8: Tôi muốn tự viết plugin cho một hãng giao hàng khác?**

→ Xem Phần B. Nếu làm cùng AI agent, dùng skill `gp247-shipping-plugin-create` — skill dựng plugin đúng hợp đồng
của `gp247/shop` thay vì để agent tự đoán.

**Câu 9: Plugin vận chuyển cũ của tôi còn chạy sau khi nâng cấp không?**

→ Còn. Plugin trả phí qua `getInfo()` vẫn hiện ở trang thanh toán như một dịch vụ duy nhất. Muốn nhiều dịch vụ hay
tạo vận đơn thì nâng cấp plugin theo Phần B.

**Câu 10: Nhiều cửa hàng thì mỗi cửa hàng dùng tài khoản hãng riêng được không?**

→ Được. Kho lấy hàng, thiết lập tab Vận chuyển và cấu hình plugin (nếu plugin hỗ trợ theo cửa hàng) đều đặt riêng
cho từng cửa hàng; ô nào để trống thì dùng giá trị chung.

---

<sub>📅 **Cập nhật lần cuối:** 2026-10-18 · ✍️ **Tác giả (Author):** GP247</sub>
