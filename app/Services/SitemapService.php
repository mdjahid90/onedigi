<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class SitemapService
{
    /**
     * @return Collection<int, array{loc: string, lastmod: ?Carbon, changefreq: string, priority: string}>
     */
    public function entries(): Collection
    {
        $entries = collect();
        $latestContentUpdate = $this->latestContentUpdate();

        $this->add($entries, URL::to('/'), $latestContentUpdate, 'daily', '1.0');

        $staticRoutes = [
            'products.index' => ['changefreq' => 'daily', 'priority' => '0.9'],
            'categories' => ['changefreq' => 'weekly', 'priority' => '0.7'],
            'page.privacy' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            'page.terms' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            'page.refund-policy' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            'page.faq' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            'page.contact' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            'page.api' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            'page.aml' => ['changefreq' => 'monthly', 'priority' => '0.5'],
        ];

        foreach ($staticRoutes as $routeName => $meta) {
            if (Route::has($routeName)) {
                $this->add($entries, route($routeName), null, $meta['changefreq'], $meta['priority']);
            }
        }

        Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'updated_at'])
            ->each(function (Category $category) use ($entries): void {
                $this->add(
                    $entries,
                    route('products.index', ['category' => $category->slug]),
                    $category->updated_at,
                    'weekly',
                    '0.7'
                );
            });

        Product::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function (Product $product) use ($entries): void {
                $this->add($entries, route('products.show', $product), $product->updated_at, 'weekly', '0.8');
            });

        Page::query()
            ->where('is_published', true)
            ->orderBy('footer_order')
            ->orderBy('title')
            ->get(['slug', 'updated_at'])
            ->each(function (Page $page) use ($entries): void {
                $this->add($entries, route('page.show', $page), $page->updated_at, 'monthly', '0.5');
            });

        return $entries->unique('loc')->values();
    }

    public function xml(): string
    {
        $body = $this->entries()
            ->map(function (array $entry): string {
                $loc = $this->escape($entry['loc']);
                $lastmod = $entry['lastmod'] instanceof Carbon
                    ? '<lastmod>'.$entry['lastmod']->toAtomString().'</lastmod>'
                    : '';

                return '<url>'
                    . '<loc>'.$loc.'</loc>'
                    . $lastmod
                    . '<changefreq>'.$entry['changefreq'].'</changefreq>'
                    . '<priority>'.$entry['priority'].'</priority>'
                    . '</url>';
            })
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            . $body
            . '</urlset>';
    }

    /**
     * @param Collection<int, array{loc: string, lastmod: ?Carbon, changefreq: string, priority: string}> $entries
     */
    private function add(Collection $entries, string $loc, ?Carbon $lastmod, string $changefreq, string $priority): void
    {
        $entries->push([
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ]);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function latestContentUpdate(): ?Carbon
    {
        return collect([
            Product::query()->where('is_active', true)->max('updated_at'),
            Category::query()->where('is_active', true)->max('updated_at'),
            Page::query()->where('is_published', true)->max('updated_at'),
        ])
            ->filter()
            ->map(static fn (string $timestamp): Carbon => Carbon::parse($timestamp))
            ->sortDesc()
            ->first();
    }
}
