<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Domains\User\Enums\RoleType;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // TODO : tambahkan permission ke masing" role
        
        // * Role superadmin untuk permissionnya didaftarkan di AppServiceProvider
        
        // create roles
        $roleSuperadmin = Role::create(['name' => RoleType::SUPERADMIN->value]);
        $roleAdmin = Role::create(['name' => RoleType::ADMIN->value]);
        $roleOwner = Role::create(['name' => RoleType::OWNER->value]);
        $roleUser = Role::create(['name' => RoleType::USER->value]);
    }
}
