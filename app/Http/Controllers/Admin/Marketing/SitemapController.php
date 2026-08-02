<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Services\SitemapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SitemapController extends Controller
{
    public function index(SitemapService $sitemap): View
    {
        $entries = $sitemap->entries();

        return view('admin.marketing.sitemap', [
            'sitemapUrl' => url('/sitemap.xml'),
            'robotsUrl' => url('/robots.txt'),
            'entryCount' => $entries->count(),
            'previewEntries' => $entries->take(8),
        ]);
    }

    public function generate(): RedirectResponse
    {
        return back()->with('success', 'Sitemap is now automatic. It refreshes from live products, categories, and pages whenever /sitemap.xml is requested.');
    }
}
