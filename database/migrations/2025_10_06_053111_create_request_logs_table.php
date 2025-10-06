<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLogsTable extends Migration
{
    public function up()
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('article_id')->nullable();
            $table->string('url');
            $table->string('method', 10);
            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_logs');
    }
}
