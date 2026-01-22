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

        // 2. Trips
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
    }

    public function down()
    {
        Schema::dropIfExists('transport_trips');
        Schema::dropIfExists('transport_vehicles');
    }
};
