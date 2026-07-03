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

One rule: modules don't import each other's classes. Cross-module talk goes through Laravel Events or an explicit interface in `shared/` (e.g. Documents → Chat via `DocumentIndexed`). When a module needs its own scaling, this line is where you cut it out to a service.

The rule is discipline-only for now. If a second developer shows up, add Deptrac to enforce it. (Action pattern → ADR-0010.)
