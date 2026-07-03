# ADR-0002: RabbitMQ for inter-service events

Status: Accepted

The Python AI service processes documents async (parse → chunk → embed → Qdrant). Laravel must hand it `document.uploaded` reliably — if the consumer is down, the message can't just vanish.

- Redis Pub/Sub — already here for Jobs, but no persistence: consumer down when published = message gone. No good for a processing queue.
- Kafka — right if we needed log retention / partitioning, but KRaft + topic + retention config is overhead we don't have a reason for yet.
- **RabbitMQ** — ack, retry, DLQ, Management UI. Fits.

Decision: RabbitMQ for domain events between services. Redis stays for Laravel Jobs — no overlap.

```
Exchange: intellibase.events (topic)
document.uploaded → ai.document-processing → Python AI service
document.indexed  → laravel.notifications  → Job (status + email)
document.failed   → laravel.alerts         → Job (alert admin)
```

Cost: one more stateful service to run and back up. If volume ever hits millions/day, revisit Kafka.
