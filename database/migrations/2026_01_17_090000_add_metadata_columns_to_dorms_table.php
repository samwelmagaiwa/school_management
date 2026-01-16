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
        // On fresh installs the dorms table is created with these
        // metadata columns already present. This migration only
        // upgrades existing databases; skip if table is missing.
        if (! Schema::hasTable('dorms')) {
            return;
        }

        Schema::table('dorms', function (Blueprint $table) {
            if (! Schema::hasColumn('dorms', 'gender')) {
                $table->string('gender', 10)->default('mixed')->after('description');
            }

            if (! Schema::hasColumn('dorms', 'capacity')) {
                $table->unsignedInteger('capacity')->nullable()->after('gender');
            }

            if (! Schema::hasColumn('dorms', 'room_count')) {
                $table->unsignedInteger('room_count')->default(0)->after('capacity');
            }

            if (! Schema::hasColumn('dorms', 'bed_count')) {
                $table->unsignedInteger('bed_count')->default(0)->after('room_count');
            }

            if (! Schema::hasColumn('dorms', 'notes')) {
                $table->text('notes')->nullable()->after('bed_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('dorms')) {
            return;
        }

        Schema::table('dorms', function (Blueprint $table) {
            foreach (['notes', 'bed_count', 'room_count', 'capacity', 'gender'] as $column) {
                if (Schema::hasColumn('dorms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
