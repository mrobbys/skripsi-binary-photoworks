<?php

namespace App\Domains\Frontdoor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactUsStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:100'
            ],
            'email' => [
                'required',
                'email',
                'max:150'
            ],
            'subjek' => [
                'required',
                'string',
                'max:150'
            ],
            'pesan' => [
                'required',
                'string',
                'max:2000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.max' => 'Maksimal 100 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Maksimal 150 karakter.',

            'subjek.required' => 'Subjek wajib diisi.',
            'subjek.max' => 'Maksimal 150 karakter.',
            
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.max' => 'Maksimal 2000 karakter.',
        ];
    }
}
