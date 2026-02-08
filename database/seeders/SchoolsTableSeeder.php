<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolsTableSeeder extends Seeder
{
    public function run()
    {
        // DB::table('schools')->delete(); // Commented out to prevent deletion

        $schools = [
            [
                'name' => 'System Admin School',
                'school_code' => 'SYS_ADMIN',
                'status' => 'active',
                'settings' => json_encode(['theme_color' => '#333333']),
            ],
            [
                'name' => 'NEXORYATECH ACADEMY',
                'school_code' => 'NEXORYATECH',
                'status' => 'active',
                'settings' => json_encode(['theme_color' => '#667eea']),
            ]
        ];

        foreach ($schools as $school) {
            DB::table('schools')->updateOrInsert(
                ['school_code' => $school['school_code']], // unique key to check
                $school // values to update/insert
            );
        }
    }
}
