> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./api-authentication.md) · [🇯🇵 日本語](./api-authentication_ja.md)

# Đăng nhập, token và tài khoản khách qua API

## Giới thiệu

Trang này giải thích cách ứng dụng đăng nhập vào API GP247 và giữ đăng nhập an toàn: lấy token, thời hạn, giới hạn số lần đăng nhập,
refresh token, quản lý phiên trên từng thiết bị, xác thực hai lớp và các việc tài khoản khách làm được qua API (đăng ký, quên mật khẩu, hồ
sơ, sổ địa chỉ). Dành cho lập trình viên làm app di động, website khách hàng hay công cụ quản trị. Chưa gọi API lần nào? Bắt đầu ở
[Bắt đầu nhanh](./api-quickstart_vi.md).

## Xác thực (đăng nhập vào API)

GP247 dùng **Laravel Sanctum** để cấp **token** (một chuỗi ký tự dài đóng vai trò "vé vào cửa").
Cơ chế hoạt động chung cho cả hai nhóm:

1. Ứng dụng gửi thông tin đăng nhập tới endpoint `login`: quản trị viên gửi **`username` + `password`** tới
   `api/v2/core/login`, khách gửi **`email` + `password`** tới `api/v2/front/login`.
2. Nếu đúng, hệ thống trả về trong khoá `data` một **`access_token`** (token dạng Bearer) kèm hạn dùng
   (`expires_at`, và `expires_in` tính bằng giây).
3. Từ đó về sau, mọi yêu cầu cần đăng nhập đều gắn kèm token này trong phần **header**:

   ```
   Authorization: Bearer <access_token>
   ```

4. Khi muốn thoát, gọi **`POST logout`** để **đăng xuất phiên hiện tại** (token cũ và refresh token của nó hết hiệu lực ngay).

### Phạm vi quyền (scope / ability)

Mỗi token được cấp kèm **phạm vi** cho biết nó là token của ai:

| Scope | Ý nghĩa |
| --- | --- |
| `admin` | Token của quản trị viên (API Core) |
| `admin-supper` | Token của quản trị viên có vai trò `administrator` (API Core) |
| `user` | Token của thành viên (API Front) |

