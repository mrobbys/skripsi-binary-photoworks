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
        $roleSuperadmin = Role::create(['name' => RoleType::SUPERADMIN->value]);
        $roleAdmin = Role::create(['name' => RoleType::ADMIN->value]);
        $roleOwner = Role::create(['name' => RoleType::OWNER->value]);
        $roleUser = Role::create(['name' => RoleType::USER->value]);

        // Memberikan permission ke role admin
        // Untuk permission kecuali 'system settings'
        $roleAdmin->givePermissionTo([
            'dashboard-admin-view',

            'scheduleSession-view',
            'scheduleSession-calendar',

            'booking-management-view',
            'booking-management-create',
            'booking-management-update',

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
        // Untuk permission hanya view saja
        $roleOwner->givePermissionTo([
            'dashboard-admin-view',

            'scheduleSession-view',
            'scheduleSession-calendar',

            'booking-management-view',

            'review-client-view',

            'report-view',
        ]);
    }
}
