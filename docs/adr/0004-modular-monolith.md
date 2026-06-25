# ADR-0004: Modular monolith over DDD or microservices

## Status
Accepted

## Context

We need to pick an architectural style for the Laravel backend. The domain is well-understood: Users, Documents, KnowledgeBases, Chat. The team is small (< 5 engineers). Priority is a working product, not an architecture showcase.

Options:

- **DDD** — powerful for complex domains. But with 4 entities, DDD generates 50+ boilerplate files per CRUD feature and delivers no real return at our scale. Time spent, nothing gained
- **Microservices from day one** — each module becomes a separate service. Distributed systems overhead (inter-service auth, network failures, local dev complexity) with zero real benefit at this point. Premature optimization
- **Modular Monolith (Vertical Slice)** — code split by business module, each module self-contained, everything runs in one process

## Decision

Modular Monolith with the **Action** pattern.

Module structure:
```
app/Modules/
├── Users/          {Controllers, Actions, Models, DTO, Events, Jobs}
├── Documents/      {Controllers, Actions, Models, DTO, Events, Jobs}
├── KnowledgeBases/ {Controllers, Actions, Models, DTO}
└── Chat/           {Controllers, Actions, Models, DTO}
```

Cross-module communication rule:
```
# Allowed: events and explicit shared interfaces
Documents → Chat: via Laravel Events (DocumentIndexed → Chat listens)
Documents → Chat: via explicit interface in shared/

# Not allowed: direct class imports across module boundaries
Documents → Chat: cannot import a class from another module directly
```

## Consequences

What we get:
- Fast to start — no boilerplate, no ceremony
- Vertical Slice — all code for a feature lives in one place, easy to navigate
- Action = one class, one responsibility, easy to unit test
- Clear migration path: when a module needs independent scaling, extract it to a microservice
- Standard Laravel structure — easier to onboard developers without a long ramp-up

Where to be careful:
- Requires discipline: don't reach across module boundaries directly — only through Events or interfaces. Once the team grows, add architecture tests (Deptrac or similar) to enforce this automatically
- At 5+ engineers working in parallel, revisit whether high-churn modules should become separate services
