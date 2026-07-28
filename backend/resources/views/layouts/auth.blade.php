<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CareerGuide') — Smart Career Guidance · Luwinga</title>

    <script>
        (function(){
            var h=document.documentElement;
            if(localStorage.getItem('cg-dark')==='1')     h.classList.add('dark-mode');
            if(localStorage.getItem('cg-contrast')==='1') h.classList.add('high-contrast');
            if(localStorage.getItem('cg-kbd')==='1')      h.classList.add('keyboard-nav');
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
        tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','sans-serif']},colors:{brand:{50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#0f2550'},blue:{50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#0f2550'}}}}}
        </script>
    @endif

    <style>
        *{-webkit-font-smoothing:antialiased;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;margin:0;padding:0}

        .auth-left{
            background:linear-gradient(150deg,#0f2550 0%,#2563eb 50%,#1d4ed8 100%);
            position:relative;overflow:hidden;
        }
        .auth-left::before{
            content:'';position:absolute;inset:0;
            background-image:
                radial-gradient(circle at 20% 80%,rgba(59,130,246,0.3) 0%,transparent 50%),
                radial-gradient(circle at 80% 20%,rgba(147,197,253,0.15) 0%,transparent 40%),
                radial-gradient(circle at 50% 50%,rgba(255,255,255,0.02) 0%,transparent 60%);
            pointer-events:none;
        }
        .auth-left::after{
            content:'';position:absolute;bottom:-60px;left:-60px;
            width:320px;height:320px;border-radius:50%;
            border:1px solid rgba(255,255,255,0.06);
        }
        .auth-orb{
            position:absolute;border-radius:50%;
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.08);
        }

        .auth-card{
            background:#fff;border-radius:1.25rem;
            border:1px solid #e9edf4;
            box-shadow:0 8px 40px rgba(15,35,80,0.09),0 2px 8px rgba(15,35,80,0.04);
            overflow:hidden;
        }

        .form-input{
            width:100%;padding:.625rem .875rem;font-size:.875rem;color:#111827;
            background:#fff;border:1.5px solid #e5e7eb;border-radius:.75rem;
            outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;
        }
        .form-input::placeholder{color:#9ca3af}
        .form-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
        .form-label{display:block;font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.375rem}
        .btn-primary{
            display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
            width:100%;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;
            border:none;border-radius:.875rem;padding:.8125rem 1.5rem;
            font-size:.9375rem;font-weight:700;cursor:pointer;
            box-shadow:0 2px 8px rgba(79,70,229,.3),0 4px 16px rgba(79,70,229,.15);
            transition:all .15s;font-family:inherit;
        }
        .btn-primary:hover{background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 4px 12px rgba(79,70,229,.35),0 6px 20px rgba(79,70,229,.2);transform:translateY(-1px)}
        .btn-primary:active{transform:translateY(0)}

        .input-icon-wrap{position:relative}
        .input-icon-wrap .icon{position:absolute;top:50%;left:.75rem;transform:translateY(-50%);width:1.0625rem;height:1.0625rem;color:#9ca3af;pointer-events:none;transition:color .15s}
        .input-icon-wrap:focus-within .icon{color:#3b82f6}
        .input-icon-wrap .form-input{padding-left:2.375rem}

        @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .fade-up{animation:fadeUp .4s ease both}
        .stagger-1{animation-delay:.05s}
        .stagger-2{animation-delay:.1s}
        .stagger-3{animation-delay:.15s}
        .stagger-4{animation-delay:.2s}
        .stagger-5{animation-delay:.25s}

        html.dark-mode body{background:#0f172a!important}
        html.dark-mode .auth-card{background:#1e293b!important;border-color:#334155!important}
        html.dark-mode .form-input{background:#334155!important;border-color:#475569!important;color:#e2e8f0!important}
        html.dark-mode .form-label{color:#e2e8f0!important}
        html.dark-mode .text-slate-700,.text-gray-700{color:#cbd5e1!important}
        html.high-contrast body{background:#000!important}
        html.high-contrast .auth-card{background:#000!important;border:2px solid #fff!important;color:#fff!important}
        html.keyboard-nav *:focus{outline:3px solid #2563eb!important;outline-offset:2px!important}

        [x-cloak]{display:none!important}
        .auth-shell{display:flex;min-height:100vh;width:100%}
        .auth-left-panel{display:none;flex-direction:column;justify-content:space-between;width:46%;min-height:100vh;padding:3rem;position:relative}
        .auth-right-panel{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.5rem 2.5rem;min-height:100vh;width:100%;background:#f4f7fb}
        .auth-right-panel .auth-card{width:100%;max-width:28rem;margin:0 auto}
        @media(min-width:1024px){
            .auth-left-panel{display:flex}
            .auth-right-panel{width:54%;flex:1}
        }
    </style>

    <script src="{{ asset('js/accessibility.js') }}"></script>
</head>

<body style="min-height:100vh;display:flex;flex-direction:column">

    {{-- Dark mode toggle button — top right, always visible on login page --}}
    <button onclick="(function(){var h=document.documentElement;var isDark=h.classList.toggle('dark-mode');localStorage.setItem('cg-dark',isDark?'1':'0');this.textContent=isDark?'☀️':'🌙'})()"
            title="Toggle dark/light mode"
            style="position:fixed;top:1rem;right:1rem;z-index:9999;width:2.5rem;height:2.5rem;border-radius:50%;background:rgba(255,255,255,0.9);border:1.5px solid #e5e7eb;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;font-size:1.125rem;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(8px)">🌙</button>

    {{-- Accessibility panel — bottom-right corner, always visible --}}
    <div class="fixed bottom-4 right-4 z-50 flex flex-col items-end gap-1">
        <p class="text-[10px] font-semibold text-black/40 bg-white/90 px-2 py-0.5 rounded-full border border-black/10 shadow-sm">
            ♿ Accessibility Tools
        </p>
        @include('partials.accessibility-panel')
    </div>

    <div class="auth-shell">

        {{-- ══ LEFT PANEL — branding ══ --}}
        <div class="auth-left auth-left-panel">

            {{-- Decorative orbs --}}
            <div class="auth-orb" style="width:300px;height:300px;top:-80px;right:-80px"></div>
            <div class="auth-orb" style="width:200px;height:200px;bottom:20%;left:-60px"></div>
            <div class="auth-orb" style="width:120px;height:120px;top:40%;right:10%"></div>

            {{-- Logo + School Crest --}}
            <div class="relative z-10 fade-up">
                <div class="flex items-center gap-4 mb-1">
                    <img src="{{ asset('images/luwinga-logo.png') }}" alt="Luwinga Secondary School"
                         class="w-14 h-14 rounded-2xl object-contain"
                         style="background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);padding:4px">
                    <div>
                        <p class="text-white font-bold text-base leading-tight">CareerGuide</p>
                        <p class="text-xs font-semibold leading-tight" style="color:rgba(255,255,255,0.7)">Luwinga Secondary School</p>
                        <p class="text-[10px] leading-tight mt-0.5" style="color:rgba(255,255,255,0.4)">Mzuzu, Malawi</p>
                    </div>
                </div>
            </div>

            {{-- Headline --}}
            <div class="relative z-10 space-y-6 fade-up stagger-1">
                <div>
                    <h1 class="text-white font-extrabold leading-tight mb-3"
                        style="font-size:2.625rem;letter-spacing:-0.02em">
                        Discover your <br>
                        <span style="color:#93c5fd">perfect career path</span>
                    </h1>
                    <p style="color:rgba(255,255,255,0.65);font-size:1.0625rem;line-height:1.65">
                        Smart career guidance tailored to your grades, interests, and ambitions — helping every Luwinga student choose the right MSCE subjects and university programme.
                    </p>
                </div>

                {{-- Feature pills --}}
                <div class="space-y-3 fade-up stagger-2">
                    @foreach([
                        ['🎯', 'Personalised career recommendations based on your self-assessment'],
                        ['📚', 'MSCE subject combination guidance for Form 3 & 4'],
                        ['🏛️', 'University programme eligibility & matching for Malawian universities'],
                        ['📊', 'Academic progress tracking across all four forms'],
                    ] as $feature)
                    <div class="flex items-center gap-3 fade-up stagger-{{ $loop->iteration + 1 }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm"
                             style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.15)">
                            {{ $feature[0] }}
                        </div>
                        <p class="text-sm" style="color:rgba(255,255,255,0.75)">{{ $feature[1] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer quote --}}
            <div class="relative z-10 fade-up stagger-5">
                <blockquote style="border-left:3px solid rgba(147,197,253,0.5);padding-left:1rem">
                    <p class="text-sm italic" style="color:rgba(255,255,255,0.55)">
                        "Education is the most powerful weapon which you can use to change the world."
                    </p>
                    <p class="text-xs mt-1 font-semibold" style="color:rgba(255,255,255,0.35)">— Nelson Mandela</p>
                </blockquote>
            </div>
        </div>

        {{-- ══ RIGHT PANEL — form ══ --}}
        <div class="auth-right-panel">

            {{-- Logo — always shown at top of right panel --}}
            <a href="{{ route('welcome') }}"
               class="flex items-center gap-3 mb-8 fade-up">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-md"
                     style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-base" style="color:#0d1f3c">CareerGuide</p>
                    <p class="text-xs font-medium" style="color:#3b82f6">Luwinga Secondary School · Smart Guidance</p>
                </div>
            </a>

            <div class="auth-card w-full max-w-md fade-up stagger-1">
                <div class="p-8 sm:p-10">
                    @yield('auth-content')
                </div>
            </div>

            <p class="text-xs text-center mt-6 fade-up stagger-2" style="color:#9ca3af">
                &copy; {{ date('Y') }} Smart Career &amp; Subject Guidance Tool &middot; Luwinga Secondary School, Mzuzu
            </p>
        </div>
    </div>

@if (!file_exists(public_path('build/manifest.json')))
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
@endif
</body>
</html>
