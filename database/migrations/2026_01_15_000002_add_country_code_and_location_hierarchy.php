<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryCodeAndLocationHierarchy extends Migration
{
    public function up()
    {
        // On fresh installs the states/lgas tables are created later by
        // the 2026_09_22_* migrations. This migration only upgrades
        // existing databases, so skip if base tables don't exist.
        if (! Schema::hasTable('states')) {
            return;
        }

        // Add country_code to states so we can distinguish countries
        Schema::table('states', function (Blueprint $table) {
            if (!Schema::hasColumn('states', 'country_code')) {
                $table->string('country_code', 3)->nullable()->after('name');
            }
        });

        // Create wards table if it does not exist
        if (!Schema::hasTable('wards')) {
            Schema::create('wards', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('lga_id');
                $table->string('name');
                $table->timestamps();
            });
        }

        // Create villages table if it does not exist
        if (!Schema::hasTable('villages')) {
            Schema::create('villages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ward_id');
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('villages')) {
            Schema::drop('villages');
        }

        if (Schema::hasTable('wards')) {
            Schema::drop('wards');
        }

        if (! Schema::hasTable('states')) {
            return;
        }

        Schema::table('states', function (Blueprint $table) {
            if (Schema::hasColumn('states', 'country_code')) {
                $table->dropColumn('country_code');
            }
        });
    }
}
