# ADR-0012: search_vector maintained by a Postgres trigger, not PHP

Status: Accepted

`documents.search_vector` is a `tsvector` derived from title (A), content (B), and `metadata->>'department'` (C), GIN-indexed. It has to be recomputed on every write and must never drift from the source fields. Question is where that recompute lives.

It's a data invariant, not business logic: "if the row exists, its vector matches its content" must hold for *every* writer — seeders, the future Python service, a manual `UPDATE` — not just Eloquent. Do it in a PHP observer and any write that skips the observer silently leaves `NULL` → the document quietly drops out of search. So it belongs in the DB, same as a foreign key.

Decision: a `BEFORE INSERT OR UPDATE` trigger (created via `DB::statement()` in the migration) resolves the text-search config from `language` (fallback `simple`) and builds the weighted vector. PHP treats the column as read-only: `Document` is marked `#[Hidden(['search_vector'])]`, never written, only read via FTS.

Trade-offs:
- Invisible from the model — a dev reading only the PHP won't know the column is auto-maintained. This ADR + the `#[Hidden]` attribute are the signposts.
- Changing weights or language mapping means a new migration (`CREATE OR REPLACE FUNCTION`), not a code edit.
- Binds this to Postgres — already committed by ADR-0001.
- Verify with a DB/feature test: insert a document, assert it's found via FTS.
