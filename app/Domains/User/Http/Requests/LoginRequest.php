<?php

namespace App\Domains\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'max:255',
                'email:rfc'
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->max(255)
                    ->mixedCase()
                    ->numbers()
            ],
            'remember' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email harus diisi',
            'email.string' => 'Email harus berupa string',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter',
            'email.email' => 'Format email tidak valid',
            'email.rfc' => "Format email tidak sesuai standar RFC 5322",

            'password.required' => 'Password harus diisi',
            'password.string' => 'Password harus berupa string',
            'password.min' => 'Password harus terdiri dari minimal 8 karakter',
            'password.max' => 'Password tidak boleh lebih dari 255 karakter',
            'password.mixed' => 'Password harus mengandung huruf besar dan kecil',
            'password.numbers' => 'Password harus mengandung angka',
        ];
    }
}
