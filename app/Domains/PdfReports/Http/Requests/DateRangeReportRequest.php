<?php

namespace App\Domains\PdfReports\Http\Requests;

use App\Domains\Booking\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DateRangeReportRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'start_date' => [
        'required',
        'date',
        'before_or_equal:today'
      ],
      'end_date' => [
        'required',
        'date',
        'after_or_equal:start_date',
        'before_or_equal:today'
      ],
      /**
       * Opsional
       * Karena ada beberapa report yang tidak membutuhkan status
       */
      'status' => [
        'nullable',
        'string',
        Rule::enum(BookingStatus::class)
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'start_date.required' => 'Tanggal awal wajib diisi.',
      'start_date.date' => 'Format tanggal awal tidak valid.',
      'start_date.before_or_equal' => 'Tanggal awal tidak boleh melebihi hari ini.',

      'end_date.required' => 'Tanggal akhir wajib diisi.',
      'end_date.date' => 'Format tanggal akhir tidak valid.',
      'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
      'end_date.before_or_equal' => 'Tanggal akhir tidak boleh melebihi hari ini.',

      'status.enum' => 'Status booking yang dipilih tidak valid.',
    ];
  }
}
