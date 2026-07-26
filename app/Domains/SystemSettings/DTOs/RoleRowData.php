<?php

namespace App\Domains\SystemSettings\DTOs;

use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Role;

class RoleRowData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $name,
    public readonly int $permissions_count,
    public readonly string $show_url,
    public readonly string $edit_url,
  ) {}

  public static function fromModel(Role $role): self
  {
    return new self(
      id: $role->id,
      name: $role->name,
      permissions_count: $role->permissions_count,
      show_url: route('backdoor.system-settings.roles.show', $role->id),
      edit_url: route('backdoor.system-settings.roles.edit', $role->id),
    );
  }
}
