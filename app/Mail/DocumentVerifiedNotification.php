<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DocumentVerifiedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $user, public $document, public $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: '✔️ Document Verified - ' . $this->document->document_type,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-verified',
        );
    }

    public function attachments(): array
    {
        return $this->documentAttachment();
    }

    private function documentAttachment(): array
    {
        if (!$this->document->file_path || !Storage::disk('public')->exists($this->document->file_path)) {
            return [];
        }

        $attachment = Attachment::fromStorageDisk('public', $this->document->file_path)
            ->as($this->document->file_name ?: basename($this->document->file_path));

        if (($this->document->file_type ?? null) === 'html') {
            $attachment = $attachment->withMime('text/html');
        }

        return [$attachment];
    }
}
