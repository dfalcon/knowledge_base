# ADR-0005: MinIO for file storage

## Status
Accepted

## Context

Users upload documents (PDF, DOCX, TXT, and others). Those files need to live somewhere before processing and after — for downloads and re-processing. We need blob/object storage with a decent API that stays on-premise.

Options:

- **AWS S3 / Google Cloud Storage / Azure Blob** — managed, reliable, zero ops. But data leaves our infrastructure, which is a non-starter for a corporate knowledge base. Plus vendor lock-in and egress costs
- **Local filesystem** — simplest option, but doesn't scale across multiple instances, no proper API, backups are manual
- **MinIO** — self-hosted, S3-compatible API, written in Go, runs fine in Docker/Kubernetes. Laravel's `s3` driver and Python's `boto3` work without changes — just swap the endpoint

## Decision

MinIO. Deployed as a separate service; Laravel and the Python AI Service talk to it via the standard S3 API.

Bucket structure:
```
intellibase-documents/
├── {knowledge_base_id}/
│   └── {document_id}/
│       └── original.{ext}     ← original file after upload
```

Access pattern:
- Laravel → uploads the file, stores path or presigned URL in PostgreSQL
- Python AI Service → reads the file by path for processing
- Presigned URLs for downloads — files aren't served through the application server

## Consequences

What we get:
- Data stays in our infrastructure
- S3-compatible API — if we ever move to real AWS S3, we change the endpoint and credentials, nothing else
- Laravel `s3` driver + Python `boto3` — nothing new to learn
- MinIO Console — browse buckets and files from a browser, handy for debugging
- Versioning and lifecycle policies available if needed

Where it might hurt:
- Another stateful service (persistent volume, backup strategy — important, files can't be recovered from the database)
- MinIO in single-node mode has no HA — if fault tolerance becomes a requirement, we'd need distributed mode or a managed S3. Single-node is fine for early stages
- Presigned URLs have an expiry — needs to be accounted for when generating download links
