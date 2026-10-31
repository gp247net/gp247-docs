> 🌐 **Language:** [🇻🇳 Tiếng Việt](./README_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./README_ja.md)

# GP247 Documentation (gp247-docs)

## Introduction
This is the shared GP247 documentation repo. This page is the **index** listing all documents in the
repo with links to each one. Documents are grouped by topic (API, extensions/plugins, releases, system, S-Cart).

## Document list

### API
| Document | Summary | Last updated |
| --- | --- | --- |
| [GP247 API Introduction](./api/api-introduction.md) | Overview of API v2: authentication, customer accounts, cart & ordering, how to call it | 2026-10-31 |
| [API v1 (no longer developed)](./api/api-v1.md) | The old API for apps written before v2: endpoint list, turning it on, moving to v2 | 2026-10-31 |
| [How to build an API correctly](./api/create-api.md) | How to write a new API endpoint the GP247 way | 2026-10-31 |

### Extensions (Plugins)
| Document | Summary | Last updated |
| --- | --- | --- |
| [Creating a Plugin](./extension/create-plugin.md) | Starting point: pick the right guide for your kind of plugin, a 6-step roadmap with code (settings in the database, settings screen, install, release) | 2026-10-22 |
| [Creating a payment plugin](./extension/create-payment-plugin.md) | Step by step with code: a bank transfer plugin that works right away + a skeleton for an online gateway (money recorded once, webhook) | 2026-10-22 |
| [Creating a shipping plugin](./extension/create-shipping-plugin.md) | Step by step with code: a weight-based price plugin that works right away + a skeleton for a carrier with an API (shipments, webhook, COD) | 2026-10-22 |
| [Update-safe plugins](./extension/create-plugin-update-safety.md) | How updates run, 6 rules, the `update()` hook, own data tables | 2026-10-22 |
| [Advanced plugins](./extension/create-plugin-advanced.md) | Per-store settings, secret keys, storefront pages / blocks / hooks, coupons, menus | 2026-10-22 |
| [Creating a Template (storefront theme)](./extension/create-template.md) | Build a new storefront template; the gp247/shop view fallback mechanism | 2026-10-22 |
| [Installing Plugins & Templates](./extension/install-extension.md) | 4 methods: online (library), import (.zip), manual, CLI (`gp247:ext-*`) | 2026-10-22 |
| [Converting a Plugin from v1 to v2](./extension/convert-plugin-v1-to-v2.md) | Upgrade a plugin from Core 1.x to Core 2.0 | 2026-10-22 |

### Releases
| Document | Summary | Last updated |
| --- | --- | --- |
| [S-Cart releases and security](./release/release.md) | Is your site safe (minimum version per package); highlights; how to update; S-Cart releases (each bundling core/front/shop): what is new, bug fixes, what to do when updating; security fixes; reporting a vulnerability | 2026-10-31 |

### System
| Document | Summary | Last updated |
| --- | --- | --- |
| [GP247 v3.0 release notes — order money](./system/release-notes-3.0.md) | Upgrading from v2.1/v3.0: payment ledger per order, cash flow counted by receipt date, payment status off-by-one fixed, **discount taken before tax**; note for plugin developers | 2026-10-22 |
| [Command-Line (CLI) Reference](./system/command-line-reference.md) | Reference for all GP247 CLI commands: `--json`/exit-code output contract, `gp247:ext-*` lifecycle, install/update/doctor/info; `core-update` runs the upgrade migrations (core 2.2) | 2026-10-22 |
| [How to update GP247](./system/update-gp247.md) | Safe update for a live site, prioritizing the standardized `gp247:update`; automatic data conversion since the public v2.1; `--overwrite-lang`, `--publish` options; applies extension data updates after `git pull` | 2026-10-22 |
| [Multi-language system](./system/language-system.md) | Using languages / i18n in GP247 | 2026-10-22 |
| [Mail system](./system/mail-system.md) | Mail sending flow (with diagrams), SMTP config, channel selection | 2026-10-22 |
| [Sensitive data encryption](./system/data-encryption.md) | At-rest secret encryption (SMTP/OAuth/license); dedicated `GP247_ENCRYPTION_KEY`; **safe key-change steps** with `gp247:encryption-key-rotate`; encrypting custom columns | 2026-10-22 |
| [Scheduler & Queue](./system/schedule-and-queue.md) | schedule:run vs queue:work; per-environment cron for mail | 2026-10-22 |
| [Cache Handling](./system/cache-system.md) | Config Cache Manager screen; what is/isn't cached; version-bump; helper functions | 2026-10-22 |
| [Custom Fields](./system/custom-fields.md) | The 4 hook links; coverage limited to customer/product; dev guide to wire other tables | 2026-10-22 |
| [Layout Blocks](./system/layout-block-system.md) | How a block renders: template-first then plugin fallback, three update-safe ways to customise, how a plugin registers a block | 2026-10-22 |
| [Permissions (Permission · Role · User)](./system/permission-and-role.md) | Admin RBAC: 3 building blocks, 2 special roles, address+method gating, strategy formula | 2026-10-22 |

