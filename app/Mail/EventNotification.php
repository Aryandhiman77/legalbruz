<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $title,
        public string $notificationMessage,
        public array $mailAttachments = [],
        public ?string $actionUrl = null,
        public string $actionText = 'Open Application Status',
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-notification',
            with: [
                'notificationMessage' => $this->notificationMessage,
                'actionUrl' => $this->actionUrl,
                'actionText' => $this->actionText,
            ],
        );
    }

    public function attachments(): array
    {
        return collect($this->mailAttachments)
            ->map(function ($attachment) {
                if (is_string($attachment)) {
                    return Attachment::fromPath($attachment);
                }

                if (array_key_exists('data', $attachment)) {
                    $data = $attachment['data'];
                    $mailAttachment = Attachment::fromData(
                        fn () => is_callable($data) ? $data() : (string) $data,
                        $attachment['name'] ?? 'attachment'
                    );

                    if (!empty($attachment['mime'])) {
                        $mailAttachment = $mailAttachment->withMime($attachment['mime']);
                    }

                    return $mailAttachment;
                }

                $path = $attachment['path'] ?? null;

                if (!is_string($path) || $path === '') {
                    return null;
                }

                $mailAttachment = Attachment::fromPath($path);

                if (!empty($attachment['name'])) {
                    $mailAttachment = $mailAttachment->as($attachment['name']);
                }

                if (!empty($attachment['mime'])) {
                    $mailAttachment = $mailAttachment->withMime($attachment['mime']);
                }

                return $mailAttachment;
            })
            ->filter()
            ->values()
            ->all();
    }
}
