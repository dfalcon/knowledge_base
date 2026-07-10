# ADR-0010: Single-purpose Action classes, not multi-method Services

Status: Accepted

ADR-0004 named the Action pattern in passing; this records why logic lives in Actions rather than the usual Laravel `Service`.

A controller needs logic — where does it go? Not inline (untestable, not reusable from jobs). A `DocumentService` with `upload()/list()/delete()/reindex()` grows unbounded and drags every dependency into one constructor whether a given method needs it or not. An **Action** is one class, one `execute()`, one use case (`UploadDocumentAction`, `GrantPermissionAction`, `AttachTagsAction`).

Decision: business logic goes in `VerbNounAction` classes with a single `execute()`. The controller is glue only:

```php
public function store(UploadDocumentRequest $r, KnowledgeBase $kb, UploadDocumentAction $a): JsonResponse
{
    return response()->json($a->execute($kb, $r->file('file')), 201);
}
```

Validation → FormRequest. An Action's constructor injects only what that one use case needs, so reading it tells you the whole blast radius. Editing upload can't break listing — different files. Genuinely shared logic (e.g. permission checks) goes in a Service owned by the module that holds the data — today that is `KnowledgeBases\Services\PermissionService`, called directly by the Documents module. There is no `shared/` directory; one cross-module Service does not earn one. Service is the exception, not the default home.

The trade is more files. Worth it — many small honest classes beat a few fat ones. Don't add a second public method to an Action because it feels related; a second use case is a second Action.
