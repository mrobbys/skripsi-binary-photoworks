<?php

namespace App\Domains\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->max(255)
                    ->mixedCase()
                    ->numbers()
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:14',
                'regex:/^62[0-9]+$/',
                'unique:users,phone',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama Lengkap harus diisi',
            'name.string' => 'Nama Lengkap harus berupa string',
            'name.min' => 'Nama Lengkap minimal terdiri dari 3 karakter',
            'name.max' => 'Nama Lengkap maksimal terdiri dari 255 karakter',
            'name.regex' => 'Nama Lengkap hanya boleh mengandung huruf, spasi, titik, koma, dan tanda petik satu (\')',

            'email.required' => 'Email harus diisi',
            'email.string' => 'Email harus berupa string',
            'email.max' => 'Email maksimal terdiri dari 255 karakter',
            'email.email' => 'Format email tidak valid',
            'email.rfc' => "Format email tidak sesuai standar RFC 5322",
            'email.unique' => 'Email tidak dapat digunakan',

            'password.required' => 'Password harus diisi',
            'password.string' => 'Password harus berupa string',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.min' => 'Password minimal terdiri dari 8 karakter',
            'password.max' => 'Password maksimal terdiri dari 255 karakter',
            'password.mixed' => 'Password harus mengandung huruf besar dan kecil',
            'password.numbers' => 'Password harus mengandung angka',

            'phone.required' => 'Nomor telepon harus diisi',
            'phone.string' => 'Nomor telepon harus berupa string',
            'phone.min' => 'Nomor telepon minimal terdiri dari 10 karakter',
            'phone.max' => 'Nomor telepon maksimal terdiri dari 14 karakter',
            'phone.regex' => 'Nomor telepon harus berawalan 62',
            'phone.unique' => 'Nomor telepon tidak dapat digunakan',
        ];
    }
}
