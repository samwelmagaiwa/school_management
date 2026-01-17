<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Extend fee_items to support installment configuration
        if (Schema::hasTable('fee_items')) {
            Schema::table('fee_items', function (Blueprint $table) {
                if (! Schema::hasColumn('fee_items', 'allows_installments')) {
                    $table->boolean('allows_installments')->default(false)->after('default_amount');
                }

                if (! Schema::hasColumn('fee_items', 'default_installments')) {
                    $table->unsignedTinyInteger('default_installments')->nullable()->after('allows_installments');
                }
            });
        }

        // 2) Installment plans per fee structure
        if (! Schema::hasTable('fee_installment_plans')) {
            Schema::create('fee_installment_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3) Individual installments within a plan
        if (! Schema::hasTable('fee_installments')) {
            Schema::create('fee_installments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fee_installment_plan_id')->constrained('fee_installment_plans')->cascadeOnDelete();
                $table->unsignedSmallInteger('sequence');
                $table->string('label');
                $table->decimal('percentage', 5, 2)->nullable();
                $table->decimal('fixed_amount', 12, 2)->nullable();
                $table->date('due_date')->nullable();
                $table->timestamps();
            });
        }

        // 4) Student-level installment schedule tied to invoices/items
        if (! Schema::hasTable('student_installments')) {
            Schema::create('student_installments', function (Blueprint $table) {
                $table->id();
                // Core references
                $table->unsignedInteger('student_id'); // users.id (increments)
                $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
                $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->nullOnDelete();
                $table->foreignId('fee_installment_id')->constrained('fee_installments')->cascadeOnDelete();

                // Financials
                $table->decimal('amount', 12, 2);
                $table->decimal('amount_paid', 12, 2)->default(0);

                // Scheduling
                $table->date('due_date')->nullable();
                $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
                $table->timestamp('last_payment_at')->nullable();

                $table->timestamps();

                $table->foreign('student_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            });
        }

        // 5) Link payment allocations to a specific installment (optional)
        if (Schema::hasTable('payment_allocations') && ! Schema::hasColumn('payment_allocations', 'student_installment_id')) {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->foreignId('student_installment_id')->nullable()->after('invoice_item_id')
                    ->constrained('student_installments')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_allocations') && Schema::hasColumn('payment_allocations', 'student_installment_id')) {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->dropForeign(['student_installment_id']);
                $table->dropColumn('student_installment_id');
            });
        }

        if (Schema::hasTable('student_installments')) {
            Schema::dropIfExists('student_installments');
        }

        if (Schema::hasTable('fee_installments')) {
            Schema::dropIfExists('fee_installments');
        }

        if (Schema::hasTable('fee_installment_plans')) {
            Schema::dropIfExists('fee_installment_plans');
        }

        if (Schema::hasTable('fee_items')) {
            Schema::table('fee_items', function (Blueprint $table) {
                if (Schema::hasColumn('fee_items', 'allows_installments')) {
                    $table->dropColumn('allows_installments');
                }
                if (Schema::hasColumn('fee_items', 'default_installments')) {
                    $table->dropColumn('default_installments');
                }
            });
        }
    }
};
