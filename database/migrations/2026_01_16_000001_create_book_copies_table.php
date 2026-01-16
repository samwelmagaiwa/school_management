<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookCopiesTable extends Migration
{
    public function up()
    {
        // If the table already exists (for example, from a partial/failed migration run),
        // just ensure the foreign key is present and mark this migration as completed.
        if (Schema::hasTable('book_copies')) {
            if (Schema::hasTable('books')) {
                Schema::table('book_copies', function (Blueprint $table) {
                    $table->foreign('book_id')
                        ->references('id')
                        ->on('books')
                        ->onDelete('cascade');
                });
            }

            return;
        }

        Schema::create('book_copies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('book_id');
            $table->string('copy_code')->unique();
            $table->string('status')->default('available'); // available, borrowed, damaged, lost
            $table->string('notes')->nullable();
            $table->timestamps();

            if (Schema::hasTable('books')) {
                $table->foreign('book_id')
                    ->references('id')
                    ->on('books')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('book_copies');
    }
}
