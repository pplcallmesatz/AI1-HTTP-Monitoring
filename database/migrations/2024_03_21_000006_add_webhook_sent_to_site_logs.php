<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebhookSentToSiteLogs extends Migration
{
    public function up()
    {
        Schema::table('site_logs', function (Blueprint $table) {
            $table->boolean('webhook_sent')->default(false)->after('message');
        });
    }

    public function down()
    {
        Schema::table('site_logs', function (Blueprint $table) {
            $table->dropColumn('webhook_sent');
        });
    }
} 