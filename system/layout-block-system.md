> 🌐 **Language:** [🇻🇳 Tiếng Việt](./layout-block-system_vi.md) · 🇬🇧 English (current)

# Layout Blocks: how they render, how to customise them, and the fallback

## Introduction
This document explains **Layout Block** — the tool that lets you add content blocks to your storefront (product strips, banners, plain HTML, blocks provided by a plugin) without touching source code. The first half is for **site owners**: adding a block, changing how one looks. The second half is for **plugin and template developers**: how a plugin registers its own block and the order the system uses to pick a file at render time. By the end you will know where a block's content comes from, and where to edit it so an update never wipes your work.

## What a Layout Block is

Go to **Admin → Appearance → Layout block** (path `.../layout_block`). Each row there is **one block**, with these fields:

| Field | Meaning |
| --- | --- |
| **Name** | A label for your own reference; only visible in admin |
| **Position** | The region of the page the block goes into (top, bottom, left column…) |
| **Page** | Which page type(s) the block appears on; `*` means every page |
| **Type** | The kind of content: `html`, `view` or `page` |
| **Text** | The content itself, or the **name** of a block/page depending on Type |
| **Sort** | Higher numbers come first when several blocks share a region |
| **Status** | Turns the block on or off |

The three content kinds:

1. **`html`** — you paste raw HTML into the Text field. Simplest, no coding.
2. **`view`** — the block is an existing view file (provided by your template or by a plugin). Text holds the **block name**, for example `shop_product_home` or `product_flash_sale`. This is the kind this document mostly talks about.
3. **`page`** — embeds one of the static (CMS) pages you created.

## The regions

Your template decides which regions exist. The default template **GP247Front** renders these:

| Region | Where it sits |
| --- | --- |
| `top` | Just under the header, above the main content |
| `left` / `right` | Left / right column (the page switches to a two-column layout when one has content) |
| `center` | In the middle of the main content |
| `bottom` | End of the content, above the footer |
| `footer` | Inside the footer |

The default home page uses `bottom` for its product strips (new products, promotions, Flash Sale…).

## How rendering works: which file does the system pick?

This is the core of it. For a **`view`** block, the Text field only stores the **block name** (e.g. `product_flash_sale`) — **not a file path**. The file is looked up at render time, in this exact order:

```
Block named "abc" needs to render
│
├─ 1. Does your template have its own file?
│     app/GP247/Templates/{TEMPLATE_NAME}/blocks/abc.blade.php
│     → YES ⇒ use this file. STOP.
│
├─ 2. Has a plugin registered the block name "abc"?
│     → YES ⇒ use the plugin's view, where:
│           a) resources/views/vendor/Plugins/{PluginName}/…   ← your override
│           b) app/GP247/Plugins/{PluginName}/Views/…          ← the plugin's original
│
└─ 3. Found nowhere ⇒ the block quietly renders nothing (the page itself is fine).
```

Three things follow from this:

- **Your copy always wins.** Putting a file with the same name in your template is enough to replace a block — no admin change needed, and no editing of plugin code.
- **The plugin's copy is the fallback.** Delete your custom file and the block goes straight back to the plugin's version; nothing breaks.
- **Nothing found means silence.** The system deliberately does not print errors on a customer-facing page. If your block "disappeared", see *Conditions & Rules* below.

A broken block (say, a plugin bug) costs **only that block**; the other blocks and the page around them still render.

## Customising a block (for site owners)

The examples use the `product_flash_sale` block from the *Product Flash Sale* plugin. Replace `{TEMPLATE_NAME}` with the template your site runs (`GP247Front` by default) and `{PluginName}` with the plugin's folder name (e.g. `ProductFlashSale`).

> ⚠️ **Do not edit files inside the plugin folder** (`app/GP247/Plugins/...`). The next plugin update overwrites them and your work is gone.

### Option 1 — Add around it, keep the inside (lightest, recommended)

1. Create a new file at this path (create the `blocks` folder if it does not exist yet):

   ```
   app/GP247/Templates/{TEMPLATE_NAME}/blocks/product_flash_sale.blade.php
   ```

