# ADR-0007: Two-layer authorization — Spatie roles + knowledge_base_permissions ACL

## Status
Accepted

## Context

The system has two distinct authorization needs that are easy to conflate but solve different problems:

1. **App-level**: can this user perform an action in the system at all? (approve users, upload documents, access the admin panel)
2. **Resource-level**: does this user have access to this specific knowledge base?

The temptation is to handle everything with one tool. It doesn't work cleanly either way:

- **Spatie only** — to model per-KB access you'd create a permission per knowledge base: `read-kb-{uuid}`, `write-kb-{uuid}`. 100 knowledge bases = 200 permission rows just for names, plus a row in `model_has_permissions` per user per KB. Deleting a KB means manually cleaning up Spatie permissions. Querying "which KBs can this user read?" becomes a string-parsing exercise on permission names
- **Custom ACL table only** — role checks (`is this user an admin?`) have to be done manually everywhere, no middleware, no `@can` blade directives, no policy integration

## Decision

Use both layers with a clear separation of responsibility:

**Spatie Laravel Permission** → app-level roles and global permissions

| Role | What it unlocks |
|------|----------------|
| `admin` | Approve/block users, manage any knowledge base, see admin panel |
| `member` | Upload documents, ask questions, manage own knowledge bases |

**`knowledge_base_permissions` table** → per-resource ACL

```sql
CREATE TABLE knowledge_base_permissions (
    id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    knowledge_base_id UUID REFERENCES knowledge_bases(id) ON DELETE CASCADE,
    user_id           UUID REFERENCES users(id) ON DELETE CASCADE,
    can_read          BOOLEAN DEFAULT TRUE,
    can_write         BOOLEAN DEFAULT FALSE,
    UNIQUE (knowledge_base_id, user_id)
);
```

A knowledge base can also be `is_public = true` — in that case all active users can read it without a row in `knowledge_base_permissions`.

Access check order in `PermissionService`:

```
1. user.status == 'active'?          → no  → deny
2. user has role 'admin'?            → yes → allow (admins see everything)
3. kb.is_public == true?             → yes → allow read
4. knowledge_base_permissions row exists with can_read / can_write? → allow / deny
```

Result is cached in Redis for 5 minutes per user and invalidated on permission change.

## Consequences

What we get:
- Each tool does one job: Spatie handles roles, the ACL table handles resource access
- "Which KBs can user X read?" is a simple JOIN, not string-parsing on permission names
- Deleting a KB cascades to `knowledge_base_permissions` automatically — no orphan cleanup
- Admin bypass is in one place (`PermissionService`), not scattered across controllers
- Redis cache keeps per-request permission checks cheap

Where to be careful:
- Two systems means two places to check when debugging an access issue — the order above must be followed consistently, `PermissionService` is the single entry point, never check permissions ad-hoc in controllers
- If Spatie roles grow beyond `admin` / `member` (e.g. `moderator`), document what each role can and cannot do before adding it — role sprawl is hard to untangle later
