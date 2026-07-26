<?php

namespace App\Domains\SystemSettings\Services;

use App\Domains\SystemSettings\DTOs\UserData;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
  /**
   * Query pencarian user.
   * Digunakan di halaman index table data user.
   * @param ?string $search
   */
  public function searchQuery(?string $search): Builder
  {
    $query = User::with('roles')
      ->whereDoesntHave('roles', fn($q) => $q->where('name', 'superadmin'))
      ->latest('created_at');

    if ($search) {
      $query->where(function ($q) use ($search) {
        $term = '%' . $search . '%';
        $q->where('name', 'ilike', $term)
          ->orWhere('email', 'ilike', $term)
          ->orWhere('phone', 'ilike', $term);
      });
    }

    return $query;
  }

  /**
   * Simpan user baru.
   * @param UserData $data
   */
  public function store(UserData $data): User
  {
    return DB::transaction(function () use ($data) {
      $user = User::create([
        'name' => $data->name,
        'email' => $data->email,
        'phone' => $data->phone,
        'password' => Hash::make('Password123'),
      ]);

      $user->syncRoles($data->role);

      return $user;
    });
  }

  /**
   * Update data user yang ada.
   * @param User $user
   * @param UserData $data
   */
  public function update(User $user, UserData $data): void
  {
    DB::transaction(function () use ($user, $data) {
      $user->update([
        'name' => $data->name,
        'email' => $data->email,
        'phone' => $data->phone,
      ]);

      $user->syncRoles($data->role);
    });
  }

  public function destroy(User $user): void
  {
    DB::transaction(function () use ($user) {
      if ($user->bookings()->exists()) {
        throw new \RuntimeException('User tidak dapat dihapus karena masih memiliki pemesanan terkait.');
      }

      $name = $user->name;
      $user->delete();

      activity('user')
        ->causedBy(Auth::user())
        ->withProperties(['deleted_name' => $name])
        ->log('deleted');
    });
  }

  public function resetPassword(User $user): void
  {
    DB::transaction(function () use ($user) {
      $user->update(['password' => Hash::make('Password123')]);

      activity('user')
        ->causedBy(Auth::user())
        ->performedOn($user)
        ->log('reset-password');
    });
  }
}
