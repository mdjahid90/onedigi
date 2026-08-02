<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::query()->orderBy('name')->paginate(20);

        return view('admin.email-templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(): View
    {
        return view('admin.email-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:email_templates,name'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'string', 'max:5000'],
        ]);

        $template = EmailTemplate::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'variables' => $validated['variables'] ?? null,
        ]);

        return redirect()->route('admin.email_templates.edit', $template)->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $template): View
    {
        return view('admin.email-templates.edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:email_templates,name,'.$template->id],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'string', 'max:5000'],
        ]);

        $template->update([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'variables' => $validated['variables'] ?? null,
        ]);

        return back()->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.email_templates.index')->with('success', 'Email template deleted successfully.');
    }
}
