<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 80);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url')->nullable();
            $table->string('severity', 20)->default('info');
            $table->nullableMorphs('notifiable');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
