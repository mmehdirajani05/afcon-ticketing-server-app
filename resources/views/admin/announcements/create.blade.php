@extends('admin.layouts.app')

@section('title', 'Create Announcement')
@section('page-title', 'New Announcement')
@section('breadcrumb', 'Admin › Announcements › Create')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Create Announcement</h3>
        </div>

        <form method="POST" action="{{ route('admin.announcements.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Announcement headline…"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Body <span class="text-red-500">*</span></label>
                <textarea name="body" rows="6" required placeholder="Write your announcement content here…"
                          class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white resize-none @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type</label>
                    <select name="type" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                        <option value="info" @selected(old('type')==='info')>ℹ️ Info</option>
                        <option value="warning" @selected(old('type')==='warning')>⚠️ Warning</option>
                        <option value="success" @selected(old('type')==='success')>✅ Success</option>
                        <option value="danger" @selected(old('type')==='danger')>🚨 Danger</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                        <option value="draft" @selected(old('status')==='draft' || !old('status'))>Draft</option>
                        <option value="published" @selected(old('status')==='published')>Published</option>
                        <option value="archived" @selected(old('status')==='archived')>Archived</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Publish Date</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                           class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_pinned" id="is_pinned" value="1" class="w-4 h-4 rounded accent-primary-600" {{ old('is_pinned') ? 'checked' : '' }}>
                <label for="is_pinned" class="text-xs font-semibold text-slate-700 cursor-pointer">Pin this announcement (show at top)</label>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
                        style="background:#008EC0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Announcement
                </button>
                <a href="{{ route('admin.announcements.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
