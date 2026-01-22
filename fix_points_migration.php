<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Check for the specific migrations that might be partially applied
$lastBatch = DB::table('migrations')->max('batch') ?: 0;

// Check if exam_records has points column
if (Schema::hasColumn('exam_records', 'points')) {
    $migration = '2027_01_23_120141_add_points_to_grades_and_exam_records';
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $lastBatch
        ]);
        echo "✓ Marked: $migration\n";
    } else {
        echo "Already marked: $migration\n";
    }
} else {
    echo "Column 'points' not found in exam_records table. Migration needs to run.\n";
}

echo "\nDone!\n";
