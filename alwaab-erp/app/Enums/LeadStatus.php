<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Won = 'won';
    case Lost = 'lost';
}
