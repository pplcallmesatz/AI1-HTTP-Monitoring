<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->integer('check_interval')->default(5);
            $table->integer('cooling_time')->default(3);
            $table->string('webhook_url')->nullable();
            $table->integer('webhook_retry_count')->default(0);
            $table->timestamp('last_webhook_sent_at')->nullable();
            $table->boolean('enable_logging')->default(true);
            $table->integer('logs_per_page')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_check_at')->nullable();
            $table->boolean('is_down')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sites');
    }
}; 