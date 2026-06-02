<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\Http\Requests\LoginRequest;
use App\Domains\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Domains\Auth\Traits\RedirectsUsers;

class LoginController extends Controller
{

  use RedirectsUsers;

  public function __construct(protected AuthService $authService) {}

  /**
   * Menampilkan halaman login.
   *
   * @return View
   */
  public function index(): View
  {
    return view('auth.login.index');
  }

  /**
   * Handle login pengguna.
   * 
   * @param LoginRequest $request
   * @return RedirectResponse
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    $request->ensureIsNotRateLimited();
    $loginData = $request->toDto();

    if (!$this->authService->login($loginData)) {
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
