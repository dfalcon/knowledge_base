# ADR-0005: MinIO for file storage

Status: Accepted

Users upload PDF/DOCX/TXT; files must live somewhere for processing and later downloads. Need on-prem object storage.

- AWS S3 / GCS / Azure Blob — zero ops, but data leaves our infra. Non-starter for a corporate KB.
- Local filesystem — doesn't scale across instances, manual backups, no real API.
- **MinIO** — self-hosted, S3-compatible. Laravel's `s3` driver and Python `boto3` work unchanged — just swap the endpoint.

Decision: MinIO, separate service, both Laravel and the AI service talk to it over the S3 API.

```
intellibase-documents/{knowledge_base_id}/{document_id}/original.{ext}
```

Laravel uploads and stores the path; the AI service reads by path; downloads go through presigned URLs (not through the app server).

Gotchas: it's stateful — files aren't recoverable from the DB, so back it up. Single-node MinIO has no HA (fine for early stages; distributed mode or real S3 later if needed). Presigned URLs expire — account for that when generating links.
