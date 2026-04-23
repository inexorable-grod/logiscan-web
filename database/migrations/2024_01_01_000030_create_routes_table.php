<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('center_id');
            $table->string('route_number', 20);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->unique(['center_id', 'route_number']);
            $table->index(['center_id', 'is_active']);
            $table->foreign('center_id')->references('id')->on('operation_centers')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
