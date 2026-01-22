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
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // Annual, Sick, Maternity
                $table->integer('days_allowed')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('staff_id');
                $table->foreign('staff_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('days_requested');
                $table->text('reason')->nullable();
                $table->string('attachment')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedInteger('approved_by')->nullable();
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
                $table->datetime('approved_at')->nullable();
                $table->unsignedInteger('rejected_by')->nullable();
                $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
                $table->datetime('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
    }
};
