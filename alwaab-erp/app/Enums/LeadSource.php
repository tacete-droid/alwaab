<?php

namespace App\Enums;

enum LeadSource: string
{
    case Website = 'website';
    case Referral = 'referral';
    case Exhibition = 'exhibition';
    case ColdCall = 'cold_call';
    case Portal = 'portal';
    case Other = 'other';
}
