<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Vehicles
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('type');
            $table->integer('year')->nullable();
            
            // Driver (User)
            $table->unsignedInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            
            $table->string('status')->default('Active');
            $table->string('fuel_type')->default('Diesel');
            $table->decimal('current_mileage', 10, 1)->default(0);
            
            $table->date('insurance_expiry')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Fuel Logs
        Schema::create('transport_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('transport_vehicles')->onDelete('cascade');
            $table->date('date');
            
            $table->decimal('liters', 8, 2);
            $table->decimal('cost_per_liter', 10, 2);
            $table->decimal('total_cost', 12, 2);
            
            $table->decimal('odometer_reading', 10, 1);
            $table->boolean('is_full_tank')->default(false);
            
            $table->unsignedInteger('issued_by')->nullable();
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('set null');

            $table->string('invoice_number')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });

        // 3. Trips
        Schema::create('transport_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('transport_vehicles')->onDelete('cascade');
            
            $table->unsignedInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            
            $table->dateTime('departure_time');
            $table->dateTime('return_time')->nullable();
            
            $table->string('purpose');
            $table->string('destination')->nullable();
            
            $table->decimal('start_odometer', 10, 1);
            $table->decimal('end_odometer', 10, 1)->nullable();
            $table->decimal('distance_covered', 10, 1)->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        
        // 4. Maintenance Logs
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('maintainable');
            
            $table->string('type');
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('service_provider')->nullable();
            $table->date('service_date');
            $table->date('next_due_date')->nullable();
            
            $table->string('invoice_file')->nullable();
            
            $table->unsignedInteger('reported_by')->nullable();
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('transport_trips');
        Schema::dropIfExists('transport_fuel_logs');
        Schema::dropIfExists('transport_vehicles');
    }
};
