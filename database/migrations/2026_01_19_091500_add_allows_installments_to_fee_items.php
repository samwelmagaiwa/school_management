<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_items', function (Blueprint $table) {
            $table->boolean('allows_installments')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('fee_items', function (Blueprint $table) {
            $table->dropColumn('allows_installments');
        });
    }
};
