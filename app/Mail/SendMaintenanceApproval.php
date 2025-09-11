<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMaintenanceApproval extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $name,
        private string $link
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Maintenance Work Order Approval',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.workorder.send-maintenance-approval',
            with: [
                'name' => $this->name,
                'link' => $this->link,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
