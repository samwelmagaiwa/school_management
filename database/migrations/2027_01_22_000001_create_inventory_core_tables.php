<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Categories
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('general'); 
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Suppliers
        Schema::create('inventory_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Units
        Schema::create('inventory_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation');
            $table->timestamps();
        });

        // 4. Warehouses
        Schema::create('inventory_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('contact_number')->nullable();
            
            // Users table uses increments() = unsignedInteger
            $table->unsignedInteger('keeper_id')->nullable();
            $table->foreign('keeper_id')->references('id')->on('users')->onDelete('set null');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Items
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('category_id')->constrained('inventory_categories')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('inventory_units')->onDelete('set null');
            $table->string('description')->nullable();
            $table->integer('reorder_level')->default(0);
            $table->boolean('is_asset')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_warehouses');
        Schema::dropIfExists('inventory_units');
        Schema::dropIfExists('inventory_suppliers');
        Schema::dropIfExists('inventory_categories');
    }
};