### S-Cart (store)
| Document | Summary | Last updated |
| --- | --- | --- |
| [Order lifecycle](./s-cart/order-processing/order-lifecycle.md) | Every stage of an order — placing, editing, receiving money, refunding, cancelling, re-opening, deleting — with stock/money/history at each, plus flow diagrams | 2026-10-22 |
| [Payment requests](./s-cart/order-processing/payment-request.md) | Money in/out outside the cart: online payment links, hand entries, refunds; 4 money flows, order/InOut/MultiVendor purposes, pay-out rights, flow diagrams | 2026-10-22 |
| [Payments in GP247](./s-cart/payment/payment.md) | Store setup and day-to-day use: payment gateways (test → live, webhook), the order payment ledger, COD, the instructions customers see; developers are pointed to [Creating a payment plugin](./extension/create-payment-plugin.md) | 2026-10-22 |
| [Shipping in GP247](./s-cart/shipping/shipping.md) | Store setup and day-to-day use: Shipping tab, pick-up warehouse, shipments, tracking, cash on delivery (since shop 3.1.4); developers are pointed to [Creating a shipping plugin](./extension/create-shipping-plugin.md) | 2026-10-22 |
| [Currency](./s-cart/tax-and-currency/currency.md) | Explicit base currency, value-preserving rebase, money-input hint (from shop 2.1) | 2026-10-22 |
| [Tax in GP247](./s-cart/tax-and-currency/tax.md) | How per-product tax works and how to configure it | 2026-10-22 |
| [Product Bundle (Combo)](./s-cart/product/product-bundle.md) | Create a combo product of several child products; price & stock | 2026-10-22 |
| [Product Structure (Single/Bundle/Group)](./s-cart/product/product-structure.md) | Compare the 3 product kinds with a chart; which to choose | 2026-10-22 |
| [Product stock management](./s-cart/product/product-stock-management.md) | When stock decreases/returns; over-stock config; **cancelling returns stock, deleting no longer does** (changed in v3.0) | 2026-10-22 |
| [Product Attributes (Color/Size)](./s-cart/product/product-attribute.md) | Attribute groups & values + surcharge; admin setup; price/cart/order flow; price safety | 2026-10-22 |
| [Product keyword tags (Product Tag)](./s-cart/product/product-tag.md) | Create/assign keyword tags to products; vs Delivery type; disable vs delete (from shop 2.1.6) | 2026-10-22 |

### Multi-store & Marketplace
> The two models are **mutually exclusive** — pick one: several stores of your own (Multi-Store), or one marketplace for many sellers (Multi-Vendor).

| Document | Summary | Last updated |
| --- | --- | --- |
| [Multi-Store — many websites, one admin](./multi-store/multi-store.md) | Several stores of one business, each with its own domain/theme/language/currency: the model, features, Free/Pro comparison; index of setup, operations and customization | 2026-10-22 |
| [Multi-Vendor — multi-seller marketplace](./multi-vendor/multi-vendor.md) | Turn S-Cart into a marketplace on one domain: the model, features, Free/Pro comparison, Multi-Store comparison; index of setup, operations and customization | 2026-10-22 |

### Purchasing, returns & cash flow (InOut)
| Document | Summary | Last updated |
| --- | --- | --- |
| [InOut — purchases, returns, cash flow & debts](./in-out/in-out.md) | Paid plugin that adds the buying side to S-Cart: purchase orders, return orders, cash book, two-way debts, collecting/paying debts through payment requests, period closing, Excel/PDF export; index of the 5 process guides | 2026-10-22 |

---

<sub>📅 **Last updated:** 2026-10-31 · ✍️ **Author:** GP247</sub>
