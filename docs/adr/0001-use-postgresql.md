# ADR-0001: PostgreSQL as the primary database

## Status
Accepted

## Context

We need a relational database for users, documents, conversations, and permissions. Stages 1–4 have no vector search yet, so the database itself has to carry the full-text search load — that immediately ruled out some candidates.

Looked at three options:

- **MySQL 8** — familiar, but JSONB support is weak (no GIN indexes), and full-text search falls short enough to become a real problem early on
- **MongoDB** — considered it because semi-structured document metadata maps naturally to a document store. But ACID isn't MongoDB's priority, joins require aggregation pipelines, and Atlas Search is a separate paid product. Too many trade-offs stacking up
- **PostgreSQL 17** — ACID, `tsvector`/`tsquery` with GIN out of the box, JSONB for metadata, partial indexes. FTS is strong enough to carry stages 1–4 without Elasticsearch

## Decision

PostgreSQL 17. One database, one zone of responsibility.

## Consequences

What we get:
- FTS without extra services — `tsvector` + GIN + `tsquery` is sufficient for stages 1–4
- JSONB with GIN for document metadata (`{"department": "hr", "tags": [...]}`) — filtering works fine
- Partial indexes speed up queries like `WHERE status = 'indexed'`
- `gen_random_uuid()` built-in, no extensions needed

Where it might hurt:
- Queries don't port to MySQL — the team needs to know PostgreSQL-specific syntax
- JSONB is easy to abuse — it's for semi-structured metadata, not a replacement for a proper schema
- If FTS load grows to the point PostgreSQL can't keep up — that's when we look at Elasticsearch, not now
