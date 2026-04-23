@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('breadcrumb', 'Admin › Roles & Sub-admins')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

    {{-- Roles list --}}
    <div class="xl:col-span-2 space-y-4">

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Admin Roles</h2>
            <a href="{{ route('admin.roles.create') }}"
               class="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
               style="background:#008EC0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Role
            </a>
        </div>

        <div class="space-y-3">
            @forelse($roles as $role)
            <div class="bg-white rounded-2xl shadow-card p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-sm font-bold text-slate-800">{{ $role->name }}</h3>
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 text-slate-500 rounded-full font-mono">{{ $role->slug }}</span>
                        </div>
                        @if($role->description)
                        <p class="text-xs text-slate-400 mb-3">{{ $role->description }}</p>
                        @endif

                        {{-- Permissions matrix --}}
                        @if($role->permissions)
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($role->permissions as $perm)
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full text-primary-700 bg-primary-50">
                                {{ $perm }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-1 ml-4">
                        <span class="text-[11px] text-slate-400 mr-2">{{ $role->users_count }} user{{ $role->users_count !== 1 ? 's' : '' }}</span>
                        <a href="{{ route('admin.roles.edit', $role) }}"
                           class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                              x-data onsubmit.prevent="confirm('Delete role and revoke all users?') && $el.submit()">
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
            @empty
            <div class="bg-white rounded-2xl shadow-card p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-sm text-slate-400 mb-3">No roles created yet</p>
                <a href="{{ route('admin.roles.create') }}" class="text-xs font-semibold" style="color:#008EC0">Create your first role →</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Sub-admins --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Sub-admins</h2>
            <a href="{{ route('admin.sub-admins.create') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border transition-colors hover:bg-slate-50"
               style="color:#008EC0; border-color:#008EC0">
                + Add
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-card overflow-hidden">
            @forelse($subAdmins as $sa)
            <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-center w-8 h-8 rounded-full text-[11px] font-bold text-white shrink-0"
                     style="background: linear-gradient(135deg, #006B91, #008EC0)">
                    {{ strtoupper(substr($sa->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ $sa->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ $sa->adminRole?->name ?? '—' }}</p>
                </div>
                @if($sa->is_active)
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                @else
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                @endif
            </div>
            @empty
            <div class="px-4 py-10 text-center text-xs text-slate-400">
                No sub-admins yet.
                <a href="{{ route('admin.sub-admins.create') }}" class="block mt-1 font-semibold" style="color:#008EC0">Create one →</a>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
