<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceOverridesTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_overrides', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_record_id');
            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->text('previous_remarks')->nullable();
            $table->text('new_remarks')->nullable();
            $table->text('reason');
            $table->unsignedInteger('performed_by');
            $table->timestamps();

            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_overrides');
    }
}
