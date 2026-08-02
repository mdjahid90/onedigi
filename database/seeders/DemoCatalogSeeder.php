<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = 'admin@example.com';

        User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        $categories = [
            'Accounts',
            'Templates',
            'Ebooks',
            'Design Assets',
        ];

        foreach ($categories as $name) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }

        if (Product::query()->count() > 0) {
            return;
        }

        $categoryMap = Category::query()->pluck('id', 'slug');

        $products = [
            ['title' => 'Premium Canva Templates Pack', 'category' => 'templates', 'price' => 19.99],
            ['title' => 'Marketing Ebook Bundle', 'category' => 'ebooks', 'price' => 9.99],
            ['title' => 'UI Kit Design Assets', 'category' => 'design-assets', 'price' => 29.00],
            ['title' => 'Streaming Account (Demo)', 'category' => 'accounts', 'price' => 12.50],
            ['title' => 'Notion Dashboard Template', 'category' => 'templates', 'price' => 14.00],
            ['title' => 'Logo Pack (SVG)', 'category' => 'design-assets', 'price' => 7.50],
            ['title' => 'Productivity Ebook', 'category' => 'ebooks', 'price' => 6.99],
            ['title' => 'Software License Key (Demo)', 'category' => 'accounts', 'price' => 24.99],
        ];

        foreach ($products as $p) {
            $slug = Str::slug($p['title']);

            Product::query()->create([
                'category_id' => $categoryMap[$p['category']] ?? null,
                'title' => $p['title'],
                'slug' => $slug,
                'description' => "Demo product. Delivery is manual after payment.\n\nProduct will be delivered by Email & Dashboard within 10min – 12 hours.",
                'price' => $p['price'],
                'is_active' => true,
            ]);
        }
    }
}
