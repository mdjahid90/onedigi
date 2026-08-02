<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->string('gateway')->default('demo');
            $table->string('trx_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('CREATED');
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['gateway']);
            $table->index(['trx_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
