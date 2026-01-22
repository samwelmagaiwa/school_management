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
        Schema::create('transport_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('transport_vehicles')->onDelete('cascade');
            $table->date('date');
            
            $table->decimal('liters', 8, 2);
            $table->decimal('cost_per_liter', 10, 2);
            $table->decimal('total_cost', 12, 2);
            
            $table->decimal('odometer_reading', 10, 1);
            $table->boolean('is_full_tank')->default(false);
            
            $table->string('station_name')->nullable();
            $table->string('receipt_number')->nullable();
            
            $table->unsignedInteger('filled_by')->nullable();
            $table->foreign('filled_by')->references('id')->on('users')->onDelete('set null');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_fuel_logs');
    }
};
