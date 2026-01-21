<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission; // Assuming these models exist as per PermissionsTableSeeder
use App\Models\UserType;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run()
    {
        // 1. Add New User Types
        $roles = [
            ['title' => 'storekeeper', 'name' => 'Storekeeper', 'level' => 5],
            ['title' => 'transport_officer', 'name' => 'Transport Officer', 'level' => 5],
            ['title' => 'auditor', 'name' => 'Auditor', 'level' => 5],
        ];

        foreach ($roles as $role) {
            DB::table('user_types')->updateOrInsert(
                ['title' => $role['title']],
                ['name' => $role['name'], 'level' => $role['level']]
            );
        }

        // 2. Define New Permissions
        $permissions = [
            // Inventory
            ['name' => 'inventory.manage', 'title' => 'Manage Inventory', 'description' => 'Can manage items, categories, and suppliers'],
            ['name' => 'inventory.stock_in', 'title' => 'Stock In', 'description' => 'Can add stock to inventory'],
            ['name' => 'inventory.issue', 'title' => 'Issue Items', 'description' => 'Can issue items to users'],
            
            // Warehouse
            ['name' => 'warehouse.manage', 'title' => 'Manage Warehouses', 'description' => 'Can creat/edit warehouses'],
            ['name' => 'warehouse.transfer', 'title' => 'Transfer Stock', 'description' => 'Can transfer stock between warehouses'],
            
            // Transport
            ['name' => 'transport.manage', 'title' => 'Manage Transport', 'description' => 'Can manage vehicles and drivers'],
            ['name' => 'transport.fuel', 'title' => 'Manage Fuel', 'description' => 'Can record fuel logs'],
            ['name' => 'transport.maintenance', 'title' => 'Manage Maintenance', 'description' => 'Can record maintenance logs'],
            ['name' => 'transport.trips', 'title' => 'Manage Trips', 'description' => 'Can record trips'],

            // Reports / Audit
            ['name' => 'reports.inventory', 'title' => 'Inventory Reports', 'description' => 'View inventory reports'],
            ['name' => 'reports.transport', 'title' => 'Transport Reports', 'description' => 'View transport reports'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name']], 
                array_merge($p, ['slug' => Str::slug($p['name'])])
            );
        }

        // 3. Assign Permissions to Roles
        $super_admin = UserType::where('title', 'super_admin')->first();
        $storekeeper = UserType::where('title', 'storekeeper')->first();
        $transport_officer = UserType::where('title', 'transport_officer')->first();
        $auditor = UserType::where('title', 'auditor')->first();

        $all_new_perms = Permission::whereIn('name', array_column($permissions, 'name'))->get();

        // Super Admin gets everything
        if ($super_admin) {
            $super_admin->permissions()->syncWithoutDetaching($all_new_perms->pluck('id'));
        }

        // Storekeeper
        if ($storekeeper) {
            $storekeeper_perms = $all_new_perms->filter(fn($p) => Str::startsWith($p->name, ['inventory.', 'warehouse.', 'reports.inventory']));
            $storekeeper->permissions()->sync($storekeeper_perms->pluck('id'));
        }

        // Transport Officer
        if ($transport_officer) {
            $transport_perms = $all_new_perms->filter(fn($p) => Str::startsWith($p->name, ['transport.', 'reports.transport']));
            $transport_officer->permissions()->sync($transport_perms->pluck('id'));
        }

        // Auditor
        if ($auditor) {
            $auditor_perms = $all_new_perms->filter(fn($p) => Str::startsWith($p->name, 'reports.'));
            $auditor->permissions()->sync($auditor_perms->pluck('id'));
        }
    }
}
