> 🌐 **Language:** [🇻🇳 Tiếng Việt](./api-authentication_vi.md) · 🇬🇧 English (current) · [🇯🇵 日本語](./api-authentication_ja.md)

# Sign-in, tokens and customer accounts through the API

## Introduction

This page explains how an app signs in to the GP247 API and keeps the sign-in safe: getting a token, lifetime, login attempt limits,
refresh tokens, managing sessions per device, two-factor authentication and what a customer account can do through the API (register, forgotten password,
profile, address book). It is for developers building mobile apps, customer websites or admin tools. Never called the API before? Start with
[Quick start](./api-quickstart.md).

## Authentication (logging in to the API)

GP247 uses **Laravel Sanctum** to issue a **token** (a long string that acts as an "entry ticket").
The mechanism is the same for both groups:

1. The app sends its credentials to the `login` endpoint: an administrator sends **`username` + `password`** to
   `api/v2/core/login`, a customer sends **`email` + `password`** to `api/v2/front/login`.
2. If correct, the system returns, inside the `data` key, an **`access_token`** (a Bearer token) with its expiry
   (`expires_at`, and `expires_in` in seconds).
3. From then on, every request that requires login attaches this token in the **header**:

   ```
   Authorization: Bearer <access_token>
   ```

4. To sign out, call **`POST logout`** to **sign the current session out** (the old token and its refresh token become
   invalid immediately).

### Scopes (abilities)

Each token is issued with a **scope** that tells whose token it is:

| Scope | Meaning |
| --- | --- |
| `admin` | Administrator token (Core API) |
| `admin-supper` | Token of an administrator with the `administrator` role (Core API) |
| `user` | Member token (Front API) |

> ℹ️ **Available since:** gp247/core 3.2.3 — the scope only tells the **account type**. **Which data
> you can see** is decided by the account's own permissions ([Permissions and data scope of the Core API](./api-concepts.md#permissions-and-data-scope-of-the-core-api)). The `user-guest` scope is no
> longer issued: a customer account that is locked cannot log in.

### Token lifetime

> ℹ️ **Available since:** gp247/core 3.2.3

Every token has an expiry (`expires_at`):

| Token type | Without `remember_me` | With `remember_me = true` |
| --- | --- | --- |
| Administrator | 7 days (`GP247_API_TOKEN_EXPIRE_ADMIN_DEFAULT`) | 30 days (`GP247_API_RECOMMEMBER_ADMIN`) |
| Member | 7 days (`GP247_API_TOKEN_EXPIRE_DEFAULT`) | 30 days (`GP247_API_RECOMMEMBER`) |

Signing in with `with_refresh: true` gives an access token living only **60 minutes**, plus a *refresh token* to renew it
([Refresh tokens — stay signed in with short-lived tokens](#refresh-tokens--stay-signed-in-with-short-lived-tokens)) — the recommended way for mobile apps.

> ℹ️ **Available since:** gp247/core 3.3.0 — the environment variables have correctly spelled names:
> `GP247_API_REMEMBER`, `GP247_API_REMEMBER_ADMIN`, `GP247_API_SCOPE_ADMIN_SUPER`. The old names
> (`GP247_API_RECOMMEMBER`…) still work; if both are set, the new name wins. `php artisan gp247:doctor` reports
> old names that are still in use (check `api_config_legacy_names`). Tokens carrying the `admin-super` scope are
> also accepted alongside `admin-supper`.

An expired token returns `401`; when that happens, call `refresh` (if you signed in with `with_refresh`) or `login` to get a
new token. Tokens that expired long ago (more than 7 days) are cleaned up automatically the next time the same account logs
in — no cron needed.

> ℹ️ **Available since:** gp247/core 3.4.0 — tokens issued **before 3.2.3** (they had no expiry then) get one when you
> run `php artisan gp247:update`: the token's real age plus the "remember" lifetime (admins: `GP247_API_REMEMBER_ADMIN`,
> members: `GP247_API_REMEMBER`), but **never sooner than 7 days** after the upgrade. Apps holding an old token have at
> least 7 days before they must log in again — make sure your app answers a `401` by calling `login`.

> ℹ️ **Available since:** gp247/core 3.6.0, gp247/shop 3.4.0 — changing or resetting a customer's password (on the website or
> through the API) ends **every token** of that customer. The same goes for **administrators**: changing the password on the
> **Users** screen, in **Account settings** or through "forgot password" ends every API token of that admin.

