<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

trait HasUuid
{
    use HasUuids;

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
