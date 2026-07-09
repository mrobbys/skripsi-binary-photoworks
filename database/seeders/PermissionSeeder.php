<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // TODO : tambahkan permission yang belum ditambahkan
        
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // akses dashboard admin
        Permission::create(['name' => 'dashboard-admin-view']);
        
        // data master category
        Permission::create(['name' => 'category-master-view']);
        Permission::create(['name' => 'category-master-create']);
        Permission::create(['name' => 'category-master-update']);
        Permission::create(['name' => 'category-master-delete']);

        // data master package & variant
        Permission::create(['name' => 'package-variant-master-view']);
        Permission::create(['name' => 'package-variant-master-create']);
        Permission::create(['name' => 'package-variant-master-update']);
        Permission::create(['name' => 'package-variant-master-delete']);

        // data master background
        Permission::create(['name' => 'background-master-view']);
        Permission::create(['name' => 'background-master-create']);
        Permission::create(['name' => 'background-master-update']);
        Permission::create(['name' => 'background-master-delete']);

        // data master add-ons
        Permission::create(['name' => 'addon-master-view']);
        Permission::create(['name' => 'addon-master-create']);
        Permission::create(['name' => 'addon-master-update']);
        Permission::create(['name' => 'addon-master-delete']);

        // data master schedule
        Permission::create(['name' => 'schedule-master-view']);
        Permission::create(['name' => 'schedule-master-update']);
        
        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
