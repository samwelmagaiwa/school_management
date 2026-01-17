<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fee_installments')) {
            Schema::table('fee_installments', function (Blueprint $table) {
                if (! Schema::hasColumn('fee_installments', 'grace_days')) {
                    $table->unsignedSmallInteger('grace_days')->nullable()->after('due_date');
                }
                if (! Schema::hasColumn('fee_installments', 'late_penalty_type')) {
                    $table->string('late_penalty_type', 20)->default('none')->after('grace_days');
                }
                if (! Schema::hasColumn('fee_installments', 'late_penalty_value')) {
                    $table->decimal('late_penalty_value', 12, 2)->nullable()->after('late_penalty_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fee_installments')) {
            Schema::table('fee_installments', function (Blueprint $table) {
                if (Schema::hasColumn('fee_installments', 'late_penalty_value')) {
                    $table->dropColumn('late_penalty_value');
                }
                if (Schema::hasColumn('fee_installments', 'late_penalty_type')) {
                    $table->dropColumn('late_penalty_type');
                }
                if (Schema::hasColumn('fee_installments', 'grace_days')) {
                    $table->dropColumn('grace_days');
                }
            });
        }
    }
};
