# ADR-0008: API docs — l5-swagger for Laravel, built-in OpenAPI for FastAPI

Status: Accepted

Both services are REST; frontend devs need interactive docs, and stale docs are worse than none.

FastAPI gives OpenAPI for free from Pydantic + type hints — `/docs` and `/redoc`, zero config.

For Laravel: scramble auto-generates from routes/FormRequests (stays in sync, but inference means less control) vs **l5-swagger**, which generates from manual `@OA\` annotations (explicit, predictable). Route count is small, so annotating by hand isn't a real burden and I get full control.

Decision: l5-swagger for Laravel, FastAPI built-in for the AI service.

| Service | URL |
|---|---|
| Laravel | `/api/documentation` |
| AI service | `/docs`, `/redoc` |

Gotchas:
- `@OA\` annotations go stale silently — update them in the same commit as the code change.
- Disable/protect all three doc endpoints in production (`docs_url=None` for FastAPI).
