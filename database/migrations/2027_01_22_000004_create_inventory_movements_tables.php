<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Requisitions
        Schema::create('inventory_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->unique();
            
            // User FKs
            $table->unsignedInteger('requester_id');
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
            
            // Departments (Using departments table, assuming BigInt but if not check later. Users is definitely Int)
            $table->unsignedInteger('department_id')->nullable(); 
            // NOTE: If departments is from 2026_01_16_210000_create_departments_table.php and uses increments(), this might fail.
            // I'll check departments table later if it fails. For now assume BigInt or Int. 
            // Actually, safer to check.
            
            $table->string('type')->default('Consumable');
            $table->string('status')->default('Pending');
            
            $table->date('date_needed')->nullable();
            $table->text('reason')->nullable();
            
            $table->unsignedInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Requisition Items
        Schema::create('inventory_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained('inventory_requisitions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            
            $table->integer('quantity_requested');
            $table->integer('quantity_issued')->default(0);
            
            $table->string('status')->default('Pending');
            $table->timestamps();
        });

        // 3. Issuances
        Schema::create('inventory_issuances', function (Blueprint $table) {
            $table->id();
            $table->string('issue_code')->unique();
            $table->foreignId('requisition_id')->nullable()->constrained('inventory_requisitions')->onDelete('set null');
            
            $table->unsignedInteger('issued_by');
            $table->foreign('issued_by')->references('id')->on('users');

            $table->unsignedInteger('received_by')->nullable();
            $table->foreign('received_by')->references('id')->on('users');

            $table->date('issue_date');
            
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        // 4. Issuance Items
        Schema::create('inventory_issuance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_id')->constrained('inventory_issuances')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            
            $table->foreignId('inventory_asset_id')->nullable()->constrained('inventory_assets')->onDelete('set null');
            
            $table->integer('quantity');
            $table->foreignId('source_warehouse_id')->constrained('inventory_warehouses'); 
            
            $table->timestamps();
        });

        // 5. Transfers
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code')->unique();
            $table->foreignId('source_warehouse_id')->constrained('inventory_warehouses');
            $table->foreignId('destination_warehouse_id')->constrained('inventory_warehouses');
            
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->integer('quantity');
            
            $table->string('status')->default('Pending');
            
            $table->unsignedInteger('requested_by');
            $table->foreign('requested_by')->references('id')->on('users');

            $table->unsignedInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users');
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_transfers');
        Schema::dropIfExists('inventory_issuance_items');
        Schema::dropIfExists('inventory_issuances');
        Schema::dropIfExists('inventory_requisition_items');
        Schema::dropIfExists('inventory_requisitions');
    }
};
