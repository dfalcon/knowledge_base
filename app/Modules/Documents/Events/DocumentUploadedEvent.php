<?php

namespace App\Modules\Documents\Events;

use App\Modules\Documents\Models\Document;
use DateTimeImmutable;
use JsonException;

final readonly class DocumentUploadedEvent
{
    private const EVENT = 'document.uploaded';

    public function __construct(
        public string $documentId,
        public string $knowledgeBaseId,
        public string $filePath,
        public string $mimeType,
        public DateTimeImmutable $timestamp,
        public string $version = '1.0',
    ) {}

    public static function fromDocument(Document $document): self
    {
        return new self(
            documentId: $document->id,
            knowledgeBaseId: $document->knowledge_base_id,
            filePath: $document->file_path,
            mimeType: $document->mime_type,
            timestamp: new DateTimeImmutable(),
        );
    }

    public function toArray(): array
    {
        return [
            'version'           => $this->version,
            'event'             => self::EVENT,
            'document_id'       => $this->documentId,
            'knowledge_base_id' => $this->knowledgeBaseId,
            'file_path'         => $this->filePath,
            'mime_type'         => $this->mimeType,
            'timestamp'         => $this->timestamp->format(DATE_ATOM),
        ];
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
