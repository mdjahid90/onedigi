<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\AutoTranslate;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(Page $page, AutoTranslate $autoTranslate): View
    {
        abort_unless($page->is_published, 404);

        $locale = app()->getLocale();
        $title = (string) $page->title;
        $content = (string) ($page->content ?? '');

        if ($locale !== 'en') {
            $title = $autoTranslate->translate($title, $locale, false);
            $content = $autoTranslate->translate($content, $locale, true);
        }

        return view('pages.show', [
            'page' => $page,
            'title' => $title,
            'content' => $content,
        ]);
    }
}
