<?php

namespace App\Domains\User\Http\Requests;

use App\Domains\User\DTOs\RegisterData;
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
                'email:rfc,dns',
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
                'max:15',
                'regex:/^[0-9]+$/',
                'unique:users,phone',
            ],
        ];
    }

    /**
     * Return data yang telah divalidasi ke DTO RegisterData
     *
     * @return RegisterData
     */
    public function toDto(): RegisterData
    {
        $validated = $this->validated();

        return new RegisterData(
            name: trim($validated['name']),
            email: trim($validated['email']),
            password: $validated['password'],
            phone: trim($validated['phone'])
        );
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
            'email.dns' => 'Domain email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',

            'password.required' => 'Password harus diisi.',
            'password.string' => 'Password harus berupa string.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.max' => 'Password maksimal terdiri dari 255 karakter.',
            'password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password harus mengandung angka.',

            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.string' => 'Nomor telepon harus berupa string.',
            'phone.max' => 'Nomor telepon maksimal terdiri dari 15 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh mengandung angka.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
        ];
    }
}
