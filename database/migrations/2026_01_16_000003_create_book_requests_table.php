<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookRequestsTable extends Migration
{
    public function up()
    {
        // If the table doesn't exist (fresh install), create it with the new structure
        if (!Schema::hasTable('book_requests')) {
            Schema::create('book_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('book_id');
                $table->unsignedInteger('user_id');
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
                $table->unsignedInteger('approved_by')->nullable();
                $table->unsignedInteger('assigned_copy_id')->nullable();
                $table->unsignedInteger('book_loan_id')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('assigned_copy_id')->references('id')->on('book_copies')->nullOnDelete();
                $table->foreign('book_loan_id')->references('id')->on('book_loans')->nullOnDelete();
            });

            return;
        }

        // If the legacy table already exists, upgrade it in-place by adding the
        // new columns used by the modern library request workflow. We leave the
        // old columns (start_date, end_date, returned, etc.) intact so existing
        // data is not destroyed.
        Schema::table('book_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('book_requests', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending')->after('user_id');
            }

            if (!Schema::hasColumn('book_requests', 'approved_by')) {
                $table->unsignedInteger('approved_by')->nullable()->after('status');
            }

            if (!Schema::hasColumn('book_requests', 'assigned_copy_id')) {
                $table->unsignedInteger('assigned_copy_id')->nullable()->after('approved_by');
            }

            if (!Schema::hasColumn('book_requests', 'book_loan_id')) {
                $table->unsignedInteger('book_loan_id')->nullable()->after('assigned_copy_id');
            }

            if (!Schema::hasColumn('book_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('book_loan_id');
            }

            if (!Schema::hasColumn('book_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            }

            if (!Schema::hasColumn('book_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('book_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            }
        });

        // Add missing foreign keys on the newly added columns. We don't attempt
        // to recreate the legacy FKs on book_id/user_id if they already exist;
        // those were handled in the older migration (2019_09_22_142514_create_fks).
        Schema::table('book_requests', function (Blueprint $table) {
            // Use raw statements to avoid duplicate FK name issues on existing installs
            if (Schema::hasColumn('book_requests', 'approved_by')) {
                try {
                    $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignore if FK already exists
                }
            }

            if (Schema::hasColumn('book_requests', 'assigned_copy_id')) {
                try {
                    $table->foreign('assigned_copy_id')->references('id')->on('book_copies')->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignore if FK already exists
                }
            }

            if (Schema::hasColumn('book_requests', 'book_loan_id')) {
                try {
                    $table->foreign('book_loan_id')->references('id')->on('book_loans')->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignore if FK already exists
                }
            }
        });
    }

    public function down()
    {
        // For safety, don't drop the table on rollback since it may contain
        // legacy data. Just drop the columns we introduced.
        if (Schema::hasTable('book_requests')) {
            Schema::table('book_requests', function (Blueprint $table) {
                if (Schema::hasColumn('book_requests', 'approved_by')) {
                    $table->dropForeign(['approved_by']);
                    $table->dropColumn('approved_by');
                }
                if (Schema::hasColumn('book_requests', 'assigned_copy_id')) {
                    $table->dropForeign(['assigned_copy_id']);
                    $table->dropColumn('assigned_copy_id');
                }
                if (Schema::hasColumn('book_requests', 'book_loan_id')) {
                    $table->dropForeign(['book_loan_id']);
                    $table->dropColumn('book_loan_id');
                }
                if (Schema::hasColumn('book_requests', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
                if (Schema::hasColumn('book_requests', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
                if (Schema::hasColumn('book_requests', 'rejected_at')) {
                    $table->dropColumn('rejected_at');
                }
                if (Schema::hasColumn('book_requests', 'cancelled_at')) {
                    $table->dropColumn('cancelled_at');
                }
            });
        }
    }
}
