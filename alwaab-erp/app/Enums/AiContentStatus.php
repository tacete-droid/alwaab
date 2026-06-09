<?php

namespace App\Enums;

enum AiContentStatus: string
{
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
