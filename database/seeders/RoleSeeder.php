<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Domains\Auth\Enums\RoleType;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create roles and assign permissions
        $roleSuperadmin = Role::create(['name' => RoleType::SUPERADMIN->value]);
        $roleAdmin = Role::create(['name' => RoleType::ADMIN->value]);
        $roleOwner = Role::create(['name' => RoleType::OWNER->value]);
        $roleUser = Role::create(['name' => RoleType::USER->value]);
        
        $roleUser->givePermissionTo('test');
    }
}
