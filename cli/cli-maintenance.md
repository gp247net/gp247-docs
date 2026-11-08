> 🌐 **Language:** [🇻🇳 Tiếng Việt](./cli-maintenance_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./cli-maintenance_ja.md)

# Maintenance commands: accounts, encryption key, backups, clean-up

## Introduction
This page gathers the commands used while running a site: reset a lost administrator password, change the encryption key for
secrets, back up and restore, clear expired carts. It is for site owners with server access. Each section says what the
command does, how to type it, and links to the full guide when the job is long. After reading it you can handle the common
maintenance situations without the admin.

## At a glance
| Situation | Command | Safety |
| --- | --- | --- |
| Lost the administrator password, **Forgot password** does not help | `gp247:admin-password` | ⚠️ |
| The encryption key leaked, the key must change | `gp247:encryption-key-rotate` | ⚠️ |
| Back up before a big change | `gp247:backup-run` | ✅ |
| The site is broken, data must come back | `gp247:backup-restore` | 🛑 |
| The cart table keeps growing | `gp247:shop-clear-cart` | ✅ |

- **Commands that need a secret (the backup password) always ask for it**, never as an argument — so it does not end up in
  the command history.
- **Commands that delete or overwrite ask for the backup ID again** before acting.
- **Recurring jobs** (scheduled backups, the mail queue) need just **one** cron line — see [Recurring jobs](#recurring-jobs).

## Reset an administrator password
`gp247:admin-password` sets a new password for an administrator account when the password is lost (for example the one-time
password printed at install was not copied) and **Forgot password** cannot help — the account still has the install email
`admin@gp247.local`, or the site cannot send mail yet. Only someone with server access can run it.

> ℹ️ **Since:** gp247/core 3.11.0

| Argument / option | Value | Meaning |
| --- | --- | --- |
| `username` | required | User name of the account (usually `admin`) |
| `--password` | optional | The new password — must meet the **Administrator password policy** in force. Left out: the command generates a random one |
| `--force` | `0` (default) or `1` | `1`: no confirmation — required in scripts or with `--json` |

1. Type:

   ```bash
   php artisan gp247:admin-password admin
   ```

2. The command asks for confirmation (default **no**); type `yes`.
3. The last block prints the user name and the **new password — shown only once**. Copy it now, sign in, then change the
   account email to a real address so **Forgot password** works next time.

After the reset:
- **Every application using the REST API and every MCP token of the account is revoked**; browsers that "remember me" must
  sign in again. Open sessions (not remembered) end when the session expires.
- **A locked account is not unlocked** — unlock it on the **Users** screen.
- **The site writes one log line** with the account name and no password.

With `--json`, a generated password is only in `data.password`; a password you chose is never printed back. See also
[Accounts & access](../security/security-accounts-and-access.md).

## Change the encryption key for secrets
GP247 encrypts the secrets stored in the configuration (SMTP password, payment gateway keys…) with `GP247_ENCRYPTION_KEY`.
`gp247:encryption-key-rotate` reads each secret with the **old key** and writes it back with the **new key** — the only way to
change the key without losing data.

| Option | Meaning |
| --- | --- |
| `--dry-run` | Only count the rows to convert, write nothing |

Required order (the full 8 steps are in [Data encryption](../system/data-encryption.md#how-to-change-the-encryption-key-safely-no-data-loss)):

1. Back up `.env` and the database.
2. Put the current key in `GP247_ENCRYPTION_PREVIOUS_KEYS`, set the new key in `GP247_ENCRYPTION_KEY`.
3. Clear the config cache: `php artisan config:clear`.
4. Re-encrypt:

   ```bash
   php artisan gp247:encryption-key-rotate
   ```

   If it works, each column prints a line like `admin_config.value: 5 converted, 0 already current, 0 undecryptable`.
5. Check: `php artisan gp247:doctor` — the `secret_decryptable` check must PASS.

Running it again is safe: rows already on the new key are skipped, rows that cannot be decrypted are not overwritten.

## Backup and restore
The `gp247:backup-*` family does everything the **Backup & restore** screen does, and still works when the admin cannot be
reached. Replace `<id>` with the backup ID (see `gp247:backup-list`).

| Command | What it does | Safety |
| --- | --- | --- |
| `gp247:backup-run` | Create an encrypted backup and verify it. `--kind=snapshot\|selective\|full` (default `full`), `--group=`, `--files=`, `--exclude-logs` | ✅ |
| `gp247:backup-run --scheduled` | The scheduled backup, content taken from **Backup settings**; does nothing while the schedule is off | ✅ |
| `gp247:backup-list` | List backups (including those found in the backup folder) | ✅ |
| `gp247:backup-verify <id>` | Re-check the integrity of a backup | ✅ |
| `gp247:backup-pin <id>` | Pin so it is not cleaned up automatically; `--unpin` to unpin | ✅ |
| `gp247:backup-import <file>` | Check a `.gp247bak` file and add it to the list | ✅ |
| `gp247:backup-offsite test\|list\|push\|fetch` | External destination: test the connection, list, copy a backup out, fetch one back | ✅ |
| `gp247:backup-export-sql <id>` | Export the data as an **unencrypted** `.sql` file (`--output=`) | ⚠️ |
| `gp247:backup-restore <id>` | Restore data and / or files: `--level=`, `--files=`, `--preview`, `--undo`, `--post-incident` | 🛑 |
| `gp247:backup-delete <id>` | Delete a backup | 🛑 |

Example: create a full backup and list backups:

```bash
php artisan gp247:backup-run
php artisan gp247:backup-list
```

Preview a restore before doing it for real:

```bash
php artisan gp247:backup-restore <id> --level=full --preview
```

- **The backup password is asked for when the command runs**; in a script pass it on one line of standard input with
  `--secret-stdin` (`--recovery-key` if what you pass is the recovery key).
- **Delete / restore / `.sql` export ask for the backup ID again**; when not interactive, pass `--confirm=<id>`.
- **The exported `.sql` file is not encrypted** — guard it like the database itself and delete it when done.

Full guides: [Backup & restore](../backup/backup-and-restore.md#command-line-for-people-with-cli-access) ·
[Restore guide](../backup/restore-guide.md#restore-from-the-command-line) · [Offsite destination](../backup/backup-offsite.md).

## Clear expired carts
`gp247:shop-clear-cart` deletes carts, wishlists and comparison lists older than their keep time, so the cart table does not
keep growing. The number of days is set in the `gp247-config.shop.cart_expire` configuration (`cart`, `wishlist`,
`compare`). The command exists when the site has `gp247/shop`.

```bash
php artisan gp247:shop-clear-cart
```

Run it daily from cron, for example at 2 a.m.:

```bash
0 2 * * * cd /path-to-site && php artisan gp247:shop-clear-cart >> /dev/null 2>&1
```

## Recurring jobs
GP247 registers its own recurring jobs (processing the mail queue, scheduled backups in **At a fixed hour** mode) in Laravel's
scheduler. On the server you need just **one** cron line calling the scheduler every minute:

```bash
* * * * * cd /path-to-site && php artisan schedule:run >> /dev/null 2>&1
```

Hosting without cron, VPS, Docker: see [Schedule and queue](../system/schedule-and-queue.md).

## Conditions & Rules (know before you act)

**Resetting a password**
- **A password you choose must meet the Administrator password policy** — otherwise the command reports `invalid_password`
  (the message does not repeat the password) and changes nothing.
- **An unknown user name** → `user_not_found`.
- **Scripts / `--json` need `--force=1`** → otherwise `confirmation_required`, nothing changes.

**Changing the encryption key**
- **The old key must stay in `GP247_ENCRYPTION_PREVIOUS_KEYS` while the command runs** — without it, secrets cannot be read to
  be re-encrypted (reported as "undecryptable").
- **Run `config:clear` first** — a cached configuration still holds the old key.

**Backup & restore**
- **No backup password set up yet** → the command reports `not_set_up` — set it up the first time on the **Backup & restore** screen.
- **Wrong backup password** → `wrong_secret`; the backup is not touched.
- **A backup whose origin cannot be verified** (made on another site) can only be restored when you add `--accept-unverified`.

## Q&A
**Q1: Hosting without a command line, lost administrator password — what now?**

→ Use **Forgot password** on the sign-in page — the account email must be a real address and the site must be able to send
mail. That is why you should change the administrator email right after installing.

**Q2: After the password reset, my API application stopped working?**

→ As designed: the account's API and MCP tokens are revoked so whoever had the old password can no longer use them. Create new
tokens in the admin.

**Q3: I changed `GP247_ENCRYPTION_KEY` but forgot to run the rotate command?**

→ Put the old key in `GP247_ENCRYPTION_PREVIOUS_KEYS`, run `php artisan config:clear`, then `gp247:encryption-key-rotate`.
The `secret_decryptable` check of `gp247:doctor` tells you whether it is fixed.

**Q4: How is a command-line backup different from the button on the screen?**

→ Same engine, same file format. The command line is not limited by web request timeouts, so it is safer for large sites.

**Q5: Running `backup-restore` in a script reports `confirmation_required`?**

→ Add `--confirm=<id>` (the exact backup ID again), and pass the backup password through `--secret-stdin`.

**Q6: Does `shop-clear-cart` delete the cart of a customer who is shopping?**

→ No. It only deletes carts older than the days set in `cart_expire`.

**Q7: Do I need a separate cron line for `backup-run --scheduled`?**

→ No. The shared `schedule:run` cron line is enough; GP247 registers the backup time from **Backup settings** itself.

---

<sub>📅 **Last updated:** 2026-11-08 · ✍️ **Author:** GP247</sub>
