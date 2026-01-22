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
        Schema::create('transport_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('transport_vehicles')->onDelete('cascade');
            $table->date('date');
            
            $table->string('type'); // Service, Repair, Inspection
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->decimal('cost', 12, 2)->default(0);
            $table->integer('odometer_reading')->nullable();
            $table->string('service_provider')->nullable();
            $table->date('next_service_date')->nullable();
            
            $table->string('invoice_file')->nullable();
            
            $table->unsignedInteger('performed_by')->nullable();
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_maintenance_logs');
    }
};
