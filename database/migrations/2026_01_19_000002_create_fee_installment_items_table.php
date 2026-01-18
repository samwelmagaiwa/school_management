<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_installment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_installment_id')->constrained('fee_installments')->cascadeOnDelete();
            $table->foreignId('fee_item_id')->nullable()->constrained('fee_items')->nullOnDelete();
            $table->string('name'); // e.g., "Transport", "Meals", "Cleaning"
            $table->decimal('amount', 12, 2); // Amount for this item
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_installment_items');
    }
};
