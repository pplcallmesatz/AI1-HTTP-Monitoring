<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('site_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->integer('status_code')->default(0);
            $table->float('response_time')->default(0);
            $table->string('status')->default('unknown');
            $table->text('message')->nullable();
            $table->boolean('is_successful')->default(false);
            $table->boolean('webhook_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_logs');
    }
}; 