> 🌐 **Language:** [🇻🇳 Tiếng Việt](./data-encryption_vi.md) · 🇬🇧 English (current)

# Sensitive data encryption in GP247

> ℹ️ **Version requirement:** At-rest data encryption (the dedicated `GP247_ENCRYPTION_KEY`, the `gp247:encryption-key-rotate` command, the `Secret` cast) is available **from `gp247/core` 3.0.3 onwards**. Older versions store these secrets in plain text. Check your version with `php artisan gp247:info`.

## Introduction
This document explains **how GP247 protects sensitive information** (SMTP password, Google Captcha secret, OAuth client secrets, plugin licenses…) and — most importantly — **how to change the encryption key safely without losing data**, using a built-in command. It is for store owners and deployers; the last section is for developers who want to encrypt their own data. After reading you will know which data is encrypted, where the key lives, and how to rotate it correctly.

## What "at rest" means (in brief)
"At rest" means **the data is encrypted while it sits in the database**. Instead of storing the SMTP password as plain text, GP247 stores a scrambled string starting with `enc:v2:…`. Only a system holding the correct **encryption key** can read it back.

**What it protects:** anyone who obtains a database backup or reads the tables **cannot read** the secrets without the key.

**What it does not:** anyone with **both** the database **and** the key (full server access) can still read them. This is the standard level for secrets stored in a database, not an absolute vault.

