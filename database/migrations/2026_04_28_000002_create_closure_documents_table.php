<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closure_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('route_closure_id')->constrained('route_closures')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 50);
            $table->unsignedInteger('file_size');
            $table->enum('ocr_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->longText('ocr_raw_text')->nullable();
            $table->timestamp('ocr_processed_at')->nullable();
            $table->foreignUuid('uploaded_by')->constrained('users');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closure_documents');
    }
};
