<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('request_type', ['new_client', 'update_client', 'scan_reset']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->uuid('requested_by');
            $table->uuid('center_id');
            $table->uuid('route_id')->nullable();
            $table->json('request_data');
            $table->text('admin_comment')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->enum('resolved_by_role', ['supervisor', 'ti_admin'])->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['center_id', 'status']);
            $table->index('requested_by');
            $table->index('resolved_by');
            $table->index(['request_type', 'status']);
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('center_id')->references('id')->on('operation_centers');
            $table->foreign('route_id')->references('id')->on('routes')->nullOnDelete();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requests');
    }
};
