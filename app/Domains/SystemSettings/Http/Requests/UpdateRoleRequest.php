<?php

namespace App\Domains\SystemSettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $roleId = $this->route('role')?->id;

    return [
      'name' => [
        'required',
        'string',
        'max:50',
        "unique:roles,name,{$roleId}"
      ],
      'permissions' => [
        'array'
      ],
      'permissions.*' => [
        'string',
        'exists:permissions,name'
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama role wajib diisi',
      'name.string' => 'Nama role harus berupa string',
      'name.max' => 'Nama role maksimal 50 karakter',
      'name.unique' => 'Nama role sudah digunakan',
      'permissions.*.exists' => 'Permission tidak valid',
    ];
  }
}
