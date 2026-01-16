<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_records', function (Blueprint $table) {
            if (! Schema::hasColumn('student_records', 'dorm_room_id')) {
                $table->unsignedBigInteger('dorm_room_id')->nullable()->after('dorm_id');
            }

            if (! Schema::hasColumn('student_records', 'dorm_bed_id')) {
                $table->unsignedBigInteger('dorm_bed_id')->nullable()->after('dorm_room_id');
            }

            if (! Schema::hasColumn('student_records', 'current_allocation_id')) {
                $table->unsignedBigInteger('current_allocation_id')->nullable()->after('dorm_bed_id');
            }

            if (! Schema::hasColumn('student_records', 'allocation_status')) {
                $table->enum('allocation_status', ['unassigned', 'assigned', 'vacated'])->default('unassigned')->after('current_allocation_id');
            }

            $table->foreign('dorm_room_id')->references('id')->on('dorm_rooms')->nullOnDelete();
            $table->foreign('dorm_bed_id')->references('id')->on('dorm_beds')->nullOnDelete();
            $table->foreign('current_allocation_id')->references('id')->on('dorm_allocations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_records', function (Blueprint $table) {
            if (Schema::hasColumn('student_records', 'dorm_room_id')) {
                $table->dropForeign(['dorm_room_id']);
            }

            if (Schema::hasColumn('student_records', 'dorm_bed_id')) {
                $table->dropForeign(['dorm_bed_id']);
            }

            if (Schema::hasColumn('student_records', 'current_allocation_id')) {
                $table->dropForeign(['current_allocation_id']);
            }

            foreach (['dorm_room_id', 'dorm_bed_id', 'current_allocation_id', 'allocation_status'] as $column) {
                if (Schema::hasColumn('student_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
