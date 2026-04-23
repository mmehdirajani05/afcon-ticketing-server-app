@extends('admin.layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="min-h-screen flex">

    {{-- Left — Branding Panel --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-2/5 flex-col justify-between p-12 text-white relative overflow-hidden"
         style="background: linear-gradient(145deg, #0B1629 0%, #006B91 55%, #008EC0 100%);">

        {{-- Decorative circles --}}
        <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full opacity-10 border-2 border-white"></div>
        <div class="absolute top-40 -right-10 w-48 h-48 rounded-full opacity-10 border border-white"></div>
        <div class="absolute -bottom-16 -left-16 w-80 h-80 rounded-full opacity-10 border-2 border-white"></div>

        {{-- Logo --}}
        <div class="flex items-center gap-3 relative z-10">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-base">{{ config('admin.app_name') }}</p>
                <p class="text-xs text-white/60">{{ config('admin.app_subtitle') }}</p>
            </div>
        </div>

        {{-- Hero text --}}
        <div class="relative z-10">
            <h2 class="text-4xl font-extrabold leading-tight mb-4">
                Manage the<br>tournament<br>experience.
            </h2>
            <p class="text-white/60 text-sm leading-relaxed max-w-xs">
                Monitor ticket sales, fan registrations, support chats, and announcements — all from one premium control centre.
            </p>

            {{-- Stats --}}
            <div class="mt-8 flex gap-8">
                <div>
                    <p class="text-2xl font-bold">2027</p>
                    <p class="text-[11px] text-white/50 uppercase tracking-wider">AFCON Edition</p>
                </div>
                <div class="w-px bg-white/20"></div>
                <div>
                    <p class="text-2xl font-bold">24</p>
                    <p class="text-[11px] text-white/50 uppercase tracking-wider">Nations</p>
                </div>
                <div class="w-px bg-white/20"></div>
                <div>
                    <p class="text-2xl font-bold">52</p>
                    <p class="text-[11px] text-white/50 uppercase tracking-wider">Matches</p>
                </div>
            </div>
        </div>

        <p class="text-[11px] text-white/30 relative z-10">&copy; {{ date('Y') }} {{ config('admin.app_name') }}. All rights reserved.</p>
    </div>

    {{-- Right — Login Form --}}
    <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 lg:px-16 xl:px-24 bg-white">

        <div class="w-full max-w-sm mx-auto">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-3 mb-10 lg:hidden">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl"
                     style="background: linear-gradient(135deg, #008EC0 0%, #40BADF 100%);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-800">{{ config('admin.app_name') }}</p>
            </div>

            <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Welcome back</h1>
            <p class="text-sm text-slate-500 mb-8">Sign in to your admin account to continue.</p>

            {{-- Session errors --}}
            @if(session('error'))
                <div class="mb-5 flex items-center gap-2 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="admin@example.com"
                           class="w-full px-4 py-2.5 text-sm rounded-xl border bg-white text-slate-800 placeholder-slate-400 outline-none transition-all
                                  focus:ring-2 focus:border-transparent
                                  {{ $errors->has('email') ? 'border-red-400 focus:ring-red-200' : 'border-slate-300 focus:ring-[#008EC0]/30 focus:border-[#008EC0]' }}">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4" x-data="{ show: false }">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 pr-11 text-sm rounded-xl border bg-white text-slate-800 placeholder-slate-400 outline-none transition-all
                                      focus:ring-2 focus:border-transparent
                                      {{ $errors->has('password') ? 'border-red-400 focus:ring-red-200' : 'border-slate-300 focus:ring-[#008EC0]/30 focus:border-[#008EC0]' }}">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 accent-[#008EC0]">
                        <span class="text-xs text-slate-600">Keep me signed in</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-6 text-sm font-semibold text-white rounded-xl transition-all duration-200 hover:opacity-90 active:scale-[.98] disabled:opacity-60"
                        style="background: linear-gradient(135deg, #008EC0 0%, #006B91 100%);">
                    <svg x-show="loading" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span x-text="loading ? 'Signing in…' : 'Sign in to Admin Panel'"></span>
                </button>
            </form>

            <p class="mt-8 text-center text-[11px] text-slate-400">
                Restricted access — authorised personnel only.
            </p>
        </div>
    </div>
</div>
@endsection
