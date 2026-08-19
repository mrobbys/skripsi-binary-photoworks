<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Domains\User\Enums\RoleType;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // * Role superadmin untuk permissionnya didaftarkan di AppServiceProvider
        $roleSuperadmin = Role::firstOrCreate(['name' => RoleType::SUPERADMIN->value]);
        $roleAdmin = Role::firstOrCreate(['name' => RoleType::ADMIN->value]);
        $roleOwner = Role::firstOrCreate(['name' => RoleType::OWNER->value]);
        $roleUser = Role::firstOrCreate(['name' => RoleType::USER->value]);

        // Memberikan permission ke role admin
        // Untuk operasional studio penuh (kecuali system settings)
        $roleAdmin->syncPermissions([
            'dashboard-admin-view',

            'scheduleSession-view',
            'scheduleSession-calendar',

            'booking-management-view',
            'booking-management-create',
            'booking-management-update',

            'clientData-view',

            'category-master-view',
            'category-master-create',
            'category-master-update',
            'category-master-delete',

            'packageVariant-master-view',
            'packageVariant-master-create',
            'packageVariant-master-update',
            'packageVariant-master-delete',

            'background-master-view',
            'background-master-create',
            'background-master-update',
            'background-master-delete',

            'addon-master-view',
            'addon-master-create',
            'addon-master-update',
            'addon-master-delete',

            'schedule-master-view',
            'schedule-master-update',

            'review-client-view',
            'review-client-delete',

            'report-view',
        ]);

        // Memberikan permission ke role owner
        // Untuk monitoring bisnis, audit activity log, dan cetak laporan
        $roleOwner->syncPermissions([
            'dashboard-admin-view',

            'scheduleSession-view',
            'scheduleSession-calendar',

            'booking-management-view',

            'clientData-view',

            'category-master-view',
            'packageVariant-master-view',
            'background-master-view',
            'addon-master-view',
            'schedule-master-view',

            'review-client-view',

            'report-view',

            'activityLog-management-view',
        ]);
    }
}
