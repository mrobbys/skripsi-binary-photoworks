<?php

namespace App\Domains\User\Http\Controllers;

use App\Domains\User\Services\LoginService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{

  public function __construct(protected LoginService $loginService) {}

  /**
   * Function logout.
   *
   * Setelah logout, pengguna dikembalikan ke halaman login.
   * @param Request $request
   * @return RedirectResponse
   */
  public function destroy(Request $request): RedirectResponse
  {
    $this->loginService->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')
      ->with('toast', $this->toast(
        title: 'Logout Berhasil'
      ));
  }
}
