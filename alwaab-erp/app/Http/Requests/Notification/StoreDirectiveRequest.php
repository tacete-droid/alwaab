<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDirectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'body_ar' => ['required', 'string', 'max:2000'],
            'body_en' => ['required', 'string', 'max:2000'],
            'priority' => ['required', Rule::in(['normal', 'urgent'])],
            'target' => ['required', Rule::in(['all', 'role', 'user'])],
            'target_role' => ['nullable', 'required_if:target,role', 'string', 'exists:roles,name'],
            'target_user_id' => ['nullable', 'required_if:target,user', 'uuid', 'exists:users,id'],
        ];
    }
}
