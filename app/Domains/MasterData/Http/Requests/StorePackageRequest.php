<?php

namespace App\Domains\MasterData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'category_id' => ['required', 'integer', 'exists:categories,id'],
      'name' => ['required', 'string', 'min:3', 'max:100', 'unique:packages,name'],
      'is_active' => ['required', 'boolean'],
      'features' => ['nullable', 'array'],
      'features.*' => ['string', 'max:255'],
    ];
  }

  public function messages(): array
  {
    return [
      'category_id.required' => 'Kategori wajib dipilih.',
      'category_id.exists' => 'Kategori tidak ditemukan.',

      'name.required' => 'Nama paket wajib diisi.',
      'name.min' => 'Nama paket minimal 3 karakter.',
      'name.max' => 'Nama paket maksimal 100 karakter.',
      'name.unique' => 'Nama paket sudah terdaftar.',
      
      'is_active.required' => 'Status aktif wajib diisi.',
    ];
  }
}
