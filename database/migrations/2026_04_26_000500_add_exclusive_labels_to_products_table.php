<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('exclusive_duration_label', 80)->nullable()->after('stock');
            $table->string('exclusive_account_label', 80)->nullable()->after('exclusive_duration_label');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['exclusive_duration_label', 'exclusive_account_label']);
        });
    }
};
