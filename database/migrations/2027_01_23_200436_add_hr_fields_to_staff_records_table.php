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
        Schema::table('staff_records', function (Blueprint $table) {
            // Check if columns exist before adding (defensive)
            if (!Schema::hasColumn('staff_records', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('staff_departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('staff_records', 'designation_id')) {
                $table->foreignId('designation_id')->nullable()->constrained('staff_designations')->nullOnDelete();
            }
            
            $table->string('employment_type')->nullable()->after('code'); // Full-time, Part-time, Contract
            $table->decimal('basic_salary', 15, 2)->nullable()->after('employment_type');
            $table->string('status')->default('active')->after('basic_salary'); // active, on_leave, terminated
            $table->json('bio_data')->nullable()->after('status'); // Store extra details like blood group, genotype flexible
            $table->date('date_of_hire')->nullable()->after('emp_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_records', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropColumn(['department_id', 'designation_id', 'employment_type', 'basic_salary', 'status', 'bio_data', 'date_of_hire']);
        });
    }
};
