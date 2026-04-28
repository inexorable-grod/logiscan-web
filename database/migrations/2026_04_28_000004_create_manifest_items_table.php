<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifest_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('closure_document_id')->constrained('closure_documents')->cascadeOnDelete();
            $table->foreignUuid('route_closure_id')->constrained('route_closures');
            $table->foreignUuid('order_id')->nullable()->constrained('orders');
            $table->unsignedSmallInteger('row_index')->default(0);
            $table->string('documento_desde', 50)->nullable();
            $table->string('documento_hasta', 50)->nullable();
            $table->string('pedido_number', 50);
            $table->string('nombre_local', 255);
            $table->unsignedSmallInteger('cubetas')->default(0);
            $table->unsignedSmallInteger('cajas_bolsa')->default(0);
            $table->unsignedSmallInteger('refrigerado')->default(0);
            $table->unsignedSmallInteger('controlado')->default(0);
            $table->string('forma_pago', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('confidence_score', 3, 2)->default(0.00);
            $table->boolean('is_manually_corrected')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifest_items');
    }
};
