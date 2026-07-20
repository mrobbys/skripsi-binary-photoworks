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
        
        // jadwal sesi
        Permission::create(['name' => 'scheduleSession-view']);        
        Permission::create(['name' => 'scheduleSession-update']);        
        Permission::create(['name' => 'scheduleSession-calendar']);        

        // manajemen pemesanan
        Permission::create(['name' => 'booking-management-view']);
        Permission::create(['name' => 'booking-management-create']);
        Permission::create(['name' => 'booking-management-update']);
        Permission::create(['name' => 'booking-management-delete']);
        
        // data master category
        Permission::create(['name' => 'category-master-view']);
        Permission::create(['name' => 'category-master-create']);
        Permission::create(['name' => 'category-master-update']);
        Permission::create(['name' => 'category-master-delete']);

        // data master package & variant
        Permission::create(['name' => 'packageVariant-master-view']);
        Permission::create(['name' => 'packageVariant-master-create']);
        Permission::create(['name' => 'packageVariant-master-update']);
        Permission::create(['name' => 'packageVariant-master-delete']);

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
        
        // ulasan klien
        Permission::create(['name' => 'review-client-view']);
        Permission::create(['name' => 'review-client-delete']);

        // laporan
        Permission::create(['name' => 'report-view']);

        // manajemen user
        Permission::create(['name' => 'user-management-view']);
        Permission::create(['name' => 'user-management-create']);
        Permission::create(['name' => 'user-management-update']);
        Permission::create(['name' => 'user-management-delete']);
        
        // manajemen role
        Permission::create(['name' => 'role-management-view']);
        Permission::create(['name' => 'role-management-create']);
        Permission::create(['name' => 'role-management-update']);
        Permission::create(['name' => 'role-management-delete']);

        // activity logs
        Permission::create(['name' => 'activityLog-management-view']);
        
        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
