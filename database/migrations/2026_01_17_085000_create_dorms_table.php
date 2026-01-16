<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dorms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            // Extended metadata so fresh installs don't depend on the
            // later AddMetadataColumnsToDormsTable upgrade.
            $table->string('gender', 10)->default('mixed');
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('room_count')->default(0);
            $table->unsignedInteger('bed_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dorms');
    }
}
