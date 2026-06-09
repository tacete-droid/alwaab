<?php

namespace App\Services;

use App\Domain\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'app_settings';

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'company']
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function companyDefaults(): array
    {
        return [
            'company_name_ar' => 'شركة الوعب للتجارة',
            'company_name_en' => 'AL WAAB BUILDING MATERIALS TRADING EST',
            'company_tagline' => 'FlowGuard CPVC Pipes & Fittings - DIN EN ISO15877',
            'company_phone' => '+971 547696440',
            'company_landline' => '042514228',
            'company_email' => 'info@alwaab.ae',
            'company_website' => 'www.alwaab.ae',
            'company_address' => 'No 206, Abubakar Al Siddique Rd, Mohamed Hassan Al Shaikh-2, Deira, Dubai - UAE. P.O Box - 566227.',
            'company_trn' => '',
            'company_logo' => '/images/alwaab-logo.png',
            'bank_name' => 'Abu Dhabi Islamic Bank',
            'bank_account_name' => 'AL WAAB BUILDING MATERIALS TRADING EST',
            'bank_account_no' => '19500744',
            'bank_iban' => 'AE600500000000019500744',
            'bank_account_type' => 'CURRENT',
            'bank_branch' => 'Sheikh Zayed',
            'bank_city' => 'DUBAI',
            'bank_po_box' => '566227',
            'invoice_payment_terms' => 'Full payment required upon order confirmation. Make all cheques payable to company name "AL WAAB BUILDING MATERIALS TRADING EST".',
            'invoice_delivery_terms' => 'Delivery : 7 - 8 Days from conformed LPO Date.',
            'invoice_validity_text' => 'Validity : 10 Days',
            'invoice_footer_note' => 'Thank you for your business! Should you have any enquiries concerning this quote, please contact us : 24/7 to support your emergency requirements',
            'quotation_validity_days' => '30',
            'low_stock_alert' => '1',
        ];
    }

    public function companySettings(): array
    {
        return array_merge($this->companyDefaults(), $this->all());
    }
}
