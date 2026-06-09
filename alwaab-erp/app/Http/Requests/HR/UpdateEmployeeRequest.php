<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('hr.manage') ?? false;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            'job_title' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'hire_date' => ['nullable', 'date'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emirates_id' => ['nullable', 'string', 'max:20'],
            'emirates_id_expiry' => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string', 'max:30'],
            'passport_expiry' => ['nullable', 'date'],
            'residency_number' => ['nullable', 'string', 'max:30'],
            'residency_expiry' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'housing_allowance' => ['nullable', 'numeric', 'min:0'],
            'salary_aed' => ['nullable', 'numeric', 'min:0'],
            'iban' => ['nullable', 'string', 'max:34'],
            'employee_code' => ['nullable', 'string', 'max:30', 'unique:employee_profiles,employee_code,'.$employeeId],
        ];
    }
}
