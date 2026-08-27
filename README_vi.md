> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./README.md)

# Tài liệu GP247 (gp247-docs)

## Giới thiệu
Đây là repo tài liệu chung của GP247. Trang này là **mục lục** liệt kê toàn bộ tài liệu trong repo và
link tới từng tài liệu. Tài liệu được nhóm theo chủ đề (API, mở rộng/plugin, hệ thống, S-Cart).

## Danh sách tài liệu

### API
| Tài liệu | Mô tả ngắn | Cập nhật lần cuối |
| --- | --- | --- |
| [Giới thiệu API GP247](./api/api-introduction_vi.md) | Tổng quan hệ thống API, xác thực và cách gọi thử | 2026-07-30 |
| [Hướng dẫn tạo API đúng chuẩn](./api/create-api_vi.md) | Cách tự viết endpoint API mới theo chuẩn GP247 | 2026-07-30 |

### Mở rộng (Plugin)
| Tài liệu | Mô tả ngắn | Cập nhật lần cuối |
| --- | --- | --- |
| [Hướng dẫn tạo Plugin (chuẩn v2)](./extension/create-plugin_vi.md) | Tạo plugin mới đúng chuẩn v2, an toàn khi cập nhật phiên bản | 2026-08-23 |
| [Hướng dẫn tạo Template (giao diện)](./extension/create-template_vi.md) | Tạo template storefront mới; cơ chế fallback view của gp247/shop | 2026-08-23 |
| [Cài đặt Plugin & Template](./extension/install-extension_vi.md) | 4 cách cài: online (thư viện), import (.zip), thủ công, CLI (`gp247:ext-*`) | 2026-08-24 |
| [Chuyển đổi Plugin v1 lên v2](./extension/convert-plugin-v1-to-v2_vi.md) | Nâng cấp plugin từ Core 1.x lên Core 2.0 | 2026-07-30 |

### Hệ thống
| Tài liệu | Mô tả ngắn | Cập nhật lần cuối |
| --- | --- | --- |
| [Danh sách lệnh CLI](./system/command-line-reference_vi.md) | Tham chiếu mọi lệnh CLI của GP247: hợp đồng output `--json`/mã thoát, vòng đời `gp247:ext-*`, install/update/doctor/info (core 2.1) | 2026-08-24 |
| [Hướng dẫn cập nhật GP247](./system/update-gp247_vi.md) | Cập nhật an toàn cho site đang chạy, ưu tiên lệnh chuẩn hóa `gp247:update`; tùy chọn `--overwrite-lang`, `--publish` | 2026-08-24 |
| [Hệ thống đa ngôn ngữ](./system/language-system_vi.md) | Cách dùng ngôn ngữ / i18n trong GP247 | 2026-07-30 |
| [Hệ thống gửi mail](./system/mail-system_vi.md) | Luồng gửi mail (kèm sơ đồ), cấu hình SMTP, chọn kênh gửi | 2026-08-05 |
| [Lịch chạy & Hàng đợi](./system/schedule-and-queue_vi.md) | schedule:run vs queue:work; cài cron gửi mail theo môi trường | 2026-08-05 |
| [Xử lý Cache](./system/cache-system_vi.md) | Màn Config Cache Manager; cache gì/không cache gì; version-bump; hàm helper | 2026-08-12 |
| [Trường tùy chỉnh (Custom Field)](./system/custom-fields_vi.md) | Cơ chế 4 hook; độ phủ chỉ customer/product; hướng dẫn dev bật cho bảng khác | 2026-08-14 |
| [Phân quyền (Quyền · Vai trò · Người dùng)](./system/permission-and-role_vi.md) | RBAC admin: 3 khối, 2 vai trò đặc biệt, cách gác theo địa chỉ+phương thức, công thức chiến lược | 2026-08-16 |

### S-Cart (bán hàng)
| Tài liệu | Mô tả ngắn | Cập nhật lần cuối |
| --- | --- | --- |
| [Tiền tệ (Currency)](./s-cart/currency_vi.md) | Đồng tiền gốc tường minh, đổi gốc bảo toàn giá trị, ghi chú ô nhập tiền (từ shop 2.1) | 2026-08-22 |
| [Thuế trong GP247](./s-cart/tax_vi.md) | Cách thuế theo sản phẩm hoạt động và cách cấu hình | 2026-08-04 |
| [Sản phẩm gói (Bundle/Combo)](./s-cart/product-bundle_vi.md) | Tạo sản phẩm combo gồm nhiều sản phẩm con; giá & trừ kho | 2026-08-04 |
| [Tổ chức sản phẩm (Single/Bundle/Group)](./s-cart/product-structure_vi.md) | So sánh 3 loại sản phẩm kèm sơ đồ; chọn loại nào | 2026-08-04 |
| [Quản lý tồn kho sản phẩm](./s-cart/product-stock-management_vi.md) | Tồn kho tăng/giảm khi nào; cấu hình vượt tồn; xóa đơn vs đổi trạng thái | 2026-08-27 |
| [Thuộc tính sản phẩm (Color/Size)](./s-cart/product-attribute_vi.md) | Nhóm & giá trị thuộc tính + phụ phí; khai báo ở admin; luồng giá/giỏ/đơn; an toàn giá | 2026-08-13 |
| [Thẻ từ khóa sản phẩm (Product Tag)](./s-cart/product-tag_vi.md) | Tạo/gán thẻ từ khóa cho sản phẩm; phân biệt với Loại giao hàng; tắt vs xóa (từ shop 2.1.6) | 2026-08-25 |

---

<sub>📅 **Cập nhật lần cuối:** 2026-08-27 · ✍️ **Tác giả (Author):** GP247</sub>
