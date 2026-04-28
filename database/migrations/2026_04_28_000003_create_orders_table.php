<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('route_id')->constrained('routes');
            $table->foreignUuid('client_id')->nullable()->constrained('clients');
            $table->foreignUuid('route_closure_id')->nullable()->constrained('route_closures');
            $table->string('pedido_number', 50)->index();
            $table->string('client_name_ocr', 255)->nullable();
            $table->unsignedSmallInteger('expected_cubetas')->default(0);
            $table->unsignedSmallInteger('expected_cajas_bolsa')->default(0);
            $table->unsignedSmallInteger('expected_refrigerado')->default(0);
            $table->unsignedSmallInteger('expected_controlado')->default(0);
            $table->unsignedSmallInteger('expected_total')->default(0);
            $table->enum('source', ['ocr', 'manual'])->default('ocr');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['route_id', 'pedido_number']);
            $table->index('route_closure_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
