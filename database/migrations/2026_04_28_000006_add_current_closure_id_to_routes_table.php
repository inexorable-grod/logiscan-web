<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->foreignUuid('current_closure_id')->nullable()->after('is_active')->constrained('route_closures')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['current_closure_id']);
            $table->dropColumn('current_closure_id');
        });
    }
};
