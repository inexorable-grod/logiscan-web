<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->foreignUuid('order_id')->nullable()->after('client_id')->constrained('orders')->nullOnDelete();
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
