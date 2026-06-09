<?php

namespace App\Enums;

enum LeaveType: string
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Emergency = 'emergency';
    case Unpaid = 'unpaid';
}
