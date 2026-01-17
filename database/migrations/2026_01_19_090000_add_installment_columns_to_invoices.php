<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_invoice_id')->nullable()->after('id');
            $table->boolean('is_installment')->default(false)->after('fee_structure_id');
            $table->unsignedInteger('installment_sequence')->nullable()->after('is_installment');
            $table->string('installment_label')->nullable()->after('installment_sequence');

            $table->foreign('parent_invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_invoice_id']);
            $table->dropColumn([
                'parent_invoice_id',
                'is_installment',
                'installment_sequence',
                'installment_label',
            ]);
        });
    }
};
