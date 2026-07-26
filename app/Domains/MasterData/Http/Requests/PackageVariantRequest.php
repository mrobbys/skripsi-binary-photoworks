<?php

namespace App\Domains\MasterData\Http\Requests;

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
			'name' => [
				'required',
				'string',
				'min:3',
				'max:100'
			],
			'price' => [
				'required',
				'integer',
				'min:1'
			],
			'duration' => [
				'required',
				'integer',
				'min:1'
			],
			'is_whatsapp_only' => [
				'required',
				'boolean'
			],
			'is_active' => [
				'required',
				'boolean'
			],
			'features' => [
				'nullable',
				'array'
			],
			'features.*' => [
				'string',
				'max:255'
			],
		];
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
