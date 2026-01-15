<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWardAndStreetToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ward')) {
                $table->string('ward')->nullable()->after('address');
            }

            if (!Schema::hasColumn('users', 'street')) {
                $table->string('street')->nullable()->after('ward');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'street')) {
                $table->dropColumn('street');
            }
            if (Schema::hasColumn('users', 'ward')) {
                $table->dropColumn('ward');
            }
        });
    }
}
