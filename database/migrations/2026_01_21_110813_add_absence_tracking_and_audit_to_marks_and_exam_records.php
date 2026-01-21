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
        Schema::table('marks', function (Blueprint $table) {
            // Absence tracking
            $table->boolean('is_absent')->default(false)->after('grade_id');
            $table->string('exemption_reason')->nullable()->after('is_absent');
            
            // Audit trail
            $table->unsignedInteger('entered_by')->nullable()->after('updated_at');
            $table->unsignedInteger('modified_by')->nullable()->after('entered_by');
            
            // Foreign keys
            $table->foreign('entered_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('exam_records', function (Blueprint $table) {
            // Audit trail  
            $table->unsignedInteger('entered_by')->nullable()->after('updated_at');
            $table->unsignedInteger('modified_by')->nullable()->after('entered_by');
            
            // Foreign keys
            $table->foreign('entered_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->dropForeign(['entered_by']);
            $table->dropForeign(['modified_by']);
            $table->dropColumn(['is_absent', 'exemption_reason', 'entered_by', 'modified_by']);
        });

        Schema::table('exam_records', function (Blueprint $table) {
            $table->dropForeign(['entered_by']);
            $table->dropForeign(['modified_by']);
            $table->dropColumn(['entered_by', 'modified_by']);
        });
    }
};
