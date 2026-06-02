<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Http\Requests\RegisterRequest;
use App\Domains\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
  public function __construct(protected AuthService $authService) {}

  public function index(): View
  {
    return view('auth.register.index');
  }

  public function store(RegisterRequest $request): RedirectResponse
  {
    $registerData = $request->toDto();

    $this->authService->register($registerData);

    return redirect()->route('login')->with('alert', [
      'type' => 'success',
      'title' => 'Daftar Akun Berhasil!',
      'message' => 'Silahkan Login dengan akun anda.'
    ]);
  }
}
