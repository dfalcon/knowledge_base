# ADR-0003: Qdrant as the vector DB

Status: Accepted

RAG stores chunk embeddings and does semantic search filtered by `knowledge_base_id`. That filter is a security boundary — it has to run *inside* the vector search, not as an app-side post-filter. A post-filter that a bug can skip leaks chunks from KBs the user can't see.

- pgvector — nothing new to add, but degrades past ~1M vectors, no native hybrid search, limited payload filtering. Fine under 500K; we plan to grow.
- Pinecone — managed and simple, but paid + vendor lock-in + company data leaves our infra. No for an internal KB.
- Weaviate — capable, but JVM ops are heavier and the edge over Qdrant here isn't clear.
- **Qdrant** — Rust, self-hosted, payload filtering inside the index, native hybrid search.

Decision: Qdrant, introduced at **stage 5**. Stages 1–4 search on Postgres FTS only.

Hybrid search once it's in:
```
Qdrant (semantic)  → top-5 by cosine   ┐
Postgres FTS       → top-5 by ts rank  ┤ → merge & dedupe → LLM context
                                        ┘
```
Semantic catches paraphrase ("sick leave" ≈ "больничный"), FTS catches exact codes/names.

Cost: another StatefulSet + PVC + backups. If at stage 5 we're under 500K vectors with no latency pressure, reconsider pgvector with real numbers.
