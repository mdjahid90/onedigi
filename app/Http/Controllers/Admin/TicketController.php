<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $q = trim($request->string('q')->toString());

        $tickets = SupportTicket::query()
            ->with('user:id,name,email')
            ->withCount('messages')
            ->when(in_array($status, ['open', 'closed'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('subject', 'like', '%' . $q . '%')
                        ->orWhereHas('user', function ($userQuery) use ($q) {
                            $userQuery->where('name', 'like', '%' . $q . '%')
                                ->orWhere('email', 'like', '%' . $q . '%');
                        });
                });
            })
            ->latest('last_reply_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'activeStatus' => $status,
            'q' => $q,
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load([
            'user:id,name,email',
            'messages' => function ($query) {
                $query->with('user:id,name,email,is_admin')->oldest();
            },
        ]);

        return view('admin.tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000', 'required_without:image'],
            'image' => ['nullable', 'image', 'max:5120', 'required_without:message'],
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('tickets', 'public')
            : null;

        DB::transaction(function () use ($request, $ticket, $validated, $imagePath) {
            $ticket->messages()->create([
                'user_id' => $request->user()->id,
                'message' => trim((string) ($validated['message'] ?? '')),
                'attachment_path' => $imagePath,
                'is_admin' => true,
            ]);

            $ticket->update([
                'status' => 'open',
                'last_reply_at' => now(),
            ]);
        });

        UserNotificationService::create(
            $ticket->user_id,
            'support_ticket_admin_reply',
            'Admin replied to ticket #'.$ticket->id,
            trim((string) ($validated['message'] ?? '')) !== ''
                ? str($validated['message'])->limit(120)->toString()
                : 'Admin added an attachment to your ticket.',
            route('tickets.show', $ticket),
            'info',
            $ticket
        );

        return back()->with('success', 'Reply sent to user.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
            'priority' => ['required', 'in:low,normal,high'],
        ]);

        $previousStatus = (string) $ticket->status;
        $previousPriority = (string) $ticket->priority;

        $ticket->update([
            'status' => $validated['status'],
            'priority' => $validated['priority'],
        ]);

        if ($previousStatus !== $validated['status'] || $previousPriority !== $validated['priority']) {
            UserNotificationService::create(
                $ticket->user_id,
                'support_ticket_updated',
                'Ticket #'.$ticket->id.' updated',
                'Status: '.strtoupper($validated['status']).' | Priority: '.ucfirst($validated['priority']),
                route('tickets.show', $ticket),
                $validated['status'] === 'closed' ? 'success' : 'info',
                $ticket
            );
        }

        return back()->with('success', 'Ticket updated.');
    }
}
