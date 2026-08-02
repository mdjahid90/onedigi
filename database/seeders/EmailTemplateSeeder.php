<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        EmailTemplate::query()->updateOrCreate(
            ['name' => 'Order Received'],
            [
                'subject' => 'Order Received - #{{order_id}}',
                'body' => '<p>Hello {{name}},</p><p>We have received your order <strong>#{{order_id}}</strong> for <strong>${{amount}}</strong>.</p><p>Delivery via Email &amp; Dashboard within 10min – 12 hours after payment.</p>',
                'variables' => '{{name}}, {{order_id}}, {{amount}}',
            ]
        );

        EmailTemplate::query()->updateOrCreate(
            ['name' => 'Order Delivered'],
            [
                'subject' => 'Order Delivered - #{{order_id}}',
                'body' => '<p>Hello {{name}},</p><p>Your order <strong>#{{order_id}}</strong> has been delivered.</p><p>Delivery link: {{delivery_link}}</p><p>Download: {{delivery_download_url}}</p>',
                'variables' => '{{name}}, {{order_id}}, {{amount}}, {{delivery_link}}, {{delivery_download_url}}',
            ]
        );

        EmailTemplate::query()->updateOrCreate(
            ['name' => 'Password Reset'],
            [
                'subject' => 'Password Reset',
                'body' => '<p>Hello {{name}},</p><p>Use the following link to reset your password:</p><p>{{reset_link}}</p>',
                'variables' => '{{name}}, {{reset_link}}',
            ]
        );

        EmailTemplate::query()->updateOrCreate(
            ['name' => 'Welcome Email'],
            [
                'subject' => 'Welcome to DIGIFY',
                'body' => '<p>Hello {{name}},</p><p>Welcome to DIGIFY. Your account is ready.</p>',
                'variables' => '{{name}}',
            ]
        );
    }
}
