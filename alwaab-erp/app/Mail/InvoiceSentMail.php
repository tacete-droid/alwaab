<?php

namespace App\Mail;

use App\Domain\Invoices\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
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
            subject: $this->invoice->type->labelEn().' '.$this->invoice->number.' — Al Waab Building Materials',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice-sent',
            with: [
                'invoice' => $this->invoice,
                'clientName' => $this->invoice->client_name
                    ?? $this->invoice->contact?->name_en
                    ?? 'Customer',
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
