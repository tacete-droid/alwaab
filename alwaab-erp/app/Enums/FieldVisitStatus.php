<?php

namespace App\Enums;

enum FieldVisitStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
