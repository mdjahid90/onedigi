<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->string('code', 80)->nullable()->after('id')->unique();
            $table->string('base_url')->nullable()->after('name');
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'base_url', 'sort_order']);
        });
    }
};
