> 🌐 **Language:** [🇻🇳 Tiếng Việt](./multi-store-customize_vi.md) · 🇬🇧 English (current)

# Multi-Store — Part 3: Customization

## Introduction
This document explains how to **give each store its own identity** without breaking your ability to update: per-store themes and home-page layouts, on-screen text, permissions for your staff, and the principles developers should follow when writing plugins that work on a multi-store system. It is written for site administrators and developers. After reading it you will know what may be changed, how, and what you should never touch.

One principle runs through all of it: **do not edit files inside the plugin folder**. Anything changed there is lost on the next update.

## Four levels of customization

| Level | What you can do | What you need |
| --- | --- | --- |
| 1. Per-store branding | Theme, logo, brand colours, maintenance page | Admin access only |
| 2. Home-page layout | Arrange display blocks per store | Admin access only |
| 3. Text and permissions | Screen labels, languages, who may do what | Admin access only |
| 4. Code extensions | Write plugins that behave correctly across stores | A developer |

## Level 1 — Per-store branding
Go to **Multi-store → Store list → Configure** on the store you want to change. Here you set, for that store alone:

- **Theme (template)** — each store picks any template installed on the system.
- **Logo, favicon, social share image**.
- **Name, description and SEO keywords** per language.
- **Contact details**: phone, hotline, email, opening hours, address, office, warehouse.
- **Maintenance page content** per language — what customers see when you pause the store.
- The store's **default language and currency**.

> ⚠️ **Changing the theme is a heavy operation and requires confirmation.** The system removes that store's old theme data and builds the new theme's data. Concretely: **that store's layout-block arrangement is lost and must be redone** for the new theme. Products, orders and customers are unaffected.

For a genuinely different design (not just a different logo), build a new template and assign it to the store: see [Creating a Template](../extension/create-template.md).

## Level 2 — A home-page layout per store
**Layout blocks** are the content blocks stacked on the home page: hero banners, featured products, categories, news…

Unlike products or banners (which can belong to several stores), **layout blocks are exclusive to one store**. Each store decides what its home page shows and in what order, without affecting the others. Open **Layout blocks** in the admin, pick the store, and arrange them.

This is the cheapest way to make two stores on the same template look clearly different.

## Level 3 — Text and permissions

### Changing on-screen text
Every label in the plugin is a **language string stored in the database**, editable right in the admin with **no file editing**:

1. Go to **Localisation → Language manager**.
2. Filter **Group** = `multi_store`, pick a language, and search by key or by the text currently on screen.
3. Edit and **Save** — it takes effect immediately and **survives plugin updates**.

To restore a default: delete that row, then **open the Store list screen once** — the plugin reseeds the original string. How the language system works: [Multi-language system](../system/language-system.md).

### Permissions for your staff
The Multi-store screens sit inside S-Cart's shared permission system: go to **User permissions → Roles / Permissions** and grant access **by screen path**. Staff without a permission never see the matching menu item.

With the Pro edition you also get **two preset roles** for handing a store to someone else:

| Preset role | Scope |
| --- | --- |
| **Store Admin** | The store's full business scope, including its configuration and reports |
| **Store Member** | Day-to-day operations: products, orders, customers, content |

You may build your own roles instead. But remember: **the store boundary is not a permission setting**. Whatever role or permissions they hold, a store administrator is always blocked by the system from system-level actions and from other stores' data — details in [Part 2 — Operations](./multi-store-operations.md).

RBAC fundamentals: [Permissions (Permission · Role · User)](../system/permission-and-role.md).

## Level 4 — For developers

**Writing a plugin that behaves correctly across stores.** If your plugin has settings each store needs to set differently (shipping fees, payment gateway keys, promotion thresholds…), declare that it operates **per store** in its `gp247.json` manifest:

```json
"storeScope": "store"
```

The plugin management screen then lets the administrator **enable and configure it separately for each store**, and your plugin reads the value belonging to the store currently being served — you do not have to build that mechanism yourself. GP247's own payment, shipping, promotion, news and product-review plugins all declare it.

**Things to keep in mind when writing code on a multi-store system:**

- **Always ask "which store?" before reading or writing data.** A query without a store filter returns data from every store — right for a system-wide report, wrong almost everywhere else.
- **Never assume there is only one store.** Hard-coding a domain, or reading a system-wide setting for something that is genuinely per store, are the two most common mistakes.
- **Never assume the root store is the one being served** — the visitor may be on another store's domain.
- **Do not edit files inside an installed plugin folder** (`app/GP247/Plugins/...`) — changes are lost on update. Use language strings, template views and the official hooks.
- **After replacing files by hand**, run `php artisan gp247:cache-rebuild` and open the plugin's admin screen once.

See also: [Creating a Plugin (v2 standard)](../extension/create-plugin.md) and [Creating a Template](../extension/create-template.md).

## Conditions & Rules (know before you act)

**When changing a store's theme**
- **It requires confirmation before it runs** — it destroys that store's old theme data.
- **That store's layout-block arrangement is lost** and must be redone for the new theme.
- **Only that store is affected** — the other stores keep their own themes.

**When editing language strings**
- **Editing in the admin survives updates; editing files in the plugin folder does not.**
- **Deleting a string row restores the default**, but you must open the Store list screen so the plugin reseeds it — otherwise the screen shows the raw key instead of text.

**When granting permissions**
- **Permissions are granted by screen path, not by menu label.**
- **Permissions cannot widen a store administrator's scope** — the store boundary is held by the system, and granting more rights does not cross it.

**When writing plugins**
- **A plugin that does not declare `storeScope` has system-wide settings** — every store shares one value. Declare it when the settings must differ per store.
- **Uninstalling Multi-Store does not delete your store data**, but the website will stop resolving stores by domain — think carefully before uninstalling on a live site.

## Q&A
**Q1: My two stores use the same template — how do I make them look different?**

→ Change the logo and colours, and **arrange each store's home-page layout blocks separately** — layout blocks are per-store data. For a genuinely different design, build a separate template and assign it.

**Q2: Does changing a store's theme lose products or orders?**

→ No. Only that theme's own data is rebuilt, most notably the store's layout-block arrangement.

**Q3: Can each store speak a different language?**

→ Yes. Each store has its own default language, and content (name, description, maintenance page) is entered per language.

**Q4: My employee should manage only one store — how?**

→ With the Pro edition: assign the store to that account on the **Store administrators** screen and pick the Store Admin or Store Member preset role. They sign in on that store's domain and only see their own scope.

**Q5: Will the plugin I wrote myself work on a multi-store system?**

→ Yes, but declare `"storeScope": "store"` in `gp247.json` if its settings must differ per store, and always filter data by the store currently being served.

**Q6: A screen shows a raw key (like `multi_store.something`) instead of text — why?**

→ The language string has not been seeded. Open the **Store list** screen once, or disable and re-enable the plugin.

---

⬅️ [Part 2 — Operations](./multi-store-operations.md) · [Index](./multi-store.md)

---

<sub>📅 **Last updated:** 2026-09-21 · ✍️ **Author:** GP247</sub>
