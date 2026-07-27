<?php

namespace App\Domains\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsellAddonRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'addon_id' => [
        'required',
        'integer',
        'exists:addons,id'
      ],
      'quantity' => [
        'required',
        'integer',
        'min:1'
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'addon_id.required' => 'Layanan tambahan wajib dipilih.',
      'addon_id.exists' => 'Layanan tambahan tidak ditemukan.',
      'quantity.required' => 'Kuantitas wajib diisi.',
      'quantity.min' => 'Kuantitas minimal 1.',
    ];
  }
}
