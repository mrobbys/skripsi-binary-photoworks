<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create roles and assign permissions
        $roleSuperadmin = Role::create(['name' => 'superadmin']);
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleOwner = Role::create(['name' => 'owner']);

        $roleUser = Role::create(['name' => 'user']);
        $roleUser->givePermissionTo('test');
    }
}
