<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Prospecting = 'prospecting';
    case Active = 'active';
    case Completed = 'completed';
    case OnHold = 'on_hold';
}
