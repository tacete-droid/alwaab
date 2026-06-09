<?php

namespace App\Http\Requests\AIStudio;

use Illuminate\Foundation\Http\FormRequest;

class TopUpCreditsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