## Where the key lives: `GP247_ENCRYPTION_KEY`
GP247 uses a **dedicated key** to encrypt secrets: the `GP247_ENCRYPTION_KEY` variable in your `.env`. It is deliberately **separate** from `APP_KEY` (Laravel's general key): so even if `APP_KEY` is changed or regenerated, your encrypted secrets are **not affected**.

If you have **not set** `GP247_ENCRYPTION_KEY`, GP247 temporarily uses `APP_KEY` so nothing breaks — but `gp247:doctor` will **remind** you to set the dedicated key. Set it from the start (below).

### Set the dedicated key (do this right after install)
1. Open a **Terminal** in the project folder and generate a new key:

   ```
   php artisan key:generate --show
   ```

   This only **prints** a key like `base64:…=`; it changes nothing. Copy that value.

2. Open `.env`, find the line `GP247_ENCRYPTION_KEY=` and paste the key:

   ```
   GP247_ENCRYPTION_KEY=base64:YourNewKey...=
   ```

3. Clear the config cache so the new key is picked up:

   ```
   php artisan config:clear
   ```

4. **Back up `.env`** (which holds the key) to a safe place off the server. Losing the key means losing the ability to read encrypted secrets.

> If the site already has secrets encrypted with `APP_KEY` (from before you set the dedicated key), run **`php artisan gp247:encryption-key-rotate`** afterwards to re-encrypt everything with the dedicated key (same procedure as the key-change section below).

## Which data is encrypted
Fields marked "secret" are encrypted automatically on save:

| Data | Screen |
| --- | --- |
| SMTP password (`smtp_password`) | Configuration → Email/SMTP |
| Google Captcha secret key | GoogleCaptcha plugin |
| OAuth client secret (Facebook/Google/GitHub…) | Social login plugin (LoginSocial) |
| Paid plugin license | Extension/License manager |

Admin account passwords are **not** here — they are one-way hashed (bcrypt), not reversibly encrypted.

## How to CHANGE the encryption key safely (no data loss)
This is the most important part. Only change the key when you **suspect it is exposed** (e.g. `.env` leaked to Git) or **security policy requires it**. GP247 ships a command that does the heavy lifting: **`gp247:encryption-key-rotate`** — it reads every secret with the **old key** and re-encrypts it with the **new key**.

> ⚠️ Do **NOT** just edit `GP247_ENCRYPTION_KEY` to a new value and stop. That makes all old secrets **unreadable**. Follow the 8 steps below.

1. **Back up first.** Copy `.env` to a safe place (e.g. `.env.backup`) and back up the database. This is your safety net.

2. **Record the current key.** Open `.env` and copy the **exact** value of `GP247_ENCRYPTION_KEY` (`base64:…=`) somewhere. This is the **old key**.

3. **Generate a new key (not applied yet).** In the Terminal:

   ```
   php artisan key:generate --show
   ```

   Copy the printed `base64:…=` value — this is the **new key**.

4. **Edit `.env`** — do **two** things:
   - Put the **old key** (Step 2) into `GP247_ENCRYPTION_PREVIOUS_KEYS`.
   - Change `GP247_ENCRYPTION_KEY` to the **new key** (Step 3).

   The result looks like this (use your real values):

   ```
   GP247_ENCRYPTION_KEY=base64:NewKey...=
   GP247_ENCRYPTION_PREVIOUS_KEYS=base64:OldKey...=
   ```

   > `GP247_ENCRYPTION_PREVIOUS_KEYS` can hold several old keys, comma-separated.

5. **Clear the config cache:**

   ```
   php artisan config:clear
   ```

6. **Run the key-rotation command** to re-encrypt every secret with the new key:

   ```
   php artisan gp247:encryption-key-rotate
   ```

   On success it lists each table with counts, e.g.
   `admin_config.value: 5 converted, 0 already current, 0 undecryptable`.
   If any row is **"undecryptable"** → the old key in Step 4 is wrong; check `GP247_ENCRYPTION_PREVIOUS_KEYS` and run again. The command does **not** overwrite failing rows, so data is never damaged.

   > To preview without writing, run `php artisan gp247:encryption-key-rotate --dry-run`.

7. **Verify with the diagnostic command:**

   ```
   php artisan gp247:doctor
   ```

   Find the `secret_decryptable` line — **PASS** with "N secrets OK" means every secret reads under the new key.

8. **Clean up.** Once Step 6 reports all rows converted (0 "undecryptable") and `doctor` PASSes, you may **remove** `GP247_ENCRYPTION_PREVIOUS_KEYS` from `.env` and run `php artisan config:clear` again. If unsure, leave it — keeping old keys does no harm.

## If you changed the key wrong / lost it
- **You still have the old key** (in `.env.backup` or copied out): put it into `GP247_ENCRYPTION_PREVIOUS_KEYS`, run `php artisan config:clear`, then redo from Step 6. Data becomes readable again.
- **The old key is gone everywhere:** old secrets **cannot be recovered** — you must **re-enter** them by hand on each config screen (SMTP password, secrets, licenses…). The site does not crash; features using secrets (mail, payment…) simply pause until re-entered. This is why **Step 1 (backup) is mandatory**.

## For developers: encrypt your own column (outside the config table)
If your plugin/feature has a column holding a secret (e.g. a third-party token) **not** in the config table, encrypt it with GP247's **shared cast** — one line, no core changes:

```php
// In the model:
protected $casts = [
    'api_token' => \GP247\Core\Casts\Secret::class,
];
```

From then on, reading/writing `$model->api_token` is plaintext in PHP, while the database always holds `enc:v2:…`.

To make `gp247:doctor` and `gp247:encryption-key-rotate` **also cover** that column (check and rotate it), register it in the plugin's `Provider.php` (inside the active-plugin block):

```php
config(['gp247-config.security.encrypted_columns.my_plugin_tokens' => ['api_token']]);
```

Three notes: (1) the column must be **TEXT** (ciphertext is long); (2) you **cannot search/filter** an encrypted column (add a separate "blind index" column if you need lookup); (3) the table must have an `id` primary key so the rotation command can update rows.

## Conditions & Rules (know before you act)
- **Back up the encryption key (`GP247_ENCRYPTION_KEY`) and `.env`** — losing the key means losing the ability to read every encrypted secret; there is no other recovery.
- **Before changing the key, you must put the old key into `GP247_ENCRYPTION_PREVIOUS_KEYS`** — otherwise the rotation command cannot read the old secrets. This is the most commonly missed step.
- **Do not hand-edit `GP247_ENCRYPTION_KEY` to "change the key"** — always rotate via `gp247:encryption-key-rotate`, or old data becomes unreadable.
- **A decryption failure never crashes the site** — a wrong key makes the secret read as empty and the related feature pauses, instead of a blank error page (by design, to keep the site running on limited hosting).
- **You cannot search/filter encrypted data** — the stored value is scrambled, so `WHERE = real value` is meaningless.
- **After any `.env` change, run `php artisan config:clear`** — otherwise the system may keep using the old cached configuration.

## Q&A
**Q1: Do I have to set `GP247_ENCRYPTION_KEY`?**

→ Strongly recommended, right after install. Until then GP247 uses `APP_KEY` and `gp247:doctor` reminds you. A dedicated key means changing `APP_KEY` won't affect secrets.

**Q2: Which command changes the key?**

→ `php artisan gp247:encryption-key-rotate` — it re-encrypts every secret with the new key. But do the full "How to CHANGE the key" steps first (put the old key into `GP247_ENCRYPTION_PREVIOUS_KEYS`).

**Q3: `gp247:doctor` says "secrets undecryptable" — what does it mean?**

→ Some secret cannot be decrypted, usually because the key changed without declaring the old one. Put the old key into `GP247_ENCRYPTION_PREVIOUS_KEYS`, run `php artisan config:clear`, then run `gp247:encryption-key-rotate` again. If the old key is gone, re-enter the secrets.

**Q4: Is the rotation command safe to run multiple times?**

→ Yes. It skips rows already on the new key, so re-running is harmless. Add `--dry-run` to preview without writing.

**Q5: Does changing `APP_KEY` (not the encryption key) lose secrets?**

→ No, if you have set a dedicated `GP247_ENCRYPTION_KEY`. Secrets are tied to the dedicated key, independent of `APP_KEY`.

**Q6: Is the SMTP password exposed on the admin screen?**

→ It is not shown as plain text — masked with ●●●, and stored encrypted (`enc:v2:…`) in the database.

**Q7: My plugin has its own secret column — how do I encrypt it?**

→ Use the `\GP247\Core\Casts\Secret::class` cast in the model's `$casts`, and register the column in `gp247-config.security.encrypted_columns` so doctor/rotation cover it. See "For developers".

**Q8: Can someone with server access still read the secrets?**

→ Yes. Anyone with both the database and the key can decrypt. At-rest encryption protects against leaked backups / DB reads, not against someone who already has full server access.

---

<sub>📅 **Last updated:** 2026-09-04 · ✍️ **Author:** GP247</sub>
