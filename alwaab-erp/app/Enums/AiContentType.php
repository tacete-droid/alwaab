<?php

namespace App\Enums;

enum AiContentType: string
{
    case Text = 'text';
    case Image = 'image';
    case Video = 'video';

    public function creditCost(): int
    {
        return match ($this) {
            self::Text => 0,
            self::Image => 1,
            self::Video => 5,
        };
    }
}
