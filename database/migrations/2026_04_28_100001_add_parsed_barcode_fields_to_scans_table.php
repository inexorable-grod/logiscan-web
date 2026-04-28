<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->string('pedido_number', 20)->nullable()->after('barcode');
            $table->string('package_number', 10)->nullable()->after('pedido_number');
        });

        // Add index for grouping scans by order
        Schema::table('scans', function (Blueprint $table) {
            $table->index(['route_id', 'pedido_number']);
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropIndex(['route_id', 'pedido_number']);
            $table->dropColumn(['pedido_number', 'package_number']);
        });
    }
};
