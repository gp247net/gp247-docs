> 🌐 **Language:** [🇻🇳 Tiếng Việt](./release-notes-3.0_vi.md) · 🇬🇧 English (current)

# GP247 v3.0 release notes — order money

## Introduction
Version 3.0 reworks how GP247 records **the money a customer has paid on an order**.
This document is for **shop owners and site operators** running v2.1 or v2.2: it tells you what to do
to upgrade and — more importantly — **which numbers on your screens will change afterwards**,
so you do not mistake the new figures for a miscalculation. A separate section at the end is
for plugin developers.

## How to upgrade

1. **Back up your database first.** This release converts data, and although it has been
   tested carefully, a backup is the only way back if something goes wrong. This is the most
   important step.

2. Open a **Terminal** ("Command Prompt" on Windows), go to your website's root folder and type:

   ```
   composer update
   ```

   This only downloads the new source code; it does **not** touch your data yet.

3. Then type the following and press Enter:

   ```
   php artisan gp247:update
   ```

   This applies the change to your site: converts data, refreshes seed data, clears caches.
   If it succeeds, the screen prints the steps it ran with no error lines.

4. Done. There is no other manual step — you do **not** need to run `php artisan migrate`
   yourself.

> Running step 3 more than once is harmless: every conversion is written so that a second run
> changes nothing further.

---

## Change 1 — An order's money is now a ledger, not a single number

**Before**, each order had one "Received" box. That box could not say **when** the money
arrived, **how** it arrived, and could not record a **partial refund** at all.

**From v3.0**, every payment received and every refund is **its own line** in the order's
payment ledger, with a date, an amount, a method and a transaction id. On the order detail
screen you will see a **payment history** under the totals, listing each movement.

Three things you will notice right away:

- **Orders paid through a gateway (PayPal) no longer show the full amount as owed.** Previously
  the gateway only changed the *status label* without recording money, so an order the customer
  had genuinely paid stayed in the receivables list and **was not counted as revenue**. Now the
  money is recorded the moment the gateway reports back.
- **Partial refunds are recorded for their real amount.** Previously refunding 10% flipped the
  whole order to "refunded", and the refunded amount was stored nowhere.
- **The "Received" box works differently.** Instead of editing a number, you **record a receipt**
  (amount + date + method). To refund, you record a refund.

The upgrade **creates the ledger for existing orders**: every order that already holds money gets
a matching receipt line, so your "Received" figures are **unchanged** after upgrading.

---

## Change 2 — Cash-flow reporting now uses the DATE MONEY WAS RECEIVED (figures will change)

> ⚠️ **This is the change most likely to be misread. Read this section carefully.**

Applies only if you use the **InOut** plugin (cash flow & receivables).

**Before**, sales revenue was counted by the **order date**. So an order placed in July but paid
in August was counted in **July** — meaning the cash-flow report was using the order date in
place of the date the money actually arrived.

**From v3.0**, revenue is counted by the **date the money was received**. In the example above,
that amount now sits in **August**.

What to prepare for:

- **Period revenue will differ from your old figures.** The new number is **more correct**, not a
  bug.
- **Data from before the upgrade keeps the old behaviour.** The system never stored the real
  payment date for old orders (there was nowhere to store it), so at upgrade time their money is
  recorded against the **order creation date** — exactly the old way of counting. The dividing
  line is therefore **the day you upgrade**: before it, the figures behave as before; after it,
  they follow the real receipt date.
- **Close your books for the current period before upgrading**, or export/print your latest period
  report so you can compare.

### Manual receipts can now be attached to an order

On the Cash flow screen, the receipt/payment form has a new **"Belongs to order"** field. If the
money belongs to an order, pick that order — it will go into the **order's ledger** instead of
becoming a loose voucher.

Why: the cash book's total income adds up both sales revenue and manual receipts. If the same money
sits on an order *and* on a manual receipt, it is **counted twice**. Previously accountants were all
but forced to write manual receipts (because the gateway recorded no money), so this was very easy
to hit.

