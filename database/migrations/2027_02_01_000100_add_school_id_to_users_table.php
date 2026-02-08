<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools');
        });

        $defaultSchoolId = DB::table('schools')->orderBy('id')->value('id');

        if ($defaultSchoolId) {
            DB::table('users')->update(['school_id' => $defaultSchoolId]);
        }

        // Enforce NOT NULL after data backfill
        DB::statement('UPDATE users SET school_id = (SELECT id FROM schools ORDER BY id LIMIT 1) WHERE school_id IS NULL');
        DB::statement('ALTER TABLE users MODIFY school_id BIGINT UNSIGNED NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            // Drop legacy unique indexes so we can scope by school
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_username_unique');

            $table->unique(['email', 'school_id']);
            $table->unique(['username', 'school_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_school_id_unique');
            $table->dropUnique('users_username_school_id_unique');

            $table->unique('email');
            $table->unique('username');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });
    }
};