> ℹ️ **Có từ:** gp247/core 3.2.3 — scope chỉ phân biệt **loại tài khoản**. **Được xem dữ liệu nào**
> do phân quyền của chính tài khoản quyết định ([Phân quyền và phạm vi dữ liệu của API Core](./api-concepts_vi.md#phân-quyền-và-phạm-vi-dữ-liệu-của-api-core)). Scope `user-guest` không còn được cấp:
> tài khoản khách đang bị khoá không đăng nhập được.

### Thời hạn token

> ℹ️ **Có từ:** gp247/core 3.2.3

Mọi token đều có hạn dùng (`expires_at`):

| Loại token | Không gửi `remember_me` | Gửi `remember_me = true` |
| --- | --- | --- |
| Quản trị viên | 7 ngày (`GP247_API_TOKEN_EXPIRE_ADMIN_DEFAULT`) | 30 ngày (`GP247_API_RECOMMEMBER_ADMIN`) |
| Thành viên | 7 ngày (`GP247_API_TOKEN_EXPIRE_DEFAULT`) | 30 ngày (`GP247_API_RECOMMEMBER`) |

Đăng nhập kèm `with_refresh: true` thì token truy cập chỉ sống **60 phút** và đi cùng một *refresh token* để tự gia hạn
([Refresh token — giữ đăng nhập mà token ngắn hạn](#refresh-token--giữ-đăng-nhập-mà-token-ngắn-hạn)) — cách được khuyên dùng cho ứng dụng di động.

> ℹ️ **Có từ:** gp247/core 3.3.0 — biến môi trường có tên viết đúng: `GP247_API_REMEMBER`,
> `GP247_API_REMEMBER_ADMIN`, `GP247_API_SCOPE_ADMIN_SUPER`. Tên cũ (`GP247_API_RECOMMEMBER`…) vẫn dùng được;
> khai cả hai thì tên mới thắng. `php artisan gp247:doctor` báo tên cũ còn dùng (mục `api_config_legacy_names`).
> Token mang scope `admin-super` cũng được chấp nhận bên cạnh `admin-supper`.

Token hết hạn trả về `401`; khi đó gọi `refresh` (nếu đăng nhập kèm `with_refresh`) hoặc `login` để lấy token mới. Token đã
hết hạn lâu (quá 7 ngày) được tự dọn ở lần đăng nhập kế tiếp của cùng tài khoản — không cần cron.

> ℹ️ **Có từ:** gp247/core 3.4.0 — token cấp **trước bản 3.2.3** (lúc đó chưa có hạn) được đặt hạn khi chạy
> `php artisan gp247:update`: tuổi thật của token cộng thời hạn "remember" (quản trị viên: `GP247_API_REMEMBER_ADMIN`,
> thành viên: `GP247_API_REMEMBER`), nhưng **không sớm hơn 7 ngày** kể từ lúc nâng cấp. Ứng dụng đang giữ token cũ
> có ít nhất 7 ngày trước khi phải đăng nhập lại — hãy chắc chắn ứng dụng xử lý `401` bằng cách gọi `login`.

> ℹ️ **Có từ:** gp247/core 3.6.0, gp247/shop 3.4.0 — đổi hoặc đặt lại mật khẩu của khách (trên website hay qua API) làm
> **mọi token** của khách đó hết hiệu lực. Với **quản trị viên** cũng vậy: đổi mật khẩu ở màn **Người dùng**, ở **Cài đặt tài
> khoản** hoặc qua "quên mật khẩu" làm mọi token API của admin đó hết hiệu lực.

### Giới hạn số lần đăng nhập

> ℹ️ **Có từ:** gp247/core 3.2.3

Để chống dò mật khẩu, `api/v2/core/login`, `api/v2/front/login` và form đăng nhập quản trị giới hạn số lần thử.
Form đăng nhập quản trị và `api/v2/core/login` dùng chung một bộ đếm: lần sai ở bên này được tính cho bên kia.

| Giới hạn | Mặc định | Đổi bằng biến môi trường |
| --- | --- | --- |
| Mỗi tài khoản (từ cùng một IP) | 5 lần / phút | `GP247_LOGIN_THROTTLE` (dạng `số_lần,phút`) |
| Mỗi IP, trên mọi tài khoản | 20 lần / phút | `GP247_LOGIN_IP_THROTTLE` |

> ⚠️ **Đổi tên ở gp247/core 3.5.0:** tên cũ `GP247_API_LOGIN_THROTTLE` và `GP247_API_LOGIN_IP_THROTTLE`
> không còn được đọc. Nếu bạn đã đặt chúng, hãy đổi tên trong `.env`; nếu không, giá trị mặc định ở trên được dùng.

Vượt giới hạn → HTTP `429` kèm header `Retry-After` (số giây cần chờ). Đăng nhập đúng sẽ xoá bộ đếm của
tài khoản đó. Ngoài ra, mọi endpoint API có giới hạn chung 1000 lần/phút (`GP247_API_THROTTLE`): đếm **theo tài khoản** khi
đã đăng nhập, theo IP khi chưa đăng nhập.

> ℹ️ **Có từ:** gp247/core 3.8.0 — trước bản này giới hạn chung đếm theo IP, nên nhiều khách dùng ứng dụng sau cùng một mạng
> (NAT của nhà mạng, văn phòng) dùng chung một bộ đếm. Nếu nhiều công cụ dùng **chung một tài khoản**, nay chúng chung bộ đếm —
> hãy cấp mỗi công cụ một tài khoản hoặc tăng `GP247_API_THROTTLE`.

## Tài khoản khách & phiên đăng nhập

> ℹ️ **Có từ:** gp247/core 3.6.0, gp247/shop 3.4.0

Ứng dụng mua sắm làm được trọn vòng tài khoản khách qua `api/v2/front`, với **đúng luật của form trên website**:
trường bắt buộc, trường tuỳ chỉnh, độ mạnh mật khẩu và captcha đều theo cấu hình của cửa hàng. Danh sách endpoint ở
[Luồng làm việc API Front (Storefront)](./api-storefront_vi.md#luồng-làm-việc-api-front-storefront).

### Đăng ký, quên mật khẩu, hồ sơ, sổ địa chỉ

- **`POST register`** — gửi các trường như form đăng ký (`first_name`, `email`, `password`, `password_confirmation`,
  cùng các trường cửa hàng bật như `address1`, `phone`…). Thành công → HTTP `201`, thân gồm `customer` và token như
  `login`. Không đặt được `status` của chính mình. **Mật khẩu bắt buộc**: thiếu hoặc để trống `password` → `422`
  (`errors.password`), không tạo tài khoản.
- **`POST password/forgot`** — gửi email đặt lại mật khẩu. Luôn trả `{"data": {"sent": true}}`, dù email có tài khoản
  hay không, để không ai dò được email nào đã đăng ký.
- **`POST password/reset`** — gửi `email`, `token` (trong email), `password`, `password_confirmation`. Thành công thì
  **mọi token API** của khách bị huỷ; ứng dụng gọi `login` với mật khẩu mới.
- **`GET` / `PUT member/profile`** — hồ sơ kèm `email_verified`, `email_verification_required` và địa chỉ mặc định.
  Mật khẩu không đổi được ở đây.
- **`PUT member/password`** — gửi `password_old`, `password`, `password_confirmation`. Phiên đang gọi được giữ, **mọi
  thiết bị khác** bị đăng xuất. Mật khẩu cũ sai được tính vào giới hạn đăng nhập của tài khoản. Thiếu hoặc để trống
  mật khẩu mới → `422` (`errors.password`), mật khẩu cũ giữ nguyên.

> ℹ️ **Có từ:** gp247/shop 3.5.0 — `register` và `member/password` luôn đòi mật khẩu (trước đây thân thiếu khoá `password`
> trả HTTP `500`, còn mật khẩu rỗng được chấp nhận). Thân JSON thiếu hẳn một trường bắt buộc nay luôn trả `422`.
- **`member/address`** — thêm/sửa/xoá địa chỉ; gửi `"default": true` để đặt làm mặc định. Không xoá được địa chỉ
  mặc định (HTTP `422`) — hãy đặt địa chỉ khác làm mặc định trước.
- **`POST member/email/verify`** — gửi lại email xác minh khi cửa hàng bật *Cần xác thực email*. Khách bấm link trong
  email ở bất kỳ trình duyệt nào là xong, **không cần** đăng nhập website.

`register`, `password/*` và `member/email/verify` có giới hạn riêng: **5 lần/phút mỗi IP** (`GP247_API_ACCOUNT_THROTTLE`,
dạng `số_lần,phút`).

> ⚠️ Nếu cửa hàng bật captcha cho trang *Đăng ký* hoặc *Quên mật khẩu* (tab **Captcha**), API cũng đòi trường captcha
> của plugin captcha đang dùng — thiếu/sai → `422` (`errors.captcha_field`). Ứng dụng cần lấy mã captcha bằng SDK
> của nhà cung cấp captcha, hoặc quản trị viên bỏ chọn trang đó.

### Refresh token — giữ đăng nhập mà token ngắn hạn

Gửi `with_refresh: true` (và tuỳ chọn `device_name`) khi `login`, `register` hoặc `login/challenge`:

| | Không `with_refresh` | Có `with_refresh` |
| --- | --- | --- |
| Token truy cập | 7 ngày (30 với `remember_me`) | **60 phút** (`GP247_API_ACCESS_MINUTES`) |
| Refresh token | — | Khách 30 ngày (`GP247_API_REFRESH_DAYS`), admin 7 ngày (`GP247_API_REFRESH_ADMIN_DAYS`), tính từ lúc đăng nhập |

Khi token truy cập hết hạn (`401`), gọi `POST refresh` với `{"refresh_token": "…"}` → nhận **cặp token mới**. Mỗi
refresh token chỉ dùng được **một lần**:

- Gửi lại refresh token đã dùng → `401 refresh_token_reused` và **cả phiên bị đăng xuất** (dấu hiệu token bị đánh cắp).
- Ngoại lệ: gửi lại trong vòng 10 giây (mạng chập chờn, ứng dụng gửi trùng) → `409 refresh_in_progress`, phiên vẫn còn.
  Hãy cho ứng dụng gọi `refresh` **lần lượt**, không song song.
- Hạn của phiên không kéo dài khi refresh; hết hạn thì đăng nhập lại.

```bash
curl -X POST "https://shop.example.com/api/v2/front/login" -H "Content-Type: application/json" \
  -d '{"email":"khach@example.com","password":"MatKhau#123","with_refresh":true,"device_name":"iPhone của Lan"}'
curl -X POST "https://shop.example.com/api/v2/front/refresh" -H "Content-Type: application/json" \
  -d '{"refresh_token":"<refresh_token nhận được>"}'
```

### Quản lý phiên đăng nhập

Mỗi lần đăng nhập là một **phiên**. `GET member/sessions` (khách) và `GET sessions` (admin) liệt kê tên thiết bị,
IP, lần dùng cuối và phiên nào là phiên hiện tại (`current`). `DELETE …/sessions/{id}` đăng xuất một thiết bị.
`POST logout` đăng xuất phiên hiện tại, kể cả refresh token của nó. Đổi mật khẩu (khách hoặc quản trị viên, trên website hay
qua API) đăng xuất **mọi** phiên và token API của tài khoản đó; phiên đăng nhập website đang dùng vẫn giữ.

### Xác thực hai lớp (MFA) qua plugin

Core không tự gửi mã OTP; plugin bảo mật có thể gắn thêm bước xác thực. Khi đó `login` đúng mật khẩu trả:

```json
{ "error": 1, "code": "challenge_required", "msg": "…",
  "challenge": { "ticket": "…", "method": "totp", "meta": {}, "expires_in": 300 } }
```

Ứng dụng hỏi mã của người dùng rồi gọi `POST login/challenge` với `ticket` và các trường plugin yêu cầu (ví dụ `code`).
Vé dùng một lần, hết hạn sau 5 phút; mã sai được tính vào giới hạn đăng nhập (API v1 không có bước này — xem
[API v1](./api-v1_vi.md)). Plugin **MFA** miễn phí của GP247 (từ bản 2.3.0) dùng sẵn cơ
chế này cho khách và quản trị viên đã bật xác thực hai lớp (mã TOTP hoặc mã khôi phục). Lập trình viên plugin xem
[Hướng dẫn tạo API — mục 7](./create-api_vi.md).

### Nhật ký truy cập API quản trị

Mỗi lần gọi API Core (trừ `login`) được ghi vào **Nhật ký thao tác** của trang quản trị — như khi thao tác trên
màn admin: người dùng, đường dẫn, IP, thiết bị; mật khẩu/khoá bị che, nội dung phản hồi không được lưu. Lần gọi bị từ chối
quyền (`403`) cũng được ghi. Tắt cùng nhật ký của trang quản trị (`GP247_ADMIN_LOG=0`). Công cụ đồng bộ gọi API liên tục sẽ
làm nhật ký tăng nhanh — dọn định kỳ ở màn **Nhật ký thao tác**.

## Hỏi & Đáp (Q&A)

**Câu 1: Token lấy ở đâu và tồn tại bao lâu?**

→ Token do endpoint `login` cấp. Mặc định hết hạn sau 7 ngày; gửi `remember_me = true` thì 30 ngày.
Đổi qua biến môi trường ở [Xác thực (đăng nhập vào API)](#xác-thực-đăng-nhập-vào-api). Ứng dụng di động nên đăng nhập kèm `with_refresh: true` để dùng token 60 phút và tự
gia hạn bằng refresh token ([Refresh token — giữ đăng nhập mà token ngắn hạn](#refresh-token--giữ-đăng-nhập-mà-token-ngắn-hạn)). Muốn thoát an toàn, gọi `POST logout` với token hiện tại — phiên đó bị đăng xuất
và token không dùng lại được nữa.

**Câu 2: App di động nên dùng refresh token không?**

→ Nên. Đăng nhập kèm `with_refresh: true`: token chính chỉ sống 60 phút, app tự xin token mới bằng refresh token nên khách không phải
đăng nhập lại, và token bị lộ chỉ dùng được thời gian ngắn.

**Câu 3: Đổi mật khẩu thì token đang dùng có còn chạy không?**

→ Không. Đổi hoặc đặt lại mật khẩu (trên website hay qua API) làm mọi token của tài khoản đó hết hiệu lực; app phải đăng nhập lại.

---

<sub>📅 **Cập nhật lần cuối:** 2026-11-03 · ✍️ **Tác giả (Author):** GP247</sub>
