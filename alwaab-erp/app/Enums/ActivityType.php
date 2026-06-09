<?php

namespace App\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case Meeting = 'meeting';
    case Email = 'email';
    case Visit = 'visit';
    case Task = 'task';
}
