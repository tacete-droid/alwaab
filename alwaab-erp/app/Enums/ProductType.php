<?php

namespace App\Enums;

enum ProductType: string
{
    case Pipe = 'pipe';
    case Fitting = 'fitting';
    case Valve = 'valve';
    case Adhesive = 'adhesive';
}
