<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    public static function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
