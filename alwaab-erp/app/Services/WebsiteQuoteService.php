<?php

namespace App\Services;

use App\Domain\CRM\Models\Contact;
use App\Domain\Invoices\Models\Invoice;
use App\Domain\Quotations\Models\Rfq;
use App\Enums\ContactStatus;
use App\Enums\ContactType;
use App\Enums\RfqStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WebsiteQuoteService
{
    public function __construct(
        private QuotationService $quotationService,
        private InvoiceService $invoiceService,
        private AppNotificationService $notifications,
    ) {}

    public function submit(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $contact = $this->resolveContact($data);
            $admin = $this->adminUser();

            $rfq = Rfq::create([
                'number' => $this->quotationService->generateNumber('RFQ'),
                'contact_id' => $contact->id,
                'status' => RfqStatus::Pending,
                'source' => 'website',
                'description' => $this->buildDescription($data),
                'website_meta' => $data,
            ]);

            $invoice = $this->invoiceService->createFromWebsiteRequest(
                $data,
                $contact,
                $rfq->id,
                $admin,
            );

            $this->notifyAdmins($rfq, $contact, $invoice);

            return [
                'rfq' => $rfq->load('contact:id,name_ar,name_en,company,email,phone'),
                'invoice' => $invoice,
            ];
        });
    }

    private function adminUser(): User
    {
        return User::role('super_admin')->where('is_active', true)->first()
            ?? User::where('is_active', true)->first()
            ?? throw new \RuntimeException('No active user found for website quote intake.');
    }

    private function resolveContact(array $data): Contact
    {
        $contact = Contact::where('email', $data['email'])->first();

        if ($contact) {
            $contact->update(array_filter([
                'name_en' => $data['name'],
                'name_ar' => $data['name'],
                'company' => $data['company'] ?? $contact->company,
                'phone' => $data['phone'] ?? $contact->phone,
            ], fn ($value) => $value !== null && $value !== ''));

            return $contact;
        }

        return Contact::create([
            'type' => ContactType::Lead,
            'status' => ContactStatus::Prospect,
            'name_en' => $data['name'],
            'name_ar' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);
    }

    private function buildDescription(array $data): string
    {
        $lines = ['[Website Quote Request]'];

        if (! empty($data['product_interest'])) {
            $lines[] = 'Product: '.$data['product_interest'];
        }

        if (! empty($data['project'])) {
            $lines[] = 'Project: '.$data['project'];
        }

        if (! empty($data['items'])) {
            $lines[] = '';
            $lines[] = 'Selected Products:';
            foreach ($data['items'] as $item) {
                $line = '- '.($item['name'] ?? 'Product');
                if (! empty($item['sku'])) {
                    $line .= ' ('.$item['sku'].')';
                }
                if (! empty($item['sizes']) && is_array($item['sizes'])) {
                    $sizes = collect($item['sizes'])
                        ->filter(fn ($qty) => (float) $qty > 0)
                        ->map(fn ($qty, $size) => "{$size}: {$qty}")
                        ->implode(', ');
                    if ($sizes !== '') {
                        $line .= ' — '.$sizes;
                    }
                }
                $lines[] = $line;
            }
        }

        if (! empty($data['message'])) {
            $lines[] = '';
            $lines[] = 'Message:';
            $lines[] = $data['message'];
        }

        if (! empty($data['page'])) {
            $lines[] = '';
            $lines[] = 'Page: '.$data['page'];
        }

        return implode("\n", $lines);
    }

    private function notifyAdmins(Rfq $rfq, Contact $contact, Invoice $invoice): void
    {
        $admins = User::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $user) => $user->hasRole('super_admin')
                || $user->can('invoices.view')
                || $user->can('quotations.view'));

        foreach ($admins as $admin) {
            $this->notifications->notifyUser($admin, [
                'category' => 'system',
                'sound' => 'default',
                'priority' => 'high',
                'icon' => '🌐',
                'title' => __('notifications.website_quote_title'),
                'body' => __('notifications.website_quote_body', [
                    'name' => $contact->name_en,
                    'number' => $invoice->number,
                ]),
                'url' => '/invoices/'.$invoice->id,
            ]);
        }
    }
}
