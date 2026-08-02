<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Setting;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $mailFailed = false;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $message = ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'url' => $request->fullUrl(),
                'headers' => collect($request->headers->all())
                    ->except(['cookie', 'authorization'])
                    ->toArray(),
                'meta' => [
                    'method' => $request->method(),
                    'accept_language' => $request->headers->get('accept-language'),
                ],
            ]);

            AdminNotificationService::create(
                'contact_message',
                'New contact message',
                $validated['name'].' sent: '.$validated['subject'],
                route('admin.messages.show', $message),
                'info',
                $message
            );
        } catch (\Throwable $e) {
            Log::error('Contact message save failed', [
                'message' => $e->getMessage(),
                'email' => $validated['email'],
            ]);
        }

        $supportEmail = trim((string) Setting::getValue('support_email', ''));

        try {
            if ($supportEmail === '') {
                $mailFailed = true;
            } else {
                Mail::send([], [], function ($mail) use ($validated, $supportEmail) {
                    $mail->to($supportEmail)
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('[Digify Contact] '.$validated['subject'])
                    ->html(
                        '<div style="font-family: ui-sans-serif, system-ui; line-height: 1.6">'
                        .'<h2 style="margin:0 0 12px 0">New contact message</h2>'
                        .'<p style="margin:0 0 6px 0"><strong>Name:</strong> '.e($validated['name']).'</p>'
                        .'<p style="margin:0 0 6px 0"><strong>Email:</strong> '.e($validated['email']).'</p>'
                        .'<p style="margin:0 0 12px 0"><strong>Subject:</strong> '.e($validated['subject']).'</p>'
                        .'<div style="padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb">'
                        .nl2br(e($validated['message']))
                        .'</div>'
                        .'</div>'
                    );
                });
            }
        } catch (\Throwable $e) {
            $mailFailed = true;

            Log::error('Contact email send failed', [
                'message' => $e->getMessage(),
                'email' => $validated['email'],
            ]);
        }

        return back()->with('success', $mailFailed
            ? 'Thank you! Your message has been received successfully.'
            : 'Thank you! Your message has been sent successfully.'
        );
    }
}
