<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_recurring')->default(true);
            $table->string('gl_code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('academic_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('due_date');
            $table->unsignedInteger('ordering')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('late_fee_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('grace_days')->default(0);
            $table->decimal('max_cap', 12, 2)->nullable();
            $table->foreignId('fee_category_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('academic_year');
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });

        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_item_id')->constrained()->cascadeOnDelete();
            // Match my_classes.id / sections.id (increments -> unsignedInteger)
            $table->unsignedInteger('my_class_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_recurring')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('my_class_id')
                ->references('id')->on('my_classes')
                ->onDelete('set null');

            $table->foreign('section_id')
                ->references('id')->on('sections')
                ->onDelete('set null');
        });

        Schema::create('student_fee_assignments', function (Blueprint $table) {
            $table->id();
            // Match student_records.id (increments -> unsignedInteger)
            $table->unsignedInteger('student_record_id')->nullable();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->nullOnDelete();
            $table->enum('scope', ['student', 'class', 'group'])->default('student');
            // Match my_classes.id / sections.id (increments -> unsignedInteger)
            $table->unsignedInteger('my_class_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->string('group_tag')->nullable();
            $table->enum('status', ['pending', 'applied', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->foreign('student_record_id')
                ->references('id')->on('student_records')
                ->onDelete('set null');

            $table->foreign('my_class_id')
                ->references('id')->on('my_classes')
                ->onDelete('set null');

            $table->foreign('section_id')
                ->references('id')->on('sections')
                ->onDelete('set null');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            // Match users.id (increments -> unsignedInteger)
            $table->unsignedInteger('student_id');
            // Match student_records.id (increments -> unsignedInteger)
            $table->unsignedInteger('student_record_id')->nullable();
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->nullOnDelete();
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->nullOnDelete();
            $table->enum('status', ['draft', 'issued', 'partially_paid', 'paid', 'cancelled'])->default('draft');
            // Match users.id
            $table->unsignedInteger('issued_by')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('penalty_total', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->string('currency', 3)->default('TZS');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('student_record_id')
                ->references('id')->on('student_records')
                ->onDelete('set null');

            $table->foreign('issued_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('fee_item_id')->nullable()->constrained('fee_items')->nullOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('waiver_amount', 12, 2)->default(0);
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
        });

        Schema::create('discount_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 12, 2);
            $table->string('reason');
            // Match users.id (increments -> unsignedInteger)
            $table->unsignedInteger('requested_by');
            $table->unsignedInteger('approved_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->foreign('requested_by')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('approved_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        Schema::create('payments_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            // Match users.id (increments -> unsignedInteger)
            $table->unsignedInteger('student_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'mobile_money', 'cheque']);
            $table->string('reference')->nullable();
            $table->timestamp('received_at')->nullable();
            // Match users.id
            $table->unsignedInteger('recorded_by');
            $table->string('currency', 3)->default('TZS');
            $table->enum('source', ['fee', 'non_fee'])->default('fee');
            $table->enum('status', ['open', 'allocated', 'refunded'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('recorded_by')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments_ledger')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
            $table->decimal('amount_applied', 12, 2);
            $table->enum('strategy', ['oldest_first', 'specific_invoice', 'specific_item'])->default('oldest_first');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });

        Schema::create('receipts_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments_ledger')->cascadeOnDelete();
            // Match users.id
            $table->unsignedInteger('printed_by')->nullable();
            $table->unsignedInteger('reprint_count')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('printed_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        Schema::create('non_fee_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque']);
            $table->string('receipt_number')->nullable();
            $table->date('received_on');
            // Match users.id
            $table->unsignedInteger('recorded_by');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('recorded_by')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('title');
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque']);
            $table->string('reference')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            // Match users.id
            $table->unsignedInteger('recorded_by');
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('recorded_by')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('approved_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        Schema::create('expense_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('schedule_expression');
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->string('category');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('financial_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();
            // Match users.id
            $table->unsignedInteger('locked_by');
            $table->timestamp('locked_at');
            $table->text('reason')->nullable();
            $table->unsignedInteger('unlock_requested_by')->nullable();
            $table->unsignedInteger('unlocked_by')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->enum('status', ['locked', 'unlock_requested', 'unlocked'])->default('locked');
            $table->timestamps();

            $table->foreign('locked_by')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('unlock_requested_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('unlocked_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_locks');
        Schema::dropIfExists('recurring_expenses');
        Schema::dropIfExists('expense_attachments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('non_fee_incomes');
        Schema::dropIfExists('receipts_new');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments_ledger');
        Schema::dropIfExists('discount_requests');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('student_fee_assignments');
        Schema::dropIfExists('fee_structure_items');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('late_fee_rules');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('fee_items');
        Schema::dropIfExists('fee_categories');
    }
};
