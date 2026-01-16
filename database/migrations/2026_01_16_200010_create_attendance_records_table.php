<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_session_id');
            $table->unsignedInteger('student_id');
            $table->enum('status', ['unmarked', 'present', 'absent', 'late', 'excused'])->default('unmarked');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('marked_by');
            $table->timestamps();

            $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['attendance_session_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
}
