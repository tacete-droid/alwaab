<?php

namespace App\Enums;

enum HrDocumentType: string
{
    case EmiratesId = 'emirates_id';
    case Passport = 'passport';
    case Residency = 'residency';
    case Contract = 'contract';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
