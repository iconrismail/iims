<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('employee')->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'min:8'],
            'department_id' => ['required', 'exists:departments,id'],
            'position' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['required', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'status'             => ['required', 'in:active,inactive'],
            'contract_type'      => ['required', 'in:permanent,fixed-term,probation'],
            'contract_end_date'  => ['nullable', 'date', 'required_if:contract_type,fixed-term'],
            'probation_end_date' => ['nullable', 'date'],
        ];
    }
}
