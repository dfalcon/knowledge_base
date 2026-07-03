# ADR-0009: Single centralized `routes/api.php` instead of per-module route files

## Status
Accepted

## Context

ADR-0004 splits the backend into self-contained modules (`Users`, `Documents`, `KnowledgeBases`, `Chat`), each owning its Controllers, Actions, Models. A natural follow-up question: should routing also be modular — each module registering its own `routes.php` (e.g. `RouteServiceProvider` loading `app/Modules/*/routes.php`)?

Two real forces push against per-module route files at this scale:

- **Auth layering is cross-cutting, not per-module.** The API has three concentric access layers — public, `auth:sanctum`, and `auth:sanctum + role:admin`. These layers cut across every module. A single file expresses them once as nested `->group()` blocks; per-module files would each re-declare the same middleware stack, inviting drift (one module forgetting `role:admin`).
- **Nested resources cross module boundaries.** `POST /knowledge-bases/{kb}/documents` is a `KnowledgeBases`-shaped URL served by `DocumentController`; `/knowledge-bases/{kb}/tags` is served by `TagController`. If routes were owned by modules, these endpoints would have no single obvious home and the URL hierarchy would be scattered across files.

The route count is small (~21 endpoints, one file of ~55 lines). The whole API surface is readable at a glance.

## Decision

All API routes live in a single `routes/api.php`, organized by **middleware layer first, URL prefix second**:

```php
Route::prefix('auth')->group(...)                    // public: register, login, google
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(...)                // logout, me
    Route::prefix('conversations')->group(...)
    Route::prefix('knowledge-bases')->group(...)     // + nested documents, tags, permissions
    Route::middleware('role:admin')->prefix('admin')->group(...)
});
```

Module boundaries are enforced at the **class level** (controllers/actions live in `app/Modules/*`, no cross-module imports — ADR-0004), *not* at the routing level. Routing stays flat and centralized.

## Consequences

What we get:
- The entire access-control model is visible in one place — public vs authenticated vs admin is structurally obvious from indentation
- Nested resources have an unambiguous home regardless of which module's controller handles them
- No duplicated middleware declarations, so no risk of an admin route silently missing `role:admin`
- Standard Laravel layout — nothing to explain to a new developer

Where to be careful:
- This does not scale forever. Past ~50–80 routes, or once modules are extracted toward services, split into per-module route files loaded by a `RouteServiceProvider`. The trigger is file length and merge-conflict frequency on `api.php`, not a fixed number
- The convention "middleware layer first, prefix second" must be kept — a new endpoint goes into the correct auth group, not appended at the bottom
