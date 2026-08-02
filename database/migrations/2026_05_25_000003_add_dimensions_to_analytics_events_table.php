<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('ip_address');
            $table->string('country_name', 80)->nullable()->after('country_code');
            $table->string('device_type', 30)->nullable()->after('country_name');
            $table->string('browser', 60)->nullable()->after('device_type');
            $table->string('source', 80)->nullable()->after('browser');

            $table->index(['country_code', 'occurred_at']);
            $table->index(['device_type', 'occurred_at']);
            $table->index(['source', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex(['country_code', 'occurred_at']);
            $table->dropIndex(['device_type', 'occurred_at']);
            $table->dropIndex(['source', 'occurred_at']);
            $table->dropColumn(['country_code', 'country_name', 'device_type', 'browser', 'source']);
        });
    }
};
