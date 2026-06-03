<?php

namespace App\Domains\Auth\Http\Requests;

use App\Domains\Auth\DTOs\ForgotPasswordData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class ForgotPasswordRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'max:255',
                'email:rfc,dns'
            ],
        ];
    }

    /**
     * Return data yang telah di validasi ke DTO ForgotPasswordData
     * 
     * @return ForgotPasswordData
     */
    public function toDto(): ForgotPasswordData
    {
        return new ForgotPasswordData(
            email: trim($this->validated('email'))
        );
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email harus diisi.',
            'email.string' => 'Email harus berupa string.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.rfc' => "Format email tidak sesuai standar RFC 5322.",
            'email.dns' => 'Domain email tidak valid.',
        ];
    }
}
