<?php

namespace App\Services;

use App\Support\DatabaseHelper;

use App\Domain\Inventory\Models\Product;
use App\Domain\Quotations\Models\Quotation;
use App\Domain\Quotations\Models\QuotationItem;
use App\Domain\Quotations\Models\Rfq;
use App\Domain\Quotations\Models\RfqBoqItem;
use App\Enums\QuotationStatus;
use App\Enums\RfqStatus;
use App\Mail\QuotationSentMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuotationService
{
    public function generateNumber(string $prefix): string
    {
        $year = now()->year;
        $pattern = "{$prefix}-{$year}-%";

        $latest = match ($prefix) {
            'RFQ' => Rfq::where('number', DatabaseHelper::likeOperator(), $pattern)->orderByDesc('number')->value('number'),
            'QUO' => Quotation::where('number', DatabaseHelper::likeOperator(), $pattern)->orderByDesc('number')->value('number'),
            default => null,
        };

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('%s-%d-%03d', $prefix, $year, $seq);
    }

    public function createQuotation(array $data, User $user): Quotation
    {
        return DB::transaction(function () use ($data, $user) {
            $quotation = Quotation::create([
                'number' => $this->generateNumber('QUO'),
                'contact_id' => $data['contact_id'],
                'project_id' => $data['project_id'] ?? null,
                'rfq_id' => $data['rfq_id'] ?? null,
                'status' => QuotationStatus::Draft,
                'version' => 1,
                'discount_aed' => $data['discount_aed'] ?? 0,
                'valid_until' => $data['valid_until'] ?? now()->addDays(30),
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->syncItems($quotation, $data['items'] ?? []);
            $this->recalculateTotals($quotation);

            if (! empty($data['rfq_id'])) {
                Rfq::where('id', $data['rfq_id'])->update(['status' => RfqStatus::Quoted]);
            }

            return $quotation->fresh(['items.product', 'contact', 'project']);
        });
    }

    public function createQuotationFromRfq(Rfq $rfq, User $user): Quotation
    {
        $rfq->load('boqItems.product');

        abort_if($rfq->boqItems->isEmpty(), 422, __('quotations.boq_empty'));

        $items = $rfq->boqItems->map(fn (RfqBoqItem $item) => [
            'product_id' => $item->product_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'unit_price_aed' => $item->unit_price_aed ?? $item->product?->price_aed ?? 0,
        ])->all();

        return $this->createQuotation([
            'contact_id' => $rfq->contact_id,
            'project_id' => $rfq->project_id,
            'rfq_id' => $rfq->id,
            'items' => $items,
        ], $user);
    }

    public function syncItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();

        foreach ($items as $index => $item) {
            $product = ! empty($item['product_id']) ? Product::find($item['product_id']) : null;
            $qty = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price_aed'] ?? $product?->price_aed ?? 0);

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'product_id' => $product?->id,
                'description' => $item['description'] ?? $product?->name_en,
                'quantity' => $qty,
                'unit' => $item['unit'] ?? $product?->unit ?? 'pcs',
                'unit_price_aed' => $unitPrice,
                'total_aed' => round($qty * $unitPrice, 2),
                'sort_order' => $index + 1,
            ]);
        }
    }

    public function recalculateTotals(Quotation $quotation): Quotation
    {
        $quotation->load('items');
        $subtotal = $quotation->items->sum('total_aed');
        $discount = (float) $quotation->discount_aed;
        $total = max(0, $subtotal - $discount);

        $quotation->update([
            'subtotal_aed' => $subtotal,
            'total_aed' => $total,
        ]);

        return $quotation;
    }

    public function approve(Quotation $quotation, User $user): Quotation
    {
        $quotation->update([
            'status' => QuotationStatus::Approved,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $quotation->load('creator');
        if ($quotation->creator && $quotation->creator->id !== $user->id) {
            app(AppNotificationService::class)->notifyUser($quotation->creator, [
                'category' => 'system',
                'sound' => 'default',
                'priority' => 'normal',
                'icon' => '💰',
                'title' => __('notifications.quotation_approved_title'),
                'body' => __('notifications.quotation_approved_body', ['number' => $quotation->number]),
                'url' => '/quotations/'.$quotation->id,
            ]);
        }

        return $quotation;
    }

    public function send(Quotation $quotation): Quotation
    {
        $quotation->load(['creator', 'contact']);

        $email = $quotation->contact?->email;
        abort_if(! $email, 422, __('quotations.no_contact_email'));

        $pdf = app(PdfService::class)->quotationPdf($quotation);
        $filename = str_replace(['/', '\\'], '-', $quotation->number).'.pdf';

        Mail::to($email)->send(new QuotationSentMail(
            $quotation,
            $pdf->output(),
            $filename,
        ));

        $quotation->update(['status' => QuotationStatus::Sent]);

        if ($quotation->creator) {
            app(AppNotificationService::class)->notifyUser($quotation->creator, [
                'category' => 'system',
                'sound' => 'default',
                'priority' => 'normal',
                'icon' => '📤',
                'title' => __('notifications.quotation_sent_title'),
                'body' => __('notifications.quotation_sent_body', [
                    'number' => $quotation->number,
                    'email' => $email,
                ]),
                'url' => '/quotations/'.$quotation->id,
            ]);
        }

        return $quotation;
    }
}
