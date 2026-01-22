<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class HrSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'Academic',
            'Administration',
            'Accounts',
            'Transport',
            'Hostel',
            'Security',
            'Cleaning',
            'IT Support',
        ];

        foreach ($departments as $d) {
            DB::table('staff_departments')->updateOrInsert(
                ['name' => $d],
                ['slug' => Str::slug($d), 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $designations = [
            'Principal',
            'Vice Principal',
            'Head of Department',
            'Senior Teacher',
            'Teacher',
            'Accountant',
            'Bus Driver',
            'Hostel Matron',
            'Security Guard',
            'Cleaner',
            'IT Technician',
            'Secretary',
        ];

        foreach ($designations as $d) {
            DB::table('staff_designations')->updateOrInsert(
                ['title' => $d],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
