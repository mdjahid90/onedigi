<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 80);
            $table->timestamp('occurred_at')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 40)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('route_name')->nullable();
            $table->string('path')->nullable();
            $table->string('referrer')->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'occurred_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('route_name');
            $table->index('session_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
