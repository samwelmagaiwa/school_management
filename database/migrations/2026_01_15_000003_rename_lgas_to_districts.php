<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameLgasToDistricts extends Migration
{
    public function up()
    {
        // Drop foreign keys that reference lgas
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lga_id')) {
                // Uses array syntax so Laravel infers the FK name
                $table->dropForeign(['lga_id']);
            }
        });

        Schema::table('lgas', function (Blueprint $table) {
            if (Schema::hasColumn('lgas', 'state_id')) {
                $table->dropForeign(['state_id']);
            }
        });

        // Rename the lgas table to districts
        if (Schema::hasTable('lgas') && ! Schema::hasTable('districts')) {
            Schema::rename('lgas', 'districts');
        }

        // Recreate foreign keys pointing at the new districts table
        Schema::table('districts', function (Blueprint $table) {
            if (Schema::hasColumn('districts', 'state_id')) {
                $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lga_id')) {
                $table->foreign('lga_id')->references('id')->on('districts')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        // Drop foreign keys referencing districts
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lga_id')) {
                $table->dropForeign(['lga_id']);
            }
        });

        Schema::table('districts', function (Blueprint $table) {
            if (Schema::hasColumn('districts', 'state_id')) {
                $table->dropForeign(['state_id']);
            }
        });

        // Rename back to lgas if needed
        if (Schema::hasTable('districts') && ! Schema::hasTable('lgas')) {
            Schema::rename('districts', 'lgas');
        }

        // Restore original foreign keys
        Schema::table('lgas', function (Blueprint $table) {
            if (Schema::hasColumn('lgas', 'state_id')) {
                $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lga_id')) {
                $table->foreign('lga_id')->references('id')->on('lgas')->onDelete('set null');
            }
        });
    }
}
