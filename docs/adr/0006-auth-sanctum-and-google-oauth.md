# ADR-0006: Sanctum for API auth + Socialite for Google OAuth

Status: Accepted

One first-party SPA client (plus internal service calls later). Need token auth, and users should be able to sign in with Google instead of a password.

For API auth: Passport is a full OAuth2 server — overkill for one first-party client. JWT works but revocation needs a blacklist and the package lags Laravel releases. **Sanctum** ships with Laravel, tokens live in the DB (instantly revocable), handles both SPA cookies and Bearer tokens, zero config.

For Google: no reason to hand-roll the OAuth2 dance — **Socialite** does state, token exchange, user fetch, and adding another provider later is a config line.

Decision: Sanctum + Socialite.

Google callback lookup order (`HandleGoogleCallbackAction`):
```
1. user by google_id           → login
2. user by email (had password)→ attach google_id → login
3. none                         → create user, status=pending, password=null
```

Two nullable columns on `users`: `password` (null for OAuth-only), `google_id UNIQUE` (null for password users).

Gotchas:
- `password` is nullable → login must check it's not null before `Hash::check()`, or OAuth-only accounts throw.
- Step 2 silently links google_id to an existing email account. Convenience choice; add a confirmation step if strict linking is ever needed.
- Sanctum tokens don't expire by default — set `sanctum.expiration` or prune on a schedule.
