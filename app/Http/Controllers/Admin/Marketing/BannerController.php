<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::query()->latest()->paginate(15);

        return view('admin.marketing.banners.index', [
            'banners' => $banners,
        ]);
    }

    public function create(): View
    {
        return view('admin.marketing.banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2048'],
            'image' => ['required', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'title' => $validated['title'],
            'link' => $validated['link'] ?? null,
            'image' => $imagePath,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('admin.marketing.banners.edit', $banner)->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.marketing.banners.edit', [
            'banner' => $banner,
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $update = [
            'title' => $validated['title'],
            'link' => $validated['link'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if ($request->hasFile('image')) {
            $update['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($update);

        return back()->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()->route('admin.marketing.banners.index')->with('success', 'Banner deleted successfully.');
    }
}
