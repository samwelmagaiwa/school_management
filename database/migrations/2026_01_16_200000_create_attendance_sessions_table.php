<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceSessionsTable extends Migration
{
    public function up()
    {
        // If the table already exists (for example, from a partial/failed migration run),
        // just ensure the foreign keys are present and mark this migration as completed.
        if (Schema::hasTable('attendance_sessions')) {
            // my_class_id -> my_classes.id
            if (Schema::hasTable('my_classes')) {
                try {
                    Schema::table('attendance_sessions', function (Blueprint $table) {
                        $table->foreign('my_class_id')
                            ->references('id')
                            ->on('my_classes')
                            ->onDelete('cascade');
                    });
                } catch (\Throwable $e) {
                    // Likely the foreign key already exists; ignore.
                }
            }

            // section_id -> sections.id
            if (Schema::hasTable('sections')) {
                try {
                    Schema::table('attendance_sessions', function (Blueprint $table) {
                        $table->foreign('section_id')
                            ->references('id')
                            ->on('sections')
                            ->onDelete('set null');
                    });
                } catch (\Throwable $e) {
                }
            }

            // subject_id -> subjects.id
            if (Schema::hasTable('subjects')) {
                try {
                    Schema::table('attendance_sessions', function (Blueprint $table) {
                        $table->foreign('subject_id')
                            ->references('id')
                            ->on('subjects')
                            ->onDelete('set null');
                    });
                } catch (\Throwable $e) {
                }
            }

            // time_slot_id -> time_slots.id (if the table exists)
            if (Schema::hasTable('time_slots')) {
                try {
                    Schema::table('attendance_sessions', function (Blueprint $table) {
                        $table->foreign('time_slot_id')
                            ->references('id')
                            ->on('time_slots')
                            ->onDelete('set null');
                    });
                } catch (\Throwable $e) {
                }
            }

            // user-related foreign keys
            if (Schema::hasTable('users')) {
                foreach (['taken_by' => 'cascade', 'submitted_by' => 'set null', 'locked_by' => 'set null', 'created_by' => 'cascade', 'updated_by' => 'set null'] as $column => $onDelete) {
                    try {
                        Schema::table('attendance_sessions', function (Blueprint $table) use ($column, $onDelete) {
                            $table->foreign($column)
                                ->references('id')
                                ->on('users')
                                ->onDelete($onDelete);
                        });
                    } catch (\Throwable $e) {
                    }
                }
            }

            return;
        }

        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedInteger('my_class_id');
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('subject_id')->nullable();
            $table->unsignedInteger('time_slot_id')->nullable();
            $table->enum('type', ['daily', 'subject', 'event'])->default('daily');
            $table->unsignedInteger('taken_by');
            $table->enum('status', ['open', 'submitted', 'locked'])->default('open');
            $table->unsignedInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            if (Schema::hasTable('my_classes')) {
                $table->foreign('my_class_id')
                    ->references('id')
                    ->on('my_classes')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('sections')) {
                $table->foreign('section_id')
                    ->references('id')
                    ->on('sections')
                    ->onDelete('set null');
            }

            if (Schema::hasTable('subjects')) {
                $table->foreign('subject_id')
                    ->references('id')
                    ->on('subjects')
                    ->onDelete('set null');
            }

            if (Schema::hasTable('time_slots')) {
                $table->foreign('time_slot_id')
                    ->references('id')
                    ->on('time_slots')
                    ->onDelete('set null');
            }

            if (Schema::hasTable('users')) {
                $table->foreign('taken_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('submitted_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->foreign('locked_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('updated_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }

            $table->index(['date', 'my_class_id', 'section_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_sessions');
    }
}
