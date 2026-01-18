<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_installments', function (Blueprint $table) {
            // Add link to terms (new hierarchy)
            $table->foreignId('fee_structure_term_id')->nullable()->after('id')->constrained('fee_structure_terms')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_installments', function (Blueprint $table) {
            $table->dropForeign(['fee_structure_term_id']);
            $table->dropColumn('fee_structure_term_id');
        });
    }
};
