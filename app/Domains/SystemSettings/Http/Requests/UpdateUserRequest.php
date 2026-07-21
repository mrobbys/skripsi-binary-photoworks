<?php

namespace App\Domains\SystemSettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    // Ambil ID user dari route model binding untuk unique ignore
    $userId = $this->route('user')?->id;

    return [
      'name' => [
        'required',
        'string',
        'min:3',
        'max:255',
        'regex:/^[a-zA-Z\s.,\']+$/'
      ],
      'email' => [
        'required',
        'string',
        'max:255',
        'email:rfc',
        "unique:users,email,{$userId}",
      ],
      'phone' => [
        'required',
        'string',
        'min:10',
        'max:14',
        'regex:/^62[0-9]+$/',
        "unique:users,phone,{$userId}",
      ],
      'role' => [
        'required',
        'string',
        Rule::exists('roles', 'name')->whereNot('name', 'superadmin'),
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama Lengkap harus diisi.',
      'name.string' => 'Nama Lengkap harus berupa string.',
      'name.min' => 'Nama Lengkap minimal terdiri dari 3 karakter.',
      'name.max' => 'Nama Lengkap maksimal terdiri dari 255 karakter.',
      'name.regex' => 'Nama Lengkap hanya boleh mengandung huruf, spasi, titik, koma, dan tanda petik satu (\').',

      'email.required' => 'Email harus diisi.',
      'email.string' => 'Email harus berupa string.',
      'email.max' => 'Email maksimal terdiri dari 255 karakter.',
      'email.email' => 'Format email tidak valid.',
      'email.rfc' => "Format email tidak sesuai standar RFC 5322.",
      'email.unique' => 'Email sudah terdaftar.',

      'phone.required' => 'Nomor telepon harus diisi.',
      'phone.min' => 'Nomor telepon minimal terdiri dari 10 karakter.',
      'phone.max' => 'Nomor telepon maksimal terdiri dari 14 karakter.',
      'phone.regex' => 'Nomor telepon harus berawalan 62.',
      'phone.unique' => 'Nomor telepon sudah terdaftar.',

      'role.required'  => 'Role wajib dipilih.',
      'role.exists'    => 'Role tidak valid.',
    ];
  }
}
