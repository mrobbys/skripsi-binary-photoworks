<?php

namespace App\Domains\User\Http\Requests;

use App\Domains\User\DTOs\ResetPasswordData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'token' => [
                'required',
                'string'
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                'email:rfc,dns'
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
        ];
    }

    /**
     * Return data yang telah di validasi ke DTO ResetPasswordData
     */
    public function toDto(): ResetPasswordData
    {
        $validated = $this->validated();

        return new ResetPasswordData(
            token: $validated['token'],
            email: trim($validated['email']),
            password: $validated['password']
        );
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token reset tidak valid.',

            'email.required' => 'Email harus diisi.',
            'email.string' => 'Email harus berupa string.',
            'email.max' => 'Email maksimal terdiri dari 255 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.rfc' => "Format email tidak sesuai standar RFC 5322.",
            'email.dns' => 'Domain email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',

            'password.required' => 'Password harus diisi.',
            'password.string' => 'Password harus berupa string.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.max' => 'Password maksimal terdiri dari 255 karakter.',
            'password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password harus mengandung angka.',
        ];
    }
}
