@extends('admin.layouts.app')

@section('title', 'Edit Announcement')
@section('page-title', 'Edit Announcement')
@section('breadcrumb', 'Admin › Announcements › Edit')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Edit: {{ $announcement->title }}</h3>
        </div>

        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="p-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Body <span class="text-red-500">*</span></label>
                <textarea name="body" rows="6" required
                          class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white resize-none @error('body') border-red-400 @enderror">{{ old('body', $announcement->body) }}</textarea>
                @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type</label>
                    <select name="type" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                        @foreach(['info'=>'ℹ️ Info','warning'=>'⚠️ Warning','success'=>'✅ Success','danger'=>'🚨 Danger'] as $val=>$label)
                        <option value="{{ $val }}" @selected(old('type',$announcement->type)===$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                        @foreach(['draft'=>'Draft','published'=>'Published','archived'=>'Archived'] as $val=>$label)
                        <option value="{{ $val }}" @selected(old('status',$announcement->status)===$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Publish Date</label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_pinned" id="is_pinned" value="1" class="w-4 h-4 rounded accent-primary-600"
                       {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                <label for="is_pinned" class="text-xs font-semibold text-slate-700 cursor-pointer">Pin this announcement</label>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
                        style="background:#008EC0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
                <a href="{{ route('admin.announcements.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                    Cancel
                </a>
                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="ml-auto"
                      x-data onsubmit.prevent="confirm('Delete this announcement?') && $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-700 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>
@endsection
