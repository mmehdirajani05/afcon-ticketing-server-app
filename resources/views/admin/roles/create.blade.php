@extends('admin.layouts.app')

@section('title', 'Create Role')
@section('page-title', 'Create Role')
@section('breadcrumb', 'Admin › Roles › Create')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">New Admin Role</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Define a role and assign permission groups.</p>
        </div>

        <form method="POST" action="{{ route('admin.roles.store') }}" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Role Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Support Agent"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description</label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Brief role description…"
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
            </div>

            {{-- Permissions matrix --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-3">Permissions</label>
                <div class="space-y-3">
                    @foreach($permissions as $group => $perms)
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-200">
                            <span class="text-xs font-bold text-slate-700 capitalize">{{ $group }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-500">
                                <input type="checkbox" class="group-check w-3.5 h-3.5 accent-primary-600"
                                       data-group="{{ $group }}"
                                       onchange="toggleGroup('{{ $group }}', this.checked)">
                                All
                            </label>
                        </div>
                        <div class="px-4 py-3 flex flex-wrap gap-3">
                            @foreach($perms as $perm)
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600 hover:text-slate-800">
                                <input type="checkbox" name="permissions[]"
                                       value="{{ $group }}.{{ $perm }}"
                                       class="perm-check-{{ $group }} w-3.5 h-3.5 rounded accent-primary-600"
                                       {{ in_array($group . '.' . $perm, old('permissions', [])) ? 'checked' : '' }}>
                                <span class="capitalize">{{ $perm }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
                        style="background:#008EC0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Role
                </button>
                <a href="{{ route('admin.roles.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleGroup(group, checked) {
    document.querySelectorAll(`.perm-check-${group}`).forEach(cb => cb.checked = checked);
}
</script>
@endpush
