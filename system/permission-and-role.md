> 🌐 **Language:** [🇻🇳 Tiếng Việt](./permission-and-role_vi.md) · 🇬🇧 English (current)

# Admin permissions in GP247 (Permission · Role · User)

## Introduction
This document explains the GP247 admin **permission system (RBAC)** and shows website
administrators how to **configure who can do what**. By the end you will understand the
three building blocks (Permission – Role – User), how the system decides which screens a
person may open, and how to **design your own permission strategy** for your staff (e.g.
an order-only clerk, a view-only manager…). No coding required.

---

## 1. The three building blocks

GP247 permissions are made of three things combined:

| Block | What it is | Everyday analogy |
| --- | --- | --- |
| **Permission** | A "bunch of keys" — opens one or a few **screens** in the admin | "Order management" opens the order-related pages |
| **Role** | A **group of permissions** bundled for quick assignment | "Sales staff" = orders + customers |
| **User** | An admin login account | Lan, Nam… |

How they combine:
- A **User** may hold **several Roles**, **and/or** be granted a few **direct Permissions**.
- A person's effective permissions = **(all permissions from their roles) + (their direct permissions)** added together.

> This lets you create a few shared Roles for a team, then still "add one extra" permission
> to a single person without creating a whole new role.

---

## 2. What is inside a "Permission"?

Each Permission has:
- **Name:** human-friendly, e.g. "Order management".
- **Slug:** a short identifier (e.g. `order.full`). **The slug is only a label** — it does
  not decide access; name it however you find easy to remember.
- **List of addresses + methods (http_uri):** this is the **deciding part** — it lists the
  **pages (URLs)** this permission opens, and the **kind of action** allowed.

Action kinds:
- **GET** = **view** the page only.
- **ANY** = **do everything** on that page (view + add + edit + delete).

Example address line inside a Permission: `ANY::gp247_admin/order/*`
- `gp247_admin/order/*` = all order pages (the `*` means "and every sub-page").
- `ANY` = allows both viewing and acting.

👉 **Key tip:** when creating a Permission, the address picker is already **grouped by
feature** (orders, products, customers…). Each group has a ready-made "all" line shaped
like `ANY::<group>/*`. **Picking a group's "all" line = granting full access to that
feature** — the fastest and most correct choice for most needs.

---

## 3. Two special roles (remember these)

GP247 ships two unusually "powerful" roles:

| Role | Meaning |
| --- | --- |
| **Administrator** | **Full access** — enter and do everything, bypassing all checks. For the owner / top admin. |
| **View.all** (view everything) | **Can view every page**, but **cannot change anything** (all add/edit/delete actions are blocked). Great for accountants, auditors, a boss who only monitors. |

> ⚠️ When you assign **Administrator** or **View.all** to a person, the system **overrides**
> and **drops that person's other roles/permissions** — because these two already cover
> everything. The User screen shows this reminder right under the role picker.

---

## 4. How does the system decide where you can go?

Just remember **one unified rule**:

> **Seeing the menu = allowed to view. Acting = depends on the kind (GET/ANY).**

Concretely:
- The **left menu** only shows items the person **can view (GET)**. No permission → the item
  is **hidden**.
- **Entering a page:** needs **view permission (GET or ANY)** for that page's address.
- **Acting (add/edit/delete):** needs **ANY** (or POST) for that page's address.
- **Administrator:** enters & does everything. **View.all:** views everything, cannot change.

In other words: **the menu, entering a screen, and acting all rely on the same list of
addresses (http_uri)** in the person's Permissions. The slug plays no role — it is only a label.

> 💡 So if you pick the "all" line `ANY::<group>/*` for a feature, the user **sees the menu**,
> **can view**, and **can act** on that feature — all in one.

---

## 5. Hands-on: create a Permission

> The admin screens live in the user/permission management area. If you don't see them on
> the menu, type the address directly in your browser (replace `your-domain` with yours):
> `https://your-domain/gp247_admin/permission`.

1. Open the **Permissions** page (`.../gp247_admin/permission`).
2. In the create box, enter a clear **Name**, e.g. `Order management`.
3. Enter a short **Slug**, e.g. `order.full` (lowercase letters, numbers, dot, dash,
   underscore only).
4. In the **address picker**, find the **order** group and **tick the "all" line**
   `ANY::gp247_admin/order/*`. To grant another feature too (e.g. customers), tick that
   group's "all" line as well (`ANY::gp247_admin/customer/*`).
5. Click **Save**. On success, the new Permission appears in the list on the right.

> Want a **view-only** permission for a feature? In step 4, instead of the `ANY::…` line,
> pick the group's **`GET::…`** lines (view only, no editing).

---

## 6. Hands-on: create a Role and assign Permissions

1. Open the **Roles** page (`.../gp247_admin/role`).
2. Enter a **Name**, e.g. `Sales staff`.
3. Enter a **Slug**, e.g. `sales_staff`.
4. In the **Permissions** field, find and select the Permissions to bundle into this role
   (e.g. "Order management", "Customer management").
5. Click **Save**.

> From now on, just assign the `Sales staff` role to a person and they instantly get all
> the permissions inside — no need to pick each permission again.

---

## 7. Hands-on: create a User and grant access

