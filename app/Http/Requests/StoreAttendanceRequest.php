<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'employee_id'  => ['required', 'exists:employees,id'],
            'date'         => ['required', 'date'],
            'status'       => ['required', 'in:present,absent,late,half_day'],
            'time_in'      => ['nullable', 'date_format:H:i'],
            'time_out'     => ['nullable', 'date_format:H:i', 'after:time_in'],
            'shift'        => ['nullable', 'in:morning,afternoon,night'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'remarks'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
