<x-user-dashboard-layout title="Support Tickets" pretitle="Support">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1 rounded-xl border border-slate-200 bg-white p-5 shadow-sm h-fit">
                <h1 class="text-base font-semibold text-slate-900">Create Support Ticket</h1>
                <p class="mt-1 text-xs text-slate-500">Tell us your problem and start chat with admin team.</p>

                <form method="POST" action="{{ route('tickets.store') }}" class="mt-4 space-y-3" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Login issue, Delivery issue..." required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Priority</label>
                        <select name="priority" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Message</label>
                        <textarea name="message" rows="5" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Write your full issue in detail...">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Attach Image (optional)</label>
                        <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-black">
                    </div>

                    <button class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black transition">Create Ticket</button>
                </form>
            </div>

            <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">My Tickets</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-5 py-3 text-left font-medium">Subject</th>
                                <th class="px-5 py-3 text-left font-medium">Priority</th>
                                <th class="px-5 py-3 text-left font-medium">Status</th>
                                <th class="px-5 py-3 text-left font-medium">Updated</th>
                                <th class="px-5 py-3 text-right font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-slate-900">{{ $ticket->subject }}</td>
                                    <td class="px-5 py-3 capitalize text-slate-700">{{ $ticket->priority }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $ticket->status === 'open' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ strtoupper($ticket->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">{{ $ticket->last_reply_at?->format('Y-m-d H:i') ?? $ticket->updated_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Open Chat</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-5 py-8 text-center text-slate-500" colspan="5">No tickets yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4">
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>
</x-user-dashboard-layout>
