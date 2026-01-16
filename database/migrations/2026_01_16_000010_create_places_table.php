<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacesTable extends Migration
{
    public function up()
    {
        // If the table already exists (for example, from a manual creation or a previous run),
        // just mark the migration as completed.
        if (Schema::hasTable('places')) {
            return;
        }

        Schema::create('places', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('village_id');
            $table->string('name');
            $table->timestamps();

            if (Schema::hasTable('villages')) {
                $table->foreign('village_id')
                    ->references('id')
                    ->on('villages')
                    ->onDelete('cascade');
            }

            $table->unique(['village_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('places');
    }
}
