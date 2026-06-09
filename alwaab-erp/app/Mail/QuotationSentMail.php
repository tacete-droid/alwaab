<?php

namespace App\Mail;

use App\Domain\Quotations\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quotation $quotation,
        private string $pdfContent,
        private string $pdfFilename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name'),
            ),
            subject: 'Quotation '.$this->quotation->number.' — Al Waab Building Materials',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.quotation-sent',
            with: [
                'quotation' => $this->quotation,
                'contactName' => $this->quotation->contact?->name_en ?? 'Customer',
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
