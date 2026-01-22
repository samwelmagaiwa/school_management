<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;
use App\Models\Permission;

class HrRoleSeeder extends Seeder
{
    public function run()
    {
        // Ensure Role Exists
        $role = UserType::firstOrCreate(
            ['title' => 'hr'],
            ['name' => 'Human Resource', 'level' => 5]
        );

        // Get Permissions
        $perms = Permission::whereIn('name', [
            'staff.manage',
            'leave.manage',
            'attendance.manage',
            'payroll.manage',
            'hr.reports.view',
            'dept.manage'
        ])->pluck('id');

        // Sync
        $role->permissions()->sync($perms);
    }
}
