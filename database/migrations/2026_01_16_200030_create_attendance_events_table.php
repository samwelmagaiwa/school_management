<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceEventsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_session_id')->nullable();
            $table->unsignedBigInteger('attendance_record_id')->nullable();
            $table->string('action'); // session_created, records_marked, session_submitted, session_unlocked, record_overridden
            $table->unsignedInteger('performed_by');
            $table->string('role'); // snapshot of user_type at time of action
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->onDelete('cascade');
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['attendance_session_id', 'attendance_record_id'], 'att_events_session_record_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_events');
    }
}
