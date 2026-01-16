<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the table already exists (for example, from a partial/failed migration run),
        // just ensure the foreign keys are present and mark this migration as completed.
        if (Schema::hasTable('dorm_beds')) {
            if (Schema::hasTable('dorm_rooms')) {
                try {
                    Schema::table('dorm_beds', function (Blueprint $table) {
                        $table->foreign('dorm_room_id')
                            ->references('id')
                            ->on('dorm_rooms')
                            ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // Likely the foreign key already exists; ignore.
                }
            }

            if (Schema::hasTable('dorms')) {
                try {
                    Schema::table('dorm_beds', function (Blueprint $table) {
                        $table->foreign('dorm_id')
                            ->references('id')
                            ->on('dorms')
                            ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // Likely the foreign key already exists; ignore.
                }
            }

            return;
        }

        Schema::create('dorm_beds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dorm_room_id');
            // Match type of dorms.id (increments -> unsignedInteger)
            $table->unsignedInteger('dorm_id');
            $table->string('label');
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('current_allocation_id')->nullable();
            $table->timestamps();

            if (Schema::hasTable('dorm_rooms')) {
                $table->foreign('dorm_room_id')
                    ->references('id')
                    ->on('dorm_rooms')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('dorms')) {
                $table->foreign('dorm_id')
                    ->references('id')
                    ->on('dorms')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dorm_beds');
    }
};
