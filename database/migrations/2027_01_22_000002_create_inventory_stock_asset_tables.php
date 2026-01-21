<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Stocks (For Consumables)
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('inventory_warehouses')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate rows for same batch at same warehouse
            $table->unique(['item_id', 'warehouse_id', 'batch_number']); 
        });

        // 2. Assets (For Fixed Assets - tracked individually)
        Schema::create('inventory_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('inventory_warehouses')->onDelete('set null'); // Current location
            
            $table->string('unique_tag')->unique()->nullable(); // Asset Tag
            $table->string('serial_number')->nullable();
            $table->string('condition')->default('Good'); // New, Good, Fair, Damaged, Obsolete
            $table->string('status')->default('Active'); // Active, Issued, Under Maintenance, Disposed, Lost
            
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->decimal('depreciation_rate', 5, 2)->nullable()->default(0); // % per year
            
            $table->foreignId('supplier_id')->nullable()->constrained('inventory_suppliers')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_assets');
        Schema::dropIfExists('inventory_stocks');
    }
};
