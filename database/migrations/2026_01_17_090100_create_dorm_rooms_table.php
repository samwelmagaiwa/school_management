<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the table already exists (for example, from a partial/failed migration run),
        // just ensure the foreign key is present and mark this migration as completed.
        if (Schema::hasTable('dorm_rooms')) {
            if (Schema::hasTable('dorms')) {
                try {
                    Schema::table('dorm_rooms', function (Blueprint $table) {
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

        Schema::create('dorm_rooms', function (Blueprint $table) {
            $table->id();
            // Match type of dorms.id (increments -> unsignedInteger)
            $table->unsignedInteger('dorm_id');
            $table->string('name');
            $table->unsignedInteger('floor')->nullable();
            $table->unsignedInteger('capacity')->default(0);
            $table->enum('gender', ['male', 'female', 'mixed'])->default('mixed');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('bed_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('dorm_rooms');
    }
};
