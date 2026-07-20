<?php

namespace App\Domains\SystemSettings\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Computed;

class RoleRowData extends Data
{
  #[Computed]
  public readonly string $show_url;
  #[Computed]
  public readonly string $edit_url;

  public function __construct(
    public readonly int $id,
    public readonly string $name,
    public readonly int $permissions_count = 0,
  ) {
    $this->show_url = route('backdoor.system-settings.roles.show', $this->id);
    $this->edit_url = route('backdoor.system-settings.roles.edit', $this->id);
  }
}
