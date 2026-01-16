<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToClassesAndSubjects extends Migration
{
    public function up()
    {
        Schema::table('my_classes', function (Blueprint $table) {
            $table->unsignedInteger('department_id')->nullable()->after('class_type_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedInteger('department_id')->nullable()->after('teacher_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('my_classes', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
}
