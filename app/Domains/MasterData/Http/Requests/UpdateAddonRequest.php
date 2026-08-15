<?php

namespace App\Domains\MasterData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddonRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $addonId = $this->route('addon')?->id;

    return [
      'name' => [
        'required',
        'string',
        'max:100',
        Rule::unique('addons', 'name')->ignore($addonId),
      ],
      'price' => [
        'required',
        'integer',
        'min:0'
      ],
      'description' => [
        'required',
        'string',
        'max:255'
      ],
      'has_quantity' => [
        'required',
        'boolean'
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
      'name.required' => 'Nama add-on wajib diisi',
      'name.max' => 'Nama add-on maksimal 100 karakter',
      'name.unique' => 'Nama add-on sudah terdaftar',

      'price.required' => 'Harga wajib diisi',
      'price.integer' => 'Harga harus berupa angka',
      'price.min' => 'Harga tidak boleh negatif',

      'description.required' => 'Deskripsi wajib diisi',
      'description.max' => 'Deskripsi maksimal 255 karakter',

      'has_quantity.required' => 'Tipe input wajib dipilih',

      'is_active.required' => 'Status aktif wajib diisi',
    ];
  }
}