1. Open the **Users** page (`.../gp247_admin/user`).
2. Fill in **Full name**, **Username**, **Email**, **Password**. (You may pick an avatar;
   keep **Active** ticked so the account works.)
3. In the **Roles** field, choose one or more roles you created (e.g. `Sales staff`).
4. (Optional) In the **Permissions** field, grant **extra one-off permissions** to this
   specific person — use this when they need a feature the shared role lacks.
5. Click **Submit / Save**. The new user appears in the list on the right.

> When the new person logs in, they will **only see** the menus matching their permissions.

---

## 8. Strategy recipes (quick picks)

Here are common "recipes" — mix them to fit your needs:

- **Owner / top admin** → assign the **Administrator** role. Done, nothing else to configure.
- **Boss / accountant who only monitors** → assign the **View.all** role. They see everything, change nothing.
- **Order-only clerk** → create a Permission `ANY::gp247_admin/order/*` (and maybe
  `ANY::gp247_admin/customer/*`), bundle into an "Order staff" role, assign it.
- **Content / product staff** → create a Permission with `ANY::gp247_admin/product/*`,
  `ANY::gp247_admin/category/*`, `ANY::gp247_admin/brand/*`; bundle into a "Product editor" role.
- **Report viewer only** → create a Permission with only the `GET::` lines of the `report` group.
- **One person needs one extra feature** → keep the shared role, and on the User screen grant
  an **extra direct permission** to just that person (section 7, step 4).

> Rule of thumb: **prefer shared Roles** for teams; use **direct permissions** only for
> per-person exceptions. This keeps things easy to manage later.

---

## 9. Conditions & Rules (know before you act)

**When assigning roles to a user**
- **Choosing Administrator or View.all removes the person's other roles/permissions** —
  because these two already cover everything, so keeping extras is redundant.
- **View.all can never act** — every add/edit/delete is blocked on the server, even if you
  try to grant them write permissions on top.

**When creating/editing a User**
- **Username:** required, 3–100 characters, **only** letters, numbers, `@`, dot, underscore;
  and **must be unique**.
- **Email:** required, valid email format, **must be unique**.
- **Password:** required when creating; when editing, leaving it blank means **keep the old
  password**. Strength follows the shop's **Password policy** (min/max length, and it may
  require upper/lower case, numbers, special characters).
- **Protected admin accounts cannot be edited/deleted, and you cannot delete yourself** on
  the User screen — to avoid locking yourself out. To change your own info, use the
  **Personal settings** page.

**When creating a Permission**
- **Slug:** required, 3–50 characters, **only** letters, numbers, dot, dash, underscore; and
  **must be unique**.
- **Addresses (http_uri):** prefer picking from the grouped suggestions. An `ANY::…/*` line =
  full access to a group; a `GET::…` line = view only.

**When a user logs in without permissions**
- Someone with **no roles and no permissions** can still log in, but their **menu is nearly
  empty** and opening a feature page shows **"Access denied"** — by design (nothing granted,
  nothing entered). Grant at least one suitable role/permission.

---

## 10. Q&A

**Q1: I granted a permission to staff but clicking still shows "Access denied"?**

→ Check whether that Permission actually has the **right address (http_uri) for the feature**
(e.g. orders must include `ANY::gp247_admin/order/*`). If you only set a name/slug but **picked
no address**, the permission is empty and opens nothing.

**Q2: Staff can view but Save/Delete is blocked?**

→ The permission is **view only (GET)**. To allow acting, change it to the group's **`ANY::…/*`**
line (ANY = view plus add/edit/delete).

**Q3: Does the slug matter? Is a wrong slug a problem?**

→ The slug is **only a label** for your convenience; it **does not decide access**. Access is
decided by the **address list (http_uri)**. Just make the slug easy to understand.

**Q4: What's the difference between "granting via a Role" and "granting a direct permission"?**

→ Same effect. Roles **bundle permissions to reuse across many people**; direct permissions are
for **a single person's exception**. Final access = both added together.

**Q5: How is View.all different from granting GET on every group?**

→ View.all is a shortcut: it **views every page** and **blocks every action** on the server,
without creating GET permissions per group. Faster and safer for a "monitor only" role.

**Q6: I accidentally assigned Administrator to someone — do they now have full access?**

→ Yes. Administrator bypasses all checks. Remove the Administrator role from that person and
assign the proper role/permission instead.

**Q7: Why can't some accounts be edited/deleted on the User screen?**

→ Those are **protected admin accounts** (and the account you are currently logged in as). The
system locks them to prevent accidentally locking yourself out. Change your own info via the
**Personal settings** page.

**Q8: Do plugins I install work with this permission system?**

→ Yes. Plugins declare their permissions using the **same address (http_uri) mechanism**, so you
grant access to a plugin's screens exactly like any other GP247 feature.

**Q9: I want someone to view everything but edit only orders?**

→ Don't use View.all (it blocks all actions). Instead grant **GET** permissions for the groups
they should view, plus one **`ANY::gp247_admin/order/*`** permission for orders only.

**Q10: After changing permissions, must the user log out and back in?**

→ Reloading the page is best; changes usually take effect on the next request. If the menu
hasn't updated, ask them to reload the page (or log in again) to be sure.

---

<sub>📅 **Last updated:** 2026-08-16 · ✍️ **Author:** GP247</sub>