2. Paste this in and change the wording as you like:

   ```blade
   <div class="container-x pt-6">
       <h2 class="section-title">Today's golden hour</h2>
   </div>

   @includeIf('Plugins/ProductFlashSale::blocks.product_flash_sale')
   ```

3. Reload your home page. If it worked, your new heading sits right above the existing product strip.

Use `@includeIf`, **not** `@include`: if you remove the plugin later, `@includeIf` simply renders nothing, while `@include` makes the page fail with "View not found".

The advantage of this option: the inside is still the plugin's, so you keep getting its improvements.

### Option 2 — Rewrite the whole block

1. Same file path as step 1 above.
2. Write the whole markup yourself, pulling data through the plugin's helper:

   ```blade
   @php
       $products = gp247_product_flash(10);
   @endphp

   @foreach ($products as $product)
       <div>{{ $product->getName() }}</div>
   @endforeach
   ```

3. Reload. The plugin's block is gone entirely; only your markup remains.

### Option 3 — Change one small piece inside the block

Use this when the block is fine and you only want to change a part of it (say, just the product card).

1. Create a file with the **same name and same sub-path** as the plugin's file, but under Laravel's override folder:

   ```
   resources/views/vendor/Plugins/ProductFlashSale/partials/flash_card.blade.php
   ```

   (the original is `app/GP247/Plugins/ProductFlashSale/Views/partials/flash_card.blade.php`)

2. Write the product card the way you want it.
3. Reload the page — no cache-clearing command needed. Your card is now used **everywhere** the plugin renders one.

### Going back to the original

Delete the file you created. The system falls back to the plugin's version on the very next page load.

### Quick comparison

| | Option 1 (wrap) | Option 2 (rewrite) | Option 3 (one piece) |
| --- | --- | --- | --- |
| Effort | Low | High | Medium |
| Still receives the plugin's UI updates | ✅ Yes | ❌ No | Partly |
| Good for | Adding a heading, frame, background | A completely different design | Tweaking a card or button |

## For developers: registering a block from a plugin

If you write a plugin and want it to offer a block on the Layout block screen, **register it** — do not ship a template directory inside the plugin, and do not copy files into `app/GP247/Templates`.

### How

In the plugin's `Provider.php`, inside the existing `gp247_extension_check_active()` block, add:

```php
$blockViews = config('gp247-config.front.layout_block_views', []);
$blockViews['product_flash_sale'] = $extensionPath.'::blocks.product_flash_sale';
config(['gp247-config.front.layout_block_views' => $blockViews]);
```

- The **key** (`product_flash_sale`) is the block name an administrator sees in the Text field.
- The **value** is your plugin's view key, pointing at the real file, e.g. `app/GP247/Plugins/ProductFlashSale/Views/blocks/product_flash_sale.blade.php`.

That is all. The block shows up in the Layout block picker and renders — on **every** template.

### Why not copy a file into the template folder

The old approach (copy on install) failed in four places, and every one of them failed **silently**:

1. It needs a **writable** template directory. On a read-only deployment the copy step was skipped, so the site owner got no block and no error.
2. It only copied for the template **active at install time**. Switching templates, or adding a store, lost the block.
3. A plugin update **never refreshed** the copy — the stale copy kept rendering forever.
4. Uninstalling left an **orphan file** behind in the template folder.

Registering avoids all four: it works on a read-only server, serves every template, follows the plugin's updates, and disappears completely when the plugin is removed.

### Naming conventions

- **Prefix the name with your plugin's concern** (`product_flash_sale`, `vendor_new`), because block names share one flat namespace with the template's own blocks. Two plugins using the same name will collide.
- **Never rename a block after release**: the name is stored in site data (the Text field of each Layout block row), so renaming it makes the block vanish on every site already using it.

### Technical notes

- The admin picker and the storefront renderer read the **same source** in the **same order of precedence**, so the picker never offers something different from what will render.
- A block is only registered while the plugin is **enabled**. Disabling the plugin leaves the Layout block row in place but renders nothing; enabling it brings the block back.

