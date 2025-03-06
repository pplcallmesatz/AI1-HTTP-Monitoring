<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSiteLogsColumnsFinal extends Migration
{
    public function up()
    {
        Schema::table('site_logs', function (Blueprint $table) {
            // Drop only these columns
            $table->dropColumn([
                'is_successful',
                'webhook_sent'
            ]);

            // Make sure message column exists and is not nullable
            if (!Schema::hasColumn('site_logs', 'message')) {
                $table->text('message');
            }

            // Ensure timestamps are properly set
            if (!Schema::hasColumn('site_logs', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('site_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('site_logs', function (Blueprint $table) {
            $table->boolean('is_successful')->default(false);
            $table->boolean('webhook_sent')->default(false);
        });
    }
} 