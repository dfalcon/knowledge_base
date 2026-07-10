# ADR-0004: Modular monolith, not DDD or microservices

Status: Accepted

Domain is small and clear (Users, Documents, KnowledgeBases, Chat), solo project, goal is a working product. Full DDD generates piles of boilerplate for 4 entities and buys nothing at this size. Microservices from day one = distributed-systems pain for no benefit yet.

Decision: modular monolith, one process, code split by business module.

```
app/Modules/
├── Users/          {Controllers, Actions, Models, DTO, Events, Jobs}
├── Documents/      {Controllers, Actions, Models, DTO, Events, Jobs}
├── KnowledgeBases/ {Controllers, Actions, Models, DTO}
└── Chat/           {Controllers, Actions, Models, DTO}
```

One rule, and it constrains behaviour, not data: modules reference each other's Eloquent models freely (the foreign keys `Document` → `KnowledgeBase` → `User` make that unavoidable in a monolith). Cross-module *logic* lives in a Service owned by the module that owns the data (`KnowledgeBases\Services\PermissionService`), and asynchronous side effects go through Jobs/Events, never a direct call into another module's Actions. When a module needs its own scaling, the async seam is where you cut it out to a service — which is exactly how the Python AI worker will detach.

The rule is discipline-only for now. If a second developer shows up, add Deptrac to enforce it. (Action pattern → ADR-0010.)
