<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->string('mode')->default('normal')->after('key');
        });
    }

    public function down(): void
    {
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
