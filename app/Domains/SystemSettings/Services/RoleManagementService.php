<?php

namespace App\Domains\SystemSettings\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementService
{
	/**
	 * Query pencarian role
	 * @param ?string $search
	 */
	public function searchQuery(?string $search): Builder
	{
		$query = Role::withCount('permissions')
			->where('name', '!=', 'superadmin')
			->latest('created_at');

		if ($search) {
			$query->where('name', 'ilike', "%{$search}%");
		}

		return $query;
	}

	/**
	 * Ambil data permission, kemudian grouping berdasarkan nama depan sebelum tanda '-'
	 */
	public function getGroupedPermissions(): Collection
	{
		return Permission::all()->groupBy(
			fn($permission) => explode('-', $permission->name)[0]
		);
	}

	/**
	 * Simpan role baru beserta permission
	 * @param string $name
	 * @param array $permissions
	 */
	public function store(string $name, array $permissions): Role
	{
		return DB::transaction(function () use ($name, $permissions) {
			$role = Role::create(['name' => $name]);
			$role->syncPermissions($permissions);

			$this->logActivity($role, 'created', [], [
				'name' => $role->name,
				'permissions' => $permissions,
			]);

			return $role;
		});
	}

	/**
	 * Update role beserta permission
	 * @param Role $role
	 * @param string $name
	 * @param array $permissions
	 */
	public function update(Role $role, string $name, array $permissions): Role
	{
		return DB::transaction(function () use ($role, $name, $permissions) {
			$oldName = $role->name;
			$oldPermissions = $role->permissions->pluck('name')->toArray();

			$role->update(['name' => $name]);
			$role->syncPermissions($permissions);

			$this->logActivity($role, 'updated', [
				'name' => $oldName,
				'permissions' => $oldPermissions,
			], [
				'name' => $role->name,
				'permissions' => $permissions,
			]);

			return $role;
		});
	}

	/**
	 * Hapus role beserta permission yang terkait
	 * @param Role $role
	 */
	public function destroy(Role $role): void
	{
		$name = $role->name;
		$permissions = $role->permissions->pluck('name')->toArray();

		DB::transaction(function () use ($role, $name, $permissions) {
			if ($role->users()->exists()) {
				throw new \RuntimeException('Role tidak dapat dihapus karena masih memiliki pengguna terkait.');
			}

			$role->delete();

			$this->logActivity($role, 'deleted', [
				'name' => $name,
				'permissions' => $permissions,
			]);
		});
	}

	/**
	 * Log aktivitas role
	 * @param Role $role
	 * @param string $action
	 * @param array $old
	 * @param array $attributes
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