### Login attempt limits

> ℹ️ **Available since:** gp247/core 3.2.3

To block password guessing, `api/v2/core/login`, `api/v2/front/login` and the admin login form limit the number of attempts.
The admin login form and `api/v2/core/login` share one counter: failed attempts on one count for the other.

| Limit | Default | Change with environment variable |
| --- | --- | --- |
| Per account (from the same IP) | 5 attempts / minute | `GP247_LOGIN_THROTTLE` (format `attempts,minutes`) |
| Per IP, across all accounts | 20 attempts / minute | `GP247_LOGIN_IP_THROTTLE` |

> ⚠️ **Renamed in gp247/core 3.5.0:** the old names `GP247_API_LOGIN_THROTTLE` and `GP247_API_LOGIN_IP_THROTTLE`
> are no longer read. If you set them, rename them in your `.env`, otherwise the defaults above apply.

Over the limit → HTTP `429` with a `Retry-After` header (the number of seconds to wait). A successful
login clears the counter for that account. In addition, every API endpoint has a general limit of 1000
requests/minute (`GP247_API_THROTTLE`), counted **per account** once signed in and per IP otherwise.

> ℹ️ **Available since:** gp247/core 3.8.0 — before this release the general limit was counted per IP, so many app users
> behind one network (a carrier or office NAT) shared one counter. If several tools use **one shared account**, they now share
> its counter — give each tool its own account or raise `GP247_API_THROTTLE`.

## Customer accounts & sign-in sessions

> ℹ️ **Available since:** gp247/core 3.6.0, gp247/shop 3.4.0

