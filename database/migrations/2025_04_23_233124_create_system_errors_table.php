<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_errors', function (Blueprint $table) {
            $table->id();
            $table->string('error_code')->nullable();
            $table->text('message');
            $table->text('exception')->nullable();
            $table->text('file')->nullable();
            $table->integer('line')->nullable();
            $table->text('trace')->nullable();
            $table->text('request_data')->nullable();
            $table->string('user_id')->nullable();
            $table->string('url')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_errors');
    }
};
