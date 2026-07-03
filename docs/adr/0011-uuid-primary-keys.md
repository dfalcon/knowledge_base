# ADR-0011: UUID primary keys, not auto-increment

Status: Accepted

Every table uses a UUID PK (`HasUuids`), decided at the first migration. Why, since it's not the Laravel default:

- Multiple writers coming — the Python AI service (month 4) and Qdrant (`document_chunks.qdrant_id`). One auto-increment sequence becomes a coordination point; independent UUIDs don't.
- IDs show up in URLs (`/knowledge-bases/{kb}/documents`). Sequential ints leak counts and let anyone enumerate `/1`, `/2`, `/3`. UUIDs don't.
- One id for the same document across Postgres, MinIO, and Qdrant.

`HasUuids` in Laravel 11 mints **UUIDv7** — time-prefixed, so inserts hit the right edge of the B-tree like a sequence instead of scattering. The old "UUIDs are slow to insert" objection is about random v4, not this. (Verified: two rows created in order sort in order.)

Columns are also `DEFAULT gen_random_uuid()` as a safety net for raw inserts. The two sources don't conflict — they split by path:

- Eloquent (`::create`, `save`, `updateOrCreate`, factories) sets the id in PHP first → **v7**. The DB default is never reached.
- Raw SQL / `DB::table()->insert()` with no id → falls to `gen_random_uuid()` → **v4**.

So all app code is ordered v7; the v4 fallback only touches the rare raw-insert path where locality doesn't matter.

Gotchas:
- `DB::table()->insert()` is a raw path — `HasUuids` doesn't fire there, you get v4. Use the model to get v7.
- Never set a v4 (`Str::uuid()`) as a model PK — it reintroduces the write pathology v7 avoids.
- 2× the storage of bigint per key; fine at this scale, note it past hundreds of millions of rows. UUIDs are unfriendly in logs — that's what the `slug` columns are for.
