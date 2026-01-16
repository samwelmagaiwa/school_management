<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToLocationTables extends Migration
{
    public function up()
    {
        $districtTable = Schema::hasTable('districts') ? 'districts' : 'lgas';

        // On fresh installs the base location tables may not yet exist;
        // this migration only adds performance indexes for existing data.
        if (! Schema::hasTable('states') || ! Schema::hasTable($districtTable) ||
            ! Schema::hasTable('wards') || ! Schema::hasTable('villages')) {
            return;
        }

        Schema::table('states', function (Blueprint $table) {
            $table->index(['name', 'country_code']);
        });

        Schema::table($districtTable, function (Blueprint $table) {
            $table->index(['name', 'state_id']);
        });

        Schema::table('wards', function (Blueprint $table) {
            $table->index(['name', 'lga_id']);
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->index(['name', 'ward_id']);
        });
    }

    public function down()
    {
        $districtTable = Schema::hasTable('districts') ? 'districts' : 'lgas';

        if (! Schema::hasTable('states') || ! Schema::hasTable($districtTable) ||
            ! Schema::hasTable('wards') || ! Schema::hasTable('villages')) {
            return;
        }

        Schema::table('states', function (Blueprint $table) {
            $table->dropIndex(['name', 'country_code']);
        });

        Schema::table($districtTable, function (Blueprint $table) {
            $table->dropIndex(['name', 'state_id']);
        });

        Schema::table('wards', function (Blueprint $table) {
            $table->dropIndex(['name', 'lga_id']);
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->dropIndex(['name', 'ward_id']);
        });
    }
}
