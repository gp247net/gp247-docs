> 🌐 **Language:** [🇻🇳 Tiếng Việt](./README_vi.md) · 🇬🇧 English (current)

# GP247 Documentation (gp247-docs)

## Introduction
This is the shared GP247 documentation repo. This page is the **index** listing all documents in the
repo with links to each one. Documents are grouped by topic (API, extensions/plugins, system, S-Cart).

## Document list

### API
| Document | Summary | Last updated |
| --- | --- | --- |
| [GP247 API Introduction](./api/api-introduction.md) | Overview of the API system, authentication and how to call it | 2026-07-30 |
| [How to build an API correctly](./api/create-api.md) | How to write a new API endpoint the GP247 way | 2026-07-30 |

### Extensions (Plugins)
| Document | Summary | Last updated |
| --- | --- | --- |
| [Creating a Plugin (v2 standard)](./extension/create-plugin.md) | Build a new v2-standard plugin that updates safely | 2026-08-23 |
| [Creating a Template (storefront theme)](./extension/create-template.md) | Build a new storefront template; the gp247/shop view fallback mechanism | 2026-08-23 |
| [Installing Plugins & Templates](./extension/install-extension.md) | 4 methods: online (library), import (.zip), manual, CLI (`gp247:ext-*`) | 2026-08-24 |
| [Converting a Plugin from v1 to v2](./extension/convert-plugin-v1-to-v2.md) | Upgrade a plugin from Core 1.x to Core 2.0 | 2026-07-30 |

### System
| Document | Summary | Last updated |
| --- | --- | --- |
| [GP247 v3.0 release notes — order money](./system/release-notes-3.0.md) | Upgrading from v2.1/v3.0: payment ledger per order, cash flow counted by receipt date, payment status off-by-one fixed, **discount taken before tax**; note for plugin developers | 2026-08-30 |
| [Command-Line (CLI) Reference](./system/command-line-reference.md) | Reference for all GP247 CLI commands: `--json`/exit-code output contract, `gp247:ext-*` lifecycle, install/update/doctor/info; `core-update` runs the upgrade migrations (core 2.2) | 2026-08-29 |
| [How to update GP247](./system/update-gp247.md) | Safe update for a live site, prioritizing the standardized `gp247:update`; automatic data conversion since the public v2.1; `--overwrite-lang`, `--publish` options | 2026-08-29 |
| [Multi-language system](./system/language-system.md) | Using languages / i18n in GP247 | 2026-07-30 |
| [Mail system](./system/mail-system.md) | Mail sending flow (with diagrams), SMTP config, channel selection | 2026-08-05 |
| [Scheduler & Queue](./system/schedule-and-queue.md) | schedule:run vs queue:work; per-environment cron for mail | 2026-08-05 |
| [Cache Handling](./system/cache-system.md) | Config Cache Manager screen; what is/isn't cached; version-bump; helper functions | 2026-08-12 |
| [Custom Fields](./system/custom-fields.md) | The 4 hook links; coverage limited to customer/product; dev guide to wire other tables | 2026-08-14 |
| [Permissions (Permission · Role · User)](./system/permission-and-role.md) | Admin RBAC: 3 building blocks, 2 special roles, address+method gating, strategy formula | 2026-08-16 |

### S-Cart (store)
| Document | Summary | Last updated |
| --- | --- | --- |
| [Order lifecycle](./s-cart/order-lifecycle.md) | Every stage of an order — placing, editing, receiving money, refunding, cancelling, re-opening, deleting — with stock/money/history at each, plus flow diagrams | 2026-08-29 |
| [Currency](./s-cart/currency.md) | Explicit base currency, value-preserving rebase, money-input hint (from shop 2.1) | 2026-08-22 |
| [Tax in GP247](./s-cart/tax.md) | How per-product tax works and how to configure it | 2026-08-04 |
| [Product Bundle (Combo)](./s-cart/product-bundle.md) | Create a combo product of several child products; price & stock | 2026-08-04 |
| [Product Structure (Single/Bundle/Group)](./s-cart/product-structure.md) | Compare the 3 product kinds with a chart; which to choose | 2026-08-04 |
| [Product stock management](./s-cart/product-stock-management.md) | When stock decreases/returns; over-stock config; **cancelling returns stock, deleting no longer does** (changed in v3.0) | 2026-08-29 |
| [Product Attributes (Color/Size)](./s-cart/product-attribute.md) | Attribute groups & values + surcharge; admin setup; price/cart/order flow; price safety | 2026-08-13 |
| [Product keyword tags (Product Tag)](./s-cart/product-tag.md) | Create/assign keyword tags to products; vs Delivery type; disable vs delete (from shop 2.1.6) | 2026-08-25 |

---

<sub>📅 **Last updated:** 2026-08-29 · ✍️ **Author:** GP247</sub>
