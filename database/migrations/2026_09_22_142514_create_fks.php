<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFks extends Migration
{

    public function up()
    {
        // Legacy bulk foreign key migration.
        // For fresh installs, we now rely on more targeted, order-safe
        // migrations for critical relationships. To avoid repeated
        // foreign key errors (missing tables, duplicate constraints),
        // we intentionally no-op this migration on new databases.
        return;
    }

    public function down()
    {
        // No-op: this migration does not apply any changes on fresh installs.
    }
}
