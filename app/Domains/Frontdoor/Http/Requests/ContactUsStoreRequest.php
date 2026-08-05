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
            'website_url' => [
                'prohibited',
            ],
            'nama' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z\s.,\']+$/'
            ],
            'email' => [
                'required',
                'email:rfc',
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
            'nama.required' => 'Nama lengkap wajib diisi',
            'name.min' => 'Nama Lengkap minimal terdiri dari 3 karakter',
            'nama.max' => 'Maksimal 100 karakter',
            'name.regex' => 'Nama Lengkap hanya boleh mengandung huruf, spasi, titik, koma, dan tanda petik satu (\')',

            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.rfc' => "Format email tidak sesuai standar RFC 5322",
            'email.max' => 'Maksimal 150 karakter',

            'subjek.required' => 'Subjek wajib diisi',
            'subjek.max' => 'Maksimal 150 karakter',

            'pesan.required' => 'Pesan wajib diisi',
            'pesan.max' => 'Maksimal 2000 karakter',
        ];
    }
}
