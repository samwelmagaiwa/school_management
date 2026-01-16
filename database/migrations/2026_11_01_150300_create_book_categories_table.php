<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBookCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('book_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Seed from existing distinct categories on books table, if present
        if (Schema::hasTable('books')) {
            $names = DB::table('books')
                ->select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category')
                ->all();

            foreach ($names as $name) {
                DB::table('book_categories')->updateOrInsert(
                    ['name' => $name],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('book_categories');
    }
}
