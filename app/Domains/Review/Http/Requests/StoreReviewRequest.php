<?php

namespace App\Domains\Review\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5'
            ],
            'comment' => [
                'required',
                'string',
                'min:5',
                'max:500'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Rating bintang wajib dipilih.',
            'rating.integer' => 'Format rating tidak valid.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',

            'comment.required' => 'Ulasan wajib diisi.',
            'comment.min' => 'Ulasan minimal 5 karakter.',
            'comment.max' => 'Ulasan maksimal 500 karakter.',
        ];
    }
}
