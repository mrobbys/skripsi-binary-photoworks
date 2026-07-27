<?php

namespace App\Domains\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGdriveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gdrive_link' => [
                'required',
                'url',
                'starts_with:http://,https://'
            ],
            'send_wa_notification' => [
                'nullable',
                'boolean'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'gdrive_link.required' => 'Link Google Drive wajib diisi.',
            'gdrive_link.url' => 'Format link tidak valid.',
            'gdrive_link.starts_with' => 'Link harus diawali dengan http:// atau https://',
        ];
    }
}
