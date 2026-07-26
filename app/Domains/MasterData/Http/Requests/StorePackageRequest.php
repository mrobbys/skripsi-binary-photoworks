<?php

namespace App\Domains\MasterData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'category_id' => [
				'required',
				'integer',
				'exists:categories,id'
			],
			'name' => [
				'required',
				'string',
				'min:3',
				'max:100',
				'unique:packages,name'
			],
			'description' => [
				'required',
				'string',
				'max:500'
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
			'category_id.required' => 'Kategori wajib dipilih.',
			'category_id.exists' => 'Kategori tidak ditemukan.',

			'name.required' => 'Nama paket wajib diisi.',
			'name.min' => 'Nama paket minimal 3 karakter.',
			'name.max' => 'Nama paket maksimal 100 karakter.',
			'name.unique' => 'Nama paket sudah terdaftar.',

			'description.required' => 'Deskripsi wajib diisi.',
			'description.max' => 'Deskripsi maksimal 500 karakter.',

			'image.required' => 'Gambar wajib diunggah.',
			'image.file' => 'Gambar harus berupa file.',
			'image.image' => 'File harus berupa gambar.',
			'image.mimes' => 'Format gambar harus jpeg, jpg, png, webp.',
			'image.max' => 'Ukuran gambar maksimal 2MB.',

			'is_active.required' => 'Status aktif wajib diisi.',

		];
	}
}
