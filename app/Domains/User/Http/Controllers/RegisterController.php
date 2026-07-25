<?php

namespace App\Domains\User\Http\Controllers;

use App\Domains\User\DTOs\RegisterData;
use App\Domains\User\Enums\RoleType;
use App\Domains\User\Http\Requests\RegisterRequest;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
  public function index(): View
  {
    return view('auth.register.index');
  }

  /**
   * Handle register dan menambahkan role user
   * @param RegisterRequest $request
   */
  public function store(RegisterRequest $request): RedirectResponse
  {
    $registerData = RegisterData::fromRequest($request);

    $user = User::create([
      'name' => $registerData->name,
      'email' => $registerData->email,
      'phone' => $registerData->phone,
      'password' => Hash::make($registerData->password),
    ]);
    $user->assignRole(RoleType::USER->value);

    return redirect()->route('login')
      ->with('alert', $this->alert(
        title: 'Daftar Akun Berhasil!',
        message: 'Silahkan Login dengan akun anda.'
      ));
  }
}
