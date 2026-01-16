<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookLoansTable extends Migration
{
    public function up()
    {
        // If the table already exists (for example, from a partial/failed migration run),
        // just ensure the foreign keys are present and mark this migration as completed.
        if (Schema::hasTable('book_loans')) {
            if (Schema::hasTable('book_copies')) {
                try {
                    Schema::table('book_loans', function (Blueprint $table) {
                        $table->foreign('book_copy_id')
                            ->references('id')
                            ->on('book_copies')
                            ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // Likely the foreign key already exists; ignore.
                }
            }

            if (Schema::hasTable('users')) {
                try {
                    Schema::table('book_loans', function (Blueprint $table) {
                        $table->foreign('user_id')
                            ->references('id')
                            ->on('users')
                            ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // Likely the foreign key already exists; ignore.
                }
            }

            return;
        }

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

            if (Schema::hasTable('book_copies')) {
                $table->foreign('book_copy_id')
                    ->references('id')
                    ->on('book_copies')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('users')) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('book_loans');
    }
}
