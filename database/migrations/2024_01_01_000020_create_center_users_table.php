<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('center_id');
            $table->uuid('user_id');
            $table->boolean('is_active')->default(true);
            $table->uuid('assigned_by')->nullable();
            $table->timestamps();

            $table->unique(['center_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
            $table->foreign('center_id')->references('id')->on('operation_centers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_users');
    }
};
