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
            $table->text('description');
            $table->decimal('cost', 10, 2);
            $table->integer('odometer_reading')->nullable();
            $table->string('service_provider')->nullable();
            $table->date('next_service_date')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
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
