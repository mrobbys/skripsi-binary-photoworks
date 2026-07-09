<?php

namespace App\Domains\MasterData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBackgroundRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $backgroundId = $this->route('background')?->id;

    return [
      'name' => [
        'required',
        'string',
        'max:50',
        Rule::unique('backgrounds', 'name')->ignore($backgroundId),
      ],
      'description' => ['nullable', 'string', 'max:255'],
      'is_active' => ['required', 'boolean'],
      'image' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama background wajib diisi.',
      'name.max' => 'Nama background maksimal 50 karakter.',
      'name.unique' => 'Nama background sudah terdaftar.',

      'description.max' => 'Deskripsi maksimal 255 karakter.',

      'image.image' => 'File harus berupa gambar.',
      'image.mimes' => 'Format gambar harus JPEG, JPG, PNG, atau WebP.',
      'image.max' => 'Ukuran gambar maksimal 2 MB.',
    ];
  }
}
