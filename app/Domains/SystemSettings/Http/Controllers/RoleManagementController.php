<?php

namespace App\Domains\SystemSettings\Http\Controllers;

use App\Domains\SystemSettings\DTOs\RoleRowData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
  /**
   * Halaman daftar role (view).
   */
  public function index(): View
  {
    return view('backdoor.system-settings.role.index');
  }

  /**
   * JSON endpoint untuk useDatatable.
   * Filter: role superadmin tidak pernah muncul.
   */
  public function data(Request $request): JsonResponse
  {
    $search = $request->input('search', '');
    $limit = max(1, min($request->integer('limit', 10), 100));

    $query = Role::withCount('permissions')
      ->where('name', '!=', 'superadmin')
      ->when($search, fn($q) => $q->where('name', 'ilike', "%{$search}%"))
      ->latest('created_at');

    $paginated = $query->paginate($limit);

    return response()->json([
      'data' => RoleRowData::collect($paginated->items()),
      'current_page' => $paginated->currentPage(),
      'last_page' => $paginated->lastPage(),
      'total' => $paginated->total(),
    ]);
  }

  /**
   * Form tambah role (view).
   * Mengirim semua permission yang sudah dikelompokkan ke view.
   */
  public function create(): View
  {
    $groupedPermissions = Permission::all()->groupBy(
      fn($permission) => explode('-', $permission->name)[0]
    );

    return view('backdoor.system-settings.role.create', compact('groupedPermissions'));
  }

  /**
   * Simpan role baru ke database.
   */
  public function store(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
      'permissions' => ['array'],
      'permissions.*' => ['string', 'exists:permissions,name'],
    ]);

    $role = Role::create(['name' => $validated['name']]);
    $role->syncPermissions($validated['permissions'] ?? []);

    $this->logActivity($role, 'created', [], [
      'name' => $role->name,
      'permissions' => $validated['permissions'] ?? [],
    ]);

    return response()->json([
      'message' => "Role \"{$role->name}\" berhasil dibuat.",
      'redirect' => route('backdoor.system-settings.roles.index'),
    ], 201);
  }

  /**
   * Halaman detail role (view — 100% Blade statis).
   */
  public function show(Role $role): View
  {
    $role->load('permissions');

    $groupedPermissions = $role->permissions->groupBy(
      fn($permission) => explode('-', $permission->name)[0]
    );

    return view('backdoor.system-settings.role.show', compact('role', 'groupedPermissions'));
  }

  /**
   * Form edit role (view).
   * Proteksi: superadmin tidak bisa diedit.
   */
  public function edit(Role $role): View
  {
    abort_if(strtolower($role->name) === 'superadmin', 403);

    $role->load('permissions');

    $groupedPermissions = Permission::all()->groupBy(
      fn($permission) => explode('-', $permission->name)[0]
    );

    $activePermissions = $role->permissions->pluck('name')->toArray();

    return view('backdoor.system-settings.role.edit', compact('role', 'groupedPermissions', 'activePermissions'));
  }

  /**
   * Update role yang ada.
   * Proteksi hardcode: superadmin tidak bisa dimodifikasi.
   */
  public function update(Request $request, Role $role): JsonResponse
  {
    abort_if(strtolower($role->name) === 'superadmin', 403, 'Role Superadmin tidak dapat dimodifikasi.');

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:50', "unique:roles,name,{$role->id}"],
      'permissions' => ['array'],
      'permissions.*' => ['string', 'exists:permissions,name'],
    ]);

    $oldName = $role->name;
    $oldPermissions = $role->permissions->pluck('name')->toArray();

    $role->update(['name' => $validated['name']]);
    $role->syncPermissions($validated['permissions'] ?? []);

    $this->logActivity($role, 'updated', [
      'name' => $oldName,
      'permissions' => $oldPermissions,
    ], [
      'name' => $role->name,
      'permissions' => $validated['permissions'] ?? [],
    ]);

    return response()->json([
      'message' => "Role \"{$role->name}\" berhasil diperbarui.",
      'redirect' => route('backdoor.system-settings.roles.index'),
    ]);
  }

  /**
   * Hapus role.
   * Proteksi hardcode: superadmin tidak bisa dihapus.
   */
  public function destroy(Role $role): JsonResponse
  {
    abort_if(strtolower($role->name) === 'superadmin', 403, 'Role Superadmin tidak dapat dihapus.');

    $name = $role->name;
    $permissions = $role->permissions->pluck('name')->toArray();
    $role->delete();

    $this->logActivity($role, 'deleted', [
      'name' => $name,
      'permissions' => $permissions,
    ]);

    return response()->json(['message' => "Role \"{$name}\" berhasil dihapus."]);
  }

  /**
   * Helper untuk mencatat activity log beserta daftar permissions.
   */
  private function logActivity(Role $role, string $action, array $old = [], array $attributes = []): void
  {
    $properties = [];

    if (!empty($old)) {
      $properties['old'] = $old;
    }

    if (!empty($attributes)) {
      $properties['attributes'] = $attributes;
    }

    activity()
      ->performedOn($role)
      ->useLog('role')
      ->withProperties($properties)
      ->log($action);
  }
}
