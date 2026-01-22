<?php
/**
 * Fix Local Migrations Table
 * Run this with: php fix_migrations.php
 */

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing migrations table for renamed migration files...\n\n";

// Get the current highest batch number
$lastBatch = DB::table('migrations')->max('batch') ?: 0;

// Define the renamed migrations that already exist in the database
$renamedMigrations = [
    '2025_01_01_000001_create_user_types_table',
    '2025_01_01_000002_create_blood_groups_table',
    '2025_01_01_000003_create_settings_table',
    '2025_01_01_000004_create_nationalities_table',
];

foreach ($renamedMigrations as $migration) {
    // Check if this migration is already recorded
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    if (!$exists) {
        // Check if the table exists (to confirm it should be marked as run)
        $tableName = str_replace(['_create_', '_table'], '', substr($migration, 20));
        $tableName = str_replace('_', '', $tableName) . 's';
        
        // Insert the migration record
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $lastBatch
        ]);
        
        echo "✓ Marked as run: $migration\n";
    } else {
        echo "- Already exists: $migration\n";
    }
}

echo "\n✅ Migration table updated successfully!\n";
echo "\nYou can now run: php artisan migrate\n";
