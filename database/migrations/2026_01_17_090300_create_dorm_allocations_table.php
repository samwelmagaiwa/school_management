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
        if (Schema::hasTable('dorm_allocations')) {
            // Map of column => [table, onDelete]
            $relations = [
                'student_record_id' => ['student_records', 'cascade'],
                'dorm_id' => ['dorms', 'cascade'],
                'dorm_room_id' => ['dorm_rooms', 'cascade'],
                'dorm_bed_id' => ['dorm_beds', 'cascade'],
            ];

            foreach ($relations as $column => [$relatedTable, $onDelete]) {
                if (Schema::hasTable($relatedTable)) {
                    try {
                        Schema::table('dorm_allocations', function (Blueprint $table) use ($column, $relatedTable, $onDelete) {
                            $table->foreign($column)
                                ->references('id')
                                ->on($relatedTable)
                                ->onDelete($onDelete);
                        });
                    } catch (\Throwable $e) {
                        // Likely the foreign key already exists; ignore.
                    }
                }
            }

            return;
        }

        Schema::create('dorm_allocations', function (Blueprint $table) {
            $table->id();
            // Match student_records.id (increments -> unsignedInteger)
            $table->unsignedInteger('student_record_id');
            // Match dorms.id (increments -> unsignedInteger)
            $table->unsignedInteger('dorm_id');
            // Match dorm_rooms.id (bigIncrements -> unsignedBigInteger)
            $table->unsignedBigInteger('dorm_room_id');
            // Match dorm_beds.id (bigIncrements -> unsignedBigInteger)
            $table->unsignedBigInteger('dorm_bed_id');
            // Users.id is increments -> unsignedInteger
            $table->unsignedInteger('assigned_by');
            $table->unsignedInteger('vacated_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('vacated_at')->nullable();
            $table->enum('status', ['active', 'vacated', 'transferred'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            if (Schema::hasTable('student_records')) {
                $table->foreign('student_record_id')
                    ->references('id')
                    ->on('student_records')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('dorms')) {
                $table->foreign('dorm_id')
                    ->references('id')
                    ->on('dorms')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('dorm_rooms')) {
                $table->foreign('dorm_room_id')
                    ->references('id')
                    ->on('dorm_rooms')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('dorm_beds')) {
                $table->foreign('dorm_bed_id')
                    ->references('id')
                    ->on('dorm_beds')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dorm_allocations');
    }
};
