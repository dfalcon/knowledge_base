<?php

namespace App\Modules\Documents\Mail;

use App\Modules\Documents\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentIndexed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Document $document)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Document indexed: {$this->document->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.documents.indexed',
        );
    }
}
