<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessedAndReturnedByToBookLoans extends Migration
{
    public function up()
    {
        Schema::table('book_loans', function (Blueprint $table) {
            // User who processed the loan at the counter or via approval
            if (!Schema::hasColumn('book_loans', 'processed_by')) {
                $table->unsignedInteger('processed_by')->nullable()->after('user_id');
                $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            }

            // User who recorded the return/closure of the loan
            if (!Schema::hasColumn('book_loans', 'returned_by')) {
                $table->unsignedInteger('returned_by')->nullable()->after('returned_at');
                $table->foreign('returned_by')->references('id')->on('users')->nullOnDelete();
            }

            // Administrative override flags and notes
            if (!Schema::hasColumn('book_loans', 'has_override')) {
                $table->boolean('has_override')->default(false)->after('status');
            }

            if (!Schema::hasColumn('book_loans', 'override_notes')) {
                $table->text('override_notes')->nullable()->after('has_override');
            }
        });
    }

    public function down()
    {
        Schema::table('book_loans', function (Blueprint $table) {
            if (Schema::hasColumn('book_loans', 'processed_by')) {
                $table->dropForeign(['processed_by']);
                $table->dropColumn('processed_by');
            }

            if (Schema::hasColumn('book_loans', 'returned_by')) {
                $table->dropForeign(['returned_by']);
                $table->dropColumn('returned_by');
            }

            if (Schema::hasColumn('book_loans', 'has_override')) {
                $table->dropColumn('has_override');
            }

            if (Schema::hasColumn('book_loans', 'override_notes')) {
                $table->dropColumn('override_notes');
            }
        });
    }
}
