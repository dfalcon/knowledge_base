# ADR-0007: Two-layer authorization — Spatie roles + KB permissions ACL

Status: Accepted

Two different authorization questions that are easy to conflate:

1. App-level — can this user do the action at all? (approve users, admin panel)
2. Resource-level — can this user access *this* knowledge base?

One tool for both doesn't work. Spatie-only would need a permission row per KB (`read-kb-{uuid}`) — 100 KBs = 200+ rows, and "which KBs can this user read?" becomes string-parsing on permission names. A custom ACL table only would mean checking roles by hand everywhere — no middleware, no `@can`, no policies.

So: use both, split by responsibility.

**Spatie** → app-level roles: `admin` (approve/block users, manage any KB, admin panel), `member` (upload, ask, manage own KBs).

**`knowledge_base_permissions`** → per-resource ACL: `(knowledge_base_id, user_id, can_read, can_write)`, unique on the pair, cascade-delete with the KB. A KB with `is_public = true` is readable by any active user with no row.

Check order, all in `PermissionService` (single entry point, never check ad-hoc in controllers):
```
1. status == active?   no  → deny
2. role == admin?      yes → allow (admins see everything)
3. kb.is_public?       yes → allow read
4. permissions row → can_read / can_write
```
Result cached in Redis 5 min per user, invalidated on permission change.

Gotcha: two systems = two places to look when debugging access. Keep the order above and keep `PermissionService` the only door. If roles grow past admin/member, write down what each can do before adding it.
