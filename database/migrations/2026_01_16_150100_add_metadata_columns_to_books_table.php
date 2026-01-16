<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataColumnsToBooksTable extends Migration
{
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            // Core bibliographic fields
            $table->string('isbn', 191)->nullable()->after('author');
            $table->string('category', 191)->nullable()->after('isbn');
            $table->string('subject', 191)->nullable()->after('category');
            $table->string('edition', 100)->nullable()->after('subject');
            $table->string('publisher', 191)->nullable()->after('edition');
            $table->unsignedSmallInteger('publication_year')->nullable()->after('publisher');
            $table->string('language', 100)->nullable()->after('publication_year');

            // Whether the book is reference-only (cannot be borrowed)
            $table->boolean('is_reference_only')->default(false)->after('language');

            // Optional cover image for the catalog
            $table->string('cover_image_path', 191)->nullable()->after('is_reference_only');
        });
    }

    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'isbn',
                'category',
                'subject',
                'edition',
                'publisher',
                'publication_year',
                'language',
                'is_reference_only',
                'cover_image_path',
            ]);
        });
    }
}
