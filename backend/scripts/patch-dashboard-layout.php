<?php
$f = __DIR__ . '/../resources/views/layouts/dashboard.blade.php';
$c = file_get_contents($f);

$css = <<<'HTML'

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
        @media(min-width:768px){#main-content{margin-left:16rem;width:calc(100% - 16rem);max-width:calc(100vw - 16rem)}}
        @media(min-width:1024px){#main-content.sidebar-collapsed{margin-left:4.5rem;width:calc(100% - 4.5rem);max-width:calc(100vw - 4.5rem)}}
        #sidebar.collapsed{width:4.5rem}
        #sidebar.collapsed .sidebar-label,#sidebar.collapsed .sidebar-brand-text,#sidebar.collapsed .sidebar-user-text{display:none!important}
    </style>
HTML;

$anchor = "    <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap\" rel=\"stylesheet\">\n\n    @if (file_exists(public_path('build/manifest.json')))";

if (strpos($c, 'Critical layout styles') === false) {
    $c = str_replace($anchor, "    <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap\" rel=\"stylesheet\">" . $css . "\n    @if (file_exists(public_path('build/manifest.json')))", $c);
}

$old = <<<'OLD'
                {{-- Divider + breadcrumb --}}
                <span class="hidden sm:block w-px h-5 bg-slate-200"></span>
                <div class="hidden sm:flex items-center gap-1.5 text-sm min-w-0">
                    <span style="color:#9ca3af">{{ ucfirst(auth()->user()->role) }}</span>
                    @hasSection('breadcrumb')
                        <svg class="w-3.5 h-3.5 shrink-0" style="color:#d1d5db" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="font-semibold truncate" style="color:#374151">@yield('breadcrumb')</span>
                    @endif
                </div>
OLD;

$new = <<<'NEW'
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
NEW;

if (strpos($c, '{{-- Breadcrumb --}}') === false) {
    $c = str_replace($old, $new, $c);
}

file_put_contents($f, $c);
echo "dashboard layout patched\n";
