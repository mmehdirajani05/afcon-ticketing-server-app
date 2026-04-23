@extends('admin.layouts.app')

@section('title', 'Create Sub-admin')
@section('page-title', 'Add Sub-admin')
@section('breadcrumb', 'Admin › Roles › Add Sub-admin')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">New Sub-admin Account</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">This user will have limited admin access based on their assigned role.</p>
        </div>

        <form method="POST" action="{{ route('admin.sub-admins.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white @error('email') border-red-400 @enderror">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assign Role <span class="text-red-500">*</span></label>
                <select name="admin_role_id" required
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white @error('admin_role_id') border-red-400 @enderror">
                    <option value="">— Select a role —</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('admin_role_id') == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('admin_role_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @if($roles->isEmpty())
                <p class="mt-1 text-xs text-amber-600">No roles yet. <a href="{{ route('admin.roles.create') }}" class="underline">Create a role first.</a></p>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white @error('password') border-red-400 @enderror">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90"
                        style="background:#008EC0">
                    Create Sub-admin
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
