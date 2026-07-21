<?php

namespace App\Modules\Documents\Events;

use DateTimeImmutable;
use JsonException;

final readonly class DocumentIndexedEvent
{
    public function __construct(
        public string $documentId,
        public DateTimeImmutable $timestamp,
        public string $messageId,
        public string $version = '1.0',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            documentId: $data['document_id'],
            timestamp: new DateTimeImmutable($data['timestamp'] ?? 'now'),
            messageId: $data['message_id'],
            version: $data['version'] ?? '1.0',
        );
    }

    /**
     * @throws JsonException
     */
    public static function fromJson(string $json): self
    {
        return self::fromArray(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
    }
}
