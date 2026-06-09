<?php

namespace App\Services;

use App\Domain\Invoices\Models\Invoice;
use App\Domain\Quotations\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    private function logoBase64(): ?string
    {
        foreach (['alwaab-logo-excel.jpg', 'alwaab-logo.png', 'alwaab-logo.jpg'] as $file) {
            $path = public_path("images/{$file}");
            if (! file_exists($path)) {
                continue;
            }

            $mime = mime_content_type($path) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
        }

        return null;
    }

    private function pdfOptions(): array
    {
        return [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
        ];
    }

    public function quotationPdf(Quotation $quotation)
    {
        $quotation->load([
            'contact',
            'project',
            'items.product',
            'creator:id,name_ar,name_en',
        ]);

        $settings = app(SettingsService::class)->companySettings();

        return Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'settings' => $settings,
            'logoBase64' => $this->logoBase64(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions(array_merge($this->pdfOptions(), [
                'margin_top' => '20mm',
                'margin_right' => '18mm',
                'margin_bottom' => '22mm',
                'margin_left' => '18mm',
            ]));
    }

    public function invoicePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load([
            'contact',
            'project',
            'items.product',
            'creator:id,name_ar,name_en',
        ]);

        $settings = app(SettingsService::class)->companySettings();

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'settings' => $settings,
            'logoBase64' => $this->logoBase64(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions(array_merge($this->pdfOptions(), [
                'margin_top' => '20mm',
                'margin_right' => '18mm',
                'margin_bottom' => '22mm',
                'margin_left' => '18mm',
            ]));
    }
}
