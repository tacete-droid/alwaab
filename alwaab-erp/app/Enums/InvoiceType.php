<?php

namespace App\Enums;

enum InvoiceType: string
{
    case Quotation = 'quotation';
    case Sales = 'sales';
    case Proforma = 'proforma';

    public function prefix(): string
    {
        return match ($this) {
            self::Quotation => 'QUOT',
            self::Sales => 'SINV',
            self::Proforma => 'PINV',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Quotation => 'عرض السعر',
            self::Sales => 'فاتورة بيع',
            self::Proforma => 'فاتورة مبدئية',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::Quotation => 'Quotation',
            self::Sales => 'Sales Invoice',
            self::Proforma => 'Proforma Invoice',
        };
    }
}
