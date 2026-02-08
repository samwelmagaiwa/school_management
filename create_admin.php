<?php

use App\Models\School;
use Illuminate\Support\Facades\Hash;

// 1. Ensure a "System School" exists to satisfy the school_id constraint
$systemSchool = School::where('school_code', 'SYS_ADMIN')->first();

if (!$systemSchool) {
    echo "Creating 'System Admin School' placeholder...\n";
    $systemSchool = School::create([
        'name' => 'System Administration',
        'school_code' => 'SYS_ADMIN',
        'status' => 'inactive', // inactive so it doesn't show up in normal lists or allowing login
        'settings' => [],
    ]);
}

$user = \App\User::withoutGlobalScope('current_school')->where('username', 'nexoryatech')->first();

if ($user) {
    echo "User 'nexoryatech' already exists.\n";
    // Update school_id just in case
    if ($user->school_id !== $systemSchool->id) {
        $user->school_id = $systemSchool->id;
        $user->save();
        echo "Updated nexoryatech school_id linkage.\n";
    }
} else {
    $user = \App\User::withoutGlobalScope('current_school')->forceCreate([
        'name' => 'System Administrator',
        'email' => 'admin@nexoryatech.com',
        'username' => 'nexoryatech',
        'password' => Hash::make('password'),
        'user_type' => 'nexoryatech',
        'school_id' => $systemSchool->id,
        'code' => 'SYSADMIN',
        'dob' => '2000-01-01',
        'gender' => 'Male',
        'phone' => '0000000000',
        'address' => 'System',
    ]);
    echo "User 'nexoryatech' created successfully.\n";
}