## Conditions & Rules (know before you act)

### When creating a Layout block row

- **The Position must be a region your template actually renders.** The list of positions and the regions a template calls are two separate things; picking a position your template does not use means the block **saves fine, appears in the list, and produces nothing on the page**. Known case on GP247Front: the `top_site` position is not rendered by any template — so injecting code into the `<head>` area cannot currently be done from this screen.
- **Page takes a page type, not a URL.** The Page field accepts page-type codes (`front_home`, `shop_product_detail`, `shop_cart`…) or `*` for every page. Putting a URL or a CMS page name in there never matches, and the block never shows.
- **The block name (Text) must come from the list.** With `Type = view`, typing a name that does not exist means the system finds no file and skips the block — with no error message.

### When a block does not show up

Check these four, in this order — they cover almost every case:

- **The block is switched off** — the Status column must be on.
- **The row belongs to a different store or template.** Every Layout block row is tied to **one store** and **one template**. Changing a store's template stops rows created for the old template from rendering (they are not deleted — switching back brings them back).
- **The plugin providing the block is disabled or removed** — the block disappears, the row stays.
- **Wrong position or wrong page type** — see the section above.

### When customising a block's appearance

- **The file name must match the block name exactly**: `blocks/{block name}.blade.php`. One wrong character and the system ignores your file and uses the plugin's — which looks like "my edit does nothing".
- **The template name is case-sensitive** and must match the folder name under `app/GP247/Templates/`.
- **Editing files inside the plugin folder is lost on the next plugin update.** Always use one of the three options above.
- **Options 2 and 3 create a frozen copy**: if the plugin improves its markup later, the part you overrode will not receive that change. Option 1 does not have this drawback.

## Q&A

**Q1: I edited a file in the plugin folder and lost everything after updating the plugin. Can I get it back?**

→ Not automatically. Copy whatever you want to keep into `app/GP247/Templates/{TEMPLATE_NAME}/blocks/{block name}.blade.php` — from then on plugin updates will not touch it.

**Q2: I created my custom file but the page still shows the old one. Why?**

→ Check three things: the file name is exactly `{block name}.blade.php`, the template folder name matches the template your store actually uses, and the file sits inside the `blocks` folder. If it still does not take effect, run `php artisan optimize:clear` and reload.

**Q3: If I remove the plugin, will my wrapped block break the page?**

→ No, as long as you used `@includeIf`. The page will simply render your part and skip the plugin's. If you used `@include`, change it to `@includeIf`.

**Q4: My site runs a custom template — will a plugin's block still work?**

→ Yes. A registered block works on every template; you do not have to create any file.

**Q5: I run several stores (multi-store). Do I have to create the block more than once?**

→ One row per store, because each row is tied to one store and one template. The plugin's block itself only needs the plugin enabled to be available everywhere.

**Q6: What happens if two plugins register the same block name?**

→ The one registered last overrides the other, and the picker shows a single entry. That is why developers should prefix block names with their plugin's concern.

**Q7: If my block has a bug, will it take the whole page down?**

→ No. The failure is contained to that block: the block does not render, the rest of the page is unaffected, and the error goes to the system log.

**Q8: How do I inject a tracking snippet (Google Analytics, Facebook Pixel)?**

→ Create a block with `Type = html`, paste the snippet into Text, set `Page = *` and choose a position your template really renders (for example `footer`). Note from *Conditions & Rules*: the `top_site` position currently renders nothing.

**Q9: Can I hard-wire a block into my template instead of using Layout block?**

→ Yes. Add one line, `@includeIf('Plugins/{PluginName}::blocks.{block name}')`, wherever you want it in your template file.

**Q10: Does deleting a Layout block row delete content?**

→ With `Type = html`, the content lives in that row, so it goes with it — copy it somewhere first. With `Type = view` and `Type = page`, the row only points at a file or a page, so deleting it loses nothing.

---

<sub>📅 **Last updated:** 2026-09-22 · ✍️ **Author:** GP247</sub>
