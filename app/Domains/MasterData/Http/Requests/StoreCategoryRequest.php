<?php

namespace App\Domains\MasterData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   * 
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'category_code' => [
        'required',
        'string',
        'max:3',
        'unique:categories,category_code',
      ],
      'name' => [
        'required',
        'string',
        'max:100',
        'unique:categories,name',
      ],
      'is_active' => [
        'required',
        'boolean',
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'category_code.required' => 'Kode kategori wajib diisi',
      'category_code.max' => 'Kode kategori maksimal 3 karakter',
      'category_code.unique' => 'Kode kategori sudah terdaftar',

      'name.required' => 'Nama kategori wajib diisi',
      'name.max' => 'Nama kategori maksimal 100 karakter',
      'name.unique' => 'Nama kategori sudah terdaftar',
      
      'is_active.required' => 'Status aktif wajib diisi',
      'is_active.boolean' => 'Status aktif harus berupa boolean',
    ];
  }
}
