<?php

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Jobs\NotifyDocumentIndexedJob;
use App\Modules\Documents\Mail\DocumentIndexed;
use App\Modules\Documents\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpAmqpLib\Message\AMQPMessage;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

beforeEach(function () {
    seedRoles();
    Mail::fake();
});

function fireDocumentIndexed(string $payload): void
{
    $rabbitmq = Mockery::mock(RabbitMQQueue::class);
    $rabbitmq->shouldReceive('ack')->once();

    (new NotifyDocumentIndexedJob(
        app(),
        $rabbitmq,
        new AMQPMessage($payload),
        'rabbitmq',
        'laravel.notifications',
    ))->fire();
}

it('processes a document.indexed event only once when delivered twice', function () {
    $document = Document::factory()->create(['status' => DocumentStatus::Processing]);

    $payload = json_encode([
        'document_id' => $document->id,
        'timestamp'   => now()->toAtomString(),
        'version'     => '1.0',
        'message_id'  => (string) Str::uuid(),
    ]);

    fireDocumentIndexed($payload);
    fireDocumentIndexed($payload);

    Mail::assertSent(DocumentIndexed::class, 1);
    expect(DB::table('processed_message_ids')->count())->toBe(1);
    expect($document->fresh()->status)->toBe(DocumentStatus::Indexed);
});

it('processes two different document.indexed events independently', function () {
    $first = Document::factory()->create(['status' => DocumentStatus::Processing]);
    $second = Document::factory()->create(['status' => DocumentStatus::Processing]);

    foreach ([$first, $second] as $document) {
        fireDocumentIndexed(json_encode([
            'document_id' => $document->id,
            'timestamp'   => now()->toAtomString(),
            'version'     => '1.0',
            'message_id'  => (string) Str::uuid(),
        ]));
    }

    Mail::assertSent(DocumentIndexed::class, 2);
    expect(DB::table('processed_message_ids')->count())->toBe(2);
});
