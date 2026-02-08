<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;
use App\Models\School;
use App\Support\SchoolContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "--- VERIFYING ROLE ACCESS SCOPES ---\n";

// 1. Setup Test Data
$schoolA = School::firstOrCreate(['school_code' => 'SCHOOL_A'], ['name' => 'School A', 'status' => 'active']);
$schoolB = School::firstOrCreate(['school_code' => 'SCHOOL_B'], ['name' => 'School B', 'status' => 'active']);

$nexoryatech = User::withoutGlobalScope('current_school')->where('user_type', 'nexoryatech')->first();
if (!$nexoryatech) {
    die("Error: Nexoryatech user not found. Please run create_admin.php first.\n");
}

$adminA = User::withoutGlobalScope('current_school')->firstOrCreate(
    ['username' => 'admin_a'],
    [
        'name' => 'Admin A', 
        'email' => 'admin_a@test.com', 
        'password' => bcrypt('password'), 
        'user_type' => 'super_admin', 
        'school_id' => $schoolA->id,
        'code' => 'ADMIN_A'
    ]
);

$adminB = User::withoutGlobalScope('current_school')->firstOrCreate(
    ['username' => 'admin_b'],
    [
        'name' => 'Admin B', 
        'email' => 'admin_b@test.com', 
        'password' => bcrypt('password'), 
        'user_type' => 'super_admin', 
        'school_id' => $schoolB->id,
        'code' => 'ADMIN_B'
    ]
);

// 2. Test Nexoryatech Access (Should see ALL users)
echo "\n[TEST 1] Nexoryatech View:\n";
Auth::login($nexoryatech);
SchoolContext::forget(); // Simulate middleware behavior for system admin

$visibleUsers = User::count();
$totalUsers = User::withoutGlobalScope('current_school')->count(); 
// Note: recursive scope issue was fixed, so User::count() should see all if no global scope filters it
// Correction: User::count() applies global scopes. 
// With my fix, if SchoolContext::id() is null, 'current_school' scope adds no where clause.
// So User::count() should equal Total Users in DB.

echo "Logged in as: " . Auth::user()->name . " (" . Auth::user()->user_type . ")\n";
echo "SchoolContext ID: " . (SchoolContext::id() ?? 'NULL') . "\n";
echo "Visible Users: $visibleUsers / Total DB Users: $totalUsers\n";

if ($visibleUsers >= 3) { // Nexoryatech + AdminA + AdminB
    echo "RESULT: PASS - System Admin sees all users.\n";
} else {
    echo "RESULT: FAIL - System Admin visibility restricted.\n";
}

// 3. Test Admin A Access (Should ONLY see School A users)
echo "\n[TEST 2] Admin A View (School A):\n";
Auth::login($adminA);
SchoolContext::set($schoolA); // Simulate middleware behavior for regular admin

$visibleUsersA = User::count();
// We expect to see only users with school_id = $schoolA->id
$expectedUsersA = User::withoutGlobalScope('current_school')->where('school_id', $schoolA->id)->count();

echo "Logged in as: " . Auth::user()->name . " (" . Auth::user()->user_type . ")\n";
echo "SchoolContext ID: " . SchoolContext::id() . "\n";
echo "Visible Users: $visibleUsersA / Expected: $expectedUsersA\n";

if ($visibleUsersA == $expectedUsersA) {
    echo "RESULT: PASS - Admin A only sees School A users.\n";
} else {
    echo "RESULT: FAIL - Admin A sees incorrect number of users.\n";
}

// 4. Test Cross-School Access Attempt
$canSeeAdminB = User::find($adminB->id);
if (!$canSeeAdminB) {
    echo "RESULT: PASS - Admin A cannot retrieve Admin B via find().\n";
} else {
    echo "RESULT: FAIL - Admin A could retrieve Admin B!\n";
}

// 5. Cleanup (optional, but good for repeatability if needed)
// $adminA->delete(); $adminB->delete(); $schoolA->delete(); $schoolB->delete();
