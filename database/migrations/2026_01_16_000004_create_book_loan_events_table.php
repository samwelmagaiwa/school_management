<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookLoanEventsTable extends Migration
{
    public function up()
    {
        Schema::create('book_loan_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('book_loan_id');
            $table->unsignedInteger('performed_by');
            $table->string('event_type');
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->foreign('book_loan_id')->references('id')->on('book_loans')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('book_loan_events');
    }
}
