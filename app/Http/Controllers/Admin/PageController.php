<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('admin.pages.index', [
            'pages' => $pages,
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:pages,slug'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'show_in_footer' => ['nullable', 'boolean'],
            'footer_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        $slug = $slug !== '' ? $slug : Str::slug($validated['title']);

        $page = Page::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'show_in_footer' => (bool) ($validated['show_in_footer'] ?? true),
            'footer_order' => (int) ($validated['footer_order'] ?? 0),
        ]);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:pages,slug,' . $page->id],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'show_in_footer' => ['nullable', 'boolean'],
            'footer_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        $slug = $slug !== '' ? $slug : Str::slug($validated['title']);

        $page->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'show_in_footer' => (bool) ($validated['show_in_footer'] ?? true),
            'footer_order' => (int) ($validated['footer_order'] ?? 0),
        ]);

        return back()->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }
}
