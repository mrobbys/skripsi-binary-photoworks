<?php

namespace App\Domains\MasterData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBackgroundRequest extends FormRequest
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
				'max:50',
				'unique:backgrounds,name'
			],
			'description' => [
				'nullable',
				'string',
				'max:255'
			],
			'is_active' => [
				'required',
				'boolean'
			],
			'image' => [
				'required',
				'file',
				'image',
				'mimes:jpeg,jpg,png,webp',
				'max:2048'
			],
		];
	}

	public function messages(): array
	{
		return [
			'name.required' => 'Nama background wajib diisi',
			'name.max' => 'Nama background maksimal 50 karakter',
			'name.unique' => 'Nama background sudah terdaftar',

			'description.max' => 'Deskripsi maksimal 255 karakter',

			'is_active.required' => 'Status aktif wajib diisi',

			'image.required' => 'Gambar background wajib diunggah',
			'image.image' => 'File harus berupa gambar',
			'image.mimes' => 'Format gambar harus JPEG, JPG, PNG, atau WebP',
			'image.max' => 'Ukuran gambar maksimal 2 MB',
		];
	}
}
