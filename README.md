# IntelliBase

Internal corporate knowledge base with AI-powered search. Employees upload documents — the system answers questions based on their content.

**"What DB accesses do we have on prod?"** → finds the document, quotes the answer.  
**"How many paid sick days?"** → pulls the right paragraph from the HR doc.

---

## What it does

- Upload documents (PDF, DOCX, regulations)
- Semantic search + RAG Q&A over the content
- Admin approves new users and manages access to knowledge bases
- Chat interface for asking questions

## Modules

| Module | Responsibility |
|--------|---------------|
| `KnowledgeBases` | Manage knowledge bases and document collections |
| `Documents` | Upload, index, and store documents |
| `Chat` | Q&A interface, conversation history |
| `Users` | Auth, roles, admin approval flow |

## Docs

Architecture decisions, system design, and infrastructure details live in [`/docs`](./docs).

## Quick start

```bash
cp .env.example .env
docker compose up -d
composer install
php artisan migrate
php artisan key:generate
```

App runs at `http://localhost:5001`.
