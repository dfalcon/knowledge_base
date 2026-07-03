# ADR-0009: One centralized routes/api.php, not per-module route files

Status: Accepted

ADR-0004 splits code into modules, so the obvious question: should each module own its routes too? No — two reasons.

Auth layering is cross-cutting: the API has three concentric layers (public / `auth:sanctum` / `+ role:admin`) that cut across every module. One file expresses them once as nested groups; per-module files would each redeclare the middleware stack and eventually drift (one module forgets `role:admin`).

Nested resources cross module boundaries: `POST /knowledge-bases/{kb}/documents` is a KnowledgeBases-shaped URL served by `DocumentController`. Split by module, it has no obvious home.

Decision: all routes in `routes/api.php`, organized middleware-layer first, prefix second.

```php
Route::prefix('auth')->group(...)                 // public
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('conversations')->group(...)
    Route::prefix('knowledge-bases')->group(...)  // + nested documents, tags, permissions
    Route::middleware('role:admin')->prefix('admin')->group(...)
});
```

Module boundaries stay at the class level (ADR-0004), not the routing level.

~21 routes in ~55 lines — readable at a glance. This doesn't scale forever: past ~50–80 routes, or once modules get extracted to services, split into per-module files via a RouteServiceProvider. Trigger is file length and merge conflicts, not a fixed number. Until then, a new endpoint goes into the right auth group, not appended at the bottom.
