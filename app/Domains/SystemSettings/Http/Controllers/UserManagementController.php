<?php

namespace App\Domains\SystemSettings\Http\Controllers;

use App\Domains\SystemSettings\DTOs\UserData;
use App\Domains\SystemSettings\DTOs\UserRowData;
use App\Domains\SystemSettings\Http\Requests\StoreUserRequest;
use App\Domains\SystemSettings\Http\Requests\UpdateUserRequest;
use App\Domains\SystemSettings\Services\UserManagementService;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
  public function __construct(
    private readonly UserManagementService $service,
  ) {}

  public function index(): View
  {
    $roles = Role::where('name', '!=', 'superadmin')->get(['id', 'name']);

    return view('backdoor.system-settings.user.index', compact('roles'));
  }

  /**
   * JSON endpoint untuk useDatatable.
   * Filter: superadmin tidak pernah muncul.
   */
  public function data(Request $request): JsonResponse
  {
    $search = $request->input('search', '');
    $limit  = max(1, min($request->integer('limit', 10), 100));

    $query = User::with('roles')
      ->whereDoesntHave('roles', fn($q) => $q->where('name', 'superadmin'))
      ->when($search, function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
          $inner->where('name', 'ilike', "%{$search}%")
            ->orWhere('email', 'ilike', "%{$search}%")
            ->orWhere('phone', 'ilike', "%{$search}%");
        });
      })
      ->latest('created_at');

    $paginated = $query->paginate($limit);

    return response()->json([
      'data' => UserRowData::collect($paginated->items()),
      'current_page' => $paginated->currentPage(),
      'last_page' => $paginated->lastPage(),
      'total' => $paginated->total(),
    ]);
  }

  /**
   * Simpan user baru.
   */
  public function store(StoreUserRequest $request): JsonResponse
  {
    $userData = UserData::fromRequest($request);
    $user = $this->service->store($userData);

    return response()->json([
      'status'  => 'success',
      'message' => "User \"{$user->name}\" berhasil ditambahkan.",
    ], 201);
  }

  /**
   * Update data user yang ada.
   */
  public function update(UpdateUserRequest $request, User $user): JsonResponse
  {
    abort_if($user->hasRole('superadmin'), 403, 'Tidak dapat mengubah data superadmin.');

    $userData = UserData::fromRequest($request);
    $this->service->update($user, $userData);

    return response()->json([
      'status'  => 'success',
      'message' => "Data user \"{$user->name}\" berhasil diperbarui.",
    ]);
  }

  /**
   * Hard delete user.
   * Proteksi self-harm: tidak bisa menghapus akun sendiri.
   * Activity log eksplisit karena trait log `deleted` event tanpa context yang cukup.
   */
  public function destroy(User $user): JsonResponse
  {
    abort_if($user->hasRole('superadmin'), 403, 'Tidak dapat menghapus superadmin.');

    abort_if($user->id === Auth::id(), 403, 'Tidak dapat menghapus akun Anda sendiri.');

    $name = $user->name;

    $this->service->destroy($user);

    // Explicit activity log untuk delete dengan konteks yang jelas
    activity('user')
      ->causedBy(Auth::user())
      ->withProperties(['deleted_name' => $name])
      ->log('deleted');

    return response()->json([
      'status'  => 'success',
      'message' => "User \"{$name}\" berhasil dihapus.",
    ]);
  }

  /**
   * Reset password user ke Password123.
   * Activity log eksplisit karena auto-log hanya mencatat hash baru yang tidak bermakna.
   */
  public function resetPassword(User $user): JsonResponse
  {
    abort_if($user->hasRole('superadmin'), 403, 'Tidak dapat mereset password superadmin.');
    
    $this->service->resetPassword($user);

    activity('user')
      ->causedBy(Auth::user())
      ->performedOn($user)
      ->log('reset-password');

    return response()->json([
      'status'  => 'success',
      'message' => "Password \"{$user->name}\" berhasil direset ke Password123.",
    ]);
  }
}
