<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('title_bn')->nullable()->after('title');
            $table->string('title_ru')->nullable()->after('title_bn');
            $table->longText('content_bn')->nullable()->after('content');
            $table->longText('content_ru')->nullable()->after('content_bn');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['title_bn', 'title_ru', 'content_bn', 'content_ru']);
        });
    }
};
