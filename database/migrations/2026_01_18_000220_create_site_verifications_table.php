<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_verifications', function (Blueprint $table) {
            $table->id();
            $table->text('google_search_console')->nullable();
            $table->text('bing_webmaster')->nullable();
            $table->text('yandex')->nullable();
            $table->text('pinterest')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_verifications');
    }
};
