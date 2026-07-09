<?php

namespace App\Domains\User\Traits;

use App\Domains\User\Models\User;
use Illuminate\Http\RedirectResponse;

trait RedirectsUsers
{
  public function redirectPath(User $user): RedirectResponse
  {

    // Jika memiliki izin masuk ke halaman admin, lempar be halaman admin
    // Jika tidak, lempar ke halaman utama
    $routeName = $user->can('dashboard-admin-view')
      ? 'backdoor.dashboard'
      : 'frontdoor.home';

    return redirect()->intended(route($routeName));
  }
}
