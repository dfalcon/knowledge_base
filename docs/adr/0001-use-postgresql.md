# ADR-0001: PostgreSQL as the primary DB

Status: Accepted

Need one relational DB for users, documents, conversations, permissions. Stages 1–4 have no vector search, so the DB itself carries full-text search — that's what decided it.

- MySQL 8 — weak JSONB (no GIN), FTS not good enough early on.
- MongoDB — nice for semi-structured metadata, but ACID isn't its priority and joins mean aggregation pipelines. Too many trade-offs.
- **PostgreSQL 17** — `tsvector`/GIN FTS, JSONB for metadata, partial indexes, `gen_random_uuid()` built in. FTS carries stages 1–4 without Elasticsearch.

Picked Postgres.

Gotchas: queries use PG-specific syntax (won't port to MySQL); JSONB is for metadata, not an excuse to skip a schema. If FTS load ever outgrows it → Elasticsearch, but not before real numbers say so.
