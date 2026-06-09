<?php

namespace App\Enums;

enum RfqStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Quoted = 'quoted';
    case Closed = 'closed';
}
