<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->decimal('total_year_amount', 12, 2)->default(0)->after('academic_period_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn('total_year_amount');
        });
    }
};
