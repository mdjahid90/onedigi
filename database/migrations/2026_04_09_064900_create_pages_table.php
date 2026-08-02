<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('show_in_footer')->default(true);
            $table->unsignedInteger('footer_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $refundPolicy = DB::table('settings')->where('key', 'refund_policy')->value('value');

        DB::table('pages')->insert([
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '',
                'is_published' => true,
                'show_in_footer' => true,
                'footer_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'content' => '',
                'is_published' => true,
                'show_in_footer' => true,
                'footer_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'AML Policy',
                'slug' => 'aml-policy',
                'content' => '',
                'is_published' => true,
                'show_in_footer' => true,
                'footer_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Refund Policy',
                'slug' => 'refund-policy',
                'content' => $refundPolicy !== null ? nl2br((string) $refundPolicy) : '',
                'is_published' => true,
                'show_in_footer' => true,
                'footer_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '',
                'is_published' => true,
                'show_in_footer' => true,
                'footer_order' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'API',
                'slug' => 'api',
                'content' => '',
                'is_published' => true,
                'show_in_footer' => true,
                'footer_order' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
