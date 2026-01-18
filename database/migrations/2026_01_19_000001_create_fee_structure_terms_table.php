<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structure_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->string('name'); // e.g., "Term 1", "Semester 1"
            $table->unsignedInteger('sequence'); // 1, 2, 3, 4...
            $table->decimal('total_amount', 12, 2); // Total amount for this term
            $table->boolean('installments_enabled')->default(true); // Can be paid in installments?
            $table->timestamps();
            
            // Ensure unique sequence per fee structure
            $table->unique(['fee_structure_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structure_terms');
    }
};
