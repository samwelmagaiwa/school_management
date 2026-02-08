<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// 1. Test Finding User
$user = \App\User::withoutGlobalScope('current_school')->where('username', 'nexoryatech')->first();
echo "User Found: " . ($user ? 'Yes' : 'No') . "\n";
if ($user) {
    echo "User Type: " . $user->user_type . "\n";
    echo "School ID: " . $user->school_id . "\n";
}

$credentials = [
    'username' => 'nexoryatech',
    'password' => 'password'
    // 'school_id' is NOT included here, simulating LoginController logic for system admin
];

// 2. Test Auth Attempt (Manual)
// Note: Auth::attempt automatically uses the configured provider.
if (Auth::attempt($credentials)) {
    echo "Auth::attempt SUCCESS\n";
    $user = Auth::user();
    echo "Logged in user: " . $user->username . "\n";
    
    // 3. Test Qs Helper
    echo "Is Nexoryatech: " . (\App\Helpers\Qs::userIsNexoryatech() ? 'Yes' : 'No') . "\n";
    echo "Is System Admin: " . (\App\Helpers\Qs::userIsSystemAdmin() ? 'Yes' : 'No') . "\n";
    
} else {
    echo "Auth::attempt FAILED\n";
}