If the money does **not** belong to any order (a deposit taken before an order exists, collecting an
old debt…), simply leave the field empty — the system only reminds you when that customer has an
order still owing, and still lets you save.

---

## Change 3 — Payment status is no longer off by one

**Before**, a bug made the payment status display **one step lower than reality**: a **fully paid**
order showed "Partial payment", an order **needing a refund** showed "Paid", and an unpaid order
sometimes printed a bare **`0`** instead of a word. The receivables list in InOut was therefore
filtering the wrong orders too.

**From v3.0** the display is correct, and the upgrade **fixes old data automatically**. One thing to
know:

> The system only fixes orders where **the money says what the status is**. For an order with **no
> money recorded** that an admin **marked by hand** as "Paid", the system **deliberately leaves it
> alone** — that was a human decision, and re-deriving it would erase it.

If you are in the habit of marking orders "Paid" without entering an amount, those orders keep their
label but their "Received" will be 0. For complete figures, record a receipt for them.

---

## Change 4 — The discount now comes off BEFORE tax

> ⚠️ **The two kinds of coupon are affected differently. Read the one you use — a single statement would be wrong for half of you.**

**Before**, tax was charged on the full price and the discount deducted afterwards. The customer was therefore
**taxed on the part they had been forgiven**, and the tax figure stored on the order described a price nobody paid.

**From v3.0**, the discount comes off first and tax is charged on what remains — standard VAT/GST practice.

### PERCENTAGE coupons → the total does **not** change

Only the **tax figure** is corrected. An order of 100 with 10% tax and a 10% coupon:

| | Tax | Discount | Customer pays |
|---|---|---|---|
| Before | 10 | 11 | **99** |
| From v3.0 | **9** | 10 | **99** |

The customer pays exactly the same. What changes is that you were **declaring one unit of tax too much** — that
is the part being fixed.

### FIXED-AMOUNT coupons → the customer pays **less**

An order of 100 with 10% tax and a coupon of 20:

| | Tax | Discount | Customer pays |
|---|---|---|---|
| Before | 10 | 20 | **90** |
| From v3.0 | **8** | 20 | **88** |

Because tax now falls only on the remaining 80. **This is the change customers can see** — if you use
fixed-amount coupons, each such order will bring in slightly less than before.

### Percentage coupons are no longer rounded against the customer

A rounding bug made percentage coupons **give back less than advertised**: 10% off 236 returned 23 instead of
23.60. The error always favoured the shop, and it happened on every order a percentage coupon touched. Fixed.

### Partial returns finally have a correct basis

The discount is now **split across the order lines** in proportion. When a customer returns one item from an
order that used a coupon, the system knows exactly how much of the reduction belonged to that item — previously
it only knew "the order got 60 off", not how much of it applied to the item coming back, which made
**over-refunding** very easy.

The upgrade **splits the discount on existing orders too**, but **changes none of their amounts**. So an old
order will show per-line discounts while its tax remains the tax of the pre-discount price. That is **not broken
data** — it is the figure the document was issued with, and rewriting it would invalidate every reconciliation
already done against that order.

---

## Conditions & Rules (know before you act)

**When recording a receipt or a refund**
- **The amount must be greater than 0** — a zero amount is not money changing hands. To record the
  opposite direction, use the refund action rather than entering a negative number.
- **A closed accounting period cannot be posted into** on a date inside it (only if you use InOut) —
  so figures you have already closed do not change behind your back.

**When a gateway reports a payment**
- **The system records the exact amount the gateway returns**, even when it **differs** from the order
  total. A mismatch leaves the order partially paid and is logged for you to reconcile — the system
  does **not** round it to fit, because that would hide a real discrepancy.
- **The same transaction reported many times is recorded once.** Gateways may resend a notification;
  the money is not added twice.

**When applying a discount**
- **A discount may not exceed the goods (before tax)** — discounting more than the items are worth gives the
  order a negative total, which nothing downstream is built to handle. Anything larger is capped at the value of
  the goods.

