<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('access_email')->nullable()->after('meta');
            $table->string('access_password')->nullable()->after('access_email');
            $table->text('license_key')->nullable()->after('access_password');
            $table->timestamp('subscription_starts_at')->nullable()->after('license_key');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_starts_at');
            $table->text('entitlement_notes')->nullable()->after('subscription_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'access_email',
                'access_password',
                'license_key',
                'subscription_starts_at',
                'subscription_expires_at',
                'entitlement_notes',
            ]);
        });
    }
};
