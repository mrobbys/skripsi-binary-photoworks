<?php

namespace App\Domains\Booking\Http\Requests;

use App\Domains\Booking\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualBookingRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'user_id'            => ['required', 'integer', 'exists:users,id'],
      'package_variant_id' => ['required', 'integer', 'exists:package_variants,id'],
      'background_id'      => ['required', 'integer', 'exists:backgrounds,id'],
      'booking_date'       => ['required', 'date', 'after_or_equal:today'],
      'start_time'         => ['required', 'date_format:H:i'],
      'status'             => ['required', Rule::in([BookingStatus::DP_PAID->value, BookingStatus::SUCCESS->value])],
      'addons'             => ['nullable', 'array'],
      'addons.*.addon_id'  => ['required_with:addons', 'integer', 'exists:addons,id'],
      'addons.*.quantity'  => ['required_with:addons', 'integer', 'min:1'],
    ];
  }

  public function messages(): array
  {
    return [
      'user_id.required'            => 'Klien wajib dipilih.',
      'user_id.exists'              => 'Klien tidak ditemukan.',

      'package_variant_id.required' => 'Paket & varian wajib dipilih.',
      'package_variant_id.exists'   => 'Varian paket tidak ditemukan.',

      'background_id.required'      => 'Background wajib dipilih.',

      'booking_date.required'       => 'Tanggal sesi wajib diisi.',
      'booking_date.after_or_equal' => 'Tanggal sesi tidak boleh di masa lalu.',

      'start_time.required'         => 'Slot waktu wajib dipilih.',

      'status.required'             => 'Status booking wajib dipilih.',
      'status.in'                   => 'Status tidak valid.',

      'addons.*.addon_id.exists'    => 'Salah satu layanan tambahan tidak ditemukan.',
      'addons.*.quantity.min'       => 'Kuantitas layanan tambahan minimal 1.',
    ];
  }
}
