<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxRetriesToSites extends Migration
{
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->integer('max_retries')->default(3)->after('cooling_time');
        });
    }

    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('max_retries');
        });
    }
} 