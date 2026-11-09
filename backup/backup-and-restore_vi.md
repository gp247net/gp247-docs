> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./backup-and-restore.md) · [🇯🇵 日本語](./backup-and-restore_ja.md)

# Sao lưu & phục hồi — thiết lập một lần, site tự bảo vệ

## Giới thiệu
GP247 có sẵn chức năng **sao lưu & phục hồi** ngay trong trang quản trị: mọi bản sao lưu đều được mã hoá, tự kiểm sau khi tạo,
chạy được trên cả shared host không có cron. Tài liệu này dành cho **chủ site và người quản trị**: cách thiết lập lần đầu, cách tạo
bản sao lưu, cách để site tự sao lưu theo lịch, và những gì GP247 tự làm trước một thay đổi rủi ro. Phục hồi khi có sự cố xem ở
[Phục hồi theo tình huống](./restore-guide_vi.md); chép bản ra ngoài máy chủ xem ở [Đích ngoài](./backup-offsite_vi.md).

## Nhìn nhanh
Thiết lập một lần, sau đó site tự sao lưu, tự kiểm và giữ đủ số bản:

```mermaid
flowchart LR
    A["Đặt mật khẩu<br/>sao lưu"] --> B["Cất<br/>recovery key"]
    B --> C["Tạo bản<br/>hoặc để<br/>lịch chạy"]
    C --> D["Tự kiểm<br/>bản vừa tạo"]
    D -.-> E["Chép ra<br/>đích ngoài"]
```

- **Mật khẩu sao lưu + recovery key là chìa khoá duy nhất** — mất cả hai thì không mở được bản nào, kể cả đội GP247.
- **Tạo bản không cần mật khẩu, mở bản mới cần** — lịch tự động chạy được mà không ai phải nhập gì.
- **Mặc định site tự sao lưu mỗi 24 giờ** khi có quản trị viên mở trang quản trị — không cần cron.
- **Mỗi bản được kiểm ngay sau khi tạo**; chỉ bản "Đã kiểm, tốt" mới dùng để phục hồi.
- **Trước khi cập nhật hệ thống, cập nhật extension hay đổi template**, GP247 tự chụp một bản phần dữ liệu sắp bị chạm tới.

