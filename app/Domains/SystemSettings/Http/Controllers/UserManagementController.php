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
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

#[Middleware('permission:user-management-view', only: ['index', 'data'])]
#[Middleware('permission:user-management-create', only: ['store'])]
#[Middleware('permission:user-management-update', only: ['update'])]
#[Middleware('permission:user-management-delete', only: ['destroy', 'resetPassword'])]
class UserManagementController extends Controller
{
  public function __construct(
    private readonly UserManagementService $service,
  ) {}

  /**
   * Abort jika role superadmin.
   * @param User $user
   * @param string $action
   */
  private function abortIfSuperadmin(User $user, string $action): void
  {
    abort_if($user->hasRole('superadmin'), 403, "Tidak dapat {$action} superadmin.");
  }

  public function index(): View
  {
    // Kirim list role yang tersedia ke view, kecuali role superadmin
    $roles = Role::where('name', '!=', 'superadmin')->get(['id', 'name']);
    return view('backdoor.system-settings.user.index', compact('roles'));
  }

  /**
   * JSON endpoint untuk useDatatable.
   * Filter: superadmin tidak pernah muncul.
   * @param Request $request
   */
  public function data(Request $request): JsonResponse
  {
    $search = $request->query('search');
    $limit  = max(1, min($request->integer('limit', 10), 100));

    $paginated = $this->service->searchQuery($search)->paginate($limit);
    
    return response()->json([
      'data' => UserRowData::collect($paginated->items()),
      'current_page' => $paginated->currentPage(),
      'last_page' => $paginated->lastPage(),
      'total' => $paginated->total(),
    ]);
  }

  /**
   * Simpan user baru.
   * @param StoreUserRequest $request
   */
  public function store(StoreUserRequest $request): JsonResponse
  {
    try {
      $user = $this->service->store(UserData::fromRequest($request));

      return $this->successResponse("User \"{$user->name}\" berhasil ditambahkan.", null, 201);
    } catch (\RuntimeException $e) {
      return $this->errorResponse($e->getMessage(), 422);
    } catch (\Exception $e) {
      return $this->errorResponse('Terjadi kesalahan server');
    }
  }

  /**
   * Update data user yang ada.
   * @param UpdateUserRequest $request
   * @param User $user
   */
  public function update(UpdateUserRequest $request, User $user): JsonResponse
  {
    $this->abortIfSuperadmin($user, 'mengubah data');

    try {
      $this->service->update($user, UserData::fromRequest($request));

      return $this->successResponse("Data user \"{$user->name}\" berhasil diperbarui.");
    } catch (\RuntimeException $e) {
      return $this->errorResponse($e->getMessage(), 422);
    } catch (\Exception $e) {
      return $this->errorResponse('Terjadi kesalahan server');
    }
  }

  /**
   * Delete user.
   * @param User $user
   */
  public function destroy(User $user): JsonResponse
  {
    $this->abortIfSuperadmin($user, 'menghapus');
    abort_if($user->id === Auth::id(), 403, 'Tidak dapat menghapus akun Anda sendiri.');

    try {
      $this->service->destroy($user);

      return $this->successResponse("User \"{$user->name}\" berhasil dihapus.");
    } catch (\RuntimeException $e) {
      return $this->errorResponse($e->getMessage(), 422);
    } catch (\Exception $e) {
      return $this->errorResponse('Terjadi kesalahan server');
    }
  }

  /**
   * Reset password user ke Password123.
   * @param User $user
   */
  public function resetPassword(User $user): JsonResponse
  {
    $this->abortIfSuperadmin($user, 'mereset password');

    try {
      $this->service->resetPassword($user);

      return $this->successResponse("Password \"{$user->name}\" berhasil direset ke Password123.");
    } catch (\RuntimeException $e) {
      return $this->errorResponse($e->getMessage(), 422);
    } catch (\Exception $e) {
      return $this->errorResponse('Terjadi kesalahan server');
    }
  }
}