A shopping app can run the whole customer-account cycle through `api/v2/front`, with **the same rules as the forms on the
website**: required fields, custom fields, password strength and captcha all follow the store's settings. The endpoint list
is in [Front API workflow (Storefront)](./api-storefront.md#front-api-workflow-storefront).

### Register, forgotten password, profile, address book

- **`POST register`** — send the fields of the registration form (`first_name`, `email`, `password`,
  `password_confirmation`, plus the fields the store turned on, such as `address1`, `phone`…). Success → HTTP `201` with
  `customer` and the same tokens as `login`. A customer cannot set their own `status`. **The password is required**: a
  missing or empty `password` → `422` (`errors.password`), and no account is created.
- **`POST password/forgot`** — sends the password-reset e-mail. Always answers `{"data": {"sent": true}}`, whether the
  e-mail has an account or not, so nobody can find out which e-mails are registered.
- **`POST password/reset`** — send `email`, `token` (from the e-mail), `password`, `password_confirmation`. On success
  **every API token** of the customer is revoked; the app calls `login` with the new password.
- **`GET` / `PUT member/profile`** — the profile with `email_verified`, `email_verification_required` and the default
  address. The password cannot be changed here.
- **`PUT member/password`** — send `password_old`, `password`, `password_confirmation`. The calling session stays signed in,
  **every other device** is signed out. A wrong old password counts against the account's login limit. A missing or
  empty new password → `422` (`errors.password`), and the old password stays.

> ℹ️ **Available since:** gp247/shop 3.5.0 — `register` and `member/password` always require a password (before, a body
> without the `password` key got HTTP `500`, and an empty password was accepted). A JSON body missing a required field now
> always gets `422`.
- **`member/address`** — add/edit/delete addresses; send `"default": true` to make one the default. The default address
  cannot be deleted (HTTP `422`) — make another address the default first.
- **`POST member/email/verify`** — sends the verification e-mail again when the store turned on *Need verify email*. The
  customer clicks the link in any browser and is done — **no** website sign-in needed.

`register`, `password/*` and `member/email/verify` have their own limit: **5 requests/minute per IP**
(`GP247_API_ACCOUNT_THROTTLE`, format `attempts,minutes`).

> ⚠️ If the store turned captcha on for the *Register* or *Forgot password* page (**Captcha** tab), the API also requires
> the field of the active captcha plugin — missing/wrong → `422` (`errors.captcha_field`). The app gets the captcha
> answer from the captcha provider's SDK, or the administrator unticks that page.

### Refresh tokens — stay signed in with short-lived tokens

Send `with_refresh: true` (and optionally `device_name`) to `login`, `register` or `login/challenge`:

| | Without `with_refresh` | With `with_refresh` |
| --- | --- | --- |
| Access token | 7 days (30 with `remember_me`) | **60 minutes** (`GP247_API_ACCESS_MINUTES`) |
| Refresh token | — | Customer 30 days (`GP247_API_REFRESH_DAYS`), admin 7 days (`GP247_API_REFRESH_ADMIN_DAYS`), from sign-in |

When the access token expires (`401`), call `POST refresh` with `{"refresh_token": "…"}` → you get a **new pair**. Each
refresh token works **once**:

- Sending a used refresh token again → `401 refresh_token_reused` and **the whole session is signed out** (a sign that the
  token was stolen).
- Exception: sent again within 10 seconds (flaky network, duplicate request) → `409 refresh_in_progress`, the session
  stays. Make the app call `refresh` **one at a time**, not in parallel.
- Refreshing does not extend the session; when it ends, sign in again.

```bash
curl -X POST "https://shop.example.com/api/v2/front/login" -H "Content-Type: application/json" \
  -d '{"email":"customer@example.com","password":"Secret#123","with_refresh":true,"device_name":"Anna iPhone"}'
curl -X POST "https://shop.example.com/api/v2/front/refresh" -H "Content-Type: application/json" \
  -d '{"refresh_token":"<refresh_token you received>"}'
```

### Managing sign-in sessions

Each sign-in is a **session**. `GET member/sessions` (customer) and `GET sessions` (admin) list the device name, IP,
last use and which session is the current one (`current`). `DELETE …/sessions/{id}` signs one device out.
`POST logout` signs the current session out, including its refresh token. Changing a password (customer or administrator,
on the website or through the API) signs **every** API session and token of that account out; the website session in use stays.

### Two-factor authentication (MFA) through a plugin

Core does not send OTP codes itself; a security plugin can add a verification step. Then `login` with the right password
answers:

```json
{ "error": 1, "code": "challenge_required", "msg": "…",
  "challenge": { "ticket": "…", "method": "totp", "meta": {}, "expires_in": 300 } }
```

The app asks the user for the code, then calls `POST login/challenge` with the `ticket` and the fields the plugin needs
(for example `code`). A ticket works once and expires after 5 minutes; wrong codes count against the login limit (API v1
has no such step — see [API v1](./api-v1.md)). GP247's free **MFA**
plugin (from version 2.3.0) uses this mechanism for customers and admins who turned two-factor authentication on (TOTP
or recovery code). Plugin developers:
see [How to create an API — section 7](./create-api.md).

### Admin API access log

Every Core API call (except `login`) is written to the admin **Operation log** — as for actions on the admin
screens: user, path, IP, device; passwords/keys are masked and response bodies are not stored. Calls refused for missing
permission (`403`) are logged too. It is switched off together with the admin log (`GP247_ADMIN_LOG=0`). Sync tools that
call the API constantly make the log grow quickly — clean it regularly on the **Operation log** screen.

## Q&A

**Q1: Where do I get a token and how long does it last?**

→ The token is issued by the `login` endpoint. By default it expires after 7 days; with
`remember_me = true` it lasts 30 days. Change these via the environment variables in [Authentication (logging in to the API)](#authentication-logging-in-to-the-api). A mobile app should sign
in with `with_refresh: true` to use 60-minute tokens renewed by a refresh token ([Refresh tokens — stay signed in with short-lived tokens](#refresh-tokens--stay-signed-in-with-short-lived-tokens)). To log out safely, call
`POST logout` with the current token — that session is signed out and the token can no longer be reused.

**Q2: Should a mobile app use refresh tokens?**

→ Yes. Sign in with `with_refresh: true`: the main token lives only 60 minutes, the app gets a new one with the refresh token so the customer does not have to
sign in again, and a leaked token is only usable for a short time.

**Q3: After a password change, does the token in use still work?**

→ No. Changing or resetting the password (on the website or through the API) ends every token of that account; the app must sign in again.

---

<sub>📅 **Last updated:** 2026-11-03 · ✍️ **Author:** GP247</sub>
