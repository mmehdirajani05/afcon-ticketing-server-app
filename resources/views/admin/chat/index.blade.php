@extends('admin.layouts.app')

@section('title', 'Support Chat')
@section('page-title', 'Support Chat')
@section('breadcrumb', 'Admin › Support Chat')

@push('styles')
<style>
    .chat-sidebar { height: calc(100vh - 7rem); }
    .chat-messages { height: calc(100vh - 14rem); }
    .bubble-user  { border-radius: 18px 18px 18px 4px; }
    .bubble-admin { border-radius: 18px 18px 4px 18px; }
</style>
@endpush

@section('content')
<div class="bg-white rounded-2xl shadow-card overflow-hidden flex" style="height: calc(100vh - 7rem)">

    {{-- Conversations sidebar --}}
    <div class="w-72 border-r border-slate-100 flex flex-col shrink-0">
        <div class="px-4 py-3 border-b border-slate-100">
            <p class="text-xs font-bold text-slate-700">Conversations</p>
        </div>
        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $conv)
            <a href="{{ route('admin.chat.conversation', $conv) }}"
               class="flex items-center gap-3 px-4 py-3 border-b border-slate-50 hover:bg-slate-50 transition-colors
                      {{ isset($user) && $user->id === $conv->id ? 'bg-primary-50' : '' }}">
                <div class="relative shrink-0">
                    <div class="flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold text-white"
                         style="background: linear-gradient(135deg, #008EC0, #40BADF)">
                        {{ strtoupper(substr($conv->name, 0, 2)) }}
                    </div>
                    @if($conv->unread_count > 0)
                    <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center w-4 h-4 text-[9px] font-bold text-white rounded-full"
                          style="background:#008EC0">{{ $conv->unread_count }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ $conv->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ $conv->email }}</p>
                </div>
                @if($conv->last_message_at)
                <p class="text-[10px] text-slate-400 shrink-0">
                    {{ \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans(null, true, true) }}
                </p>
                @endif
            </a>
            @empty
            <div class="py-16 text-center px-4">
                <svg class="w-10 h-10 mx-auto text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-xs text-slate-400">No conversations yet</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Chat area --}}
    @if(isset($user) && isset($messages))
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Chat header --}}
        <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-100">
            <div class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white"
                 style="background: linear-gradient(135deg, #008EC0, #40BADF)">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800">{{ $user->name }}</p>
                <p class="text-[11px] text-slate-400">{{ $user->email }}</p>
            </div>
            <div class="ml-auto">
                <a href="{{ route('admin.users.show', $user) }}" class="text-[11px] font-semibold hover:underline" style="color:#008EC0">
                    View Profile →
                </a>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3" id="chatMessages">
            @forelse($messages as $msg)
            <div class="flex {{ $msg->direction === 'admin' ? 'justify-end' : 'justify-start' }}">
                @if($msg->direction === 'user')
                <div class="flex items-end gap-2 max-w-xs lg:max-w-md">
                    <div class="flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold text-white shrink-0 mb-1"
                         style="background: linear-gradient(135deg, #008EC0, #40BADF)">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="bubble-user bg-slate-100 text-slate-800 px-4 py-2.5 text-sm leading-relaxed">
                            {{ $msg->message }}
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 ml-1">{{ $msg->created_at->format('H:i') }}</p>
                    </div>
                </div>
                @else
                <div class="flex items-end gap-2 max-w-xs lg:max-w-md">
                    <div>
                        <div class="bubble-admin text-white px-4 py-2.5 text-sm leading-relaxed" style="background:#008EC0">
                            {{ $msg->message }}
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 mr-1 text-right">{{ $msg->created_at->format('H:i') }}</p>
                    </div>
                    <div class="flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold text-white shrink-0 mb-1"
                         style="background:#006B91">
                        A
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="py-12 text-center text-xs text-slate-400">No messages yet. Start the conversation!</div>
            @endforelse
        </div>

        {{-- Reply form --}}
        <div class="px-5 py-3 border-t border-slate-100">
            <form method="POST" action="{{ route('admin.chat.reply', $user) }}"
                  x-data="{ msg: '' }" class="flex gap-2">
                @csrf
                <input type="text" name="message" x-model="msg" placeholder="Type your reply…" required
                       class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                <button type="submit" :disabled="!msg.trim()"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90 disabled:opacity-40"
                        style="background:#008EC0">
                    <svg class="w-4 h-4 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Send
                </button>
            </form>
        </div>
    </div>

    @else
    {{-- Empty state --}}
    <div class="flex-1 flex items-center justify-center bg-slate-50">
        <div class="text-center px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, rgba(0,142,192,0.1), rgba(64,186,223,0.1))">
                <svg class="w-8 h-8" style="color:#008EC0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-700 mb-1">Select a conversation</h3>
            <p class="text-xs text-slate-400">Choose a user from the list to view and reply to their messages.</p>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Auto scroll to bottom of chat
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
</script>
@endpush
