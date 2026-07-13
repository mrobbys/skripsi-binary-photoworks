<?php

namespace App\Domains\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'old_password' => ['required', 'string'],
      'password' => [
        'required',
        'string',
        'confirmed',
        Password::min(8)
          ->max(255)
          ->mixedCase()
          ->numbers()
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'old_password.required' => 'Password lama harus diisi.',

      'password.required'  => 'Password baru harus diisi.',
      'password.confirmed' => 'Konfirmasi password tidak cocok.',
      'password.min'       => 'Password minimal 8 karakter.',
      'password.max'       => 'Password maksimal 255 karakter.',
      'password.mixed'     => 'Password harus mengandung huruf besar dan kecil.',
      'password.numbers'   => 'Password harus mengandung angka.',
    ];
  }
}
