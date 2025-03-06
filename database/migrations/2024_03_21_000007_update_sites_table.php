<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            // Update existing columns if needed
            $table->integer('check_interval')->default(5)->change();
            $table->integer('cooling_time')->default(2)->change();
            $table->string('webhook_url')->nullable()->change();
            $table->integer('webhook_retry_count')->default(0)->change();
            $table->timestamp('last_webhook_sent_at')->nullable()->change();
            $table->boolean('enable_logging')->default(true)->change();
            $table->integer('logs_per_page')->default(50)->change();
            $table->boolean('is_active')->default(true)->change();
            $table->timestamp('last_check_at')->nullable()->change();
            $table->boolean('is_down')->default(false)->change();
        });
    }

    public function down()
    {
        // Define rollback if needed
    }
}; 