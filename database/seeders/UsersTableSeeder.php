<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed only the core demo users; avoid creating extra generated users
        $this->createNewUsers();
    }

    protected function createNewUsers()
    {
        $password = Hash::make('12345678'); // User provided password

        // Fetch School IDs (Ensure SchoolsTableSeeder ran first!)
        $nexoryatechID = DB::table('schools')->where('school_code', 'NEXORYATECH')->value('id');
        $sysAdminID = DB::table('schools')->where('school_code', 'SYS_ADMIN')->value('id');

        if (!$nexoryatechID || !$sysAdminID) {
            echo "ERROR: Schools not found! Please run SchoolsTableSeeder first.\n";
            return;
        }

        $users = [

            // System Admin
            [
                'name' => 'System Admin',
                'email' => 'admin@nexoryatech.com',
                'username' => 'nexoryatech',
                'password' => $password,
                'user_type' => 'nexoryatech',
                'code' => 'SYSADMIN',
                'remember_token' => Str::random(10),
                'school_id' => $sysAdminID,
            ],

            ['name' => 'Nexoryatech',
                'email' => 'tech@nexoryatech.com',
                'username' => 'tech',
                'password' => $password,
                'user_type' => 'super_admin',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
                'school_id' => $nexoryatechID,
            ],

            ['name' => 'Admin KORA',
            'email' => 'admin@gmail.com',
            'password' => $password,
            'user_type' => 'admin',
            'username' => 'admin',
            'code' => strtoupper(Str::random(10)),
            'remember_token' => Str::random(10),
            'school_id' => $nexoryatechID,
            ],

            ['name' => 'Teacher Chike',
                'email' => 'teacher@gmail.com',
                'user_type' => 'teacher',
                'username' => 'teacher',
                'password' => $password,
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
                'school_id' => $nexoryatechID,
            ],

            ['name' => 'Parent Kaba',
                'email' => 'parent@gmail.com',
                'user_type' => 'parent',
                'username' => 'parent',
                'password' => $password,
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
                'school_id' => $nexoryatechID,
            ],

            ['name' => 'Accountant Jeff',
                'email' => 'accountant@gmail.com',
                'user_type' => 'accountant',
                'username' => 'accountant',
                'password' => $password,
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
                'school_id' => $nexoryatechID,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
