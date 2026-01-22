<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->integer('point')->nullable()->after('mark_to');
        });

        Schema::table('exam_records', function (Blueprint $table) {
            $table->integer('points')->nullable()->after('total');
            $table->string('division')->nullable()->after('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('point');
        });

        Schema::table('exam_records', function (Blueprint $table) {
            $table->dropColumn(['points', 'division']);
        });
    }
};
