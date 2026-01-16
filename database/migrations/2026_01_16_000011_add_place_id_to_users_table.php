<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlaceIdToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'place_id')) {
                $table->unsignedInteger('place_id')->nullable()->after('street');
                // We keep it nullable and do not enforce FK for now to avoid
                // breaking existing rows that don't yet have a mapped place.
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'place_id')) {
                $table->dropColumn('place_id');
            }
        });
    }
}
