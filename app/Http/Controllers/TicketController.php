<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->withCount('messages')
            ->latest('last_reply_at')
            ->latest('id')
            ->paginate(15);

        return view('pages.tickets.index', [
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'priority' => ['nullable', 'in:low,normal,high'],
            'message' => ['nullable', 'string', 'max:5000', 'required_without:image'],
            'image' => ['nullable', 'image', 'max:5120', 'required_without:message'],
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('tickets', 'public')
            : null;

        $ticket = DB::transaction(function () use ($request, $validated, $imagePath) {
            $ticket = SupportTicket::query()->create([
                'user_id' => $request->user()->id,
                'subject' => trim($validated['subject']),
                'status' => 'open',
                'priority' => $validated['priority'] ?? 'normal',
                'last_reply_at' => now(),
            ]);

            $ticket->messages()->create([
                'user_id' => $request->user()->id,
                'message' => trim((string) ($validated['message'] ?? '')),
                'attachment_path' => $imagePath,
                'is_admin' => false,
            ]);

            return $ticket;
        });

        AdminNotificationService::create(
            'support_ticket_created',
            'New support ticket #'.$ticket->id,
            $request->user()->name.' opened: '.$ticket->subject,
            route('admin.tickets.show', $ticket),
            'info',
            $ticket
        );

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        abort_unless((int) $ticket->user_id === (int) $request->user()->id, 403);

        $ticket->load([
            'messages' => function ($query) {
                $query->with('user')->oldest();
            },
        ]);

        return view('pages.tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless((int) $ticket->user_id === (int) $request->user()->id, 403);

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
                'is_admin' => false,
            ]);

            $ticket->update([
                'status' => 'open',
                'last_reply_at' => now(),
            ]);
        });

        AdminNotificationService::create(
            'support_ticket_reply',
            'Customer replied to ticket #'.$ticket->id,
            $request->user()->name.' added a reply.',
            route('admin.tickets.show', $ticket),
            'info',
            $ticket
        );

        return back()->with('success', 'Reply sent.');
    }

    public function close(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless((int) $ticket->user_id === (int) $request->user()->id, 403);

        $ticket->update([
            'status' => 'closed',
        ]);

        return back()->with('success', 'Ticket closed.');
    }
}
