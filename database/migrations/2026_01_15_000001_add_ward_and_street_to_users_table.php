<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWardAndStreetToUsersTable extends Migration
{
    public function up()
    {
        // On fresh installs the base users table is created later by
        // 2026_10_12_000000_create_users_table and already includes
        // ward/street columns. This migration is only needed when
        // upgrading an existing database. Guard for missing table.
        if (! Schema::hasTable('users')) {
            return;
        }

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
        if (! Schema::hasTable('users')) {
            return;
        }

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
