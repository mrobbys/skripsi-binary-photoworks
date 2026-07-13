<?php

namespace App\Domains\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
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
        'unique:users,email,' . Auth::id(),
      ],
      'phone' => [
        'required',
        'string',
        'min:10',
        'max:14',
        'regex:/^62[0-9]+$/',
        'unique:users,phone,' . Auth::id(),
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama lengkap harus diisi.',
      'name.min'      => 'Nama lengkap minimal 3 karakter.',
      'name.max'      => 'Nama lengkap maksimal 100 karakter.',

      'email.required' => 'Email harus diisi.',
      'email.email'    => 'Format email tidak valid.',
      'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
      'email.max'      => 'Email maksimal 255 karakter.',

      'phone.required' => 'Nomor telepon harus diisi.',
      'phone.min' => 'Nomor telepon minimal terdiri dari 10 karakter.',
      'phone.max' => 'Nomor telepon maksimal terdiri dari 14 karakter.',
      'phone.regex' => 'Nomor telepon harus berawalan 62.',
      'phone.unique' => 'Nomor telepon sudah terdaftar.',
    ];
  }
}
