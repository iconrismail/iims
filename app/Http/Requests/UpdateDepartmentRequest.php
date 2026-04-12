<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')->id;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $departmentId],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
