# ADR-0006: Laravel Sanctum for API auth + Socialite for Google OAuth

## Status
Accepted

## Context

The app has two types of clients: the internal SPA (React/Inertia.js) and potentially the AI Service making internal HTTP calls. We need token-based auth for both. On top of that, users should be able to sign in with Google instead of creating a password.

Options considered for API auth:

- **Laravel Passport** — full OAuth2 server: authorization codes, client credentials, refresh tokens. That's the right tool if we're building a public API or issuing tokens to third parties. We're not — we have one first-party SPA. Passport's setup cost isn't justified
- **JWT (tymon/jwt-auth)** — stateless tokens, works fine. But token revocation requires a blacklist (extra Redis key per token), and the package is historically slow to update for new Laravel versions
- **Laravel Sanctum** — SPA-first, ships with Laravel, tokens stored in the DB (revocable instantly), works for both cookie-based SPA sessions and Bearer token API calls. No configuration ceremony

For Google OAuth:

- **Custom OAuth2 flow** — doable, but we'd be reimplementing what Socialite already does: state parameter, token exchange, user info fetch, error handling. No reason to do this by hand
- **Laravel Socialite** — first-party Laravel package, handles the full OAuth2 dance, returns a normalized user object. Adding another provider later (GitHub, Microsoft) is a one-liner config change

## Decision

**Laravel Sanctum** for API token auth.  
**Laravel Socialite** for Google OAuth.

Auth flows:

```
Email/Password:
POST /api/auth/register  → RegisterUserAction → User (status=pending) → email to admin
POST /api/auth/login     → validate credentials → issue Sanctum token → return token

Google OAuth:
GET  /api/auth/google           → Socialite::redirect()
GET  /api/auth/google/callback  → Socialite::user() → HandleGoogleCallbackAction
                                    → find by google_id OR find by email OR create new user
                                    → new user: status=pending, password=null
                                    → issue Sanctum token → return token
```

`HandleGoogleCallbackAction` lookup order:
1. Find existing user by `google_id` → login
2. Find existing user by `email` (registered via password before) → attach `google_id` → login
3. Create new user with `status=pending`, `password=null` → pending approval

Schema change — two new columns on `users`:
```sql
password  VARCHAR(255)        -- NULL for OAuth-only users
google_id VARCHAR(255) UNIQUE -- NULL for email/password users
```

## Consequences

What we get:
- Sanctum is zero-config for a first-party SPA — tokens in DB, revocable, no extra packages
- `GET /api/auth/me` gives the current user without decoding a JWT
- Socialite handles state, CSRF, token exchange, and user fetch — we only write the business logic
- Google and email/password users go through the same approval flow — no special cases
- Adding a second provider later (e.g. Microsoft Entra) is a Socialite config + one new callback route

Where to be careful:
- `password` is nullable — login validation must check `password IS NOT NULL` before attempting `Hash::check()`, otherwise it'll throw on OAuth-only accounts
- If a Google account email matches an existing password account, we silently attach `google_id` to it. This is a convenience UX choice — if stricter account linking is needed later, add a confirmation step
- Sanctum tokens don't expire by default — set `sanctum.expiration` in config or use `pruneExpired()` in a scheduled job to clean up stale tokens
