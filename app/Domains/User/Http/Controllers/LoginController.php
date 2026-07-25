<?php

namespace App\Domains\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\User\Http\Requests\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Domains\User\Traits\RedirectsUsers;

class LoginController extends Controller
{
  use RedirectsUsers;

  public function index(): View
  {
    return view('auth.login.index');
  }

  /**
   * Handle login pengguna.
   * @param LoginRequest $request
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    $request->ensureIsNotRateLimited();
    $validated = $request->validated();

    if (!Auth::attempt([
      'email' => $validated['email'],
      'password' => $validated['password'],
    ], $validated['remember'] ?? false)) {
      RateLimiter::hit($request->throttleKey(), 300);

      throw ValidationException::withMessages([
        'email' => 'Email atau password salah',
      ]);
    }

    RateLimiter::clear($request->throttleKey());
    $request->session()->regenerate();

    return $this->redirectPath(Auth::user())
      ->with('toast', $this->toast(
        title: 'Login Berhasil'
      ));
  }
}
