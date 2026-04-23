@extends('admin.layouts.app')

@section('title', 'Announcements')
@section('page-title', 'News & Announcements')
@section('breadcrumb', 'Admin › Announcements')

@section('content')

{{-- Top bar --}}
<div class="flex items-center justify-between mb-4">
    <form method="GET" action="{{ route('admin.announcements.index') }}" class="flex gap-3">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title…"
                   class="pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white w-56">
        </div>
        <select name="status" class="py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white"
                onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="draft" @selected(request('status')==='draft')>Draft</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="archived" @selected(request('status')==='archived')>Archived</option>
        </select>
        <select name="type" class="py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white"
                onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="info" @selected(request('type')==='info')>Info</option>
            <option value="warning" @selected(request('type')==='warning')>Warning</option>
            <option value="success" @selected(request('type')==='success')>Success</option>
            <option value="danger" @selected(request('type')==='danger')>Danger</option>
        </select>
    </form>
    <a href="{{ route('admin.announcements.create') }}"
       class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
       style="background:#008EC0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Announcement
    </a>
</div>

{{-- Cards grid --}}
@if($announcements->isEmpty())
<div class="bg-white rounded-2xl shadow-card p-16 text-center">
    <svg class="w-14 h-14 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
    </svg>
    <p class="text-slate-400 text-sm mb-4">No announcements yet</p>
    <a href="{{ route('admin.announcements.create') }}" class="text-sm font-semibold" style="color:#008EC0">Create first announcement →</a>
</div>
@else
<div class="space-y-3">
    @php
    $typeConfig = [
        'info'    => ['icon'=>'ℹ️', 'color'=>'text-blue-700',  'bg'=>'bg-blue-50',  'border'=>'border-blue-200'],
        'warning' => ['icon'=>'⚠️', 'color'=>'text-amber-700', 'bg'=>'bg-amber-50', 'border'=>'border-amber-200'],
        'success' => ['icon'=>'✅', 'color'=>'text-emerald-700','bg'=>'bg-emerald-50','border'=>'border-emerald-200'],
        'danger'  => ['icon'=>'🚨', 'color'=>'text-red-700',   'bg'=>'bg-red-50',   'border'=>'border-red-200'],
    ];
    $statusConfig = [
        'draft'     => 'text-slate-600 bg-slate-100',
        'published' => 'text-emerald-700 bg-emerald-50',
        'archived'  => 'text-slate-400 bg-slate-50',
    ];
    @endphp

    @foreach($announcements as $ann)
    @php $tc = $typeConfig[$ann->type] ?? $typeConfig['info']; @endphp
    <div class="bg-white rounded-2xl shadow-card p-5 hover:shadow-card-hover transition-shadow">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4 flex-1 min-w-0">
                <div class="text-2xl mt-0.5 shrink-0">{{ $tc['icon'] }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <h3 class="text-sm font-bold text-slate-800">{{ $ann->title }}</h3>
                        @if($ann->is_pinned)
                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-700 rounded uppercase tracking-wider">Pinned</span>
                        @endif
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $statusConfig[$ann->status] ?? '' }}">
                            {{ ucfirst($ann->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 line-clamp-2 mb-2">{{ Str::limit(strip_tags($ann->body), 160) }}</p>
                    <div class="flex items-center gap-3 text-[10px] text-slate-400">
                        <span>By {{ $ann->author?->name ?? '—' }}</span>
                        <span>·</span>
                        <span>{{ $ann->created_at->format('d M Y') }}</span>
                        @if($ann->published_at)
                        <span>·</span>
                        <span>Published {{ $ann->published_at->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('admin.announcements.edit', $ann) }}"
                   class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}"
                      x-data onsubmit.prevent="confirm('Delete this announcement?') && $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($announcements->hasPages())
<div class="mt-4">{{ $announcements->links('pagination::tailwind') }}</div>
@endif
@endif
@endsection
