<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Saved = 'saved';
    case Issued = 'issued';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}
