<?php

namespace App\Enums;

enum ContactType: string
{
    case Customer = 'customer';
    case Consultant = 'consultant';
    case Contractor = 'contractor';
    case Developer = 'developer';
    case Lead = 'lead';
}
