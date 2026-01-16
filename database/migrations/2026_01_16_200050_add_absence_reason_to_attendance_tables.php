<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbsenceReasonToAttendanceTables extends Migration
{
    public function up()
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('absence_reason', 50)->nullable()->after('status');
        });

        Schema::table('attendance_overrides', function (Blueprint $table) {
            $table->string('previous_absence_reason', 50)->nullable()->after('previous_status');
            $table->string('new_absence_reason', 50)->nullable()->after('new_status');
        });
    }

    public function down()
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('absence_reason');
        });

        Schema::table('attendance_overrides', function (Blueprint $table) {
            $table->dropColumn(['previous_absence_reason', 'new_absence_reason']);
        });
    }
}
