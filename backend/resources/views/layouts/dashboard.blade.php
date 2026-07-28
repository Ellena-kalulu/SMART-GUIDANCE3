<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Smart MSCE Guidance · Luwinga</title>
    {{-- Page navigation progress bar --}}
    <style>
        #nprogress{position:fixed;top:0;left:0;width:100%;height:3px;z-index:9999;background:#2563eb;transition:width .25s ease;pointer-events:none}
        #nprogress::after{content:'';position:absolute;right:0;top:0;height:100%;width:60px;background:linear-gradient(to right,transparent,#3b82f6)}
    </style>

    {{-- Accessibility prefs applied before paint --}}
    <script>
        (function(){
            var h=document.documentElement;
            if(localStorage.getItem('cg-dark')==='1')     h.classList.add('dark-mode');
            if(localStorage.getItem('cg-contrast')==='1') h.classList.add('high-contrast');
            if(localStorage.getItem('cg-kbd')==='1')      h.classList.add('keyboard-nav');
            if(localStorage.getItem('cg-simple')==='1')   h.classList.add('simplified-lang');
            var f=localStorage.getItem('cg-font')||'base';
            if(f==='sm') h.classList.add('font-sm');
            if(f==='lg') h.classList.add('font-lg');
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Critical layout styles — always loaded so sidebar never overlaps main content --}}
    <style>
        *{-webkit-font-smoothing:antialiased}
        body{font-family:'Inter',sans-serif;background:#f4f7fb;color:#374151;margin:0}
        [x-cloak]{display:none!important}
        #sidebar{background:linear-gradient(180deg,#0f2550 0%,#122d5e 60%,#0e2347 100%)}
        #sidebar-nav{max-height:calc(100vh - 14rem);overflow-y:auto}
        #sidebar .nav-section-label{font-size:.6875rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.35);padding:.5rem .875rem .25rem;display:block;margin-top:.5rem}
        #main-content{transition:margin-left .25s cubic-bezier(.4,0,.2,1);min-width:0}
        .nav-active{background:rgba(255,255,255,.14);color:#fff;font-weight:600;border-radius:.625rem;position:relative}
        .nav-active::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:3px;background:#60a5fa;border-radius:0 2px 2px 0}
        .nav-inactive{color:rgba(255,255,255,.7);border-radius:.625rem}
        .nav-inactive:hover{background:rgba(255,255,255,.08);color:#fff}
        @media(min-width:1024px){#main-content{margin-left:16rem;width:calc(100% - 16rem);max-width:calc(100vw - 16rem)}}
        @media(min-width:1024px){#main-content.sidebar-collapsed{margin-left:4.5rem;width:calc(100% - 4.5rem);max-width:calc(100vw - 4.5rem)}}
        #sidebar.collapsed{width:4.5rem}
        #sidebar.collapsed .sidebar-label,#sidebar.collapsed .sidebar-brand-text,#sidebar.collapsed .sidebar-user-text{display:none!important}
    </style>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
        <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Inter','sans-serif'] },
                colors: {
                    brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#0f2550' },
                    blue:  { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#0f2550' }
                }
            }}
        }
        </script>
        <style>
            *{-webkit-font-smoothing:antialiased}
            body{font-family:'Inter',sans-serif;background:#f4f7fb;color:#374151}
            [x-cloak]{display:none!important}
            #sidebar{background:linear-gradient(180deg,#0f2550 0%,#122d5e 60%,#0e2347 100%)}
            .nav-active{background:rgba(255,255,255,.14);color:#fff;font-weight:600;border-radius:.625rem;position:relative}
            .nav-active::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:3px;background:#60a5fa;border-radius:0 2px 2px 0}
            .nav-inactive{color:rgba(255,255,255,.7);border-radius:.625rem}
            .nav-inactive:hover{background:rgba(255,255,255,.08);color:#fff}
            .card{background:#fff;border-radius:1rem;border:1px solid #e9edf4;box-shadow:0 1px 3px rgba(15,23,42,.05)}
            .btn-primary{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:.75rem;padding:.625rem 1.125rem;font-weight:600;font-size:.875rem;border:none;cursor:pointer;box-shadow:0 1px 3px rgba(79,70,229,.3)}
            .btn-primary:hover{background:linear-gradient(135deg,#1d4ed8,#1e40af)}
            .btn-secondary{display:inline-flex;align-items:center;gap:.5rem;background:#fff;color:#2563eb;border:1.5px solid #bfdbfe;border-radius:.75rem;padding:.625rem 1.125rem;font-weight:600;font-size:.875rem;cursor:pointer}
            .form-input{width:100%;padding:.625rem .875rem;font-size:.875rem;border:1.5px solid #e5e7eb;border-radius:.75rem;outline:none;background:#fff}
            .form-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
            .badge-blue{display:inline-flex;background:#dbeafe;color:#1d4ed8;border-radius:9999px;font-size:.6875rem;font-weight:700;padding:.2rem .625rem}
            .badge-green{display:inline-flex;background:#dcfce7;color:#15803d;border-radius:9999px;font-size:.6875rem;font-weight:700;padding:.2rem .625rem}
            .badge-amber{display:inline-flex;background:#fef3c7;color:#b45309;border-radius:9999px;font-size:.6875rem;font-weight:700;padding:.2rem .625rem}
            .badge-red{display:inline-flex;background:#fee2e2;color:#b91c1c;border-radius:9999px;font-size:.6875rem;font-weight:700;padding:.2rem .625rem}
            .stat-card{background:#fff;border-radius:1rem;border:1px solid #e9edf4;padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem}
            .alert-success{display:flex;align-items:flex-start;gap:.75rem;padding:.875rem 1rem;border-radius:.75rem;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;font-size:.875rem}
            .alert-error{display:flex;align-items:flex-start;gap:.75rem;padding:.875rem 1rem;border-radius:.75rem;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;font-size:.875rem}
            .alert-info{display:flex;align-items:flex-start;gap:.75rem;padding:.875rem 1rem;border-radius:.75rem;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;font-size:.875rem}
            .page-surface{background:#f4f7fb}
            .action-card{display:flex;align-items:flex-start;gap:1rem;padding:1.25rem;border-radius:1rem;border:1px solid #e9edf4;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.05);text-decoration:none;transition:all .2s}
            .action-card:hover{border-color:#93c5fd;box-shadow:0 4px 16px rgba(15,23,42,.1);transform:translateY(-2px)}
            .action-icon{width:2.75rem;height:2.75rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;background:#eff6ff;color:#2563eb;flex-shrink:0;transition:all .2s}
            .action-card:hover .action-icon{background:#2563eb;color:#fff}
            html.dark-mode body{background:#0f172a!important;color:#e2e8f0}
            html.dark-mode .card,html.dark-mode .bg-white{background:#1e293b!important;border-color:#334155!important}
            html.dark-mode header{background:#1e293b!important;border-color:#334155!important}
            html.high-contrast body{background:#000!important;color:#fff!important}
            html.high-contrast .card,html.high-contrast .bg-white{background:#000!important;color:#fff!important;border:2px solid #fff!important}
            html.high-contrast a,html.high-contrast button{color:#ff0!important}
            html.font-sm{font-size:14px!important}
            html.font-lg{font-size:20px!important}
            html.keyboard-nav *:focus{outline:3px solid #2563eb!important;outline-offset:2px!important}
            html.simplified-lang .tech-term{display:none!important}
            #sidebar .nav-section-label{font-size:.6875rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.35);padding:.5rem .875rem .25rem;display:block;margin-top:.5rem}
            #main-content{transition:margin-left .25s cubic-bezier(.4,0,.2,1)}
            @media(min-width:1024px){#main-content{margin-left:16rem;width:calc(100% - 16rem);max-width:calc(100vw - 16rem)}} #sidebar-nav{max-height:calc(100vh - 14rem);overflow-y:auto} #sidebar.collapsed{width:4.5rem} #sidebar.collapsed .sidebar-label,#sidebar.collapsed .sidebar-brand-text,#sidebar.collapsed .sidebar-user-text{display:none!important} @media(min-width:1024px){#main-content.sidebar-collapsed{margin-left:4.5rem;width:calc(100% - 4.5rem);max-width:calc(100vw - 4.5rem)}}
        </style>
    @endif

    <script src="{{ asset('js/accessibility.js') }}"></script>
    @stack('styles')
</head>

<body class="page-surface"
      @if(auth()->user()->isStudent())
      data-disability="{{ auth()->user()->studentProfile?->disability_type ?? 'none' }}"
      @endif
      data-access-mode="{{ session('access_mode', 'normal') }}">

@php $accessMode = session('access_mode', 'normal'); @endphp

@if($accessMode === 'visual')
<script src="{{ asset('js/visual-mode.js') }}" defer></script>
@endif

<div x-data="{
        mobileOpen: false,
        sidebarCollapsed: localStorage.getItem('cg-sidebar-collapsed') === '1'
     }"
     x-init="$watch('sidebarCollapsed', v => localStorage.setItem('cg-sidebar-collapsed', v ? '1' : '0'))"
     class="min-h-screen">

    {{-- Mobile overlay --}}
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition-opacity duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="mobileOpen = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 md:hidden"></div>

    {{-- ══════════ SIDEBAR ══════════ --}}
    <aside id="sidebar"
           class="fixed top-0 left-0 z-50 h-screen w-64 flex flex-col -translate-x-full lg:translate-x-0"
           :class="{ 'translate-x-0': mobileOpen, 'collapsed': sidebarCollapsed && !mobileOpen }"
           style="transition: width 0.25s cubic-bezier(0.4,0,0.2,1), transform 0.25s cubic-bezier(0.4,0,0.2,1); background:linear-gradient(180deg,#0f2550 0%,#122d5e 60%,#0e2347 100%)">

        {{-- Brand header --}}
        <div class="flex items-center gap-3 px-5 py-5" style="border-bottom:1px solid rgba(255,255,255,0.1)">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background:rgba(255,255,255,0.15);backdrop-filter:blur(4px)">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <div class="sidebar-brand-text flex-1 min-w-0">
                <p class="text-white font-bold text-sm leading-tight">CareerGuide</p>
                <p class="text-xs leading-tight" style="color:rgba(255,255,255,0.45)">Luwinga Secondary School</p>
            </div>
            <button @click="mobileOpen = false" class="lg:hidden p-1 rounded-lg transition-colors"
                    style="color:rgba(255,255,255,0.5)" aria-label="Close menu">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- User pill --}}
        <a href="{{ route('profile') }}"
           class="mx-3 mt-3 mb-1 flex items-center gap-3 px-3 py-2.5 rounded-xl group transition-all"
           style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.1)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-xs shrink-0"
                 style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0 sidebar-user-text">
                <p class="text-white text-sm font-semibold truncate leading-tight">{{ auth()->user()->name }}</p>
                <p class="text-xs leading-tight capitalize" style="color:rgba(255,255,255,0.45)">
                    {{ auth()->user()->role }}
                    @if(auth()->user()->isStudent() && auth()->user()->studentProfile)
                        · {{ auth()->user()->studentProfile->form_level }}
                    @endif
                </p>
            </div>
            <svg class="w-3.5 h-3.5 shrink-0 sidebar-user-text transition-colors group-hover:text-white"
                 style="color:rgba(255,255,255,0.3)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>

        {{-- Navigation --}}
        <nav id="sidebar-nav" class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
            @yield('sidebar')
        </nav>

        {{-- Bottom actions --}}
        <div class="px-3 pb-4 space-y-0.5" style="border-top:1px solid rgba(255,255,255,0.1); padding-top:0.75rem; margin-top:auto">
            <a href="{{ route('profile') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('profile') ? 'nav-active' : 'nav-inactive' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="sidebar-label">My Profile</span>
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all"
                        style="color:rgba(255,255,255,0.6)" onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.color='#fca5a5'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,0.6)'">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="sidebar-label">Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════ MAIN AREA ══════════ --}}
    <div id="main-content" class="main-with-sidebar flex flex-col min-h-screen min-w-0" :class="{ 'sidebar-collapsed': sidebarCollapsed && !mobileOpen }">

        {{-- ── Topbar ── --}}
        <header class="sticky top-0 z-40 flex items-center shrink-0 h-16 px-4 sm:px-6 bg-white"
                style="border-bottom:1px solid #e9edf4; box-shadow:0 1px 8px rgba(15,23,42,0.04)">

            {{-- Left: toggle + breadcrumb --}}
            <div class="flex items-center gap-3 min-w-0">
                {{-- Mobile hamburger (all roles) --}}
                <button @click="mobileOpen = !mobileOpen"
                        class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg transition-colors hover:bg-slate-100"
                        style="color:#6b7280" aria-label="Toggle menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Desktop collapse (all roles) --}}
                <button @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden lg:flex items-center justify-center w-9 h-9 rounded-lg transition-colors hover:bg-slate-100"
                        style="color:#6b7280" aria-label="Collapse sidebar">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Breadcrumb --}}
                <div class="hidden sm:flex items-center gap-1.5 text-sm min-w-0">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <span style="color:#9ca3af">{{ ucfirst(auth()->user()->role) }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0" style="color:#d1d5db" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="font-semibold truncate" style="color:#374151">Dashboard</span>
                    @endif
                </div>
            </div>

            {{-- Parent language selector — top left --}}
            @if(auth()->user()->isParent())
            <div class="ml-3 hidden sm:flex items-center">
                <form action="{{ route('parent.locale') }}" method="POST" class="flex items-center">
                    @csrf
                    <select name="locale" onchange="this.form.submit()"
                            class="text-xs rounded-lg border border-gray-200 py-1.5 px-2.5 bg-white text-gray-600 focus:ring-2 focus:ring-blue-400 focus:border-blue-300 cursor-pointer"
                            aria-label="{{ __('parent.language') }}">
                        <option value="en" @selected(session('locale', 'en') === 'en')>🇬🇧 English</option>
                        <option value="ny" @selected(session('locale') === 'ny')>🇲🇼 Chichewa</option>
                        <option value="tum" @selected(session('locale') === 'tum')>🇲🇼 Chitumbuka</option>
                    </select>
                </form>
            </div>
            @endif

            {{-- Right: actions --}}
            <div class="flex items-center gap-2 ml-auto">

                {{-- Accessibility --}}
                @include('partials.accessibility-panel')

                {{-- Notification bell --}}
                <button class="relative flex items-center justify-center w-9 h-9 rounded-lg transition-colors hover:bg-slate-100"
                        aria-label="Notifications" style="color:#6b7280">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                </button>

                <span class="w-px h-5 bg-slate-200"></span>

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 pl-1.5 pr-2.5 py-1.5 rounded-xl transition-colors hover:bg-slate-100"
                            aria-label="User menu">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                             style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-semibold leading-tight" style="color:#0d1f3c">{{ Str::words(auth()->user()->name, 2, '') }}</p>
                            <p class="text-xs leading-tight capitalize" style="color:#9ca3af">{{ auth()->user()->role }}</p>
                        </div>
                        <svg class="hidden sm:block w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''"
                             style="color:#9ca3af" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition duration-150 ease-out"
                         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition duration-100 ease-in"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl py-1.5 z-50 origin-top-right"
                         style="border:1px solid #e9edf4; box-shadow:0 8px 24px rgba(15,23,42,0.12), 0 2px 8px rgba(15,23,42,0.06)">

                        <div class="px-4 py-2.5" style="border-bottom:1px solid #f1f5f9">
                            <p class="text-sm font-bold" style="color:#0d1f3c">{{ auth()->user()->name }}</p>
                            <p class="text-xs capitalize" style="color:#9ca3af">{{ auth()->user()->role }} · {{ auth()->user()->email }}</p>
                        </div>

                        <a href="{{ route('profile') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-slate-50"
                           style="color:#374151">
                            <svg class="w-4 h-4" style="color:#9ca3af" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile Settings
                        </a>

                        <div style="border-top:1px solid #f1f5f9; margin-top:0.25rem; padding-top:0.25rem">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-red-50"
                                        style="color:#dc2626">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Page content ── --}}
        <main class="flex-1 p-4 sm:p-6 page-surface overflow-x-auto">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if (!file_exists(public_path('build/manifest.json')))
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
@endif
@stack('scripts')
<script>
// Lightweight page-load progress bar for fast visual feedback on navigation
(function(){
    var bar=null,timer=null,w=0;
    function start(){
        if(bar) bar.remove();
        bar=document.createElement('div');bar.id='nprogress';bar.style.width='0';
        document.body.appendChild(bar);
        w=0; tick();
    }
    function tick(){
        w=w<80?w+Math.random()*12:w<95?w+1:w;
        bar.style.width=w+'%';
        timer=setTimeout(tick, 250);
    }
    function done(){
        if(!bar)return;
        clearTimeout(timer);
        bar.style.width='100%';
        setTimeout(function(){if(bar)bar.remove();bar=null;},350);
    }
    document.addEventListener('click',function(e){
        var a=e.target.closest('a');
        if(a&&a.href&&!a.target&&!a.href.startsWith('#')&&!a.href.startsWith('javascript')&&!e.metaKey&&!e.ctrlKey&&a.origin===location.origin){
            start();
        }
        var btn=e.target.closest('button[type="submit"],input[type="submit"]');
        if(btn) start();
    });
    document.addEventListener('submit',function(){ start(); });
    window.addEventListener('pageshow',function(){ done(); });
    window.addEventListener('load',function(){ done(); });
})();
</script>
</body>
</html>
