<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToVillagesTable extends Migration
{
    public function up()
    {
        $districtTable = Schema::hasTable('districts') ? 'districts' : 'lgas';

        // Before adding unique indexes, we must ensure there are no remaining duplicates.
        // The cleanup command should have handled this, but we'll use unique index directly.
        
        Schema::table('states', function (Blueprint $table) {
            // Drop existing non-unique index if it exists
            $table->dropIndex(['name', 'country_code']);
            $table->unique(['name', 'country_code']);
        });

        Schema::table($districtTable, function (Blueprint $table) {
            $table->dropIndex(['name', 'state_id']);
            $table->unique(['name', 'state_id']);
        });

        Schema::table('wards', function (Blueprint $table) {
            $table->dropIndex(['name', 'lga_id']);
            $table->unique(['name', 'lga_id']);
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->dropIndex(['name', 'ward_id']);
            $table->unique(['name', 'ward_id']);
        });
    }

    public function down()
    {
        $districtTable = Schema::hasTable('districts') ? 'districts' : 'lgas';

        Schema::table('states', function (Blueprint $table) {
            $table->dropUnique(['name', 'country_code']);
            $table->index(['name', 'country_code']);
        });

        Schema::table($districtTable, function (Blueprint $table) {
            $table->dropUnique(['name', 'state_id']);
            $table->index(['name', 'state_id']);
        });

        Schema::table('wards', function (Blueprint $table) {
            $table->dropUnique(['name', 'lga_id']);
            $table->index(['name', 'lga_id']);
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->dropUnique(['name', 'ward_id']);
            $table->index(['name', 'ward_id']);
        });
    }
}
