<?php

namespace App\Domain\Settings\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuid;

    protected $fillable = ['key', 'value', 'group'];
}
