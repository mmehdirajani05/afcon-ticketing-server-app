<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('admin.app_name') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN with custom primary color config --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: {
                            50:  '#f0f9ff',
                            100: '#e0f4fe',
                            200: '#bae8fd',
                            300: '#7dd6fb',
                            400: '#38bcf0',
                            500: '#008EC0',
                            600: '#006b91',
                            700: '#005578',
                            800: '#003d58',
                            900: '#002a3d',
                        },
                        sidebar: { DEFAULT: '#0B1629', hover: '#162035', active: 'rgba(0,142,192,0.12)' },
                    },
                    boxShadow: {
                        card: '0 1px 3px 0 rgba(0,0,0,.06), 0 1px 2px -1px rgba(0,0,0,.06)',
                        'card-hover': '0 4px 12px 0 rgba(0,0,0,.10)',
                    },
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Sidebar active indicator */
        .nav-link.active { background: rgba(0,142,192,0.12); color: #008EC0; }
        .nav-link.active svg { color: #008EC0; }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: #008EC0;
            border-radius: 0 4px 4px 0;
        }

        /* Smooth transitions */
        .nav-link { position: relative; transition: background .15s, color .15s; }
        .nav-link:hover:not(.active) { background: rgba(255,255,255,0.05); }

        /* Toast notification */
        .toast { animation: slideIn .3s ease, fadeOut .4s ease 3.6s forwards; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
    </style>

    @stack('styles')
</head>
<body class="h-full bg-slate-50 font-sans antialiased">
<div x-data="{ sidebarOpen: false }" class="flex h-full">

    {{-- Mobile sidebar backdrop --}}
    <div x-show="sidebarOpen" x-cloak x-transition:enter="transition ease-in-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in-out duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col bg-[#0B1629] transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <div class="flex items-center justify-center w-9 h-9 rounded-xl shrink-0"
                 style="background: linear-gradient(135deg, #008EC0 0%, #40BADF 100%);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div class="leading-tight">
                <p class="text-sm font-bold text-white">{{ config('admin.app_name') }}</p>
                <p class="text-[10px] text-white/50 font-medium">{{ config('admin.app_subtitle') }}</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-white/30">Main</p>

            <a href="{{ route('admin.dashboard') }}"
               class="nav-link flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/70 hover:text-white {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <svg style="width:1em;height:1em;min-width:14px;min-height:14px" class="shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <p class="px-3 pt-4 mb-2 text-[10px] font-semibold uppercase tracking-widest text-white/30">Management</p>

            <a href="{{ route('admin.users.index') }}"
               class="nav-link flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/70 hover:text-white {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg style="width:1em;height:1em;min-width:14px;min-height:14px" class="shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Users
            </a>

            <a href="{{ route('admin.orders.index') }}"
               class="nav-link flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/70 hover:text-white {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                <svg style="width:1em;height:1em;min-width:14px;min-height:14px" class="shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Orders
            </a>

            <a href="{{ route('admin.announcements.index') }}"
               class="nav-link flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/70 hover:text-white {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}">
                <svg style="width:1em;height:1em;min-width:14px;min-height:14px" class="shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                Announcements
            </a>

            <p class="px-3 pt-4 mb-2 text-[10px] font-semibold uppercase tracking-widest text-white/30">Support</p>

            <a href="{{ route('admin.chat.index') }}"
               class="nav-link flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/70 hover:text-white {{ request()->routeIs('admin.chat*') ? 'active' : '' }}">
                <svg style="width:1em;height:1em;min-width:14px;min-height:14px" class="shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Support Chat
                @php $unreadCount = \App\Models\ChatMessage::where('direction','user')->where('is_read',false)->count(); @endphp
                @if($unreadCount > 0)
                    <span class="ml-auto flex items-center justify-center w-4 h-4 text-[9px] font-bold text-white rounded-full"
                          style="background:#008EC0">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </a>

            <p class="px-3 pt-4 mb-2 text-[10px] font-semibold uppercase tracking-widest text-white/30">Settings</p>

            @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('admin.roles.index') }}"
               class="nav-link flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/70 hover:text-white {{ request()->routeIs('admin.roles*') || request()->routeIs('admin.sub-admins*') ? 'active' : '' }}">
                <svg style="width:1em;height:1em;min-width:14px;min-height:14px" class="shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Roles & Permissions
            </a>
            @endif
        </nav>

        {{-- User profile --}}
        <div class="px-3 py-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white shrink-0"
                     style="background: linear-gradient(135deg, #008EC0 0%, #40BADF 100%);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-white/40 truncate">{{ Auth::user()->global_role === 'admin' ? 'Super Admin' : (Auth::user()->adminRole?->name ?? 'Sub Admin') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top navbar --}}
        <header class="sticky top-0 z-10 flex items-center h-14 px-4 lg:px-6 bg-white border-b border-slate-200 shrink-0">

            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="mr-3 p-1.5 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 lg:hidden transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page title --}}
            <div class="flex-1">
                <h1 class="text-sm font-semibold text-slate-800">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                    <p class="text-[11px] text-slate-400">@yield('breadcrumb')</p>
                @endif
            </div>

            {{-- Right actions --}}
            <div class="flex items-center gap-2">

                {{-- Notifications --}}
                <button class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(isset($unreadChatCount) && $unreadChatCount > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full" style="background:#008EC0"></span>
                    @endif
                </button>

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full text-[11px] font-bold text-white shrink-0"
                             style="background: linear-gradient(135deg, #008EC0 0%, #40BADF 100%);">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <span class="hidden sm:block text-[13px] font-medium text-slate-700">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-50">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-slate-400">{{ Auth::user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full gap-2 px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-red-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="toast mb-4 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="toast mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@stack('scripts')
</body>
</html>
