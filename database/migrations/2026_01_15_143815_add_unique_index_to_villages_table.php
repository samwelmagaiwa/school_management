<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToVillagesTable extends Migration
{
    public function up()
    {
        // Ensure base tables exist before attempting to add indexes
        if (
            ! Schema::hasTable('states') ||
            ! Schema::hasTable('wards') ||
            ! Schema::hasTable('villages')
        ) {
            return;
        }

        $districtTable = null;

        if (Schema::hasTable('districts')) {
            $districtTable = 'districts';
        } elseif (Schema::hasTable('lgas')) {
            $districtTable = 'lgas';
        }

        if (! $districtTable) {
            return;
        }

        // Add unique constraints without trying to drop non-unique ones (safer for fresh installs).
        Schema::table('states', function (Blueprint $table) {
            if (
                Schema::hasColumn('states', 'name') &&
                Schema::hasColumn('states', 'country_code')
            ) {
                $table->unique(['name', 'country_code']);
            }
        });

        Schema::table($districtTable, function (Blueprint $table) use ($districtTable) {
            if (
                Schema::hasColumn($districtTable, 'name') &&
                Schema::hasColumn($districtTable, 'state_id')
            ) {
                $table->unique(['name', 'state_id']);
            }
        });

        Schema::table('wards', function (Blueprint $table) {
            if (
                Schema::hasColumn('wards', 'name') &&
                Schema::hasColumn('wards', 'lga_id')
            ) {
                $table->unique(['name', 'lga_id']);
            }
        });

        Schema::table('villages', function (Blueprint $table) {
            if (
                Schema::hasColumn('villages', 'name') &&
                Schema::hasColumn('villages', 'ward_id')
            ) {
                $table->unique(['name', 'ward_id']);
            }
        });
    }

    public function down()
    {
        if (
            ! Schema::hasTable('states') ||
            ! Schema::hasTable('wards') ||
            ! Schema::hasTable('villages')
        ) {
            return;
        }

        $districtTable = null;

        if (Schema::hasTable('districts')) {
            $districtTable = 'districts';
        } elseif (Schema::hasTable('lgas')) {
            $districtTable = 'lgas';
        }

        if (! $districtTable) {
            return;
        }

        Schema::table('states', function (Blueprint $table) {
            if (
                Schema::hasColumn('states', 'name') &&
                Schema::hasColumn('states', 'country_code')
            ) {
                $table->dropUnique(['name', 'country_code']);
            }
        });

        Schema::table($districtTable, function (Blueprint $table) use ($districtTable) {
            if (
                Schema::hasColumn($districtTable, 'name') &&
                Schema::hasColumn($districtTable, 'state_id')
            ) {
                $table->dropUnique(['name', 'state_id']);
            }
        });

        Schema::table('wards', function (Blueprint $table) {
            if (
                Schema::hasColumn('wards', 'name') &&
                Schema::hasColumn('wards', 'lga_id')
            ) {
                $table->dropUnique(['name', 'lga_id']);
            }
        });

        Schema::table('villages', function (Blueprint $table) {
            if (
                Schema::hasColumn('villages', 'name') &&
                Schema::hasColumn('villages', 'ward_id')
            ) {
                $table->dropUnique(['name', 'ward_id']);
            }
        });
    }
}