**When writing a manual receipt in InOut**
- **Picking an order puts the money in that order's ledger**, with no extra voucher — so one amount
  never lives in two places.

---

## For plugin developers

Two changes may affect custom plugins:

- **`ShopOrderTotal::PAID` changes value from `2` to `3`** (the three sibling constants shift
  accordingly). They were **wrong** — off by one against the `shop_payment_status` table — so leaving
  them as they were is the more dangerous option. If your plugin compares `payment_status`, use
  `ShopPaymentStatus::{UNPAID, PARTIAL, PAID, REFUND}`.
- **Payment plugins should move to `ShopOrder::recordPayment()` / `recordRefund()`.**
  `processPaymentPaid()` still works (marked `@deprecated`) but it can only express "paid in full": no
  deposits, no partial payments, and no transaction id, so calling it again **cannot be
  de-duplicated**. The two new methods take an amount, a method, a transaction id and a payment date.

- **`shop_order_detail` gains two columns: `discount` and `tax_rate`.** The second is not cosmetic: a line used
  to store a tax AMOUNT while the rate lived on the product, so (a) tax could not be recomputed once a discount
  moved the base, and (b) an old order could **change its tax** simply because someone edited the product's tax.
  The rate is now frozen on the document.
- **The per-line tax field in admin now stores a RATE**, derived from the amount you type. You still type an
  amount, but from now on that figure **moves with its taxable base**, the way a tax does. The dividing line:
  **a charge that must NOT change when a discount is applied is an `other_fee`, not a `tax`.**
- If your code needs to split a discount, use `gp247_allocate_discount()` — do not re-divide it yourself; the
  rounding remainder has to land in exactly one place so that `Σ line discounts` always equals the order discount.

Status labels should come from the table (`ShopPaymentStatus::getIdAll()`), **not** be hardcoded per
id — admins are allowed to rename statuses.

---

## Q&A

**Q1: What should I do before upgrading?**

→ Back up your database. If you use InOut, export or print your latest cash-flow period report so you
can compare afterwards.

**Q2: After upgrading, last month's revenue differs from the old figure. Is the system wrong?**

→ No. Revenue is now counted by the **date money was received** rather than the order date — see
Change 2. The new number is more correct.

**Q3: Will the "Received" amounts on my existing orders be lost?**

→ No. The upgrade creates a matching receipt line for every order that already holds money, so the
figures stay the same.

**Q4: I use percentage coupons — will customers pay a different amount?**

→ No. The total is unchanged; only the tax figure is corrected (you were declaring too much). With
**fixed-amount** coupons the customer pays **less** — see Change 4.

**Q5: Will orders I marked "Paid" by hand be changed?**

→ No. The system deliberately leaves those alone, because changing them automatically would erase your
decision. Only their "Received" stays 0 until you record a receipt.

**Q6: Why can't I edit the "Received" box directly any more?**

→ Because a single number cannot say when the money arrived or how. You now record individual receipts,
each with its own date — which is what makes correct period reporting possible.

**Q7: I don't use the InOut plugin. Does any of this affect me?**

→ Change 2 does not affect you. Changes 1 and 3 still apply.

**Q8: An old order shows per-line discounts but its tax does not seem to match — is that broken?**

→ No. The upgrade deliberately **changes no amount** on an existing order, so its tax is still the tax of the
pre-discount price — the figure the document was issued with. The per-line split exists so partial returns can
be calculated correctly.

**Q9: I'm on a version older than v2.1. What then?**

→ The supported upgrade path starts at **v2.1**. A site on **v2.1 or v2.2** upgrades straight to v3.0.
If your site is older than v2.1, move it to v2.1 first, then to v3.0.

**Q10: After upgrading, how do I check the money was recorded correctly?**

→ Open a paid order and look at the **payment history** under the totals: it must show a receipt line
with the right amount and date, and the "Balance" must be 0.

---

<sub>📅 **Last updated:** 2026-08-30 · ✍️ **Author:** GP247</sub>
