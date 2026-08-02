<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->index()->after('remember_token');
            $table->string('last_seen_ip', 45)->nullable()->after('last_seen_at');
            $table->string('last_seen_user_agent')->nullable()->after('last_seen_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn(['last_seen_at', 'last_seen_ip', 'last_seen_user_agent']);
        });
    }
};
