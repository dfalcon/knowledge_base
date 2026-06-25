# ADR-0003: Qdrant as the vector database

## Status
Accepted

## Context

The RAG pipeline stores document chunk embeddings and runs semantic search filtered by `knowledge_base_id`. That filter is a security boundary — users must not see results from a knowledge base they don't have access to.

The critical point: `knowledge_base_id` filtering must happen _inside_ the vector search, not as a post-filter in application code. A post-filter applied after retrieval is a risk — if there's a bug in the application layer, chunks from the wrong knowledge base can leak through.

Candidates:

- **pgvector** — simplest option, nothing new to add. But performance degrades past ~1M vectors, there's no native Hybrid Search, and payload filtering is limited. Fine if we stay under 500K and latency isn't critical — but we're planning to grow
- **Pinecone** — managed, simple API. But vendor lock-in, it's paid, and company data leaves our infrastructure. Not acceptable for an internal knowledge base
- **Weaviate** — open source, feature-rich. But heavier ops (JVM-based, more involved in Kubernetes), and the advantage over Qdrant in our use case isn't clear enough to justify it
- **Qdrant** — open source (Rust), self-hosted, payload filtering inside the index, native Hybrid Search

## Decision

Qdrant. Introduced at **stage 5** — stages 1–4 use only PostgreSQL FTS for search.

## Consequences

What we get:
- Self-hosted — data never leaves our infrastructure
- Payload filtering on `knowledge_base_id` executes inside the vector index, not after
- Rust runtime: stable at millions of vectors, predictable latency
- gRPC + HTTP API, `qdrant-client` for Python — straightforward integration
- Native Hybrid Search: dense (semantic) + sparse (BM25) in a single request
- Free and open source

Where it might hurt:
- Another stateful service in Kubernetes (StatefulSet + PVC ~100Gi + backup strategy)
- If we get to stage 5 and vector count is under 500K with no latency pressure — worth reconsidering pgvector. Better to decide then with real numbers

Hybrid Search strategy:
```
Qdrant  (semantic)   →  top-5 by cosine similarity  ┐
PostgreSQL FTS        →  top-5 by tsvector rank      ┤ → merge & dedupe → LLM context
                                                      ┘
```
Semantic catches synonyms and paraphrases ("sick leave" matches "больничный");  
FTS catches exact matches — codes, names, abbreviations.
