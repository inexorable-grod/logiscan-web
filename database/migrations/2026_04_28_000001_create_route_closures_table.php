<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_closures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignUuid('closed_by')->constrained('users');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->date('operation_date');
            $table->enum('status', [
                'pending_documents',
                'pending_ocr',
                'pending_review',
                'pending_approval',
                'approved',
                'rejected',
            ])->default('pending_documents');
            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['route_id', 'operation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_closures');
    }
};
