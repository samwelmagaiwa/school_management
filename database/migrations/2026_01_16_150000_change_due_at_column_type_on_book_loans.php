<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeDueAtColumnTypeOnBookLoans extends Migration
{
    public function up()
    {
        // Store full date and time when a book is due
        DB::statement("ALTER TABLE book_loans MODIFY due_at DATETIME NULL");
    }

    public function down()
    {
        // Revert back to DATE only
        DB::statement("ALTER TABLE book_loans MODIFY due_at DATE NULL");
    }
}
