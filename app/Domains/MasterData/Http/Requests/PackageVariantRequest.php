<?php

namespace App\Domains\MasterData\Http\Requests;

use App\Domains\MasterData\DTOs\PackageVariantData;
use Illuminate\Foundation\Http\FormRequest;

class PackageVariantRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'min:3', 'max:100'],
      'price' => ['required', 'integer', 'min:1'],
      'duration' => ['required', 'integer', 'min:1'],
      'is_whatsapp_only' => ['required', 'boolean'],
      'is_active' => ['required', 'boolean'],
      'features' => ['nullable', 'array'],
      'features.*' => ['string', 'max:255'],
    ];
  }

  public function toDto(): PackageVariantData
  {
    return new PackageVariantData(
      name: trim($this->validated('name')),
      price: (int) $this->validated('price'),
      duration: (int) $this->validated('duration'),
      is_whatsapp_only: (bool) $this->validated('is_whatsapp_only'),
      is_active: (bool) $this->validated('is_active'),
      features: $this->validated('features') ?? [],
    );
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama varian wajib diisi.',
      'name.min' => 'Nama varian minimal 3 karakter.',

      'price.required' => 'Harga wajib diisi.',
      'price.integer' => 'Harga harus berupa angka bulat.',
      'price.min' => 'Harga minimal Rp 1.',
      
      'duration.required' => 'Durasi wajib diisi.',
      'duration.integer' => 'Durasi harus berupa angka bulat (menit).',
      'duration.min' => 'Durasi minimal 1 menit.',
    ];
  }
}
