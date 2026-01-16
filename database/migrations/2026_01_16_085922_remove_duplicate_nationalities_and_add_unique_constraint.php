<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Clean up duplicate nationalities
        // We group by name and keep the one with the smallest ID
        $duplicates = DB::table('nationalities')
            ->select('name', DB::raw('MIN(id) as min_id'))
            ->groupBy('name')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('nationalities')
                ->where('name', $duplicate->name)
                ->where('id', '>', $duplicate->min_id)
                ->delete();
        }

        // 2. Add unique constraint to the name column
        Schema::table('nationalities', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nationalities', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
