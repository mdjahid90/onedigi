<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('regular_price', 10, 2)->nullable()->after('price');
            $table->unsignedInteger('stock')->nullable()->after('regular_price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('regular_price', 10, 2)->nullable()->after('price');
            $table->unsignedInteger('stock')->nullable()->after('regular_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['regular_price', 'stock']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['regular_price', 'stock']);
        });
    }
};
