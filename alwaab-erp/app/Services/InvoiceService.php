<?php

namespace App\Services;

use App\Domain\CRM\Models\Contact;
use App\Domain\Inventory\Models\Product;
use App\Domain\Invoices\Models\Invoice;
use App\Domain\Invoices\Models\InvoiceItem;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Mail\InvoiceSentMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    public function generateNumber(InvoiceType $type): string
    {
        $prefix = $type->prefix();
        $period = now()->format('mY');
        $pattern = "{$prefix}/{$period}/%";

        $latest = Invoice::where('number', 'ilike', $pattern)
            ->orderByDesc('number')
            ->value('number');

        $seq = 1;
        if ($latest && preg_match('/\/(\d+)$/', $latest, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('%s/%s/%03d', $prefix, $period, $seq);
    }

    public function previewNumber(InvoiceType $type): string
    {
        return $this->generateNumber($type);
    }

    public function createInvoice(array $data, User $user, InvoiceStatus $status = InvoiceStatus::Saved): Invoice
    {
        return DB::transaction(function () use ($data, $user, $status) {
            $type = InvoiceType::from($data['type']);

            $invoice = Invoice::create([
                'number' => $this->generateNumber($type),
                'type' => $type,
                'status' => $status,
                'source' => $data['source'] ?? 'internal',
                'document_date' => $data['document_date'] ?? now()->toDateString(),
                'contact_id' => $data['contact_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'rfq_id' => $data['rfq_id'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'attention_to' => $data['attention_to'] ?? null,
                'project_name' => $data['project_name'] ?? null,
                'consultant' => $data['consultant'] ?? null,
                'main_contractor' => $data['main_contractor'] ?? null,
                'mep_contractor' => $data['mep_contractor'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'lpo_number' => $data['lpo_number'] ?? null,
                'address' => $data['address'] ?? null,
                'discount_aed' => $data['discount_aed'] ?? 0,
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->syncItems($invoice, $data['items'] ?? []);
            $this->recalculateTotals($invoice);

            return $invoice->fresh(['items.product', 'contact', 'project']);
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'document_date' => $data['document_date'] ?? $invoice->document_date,
                'contact_id' => $data['contact_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'attention_to' => $data['attention_to'] ?? null,
                'project_name' => $data['project_name'] ?? null,
                'consultant' => $data['consultant'] ?? null,
                'main_contractor' => $data['main_contractor'] ?? null,
                'mep_contractor' => $data['mep_contractor'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'lpo_number' => $data['lpo_number'] ?? null,
                'address' => $data['address'] ?? null,
                'discount_aed' => $data['discount_aed'] ?? 0,
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => InvoiceStatus::Saved,
            ]);

            $this->syncItems($invoice, $data['items'] ?? []);
            $this->recalculateTotals($invoice);

            return $invoice->fresh(['items.product', 'contact', 'project']);
        });
    }

    public function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $index => $item) {
            $description = trim($item['description'] ?? '');
            $product = ! empty($item['product_id']) ? Product::find($item['product_id']) : null;

            if ($product && $description === '') {
                $description = $product->name_en;
            }

            if ($description === '') {
                continue;
            }

            $qty = (int) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price_aed'] ?? ($product?->price_aed ?? 0));
            $unit = $item['unit'] ?? ($product?->unit ?? 'pcs');

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product?->id,
                'description' => $description,
                'quantity' => $qty,
                'unit' => $unit,
                'unit_price_aed' => $unitPrice,
                'total_aed' => round($qty * $unitPrice, 2),
                'sort_order' => $index + 1,
            ]);
        }
    }

    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $invoice->load('items');
        $subtotal = $invoice->items->sum('total_aed');
        $discount = (float) $invoice->discount_aed;
        $afterDiscount = max(0, $subtotal - $discount);
        $vat = round($afterDiscount * 0.05, 2);
        $total = round($afterDiscount + $vat, 2);

        $invoice->update([
            'subtotal_aed' => $subtotal,
            'vat_aed' => $vat,
            'total_aed' => $total,
        ]);

        return $invoice;
    }

    public function createFromWebsiteRequest(array $data, Contact $contact, string $rfqId, User $user): Invoice
    {
        return $this->createInvoice([
            'type' => InvoiceType::Quotation->value,
            'document_date' => now()->toDateString(),
            'contact_id' => $contact->id,
            'client_name' => $data['name'],
            'attention_to' => $data['name'],
            'project_name' => $data['project'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'valid_until' => now()->addDays(30)->toDateString(),
            'notes' => $this->websiteNotes($data),
            'items' => $this->websiteItems($data),
            'source' => 'website',
            'rfq_id' => $rfqId,
        ], $user, InvoiceStatus::Saved);
    }

    public function sendToClient(Invoice $invoice): Invoice
    {
        $invoice->load(['contact', 'items.product']);

        $email = $invoice->email ?: $invoice->contact?->email;
        abort_if(! $email, 422, __('invoices.no_client_email'));

        $pdf = app(PdfService::class)->invoicePdf($invoice);
        $filename = str_replace(['/', '\\'], '-', $invoice->number).'.pdf';

        Mail::to($email)->send(new InvoiceSentMail(
            $invoice,
            $pdf->output(),
            $filename,
        ));

        $invoice->update(['status' => InvoiceStatus::Issued]);

        if ($invoice->creator) {
            app(AppNotificationService::class)->notifyUser($invoice->creator, [
                'category' => 'system',
                'sound' => 'default',
                'priority' => 'normal',
                'icon' => '📤',
                'title' => __('notifications.invoice_sent_title'),
                'body' => __('notifications.invoice_sent_body', [
                    'number' => $invoice->number,
                    'email' => $email,
                ]),
                'url' => '/invoices/'.$invoice->id,
            ]);
        }

        return $invoice;
    }

    private function websiteNotes(array $data): string
    {
        $lines = [__('invoices.website_request')];

        if (! empty($data['product_interest'])) {
            $lines[] = __('invoices.product_interest').': '.$data['product_interest'];
        }

        if (! empty($data['message'])) {
            $lines[] = $data['message'];
        }

        return implode("\n", $lines);
    }

    private function websiteItems(array $data): array
    {
        $items = [];

        foreach ($data['items'] ?? [] as $item) {
            $description = $item['name'] ?? 'Product';
            $quantity = 1;
            $unit = 'pcs';
            $unitPrice = 0;
            $productId = null;

            if (! empty($item['sizes']) && is_array($item['sizes'])) {
                $parts = [];
                $totalQty = 0;

                foreach ($item['sizes'] as $size => $qty) {
                    if ((float) $qty > 0) {
                        $parts[] = "{$size}: {$qty}";
                        $totalQty += (float) $qty;
                    }
                }

                if ($parts !== []) {
                    $description .= ' ('.implode(', ', $parts).')';
                }

                if ($totalQty > 0) {
                    $quantity = (int) round($totalQty);
                }
            }

            if (! empty($item['sku'])) {
                $product = Product::query()
                    ->where('is_active', true)
                    ->where(function ($query) use ($item) {
                        $query->where('sku', $item['sku'])
                            ->orWhere('sku', 'ilike', '%'.$item['sku'].'%')
                            ->orWhere('name_en', 'ilike', '%'.$item['name'].'%');
                    })
                    ->first();

                if ($product) {
                    $productId = $product->id;
                    $unitPrice = (float) $product->price_aed;
                    $unit = $product->unit ?? 'pcs';
                }
            }

            $items[] = [
                'product_id' => $productId,
                'description' => $description,
                'quantity' => max(1, $quantity),
                'unit' => $unit,
                'unit_price_aed' => $unitPrice,
            ];
        }

        if ($items === []) {
            $items[] = [
                'product_id' => null,
                'description' => $data['product_interest'] ?? __('invoices.website_request'),
                'quantity' => 1,
                'unit' => 'pcs',
                'unit_price_aed' => 0,
            ];
        }

        return $items;
    }
}
