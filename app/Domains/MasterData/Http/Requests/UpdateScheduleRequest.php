<?php

namespace App\Domains\MasterData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'start_time' => [
        'required',
        'date_format:H:i'
      ],
      'end_time' => [
        'required',
        'date_format:H:i',
        'after:start_time'
      ],
      'is_active' => [
        'required',
        'boolean'
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'start_time.required' => 'Jam buka wajib diisi',
      'start_time.date_format' => 'Format jam buka tidak valid (HH:MM)',

      'end_time.required' => 'Jam tutup wajib diisi',
      'end_time.date_format' => 'Format jam tutup tidak valid (HH:MM)',
      'end_time.after' => 'Jam tutup harus lebih besar dari jam buka',

      'is_active.required' => 'Status aktif wajib diisi',
    ];
  }
}