## Trong trang này
- [Sao lưu những gì](#sao-lưu-những-gì) — dữ liệu và file nằm trong một bản sao lưu
- [Thiết lập lần đầu](#thiết-lập-lần-đầu-mật-khẩu-và-recovery-key) — mật khẩu sao lưu và recovery key
- [Tạo một bản sao lưu](#tạo-một-bản-sao-lưu) — ba cách tạo và khi nào dùng cách nào
- [Danh sách bản sao lưu](#danh-sách-bản-sao-lưu-kiểm-ghim-tải-về-xoá) — kiểm, ghim, tải về, xoá, giữ bao nhiêu bản
- [Sao lưu tự động theo lịch](#sao-lưu-tự-động-theo-lịch) — chọn cách chạy hợp với hosting của bạn
- [Bản chụp trước thay đổi](#bản-chụp-tự-động-trước-thay-đổi-rủi-ro) — GP247 tự làm gì trước một cập nhật
- [Quyền và bảo vệ](#ai-được-làm-gì-quyền-và-lớp-bảo-vệ) — ai tạo, ai tải, ai phục hồi
- [Kiểm tra sức khoẻ](#kiểm-tra-sức-khoẻ-sao-lưu) — biết chắc site đang được bảo vệ
- [Dòng lệnh](#dòng-lệnh-cho-người-có-quyền-cli) — lệnh `gp247:backup-*` cho VPS

## Sao lưu những gì
Một bản sao lưu gồm các **nhóm dữ liệu** (bảng trong cơ sở dữ liệu, gom theo nghĩa nghiệp vụ) và các **nhóm file**. Màn sao lưu
hiện đúng mô tả này dưới mỗi nhóm, để bạn biết mình đang sao lưu gì — hoặc, ở màn phục hồi, sắp thay những gì.

| Nhóm | Gồm gì | Có khi |
| --- | --- | --- |
| **Cấu hình** | Thông tin và cài đặt cửa hàng, bố cục trang tổng quan quản trị, ngôn ngữ và bản dịch, quốc gia, trường tuỳ chỉnh, kết nối API và webhook; có cửa hàng thì thêm tiền tệ, thuế, trạng thái đơn hàng, thanh toán và vận chuyển | Luôn có |
| **Tài khoản & quyền quản trị** | Tài khoản quản trị, vai trò, quyền và menu quản trị | Luôn có |
| **Nhật ký** | Nhật ký thao tác quản trị, thông báo hệ thống, sự kiện API và lượt gửi webhook, lần đăng nhập sai | Luôn có (bỏ được khi sao lưu toàn bộ) |
| **Nội dung** | Trang, banner, khối giao diện, liên kết và nhóm liên kết, chuyển hướng URL, danh sách quốc gia | Site có storefront |
| **Danh mục sản phẩm** | Sản phẩm (mô tả, ảnh, thuộc tính, khuyến mãi, file tải, tag, combo và nhóm), danh mục, thương hiệu, nhà cung cấp | Site có cửa hàng |
| **Đơn hàng** | Đơn hàng kèm dòng đơn, lịch sử và tổng tiền; giao dịch và yêu cầu thanh toán; vận chuyển | Site có cửa hàng |
| **Khách hàng** | Tin nhắn liên hệ và email đăng ký nhận tin; có cửa hàng thì thêm tài khoản khách hàng, địa chỉ và giỏ hàng đã lưu | Site có storefront / cửa hàng |
| **Nhóm của plugin** | Những gì plugin tự mô tả; không có thì "Dữ liệu do extension … lưu" | Plugin có khai bảng của mình |
| **Bảng khác** | Bảng có tiền tố của site chưa thuộc nhóm nào — **chỉ bản toàn bộ mới có** | Khi có |
| **File tải lên** | Ảnh và file tải lên qua trình quản lý file (sản phẩm, banner, trang…) | Luôn có |
| **Extension** | Plugin và template trong `app/GP247`, kể cả phần bạn đã sửa (bỏ `.git` và `node_modules`) | Luôn có |
| **File .env** | Cấu hình máy chủ, gồm khoá mã hoá và mật khẩu cơ sở dữ liệu — hãy cất riêng | Luôn có |
| **composer.lock** | Phiên bản chính xác của các package đã cài, để cài lại đúng như cũ | Luôn có |

**Chụp nhanh** lấy một nhóm dữ liệu. **Chọn nhóm** lấy các nhóm dữ liệu và nhóm file bạn tick. **Sao lưu toàn bộ** lấy mọi thứ ở
bảng trên, kể cả **Bảng khác**. Không bao giờ sao lưu: phiên đăng nhập, link đặt lại mật khẩu, token API và sổ sách riêng của chức
năng sao lưu — phục hồi chúng có thể mở lại quyền truy cập đã bị thu hồi sau thời điểm sao lưu.

> ⚠️ **Lưu ý:** bảng **không có tiền tố** của site mà chưa plugin nào khai thì **không** được sao lưu. `php artisan gp247:doctor`
> liệt kê các bảng này (dòng `backup_unassigned_tables`) — hãy báo nhà phát triển plugin sở hữu bảng đó.

## Thiết lập lần đầu: mật khẩu và recovery key
Mọi bản sao lưu được mã hoá bằng khoá của chính site; mật khẩu sao lưu và recovery key là hai cách mở khoá đó. Chỉ
**quản trị viên cấp cao (Administrator)** thiết lập được.

Hai thứ này dễ bị đặt vội rồi quên. Hãy đọc bảng dưới trước khi đặt:

| | Mật khẩu sao lưu | Recovery key |
| --- | --- | --- |
| **Ai tạo** | Bạn đặt | Hệ thống sinh, chỉ hiện **một lần** |
| **Dùng khi** | Mỗi lần mở một bản: phục hồi, kiểm lại, đưa file sao lưu vào, đổi mật khẩu. Tạo bản (kể cả tự động) **không** cần | Khi quên mật khẩu sao lưu: mở bản ở mọi chỗ hỏi mật khẩu và đặt mật khẩu mới |
| **Cất ở đâu** | Trình quản lý mật khẩu; **khác** mật khẩu đăng nhập admin | **Ngoài máy chủ này**: trình quản lý mật khẩu, giấy cất ở nơi an toàn |
| **Mất thì sao** | Dùng recovery key để đặt mật khẩu mới | Đổi mật khẩu sao lưu sẽ cấp recovery key mới |

Không ai lấy lại được hai thứ này cho bạn, kể cả nhà cung cấp hosting hay GP247. **Mất cả hai thì không mở được bản sao lưu nào.**

1. Vào **Cấu hình hệ thống → Sao lưu & phục hồi**.
2. Ở khung **Thiết lập sao lưu**, nhập **Mật khẩu sao lưu** (ít nhất 12 ký tự) hai lần, bấm **Thiết lập**.
3. Màn hình hiện **recovery key** — chỉ hiện **một lần**. Bấm **Tải về dạng file văn bản** hoặc chép ra giấy.
4. Cất recovery key **ngoài máy chủ này** (trình quản lý mật khẩu, két, ổ USB cất riêng) — không cất cùng chỗ với bản sao lưu.
5. Nhập **8 ký tự cuối** của key để chứng minh đã cất, bấm **Tôi đã cất key**.

Nếu thành công, màn hiện "Đã thiết lập sao lưu." và lịch tự động bắt đầu chạy.

> 💡 **Mẹo:** dùng mật khẩu sao lưu **khác** mật khẩu đăng nhập admin. Kẻ đoán được mật khẩu admin vẫn không mở được bản sao lưu.

Đổi mật khẩu hoặc tạo recovery key mới: **Cài đặt sao lưu → Mật khẩu sao lưu & recovery key**. Các bản đã có vẫn mở được bằng
mật khẩu mới; recovery key mới thay key cũ.

## Tạo một bản sao lưu
Màn **Sao lưu & phục hồi** có ba cách tạo bản, khác nhau ở phạm vi và thời gian chạy:

| Cách | Gồm | Thời gian | Dùng khi |
| --- | --- | --- | --- |
| **Chụp nhanh** | Một nhóm dữ liệu | Vài giây | Trước khi đổi cấu hình, sửa giá hàng loạt |
| **Chọn nhóm** | Các nhóm bạn tick | Theo dung lượng | Muốn giữ riêng đơn hàng, sản phẩm… |
| **Sao lưu toàn bộ** | Mọi nhóm dữ liệu + file tải lên, extension, `.env`, `composer.lock` | Lâu nhất | Bản đủ để dựng lại cả site |

1. Chọn cách tạo, chọn nhóm (nếu có), bấm **Bắt đầu**.
2. Giữ tab mở: bản được ghi từng phần nhỏ trong nền, rồi tự kiểm. Đóng tab giữa chừng thì lần mở sau chạy tiếp.
3. Khi xong, bản hiện trong danh sách với trạng thái **Đã kiểm, tốt**.

> ℹ️ **Thông tin:** bản rất lớn (mặc định trên 500 MB) chạy từ trình duyệt sẽ hỏi lại — trên VPS dùng
> `php artisan gp247:backup-run` an toàn hơn vì không bị giới hạn thời gian của request web.

## Danh sách bản sao lưu: kiểm, ghim, tải về, xoá
Mỗi dòng trong **Các bản sao lưu** cho biết thời điểm, loại, nhóm, cỡ, nơi lưu và kết quả kiểm. Các thao tác:

- **Kiểm lại** — đọc lại toàn bộ bản để chắc nó còn nguyên vẹn (cần mật khẩu sao lưu hoặc recovery key).
- **Ghim** — bản đã ghim **không bao giờ** bị tự xoá khi dọn bản cũ.
- **Tải về** — tải file `.gp247bak` (vẫn mã hoá) qua một link dùng **một lần**, hết hạn sau 5 phút.
- **Phục hồi** — mở màn phục hồi với bản này ([Phục hồi theo tình huống](./restore-guide_vi.md)).
- **Xoá** — gõ lại mã bản để xác nhận; không xoá được bản đang được dùng.

**Giữ bao nhiêu bản** (đổi ở **Cài đặt sao lưu**): mặc định giữ **7** bản thường và **3** bản "trước thay đổi". Bản cũ hơn chỉ bị
xoá **sau khi** bản mới đã kiểm đạt; bản tốt mới nhất và bản đã ghim luôn được giữ.

Bản sao lưu nằm ở `storage/app/private/gp247-backup/` (ngoài thư mục web công khai). Muốn đổi chỗ: **Cài đặt sao lưu → Thư mục lưu**,
nhập đường dẫn tuyệt đối ngoài thư mục web.

## Sao lưu tự động theo lịch
Lịch đặt ở **Cài đặt sao lưu → Lịch sao lưu**. Chọn cách chạy theo nơi site của bạn đang chạy:

| Site chạy ở | Chọn | Hoạt động thế nào |
| --- | --- | --- |
| **Shared host không có cron** | **Khi quản trị viên mở trang quản trị (không cần cron)** *(mặc định)* | Khi đến hạn và có admin mở trang quản trị, bản được ghi nền từng phần nhỏ — trang không chậm đi, khách không bị ảnh hưởng |
| **VPS / hosting có cron** | **Vào giờ cố định (cần cron trên máy chủ)** | Chạy đúng giờ đã chọn, kể cả khi không ai truy cập |
| Không muốn tự động | **Tắt** | Chỉ có bản do người tạo — không khuyến nghị |

Chọn thêm **Tần suất** (mỗi 6, 12, 24, 48 giờ hoặc mỗi tuần) và **Bản theo lịch chứa gì** (toàn bộ site bỏ nhật ký — mặc định,
hoặc chỉ cơ sở dữ liệu cho nhẹ). Bấm **Lưu lịch**.

**Cài cron cho chế độ "Vào giờ cố định"** (làm một lần, trên VPS hoặc trang "Cron jobs" của hosting):

1. Mở màn **Cài đặt sao lưu**, chọn **Vào giờ cố định** — màn hiện đúng dòng cron cho máy chủ của bạn.
2. Thêm dòng đó vào crontab. Dạng chung (thay đường dẫn tới thư mục site):

   ```
   * * * * * cd /duong-dan/toi/site && php artisan schedule:run >> /dev/null 2>&1
   ```

3. Sau một chu kỳ, danh sách có bản loại **Theo lịch**. Hai chu kỳ không có bản nào, màn sao lưu và `gp247:doctor` sẽ cảnh báo
   "cron không chạy".

> ℹ️ **Thông tin:** cài plugin **Scheduled tasks** thì tác vụ sao lưu theo lịch hiện trong danh sách tác vụ của plugin như mọi tác vụ khác.

Màn sao lưu luôn nói rõ **bạn quay về được tới đâu** nếu có sự cố lúc này. Chế độ "khi quản trị viên mở trang quản trị" có giới hạn
thật: admin không vào trang quản trị 3 ngày thì 3 ngày đó không có bản mới. Khi bản tốt gần nhất cũ quá **48 giờ** và lịch đang bật,
GP247 gửi email cho chủ site, tối đa **một lần mỗi ngày**.

## Bản chụp tự động trước thay đổi rủi ro
Trước ba thao tác dễ làm hỏng dữ liệu, GP247 tự chụp một bản **phần dữ liệu thao tác đó có thể chạm tới**:

| Thao tác | GP247 tự chụp |
| --- | --- |
| **Cập nhật hệ thống** (`php artisan gp247:update`) | Mọi nhóm dữ liệu trừ nhật ký |
| **Cập nhật plugin / template** | Cấu hình + dữ liệu của chính extension đó |
| **Đổi template** | Cấu hình + nội dung |

- **Chạy từ dòng lệnh**: bản được chụp xong rồi thao tác mới chạy.
- **Bấm từ trang quản trị**: nếu bản chưa xong trong vài giây, màn báo "Đang tạo bản sao lưu… hãy thao tác lại sau vài giây". Bấm lại
  sau đó: GP247 dùng bản vừa xong, **không** chụp thêm.
- **Chụp không được** (ví dụ đang có bản khác chạy, đĩa đầy): thao tác dừng và nói lý do; bạn vẫn có thể chọn tiếp tục không có bản sao lưu.
- **Chưa thiết lập sao lưu**: thao tác vẫn chạy, kèm lời nhắc chưa có bản sao lưu.

## Ai được làm gì: quyền và lớp bảo vệ
Sao lưu có ba quyền riêng, gán ở **Vai trò** như các quyền khác:

| Quyền | Cho phép | Lưu ý |
| --- | --- | --- |
| **Backup - create** | Tạo, kiểm lại, ghim, xoá bản; chạy lịch | Không tải về, không phục hồi |
| **Backup - download** | Xem danh sách, tải file `.gp247bak` | File tải về vẫn mã hoá |
| **Backup - restore** | Xem danh sách, phục hồi, đưa bản vào | Quyền mạnh nhất — chỉ cấp cho người thật sự cần |

Ba lớp bảo vệ thêm cho các thao tác đưa dữ liệu ra ngoài hoặc ghi đè dữ liệu:

- **Xác nhận lại danh tính** — trước khi tải về hay phục hồi, nhập lại mật khẩu đăng nhập trang quản trị — **không phải** mật khẩu sao lưu (và mã xác thực hai bước nếu đã bật);
  hiệu lực 5 phút trên trình duyệt đó.
- **Nhật ký** — mọi lần tạo, tải, phục hồi, xoá (kể cả lần bị từ chối) được ghi lại với người làm và thời điểm.
- **Email** — chủ site nhận email khi một bản thất bại, khi có người tải bản về và khi có người phục hồi. Email chỉ gửi được khi
  site đã bật gửi mail (**Cấu hình → Email**).

## Kiểm tra sức khoẻ sao lưu
Chạy lệnh sau (trên VPS, hoặc nhờ nhà cung cấp hosting chạy) để biết chắc site **phục hồi được thật**:

```
php artisan gp247:doctor
```

Các dòng bắt đầu bằng `backup_` cho biết: đã thiết lập chưa, bản tốt gần nhất bao nhiêu giờ trước, lịch có chạy không, đích ngoài có
dùng được không, bảng nào chưa được sao lưu, email có bật không. Dòng `backup_vault_exposed` báo **lỗi** khi tải được file sao lưu
qua địa chỉ web của site — nghĩa là web server đang phục vụ cả thư mục dự án thay vì chỉ `public/`; sửa ngay theo
[Cấu hình production](../security/security-production-config_vi.md). Lỗi này cũng hiện ở khối **System alerts** trên dashboard.

## Dòng lệnh (cho người có quyền CLI)
Mọi việc trên màn đều làm được bằng lệnh; thêm `--json` để lấy kết quả máy đọc được.

| Lệnh | Làm gì |
| --- | --- |
| `php artisan gp247:backup-run` | Tạo bản toàn bộ (`--kind=snapshot\|selective\|full`, `--group=`, `--exclude-logs`) |
| `php artisan gp247:backup-run --scheduled` | Bản theo lịch (nội dung theo **Cài đặt sao lưu**) |
| `php artisan gp247:backup-list` | Liệt kê bản |
| `php artisan gp247:backup-verify <mã>` | Kiểm lại một bản |
| `php artisan gp247:backup-pin <mã>` | Ghim (`--unpin` để bỏ ghim) |
| `php artisan gp247:backup-delete <mã>` | Xoá một bản |
| `php artisan gp247:backup-restore <mã>` | Phục hồi — xem [Phục hồi theo tình huống](./restore-guide_vi.md) |
| `php artisan gp247:backup-import <file>` | Đưa file `.gp247bak` vào danh sách |
| `php artisan gp247:backup-export-sql <mã>` | Xuất dữ liệu thành file `.sql` **không mã hoá** |
| `php artisan gp247:backup-offsite test\|list\|push\|fetch` | Đích ngoài — xem [Đích ngoài](./backup-offsite_vi.md) |

Lệnh cần mật khẩu sao lưu thì **hỏi khi chạy** (không nhận qua tham số, để mật khẩu không nằm trong lịch sử lệnh). Chạy tự động
trong script thì truyền qua một dòng của đầu vào chuẩn với `--secret-stdin`.

## Điều kiện & ràng buộc (hiểu trước khi thao tác)
**Khi thiết lập**
- **Mật khẩu sao lưu ít nhất 12 ký tự** — bản sao lưu có thể rơi vào tay người khác, mật khẩu ngắn bị dò ra.
- **Recovery key chỉ hiện một lần** — GP247 không lưu bản rõ của nó, nên không thể hiện lại.
- **Chỉ Administrator** thiết lập, đổi mật khẩu, đổi lịch và thư mục lưu — đây là chìa khoá của toàn bộ dữ liệu site.

**Khi tạo bản**
- **Mỗi lúc chỉ một việc ghi** (sao lưu, phục hồi, đưa bản vào) — hai việc cùng ghi dễ làm bản sao lưu lệch nhau.
- **Thư mục lưu phải ngoài thư mục web công khai** — nếu không, ai biết đường dẫn có thể tải bản về.

**Khi tải về / phục hồi**
- **Phải xác nhận lại danh tính trong 5 phút gần nhất** — người mượn máy đang đăng nhập không lấy được dữ liệu.
- **Link tải dùng một lần, hết hạn sau 5 phút** — link lọt ra ngoài cũng không dùng lại được.
- **File `.sql` dạng rõ chỉ xuất bằng dòng lệnh** — file này không mã hoá, không nên đi qua trình duyệt.

## Hỏi & Đáp (Q&A)
**Câu 1: Tôi quên mật khẩu sao lưu thì sao?**

→ Dùng recovery key ở mọi chỗ hỏi mật khẩu, rồi vào **Cài đặt sao lưu** đặt mật khẩu mới. Mất cả mật khẩu lẫn recovery key thì
không mở được bản nào — không ai, kể cả GP247, giải mã được.

**Câu 2: Hosting của tôi không có cron, site có tự sao lưu không?**

→ Có. Chế độ mặc định "Khi quản trị viên mở trang quản trị" chạy nền khi có admin vào trang quản trị. Chỉ cần thỉnh thoảng có người mở
trang quản trị; màn sao lưu cho biết bản tốt gần nhất là bao lâu trước.

**Câu 3: Sao lưu có làm site chậm với khách không?**

→ Không. Bản được ghi từng phần nhỏ ở request riêng của trang quản trị, không bao giờ trong request của khách.

**Câu 4: Bản sao lưu có chứa mật khẩu khách, thông tin thanh toán không?**

→ Có chứa toàn bộ dữ liệu của site (mật khẩu chỉ ở dạng đã băm), vì vậy mọi bản đều mã hoá và việc tải về / phục hồi cần quyền
riêng, xác nhận lại danh tính và có ghi nhật ký.

**Câu 5: File `.gp247bak` tải về mở bằng gì?**

→ Chỉ GP247 mở được: đưa lại vào một site GP247 (**Đưa bản sao lưu vào** hoặc `gp247:backup-import`) với mật khẩu sao lưu hoặc
recovery key của site đã tạo nó. Cần file SQL thông thường thì dùng `gp247:backup-export-sql`.

**Câu 6: Bản sao lưu nằm trên chính máy chủ, máy chủ hỏng thì sao?**

→ Bản trên cùng máy chủ mất theo máy chủ. Hãy cấu hình [Đích ngoài](./backup-offsite_vi.md) để mỗi bản đã kiểm tự chép sang nơi khác,
hoặc định kỳ tải về cất riêng.

**Câu 7: Một bản theo lịch bị lỗi thì site có thử lại ngay không?**

→ Không thử lại liên tục: chủ site nhận email báo lỗi, màn sao lưu có cảnh báo, và lịch thử lại ở chu kỳ sau. Bạn có thể tạo bản thủ công
ngay sau khi xử lý nguyên nhân (thường là đĩa đầy).

**Câu 8: Tắt chức năng sao lưu có mất các bản đã có không?**

→ Không. Tắt ở **Cài đặt sao lưu** chỉ dừng lịch, ẩn menu và bỏ bản chụp trước thay đổi; các bản và khoá vẫn giữ nguyên, bật lại là dùng tiếp.

---

<sub>📅 **Cập nhật lần cuối:** 2026-11-08 · ✍️ **Tác giả (Author):** GP247</sub>
