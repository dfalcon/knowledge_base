# ADR-0008: API documentation — l5-swagger for Laravel, built-in OpenAPI for FastAPI

## Status
Accepted

## Context

Both Laravel and the Python AI Service expose pure REST APIs. Frontend and mobile developers need interactive API docs. The docs must be accurate — stale docs are worse than no docs.

**FastAPI** ships with OpenAPI out of the box. Pydantic models and type hints are enough — `/docs` (Swagger UI) and `/redoc` are available with zero configuration.

**Laravel** options considered:

- **dedoc/scramble** — auto-generates OpenAPI from routes, FormRequest rules, and API Resources without annotations. Docs stay in sync automatically. Downside: relies on inference, less explicit control over the output
- **darkaonline/l5-swagger** — generates spec from `@OA\` PHPDoc annotations written manually on controllers. Explicit and predictable. The route count is small enough that the annotation overhead is manageable

## Decision

**l5-swagger** (`darkaonline/l5-swagger`) for Laravel.  
**FastAPI built-in** for the AI Service.

The route count is small and many endpoints share similar structure, so manual annotations are not a significant burden. The explicitness of `@OA\` gives full control over the generated spec.

URLs in development:

| Service | URL | Notes |
|---------|-----|-------|
| Laravel API | `http://localhost:5001/api/documentation` | l5-swagger UI |
| AI Service | `http://localhost:8000/docs` | FastAPI Swagger UI |
| AI Service | `http://localhost:8000/redoc` | FastAPI ReDoc |

## Consequences

What we get:
- Full control over request/response schema descriptions and examples
- FastAPI docs are free — Pydantic models generate the schema automatically
- Standard tooling that most PHP teams have encountered

Where to be careful:
- Annotations go stale: when a request field or response shape changes, the `@OA\` annotation must be updated manually. If it isn't, the docs silently lie. Treat annotation updates as part of the same commit as the code change
- l5-swagger `/api/documentation` should be disabled or protected in production
- FastAPI `/docs` and `/redoc` should be disabled in production via `docs_url=None` in the app constructor
