# ADR-0002: RabbitMQ for inter-service events

## Status
Accepted

## Context

The Python AI Service processes documents asynchronously (parse → chunk → embed → Qdrant). Laravel needs to deliver `document.uploaded` to it — and that delivery has to be reliable. If the consumer is down, the message cannot just disappear.

Options considered:

- **Redis Pub/Sub** — already in use for Laravel Jobs, so adding it here wouldn't introduce a new dependency. But Pub/Sub has no persistence: if the consumer is down when the message is published, it's gone permanently. Not acceptable for a processing queue
- **Apache Kafka** — good choice if you need log retention and partitioning by `knowledge_base_id`. But KRaft, topic management, retention configuration — that's operational overhead we don't need at our scale. That conversation can happen later, if it ever needs to
- **RabbitMQ** — classic AMQP broker: acknowledgment, retry, Dead Letter Queue, Management UI. Exactly what's needed here

The responsibility split stays clean:
- Redis → internal async Jobs within Laravel
- RabbitMQ → domain events between services

## Decision

RabbitMQ 3.13 for inter-service events. Redis stays for Laravel Jobs — the two don't overlap.

Topology:
```
Exchange: intellibase.events (topic)

document.uploaded → Queue: ai.document-processing   → Python AI Service
document.indexed  → Queue: laravel.notifications    → Laravel Job (status update + email)
document.failed   → Queue: laravel.alerts           → Laravel Job (alert admin)
```

## Consequences

What we get:
- Guaranteed delivery: acknowledgment + automatic retry when a consumer fails
- Dead Letter Queue — failed messages are visible, not silently dropped
- Topic Exchange — new consumers subscribe without touching the publisher
- Management UI — queues, consumer lag, throughput visible at a glance
- `aio-pika` provides a native async consumer for the Python AI Service

Where it might hurt:
- Another stateful service in the infrastructure (StatefulSet, persistent volume, backup)
- If message volume reaches millions per day — revisit Kafka with log retention and partitioning by `knowledge_base_id`. Not a concern now
