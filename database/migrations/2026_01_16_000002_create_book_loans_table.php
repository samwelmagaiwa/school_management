<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookLoansTable extends Migration
{
    public function up()
    {
        Schema::create('book_loans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('book_copy_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('borrowed_at');
            $table->date('due_at');
            $table->dateTime('returned_at')->nullable();
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->string('status')->default('active'); // active, returned, overdue, cancelled
            $table->timestamps();

            $table->foreign('book_copy_id')->references('id')->on('book_copies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('book_loans');
    }
}
