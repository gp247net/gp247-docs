> 🌐 **Language:** [🇻🇳 Tiếng Việt](./multi-vendor-customize_vi.md) · 🇬🇧 English (current)

# Multi-Vendor — Part 3: Customization

## Introduction
This document explains how to **make a Multi-Vendor marketplace look like your brand** without breaking your ability to update the plugin: changing on-screen text and email content, restyling shop pages, granting permissions to marketplace staff, and the principles developers should follow. It is written for site administrators and developers. After reading it you will know what may be changed, how, and what you should never touch.

One principle runs through all of it: **do not edit files inside the plugin folder**. Anything changed there is lost on the next update. The three official routes below all **survive updates**.

## Three levels of customization

| Level | What you can do | What you need |
| --- | --- | --- |
| 1. Text and email | Every screen label, message, email subject and body | Admin access only |
| 2. Design | Shop pages, directory, product grid, home-page block | Basic Blade file editing |
| 3. Code extensions | Inject content from another plugin, add pricing rules | A developer |

## Level 1 — Changing text and email content
Every piece of text in the plugin — screen labels, messages, **email subjects and bodies** — is a **language string stored in the database**, editable right in the admin with **no file editing**:

1. Go to **Localisation → Language manager** (`/gp247_admin/language_manager`).
2. Filter **Group** = `multi_vendor`, pick a language, and search by key or by the text currently on screen.
3. Edit and **Save** — it takes effect immediately and **survives plugin updates**.

Key conventions to help you find things:

| Key prefix | Contents |
| --- | --- |
| `multi_vendor.mail.*` | Email subjects and bodies (e.g. `multi_vendor.mail.vendor_approved.subject`) |
| `multi_vendor.pro.*` | Text on the Pro feature explanation pages |
| everything else | Screen labels, buttons, messages |

To restore a default: delete that row, then **open the Quick config screen once** — the plugin reseeds the original string.

> ⚠️ Do not edit the language files inside the plugin folder — they are only used for install-time messages and **will be overwritten on update**.

## Level 2 — Restyling shop pages

### The "New shops" home-page block
This block is copied into the active template's `blocks/` folder when the plugin is installed — edit it there to change how it looks, and add or remove it through **Layout blocks** in the admin.

### Replacing a whole page with your own
The remaining pages can be **replaced with your own version** without touching the plugin: create a file with the same name in the active template, under

```
app/GP247/Templates/<TemplateName>/Plugins/MultiVendor/<view-name>.blade.php
```

The system prefers the template's file and falls back to the plugin's. That is what keeps your version **safe across plugin updates**.

Replaceable pages:

| View name | Page |
| --- | --- |
| `vendor_index` | Shop directory `/shop` |
| `vendor_home` | Shop page `/shop/{code}` |
| `vendor_info` | The shop's **Info** tab |
| `vendor_product_list` | The product grid inside a shop page |
| `hooks.order_dispute_box` | The dispute box under the customer's order page (Pro) |

**The safe way to work**: copy the plugin's original file to the template path above, then edit it gradually — that way you are sure to keep every variable the page needs.

## Permissions for marketplace staff
The **Marketplace** screens sit inside S-Cart's shared permission system: go to **User permissions → Roles / Permissions** and grant access **by screen path** (for example the stores screen, the payments screen). Staff without a permission never see the matching menu item.

That lets you split work by role: a moderator only sees the review queue, an accountant only sees payments and reports.

> Seller accounts do **not** use this permission system — they sign into their own `/vendor_admin` area and are always confined to their own shop. For RBAC details: [Permissions (Permission · Role · User)](../system/permission-and-role.md).

## Level 3 — For developers

- **Do not edit files inside the plugin folder.** Anything changed in `app/GP247/Plugins/MultiVendor/` is lost on update. Use the three official routes: language strings (Level 1), template views (Level 2) and the hooks below.
- **Injecting content into the customer's order page**: the plugin attaches its dispute box through the storefront's shared hook mechanism (`gp247-config.front.plugin_hooks`, position `shop_order_detail_bottom`). Your own plugin uses the same mechanism to inject its content without touching the template.
- **Adding pricing rules**: per-shop customer-group pricing (Pro) registers with the `gp247/shop` price seam (`gp247-config.shop.price_resolvers`), which is why the product page, cart, checkout, quick order and quotes always agree. Other pricing plugins register the same way.
- **Shop URLs in code**: use the plugin's helper instead of building `/shop/...` strings by hand, so changing the `.env` variable updates every link.
- **Safe updates**: the plugin converges its own state (menus, language strings, data tables) at every entry point — fresh install, reinstall and update. After replacing files by hand, run `php artisan gp247:cache-rebuild` and open the **Quick config** screen once.

See also: [Creating a Plugin (v2 standard)](../extension/create-plugin.md) and [Creating a Template](../extension/create-template.md).

## Conditions & Rules (know before you act)

**When editing language strings**
- **Editing in the admin survives updates; editing files in the plugin folder does not** — those files are overwritten on every update.
- **Deleting a string row restores the default**, but you must open the Quick config screen so the plugin reseeds it — otherwise the screen shows the raw key instead of text.

**When replacing template views**
- **The file name must match the view name in the table above exactly** — get it wrong and the system keeps using the plugin's file while you assume your edit did not work.
- **Keep every variable the page needs** — copy from the original and edit gradually rather than writing from scratch.
- **Switching the active template leaves your overrides behind** — each template has its own override folder.

**When changing URLs**
- Read the four notes in [Part 1 — Setup](./multi-vendor-setup.md) first: never clash with a folder in `public/`, never clash with the admin path, and old URLs will 404.

**When granting permissions**
- **Permissions are granted by screen path, not by menu label** — renaming a menu item changes nothing about access.
- **Seller accounts are outside the admin permission system** — you cannot grant them admin rights this way.

## Q&A
**Q1: What happens if I edit a Blade file inside the plugin folder?**

→ It works immediately, but **will be lost on the next plugin update**. Copy it into the template folder (Level 2) to keep it permanently.

**Q2: Where do I change the emails sent to sellers?**

→ **Localisation → Language manager**, filter the `multi_vendor` group and look for keys starting with `multi_vendor.mail.`. No file editing needed.

**Q3: A screen shows a raw key (like `multi_vendor.something`) instead of text — why?**

→ The language string has not been seeded. Open the **Quick config** screen once, or disable and re-enable the plugin.

**Q4: I restyled the shop page and then updated the plugin — is my work lost?**

→ No, if you put the file in the template folder per Level 2. Yes, if you edited the plugin folder directly.

**Q5: Can my accountant see only the payments screen?**

→ Yes. Create a role containing only the payment/report screens and assign it to that account; the other menu items will not appear.

**Q6: If I switch storefront templates, do shop pages still work?**

→ Yes. Without an override in the new template the system falls back to the plugin's default design. To keep your version, copy the override folder into the new template.

---

⬅️ [Part 2 — Operations](./multi-vendor-operations.md) · [Index](./multi-vendor.md)

---

<sub>📅 **Last updated:** 2026-09-21 · ✍️ **Author:** GP247</sub>
