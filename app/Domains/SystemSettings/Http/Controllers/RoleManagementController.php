<?php

namespace App\Domains\SystemSettings\Http\Controllers;

use App\Domains\SystemSettings\DTOs\RoleRowData;
use App\Domains\SystemSettings\Http\Requests\StoreRoleRequest;
use App\Domains\SystemSettings\Http\Requests\UpdateRoleRequest;
use App\Domains\SystemSettings\Services\RoleManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

#[Middleware('permission:role-management-view', only: ['index', 'data',  'show'])]
#[Middleware('permission:role-management-create', only: ['create', 'store'])]
#[Middleware('permission:role-management-update', only: ['edit', 'update'])]
#[Middleware('permission:role-management-delete', only: ['destroy'])]
class RoleManagementController extends Controller
{
	public function __construct(
		private readonly RoleManagementService $service,
	) {}

	/**
	 * Abort jika role yang sedang login adalah superadmin
	 */
	private function abortIfSuperadmin(Role $role, string $action): void
	{
		abort_if(strtolower($role->name) === 'superadmin', 403, "Role Superadmin tidak dapat {$action}.");
	}

	public function index(): View
	{
		return view('backdoor.system-settings.role.index');
	}

	public function data(Request $request): JsonResponse
	{
		$search = $request->query('search');
		$limit = max(1, min($request->integer('limit', 10), 100));

		$paginated = $this->service->searchQuery($search)->paginate($limit);

		return response()->json([
			'data' => RoleRowData::collect($paginated->items()),
			'current_page' => $paginated->currentPage(),
			'last_page' => $paginated->lastPage(),
			'total' => $paginated->total(),
		]);
	}

	public function create(): View
	{
		// Ambil data permission
		$groupedPermissions = $this->service->getGroupedPermissions();
		return view('backdoor.system-settings.role.create', compact('groupedPermissions'));
	}

	public function store(StoreRoleRequest $request): JsonResponse
	{
		$validated = $request->validated();

		try {
			$role = $this->service->store($validated['name'], $validated['permissions'] ?? []);

			return $this->successResponse("Role \"{$role->name}\" berhasil dibuat.", null, 201, [
				'redirect' => route('backdoor.system-settings.roles.index'),
			]);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function show(Role $role): View
	{
		$role->load('permissions');

		$groupedPermissions = $role->permissions->groupBy(
			fn($permission) => explode('-', $permission->name)[0]
		);

		return view('backdoor.system-settings.role.show', compact('role', 'groupedPermissions'));
	}

	public function edit(Role $role): View
	{
		$this->abortIfSuperadmin($role, 'diedit');

		$role->load('permissions');

		// Ambil data permission
		$groupedPermissions = $this->service->getGroupedPermissions();
		// Ambil permission yang aktif dari role yang sedang diedit
		$activePermissions = $role->permissions->pluck('name')->toArray();

		return view('backdoor.system-settings.role.edit', compact('role', 'groupedPermissions', 'activePermissions'));
	}

	public function update(UpdateRoleRequest $request, Role $role): JsonResponse
	{
		$this->abortIfSuperadmin($role, 'dimodifikasi');

		$validated = $request->validated();

		try {
			$this->service->update($role, $validated['name'], $validated['permissions'] ?? []);

			return $this->successResponse("Role \"{$role->name}\" berhasil diperbarui.", null, 200, [
				'redirect' => route('backdoor.system-settings.roles.index'),
			]);
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}

	public function destroy(Role $role): JsonResponse
	{
		$this->abortIfSuperadmin($role, 'dihapus');

		try {
			$this->service->destroy($role);

			return $this->successResponse("Role \"{$role->name}\" berhasil dihapus.");
		} catch (\RuntimeException $e) {
			return $this->errorResponse($e->getMessage(), 422);
		} catch (\Exception $e) {
			return $this->errorResponse('Terjadi kesalahan server');
		}
	}
}
