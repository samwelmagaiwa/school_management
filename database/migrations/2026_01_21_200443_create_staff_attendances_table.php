<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('staff_attendances')) {
            Schema::create('staff_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
                $table->date('date');
                $table->time('clock_in_time')->nullable();
                $table->time('clock_out_time')->nullable();
                $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])->default('absent');
                $table->text('remarks')->nullable();
                $table->boolean('is_late')->default(false);
                $table->boolean('is_early_departure')->default(false);
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); // ID of admin if manual entry
                $table->timestamps();

                $table->unique(['staff_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
