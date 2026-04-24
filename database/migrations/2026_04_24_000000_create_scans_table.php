<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('route_id')->nullable()->constrained('routes');
            $table->foreignUuid('client_id')->nullable()->constrained('clients');
            $table->string('barcode');
            $table->string('scan_type'); // bulto, cubeta, rf, controlado, refrigerado
            $table->string('local_id')->nullable(); // from mobile offline queue
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->index(['user_id', 'scanned_at']);
            $table->index('route_id');
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
