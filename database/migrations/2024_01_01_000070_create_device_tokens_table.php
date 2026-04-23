<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('token', 500);
            $table->enum('platform', ['android', 'ios'])->default('android');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_used')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'token']);
            $table->index(['user_id', 'is_active']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
