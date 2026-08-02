<x-user-dashboard-layout :title="'Ticket #' . $ticket->id" pretitle="Support">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-sm font-semibold text-slate-900">{{ $ticket->subject }}</h1>
                    <p class="mt-1 text-xs text-slate-500">Ticket #{{ $ticket->id }} · {{ strtoupper($ticket->status) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tickets.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Back</a>
                    @if($ticket->status === 'open')
                        <form method="POST" action="{{ route('tickets.close', $ticket) }}">
                            @csrf
                            <button class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">Close Ticket</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-slate-50 px-4 py-4 space-y-3 max-h-[520px] overflow-y-auto">
                @foreach($ticket->messages as $msg)
                    <div class="flex {{ $msg->is_admin ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-[85%] rounded-xl px-4 py-3 text-sm {{ $msg->is_admin ? 'bg-white border border-slate-200 text-slate-800' : 'bg-indigo-600 text-white' }}">
                            <p class="text-xs mb-1 {{ $msg->is_admin ? 'text-slate-500' : 'text-indigo-100' }}">
                                {{ $msg->is_admin ? 'Admin' : 'You' }} · {{ $msg->created_at?->format('Y-m-d H:i') }}
                            </p>
                            @if(!empty($msg->message))
                                <div class="whitespace-pre-line">{{ $msg->message }}</div>
                            @endif
                            @if(!empty($msg->attachment_path))
                                <a href="{{ Storage::url($msg->attachment_path) }}" target="_blank" rel="noreferrer" class="mt-2 block">
                                    <img
                                        src="{{ Storage::url($msg->attachment_path) }}"
                                        alt="Attachment"
                                        class="rounded-lg border border-white/20 shadow-sm"
                                        style="display:block; width:auto; max-width:min(280px, 72vw); max-height:220px; object-fit:cover;"
                                    >
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-slate-100 p-4">
                @if($ticket->status === 'closed')
                    <p class="text-sm text-slate-500">This ticket is closed. Create a new ticket for new issue.</p>
                @else
                    <form method="POST" action="{{ route('tickets.reply', $ticket) }}" class="space-y-3" enctype="multipart/form-data">
                        @csrf
                        <textarea name="message" rows="4" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Write your message..."></textarea>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Attach Image (optional)</label>
                            <input type="file" name="image" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-black">
                        </div>
                        <button class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black transition">Send Message</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-user-dashboard-layout>
