<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('country');
            $table->text('notes')->nullable();

            $table->string('status')->default('PENDING');

            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['customer_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
