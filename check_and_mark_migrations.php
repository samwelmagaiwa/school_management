<?php
/**
 * Check and mark already-applied migrations as complete
 * Run this with: php check_and_mark_migrations.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking for already-applied migrations...\n\n";

$lastBatch = DB::table('migrations')->max('batch') ?: 0;
$migrationsToCheck = [];

// Check if marks table has the new columns
if (Schema::hasColumn('marks', 'is_absent')) {
    $migrationsToCheck[] = '2027_01_23_110813_add_absence_tracking_and_audit_to_marks_and_exam_records';
    echo "✓ Found: marks table already has absence tracking columns\n";
}

// Check if grades table has points column
if (Schema::hasColumn('grades', 'points')) {
    $migrationsToCheck[] = '2027_01_23_120141_add_points_to_grades_and_exam_records';
    echo "✓ Found: grades table already has points column\n";
}

// Check if inventory_stock_movements table exists
if (Schema::hasTable('inventory_stock_movements')) {
    $migrationsToCheck[] = '2027_01_23_164648_create_inventory_stock_movements_table';
    echo "✓ Found: inventory_stock_movements table exists\n";
}

// Check if transport_trips has completion fields
if (Schema::hasColumn('transport_trips', 'end_time')) {
    $migrationsToCheck[] = '2027_01_23_170212_add_completion_fields_to_transport_trips_table';
    echo "✓ Found: transport_trips has completion fields\n";
}

// Check if transport_fuel_logs table exists
if (Schema::hasTable('transport_fuel_logs')) {
    $migrationsToCheck[] = '2027_01_23_170213_create_transport_fuel_logs_table';
    echo "✓ Found: transport_fuel_logs table exists\n";
}

// Check if transport_maintenance_logs table exists
if (Schema::hasTable('transport_maintenance_logs')) {
    $migrationsToCheck[] = '2027_01_23_170214_create_transport_maintenance_logs_table';
    echo "✓ Found: transport_maintenance_logs table exists\n";
}

// Check if inventory_items has status column
if (Schema::hasColumn('inventory_items', 'status')) {
    $migrationsToCheck[] = '2027_01_23_173923_add_status_to_inventory_items_table';
    echo "✓ Found: inventory_items has status column\n";
}

// Check if staff_departments table exists
if (Schema::hasTable('staff_departments')) {
    $migrationsToCheck[] = '2027_01_23_200427_create_staff_departments_table';
    echo "✓ Found: staff_departments table exists\n";
}

// Check if staff_designations table exists
if (Schema::hasTable('staff_designations')) {
    $migrationsToCheck[] = '2027_01_23_200433_create_staff_designations_table';
    echo "✓ Found: staff_designations table exists\n";
}

// Check if staff_records has new HR fields
if (Schema::hasColumn('staff_records', 'employment_type')) {
    $migrationsToCheck[] = '2027_01_23_200436_add_hr_fields_to_staff_records_table';
    echo "✓ Found: staff_records has new HR fields\n";
}

// Check if leave management tables exist
if (Schema::hasTable('leave_types')) {
    $migrationsToCheck[] = '2027_01_23_200440_create_leave_management_tables';
    echo "✓ Found: leave management tables exist\n";
}

// Check if staff_attendances table exists
if (Schema::hasTable('staff_attendances')) {
    $migrationsToCheck[] = '2027_01_23_200443_create_staff_attendances_table';
    echo "✓ Found: staff_attendances table exists\n";
}

echo "\n";

if (empty($migrationsToCheck)) {
    echo "ℹ No already-applied migrations found. All pending migrations need to run.\n";
} else {
    echo "Marking " . count($migrationsToCheck) . " migration(s) as complete...\n\n";
    
    foreach ($migrationsToCheck as $migration) {
        $exists = DB::table('migrations')->where('migration', $migration)->exists();
        
        if (!$exists) {
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $lastBatch
            ]);
            echo "✓ Marked: $migration\n";
        } else {
            echo "- Skip: $migration (already marked)\n";
        }
    }
    
    echo "\n✅ Migration table updated!\n";
}

echo "\nYou can now run: php artisan migrate\n";
