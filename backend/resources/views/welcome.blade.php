@extends('layouts.app')

@section('title', 'Smart Career & Subject Guidance — Luwinga Secondary School')

@push('styles')
<style>
    :root { --blue-700: #1d4ed8; --blue-600: #2563eb; --blue-500: #3b82f6; }

    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
    @keyframes glow-pulse { 0%,100%{opacity:.35} 50%{opacity:.7} }
    @keyframes slide-up { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes counter-in { from{opacity:0;transform:scale(.8)} to{opacity:1;transform:scale(1)} }
    @keyframes shimmer { 0%{background-position:-200% center} 100%{background-position:200% center} }

    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-glow  { animation: glow-pulse 4s ease-in-out infinite; }
    .hero-gradient {
        background:
            radial-gradient(ellipse 80% 60% at 50% -10%, rgba(37,99,235,.18), transparent),
            radial-gradient(ellipse 60% 50% at 90% 80%, rgba(59,130,246,.12), transparent),
            linear-gradient(135deg, #eff6ff 0%, #ffffff 45%, #f0f7ff 100%);
    }
    .gradient-text {
        background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 50%, #60a5fa 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .shimmer-text {
        background: linear-gradient(90deg, #1d4ed8 0%, #60a5fa 40%, #1d4ed8 80%);
        background-size: 200% auto;
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer 3s linear infinite;
    }
    .section-fade { opacity:0; transform:translateY(28px); transition:opacity .7s ease, transform .7s ease; }
    .section-fade.visible { opacity:1; transform:translateY(0); }
    .glass-card { background:rgba(255,255,255,.75); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,.6); }
    .glass-card-dark { background:rgba(255,255,255,.1); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.18); }
    .nav-link::after { content:''; position:absolute; bottom:-4px; left:0; width:0; height:2px; background:linear-gradient(90deg,#2563eb,#3b82f6); border-radius:2px; transition:width .3s; }
    .nav-link:hover::after { width:100%; }
    .nav-link { position:relative; }
    .card-hover { transition: transform .35s ease, box-shadow .35s ease; }
    .card-hover:hover { transform:translateY(-6px); box-shadow:0 24px 48px -8px rgba(37,99,235,.18); }
    .gradient-border { position:relative; background:white; border-radius:1.25rem; }
    .gradient-border::before {
        content:''; position:absolute; inset:0; border-radius:1.25rem; padding:1px;
        background:linear-gradient(135deg, rgba(37,99,235,.35), rgba(147,197,253,.12));
        -webkit-mask:linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask:linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite:xor; mask-composite:exclude; pointer-events:none;
    }
    .feat-icon { transition:transform .3s ease; }
    .feat-icon:hover { transform:rotate(5deg) scale(1.1); }
</style>
@endpush

@section('content')

{{-- ══ NAVIGATION ════════════════════════════════════════════════════════════ --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/85 backdrop-blur-xl shadow-sm border-b border-blue-100/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
        <a href="{{ route('welcome') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('images/luwinga-logo.png') }}" alt="Luwinga" class="h-10 w-auto object-contain group-hover:scale-105 transition-transform duration-300">
            <span class="text-slate-400 text-sm hidden lg:inline font-medium tracking-wide">Luwinga Secondary School</span>
        </a>
        <div class="hidden md:flex items-center gap-7">
            <a href="#features"    class="nav-link text-slate-600 hover:text-blue-700 transition-colors font-medium text-sm">Services Offered</a>
            <a href="#how-it-works"class="nav-link text-slate-600 hover:text-blue-700 transition-colors font-medium text-sm">How It Works</a>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('login') }}" class="text-slate-600 hover:text-blue-700 font-semibold transition-colors px-3 sm:px-4 py-2 text-sm rounded-lg hover:bg-blue-50">
                Sign In
            </a>
            <a href="{{ route('register') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold px-4 sm:px-6 py-2.5 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-600/25 hover:shadow-xl hover:-translate-y-0.5 text-sm">
                Get Started Free
            </a>
        </div>
    </div>
</nav>

{{-- ══ HERO ═══════════════════════════════════════════════════════════════════ --}}
<section class="relative min-h-screen hero-gradient pt-28 sm:pt-32 pb-20 overflow-hidden">
    <div class="absolute top-20 left-8 w-80 h-80 bg-blue-400/20 rounded-full blur-3xl animate-glow pointer-events-none"></div>
    <div class="absolute bottom-24 right-8 w-96 h-96 bg-blue-300/15 rounded-full blur-3xl animate-glow pointer-events-none" style="animation-delay:2s"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-blue-200/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">

            {{-- Left Content --}}
            <div class="space-y-7 order-2 lg:order-1">
                <div class="inline-flex items-center gap-2.5 bg-white/80 backdrop-blur-sm text-blue-700 px-4 py-2 rounded-full text-xs sm:text-sm font-bold border border-blue-100 shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-600"></span>
                    </span>
                    Trusted by Luwinga Secondary School, Mzuzu
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-[4rem] xl:text-[4.5rem] font-black leading-[1.08] tracking-tight text-slate-900">
                    Discover Your
                    <span class="shimmer-text block">Perfect Path</span>
                    to Success
                </h1>

                <p class="text-slate-500 text-base sm:text-lg leading-relaxed max-w-lg">
                    The only tool that combines <strong class="text-blue-700">AI analysis</strong>, real teacher grades, and career science to give every Malawian student a personalised roadmap — from MSCE subject choices to university admission.
                </p>

                <div class="flex flex-wrap gap-3 sm:gap-4 pt-1">
                    <a href="{{ route('register') }}"
                       class="bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold px-7 sm:px-8 py-4 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-xl shadow-blue-600/30 hover:shadow-2xl hover:shadow-blue-600/35 hover:-translate-y-1 text-sm sm:text-base">
                        Start Free Assessment
                    </a>
                    <a href="#how-it-works"
                       class="glass-card text-slate-700 font-semibold px-7 sm:px-8 py-4 rounded-xl hover:border-blue-300 hover:text-blue-700 transition-all text-sm sm:text-base inline-flex items-center gap-2">
                        See How It Works
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                </div>

                {{-- Live Stats --}}
                <div class="grid grid-cols-3 gap-4 sm:gap-6 pt-3 border-t border-slate-200/70">
                    @foreach([['2,500+','Students Guided'],['50+','Career Paths'],['94%','Accuracy Rate']] as [$v,$l])
                    <div>
                        <div class="text-2xl sm:text-3xl font-black text-blue-700 counter" data-target="{{ $v }}">{{ $v }}</div>
                        <div class="text-slate-400 text-xs sm:text-sm font-medium mt-0.5">{{ $l }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: Hero Image + Floating cards --}}
            <div class="relative order-1 lg:order-2 animate-float">
                <div class="absolute -inset-6 bg-gradient-to-br from-blue-400 to-blue-600 rounded-3xl blur-2xl opacity-20 animate-glow"></div>
                <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl shadow-blue-900/20 border border-white/60">
                    <img src="{{ asset('images/luwinga-students.jpg') }}"
                         alt="Students at Luwinga Secondary School"
                         class="w-full h-60 sm:h-80 md:h-[30rem] object-cover object-center">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-900/40 via-transparent to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <div class="glass-card rounded-xl px-4 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800">Science Path — 91% Match</p>
                                <p class="text-[10px] text-slate-500">Recommended for Chisomo Phiri · Form 3</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating badge top-right --}}
                <div class="absolute -top-4 -right-3 sm:-right-5 glass-card rounded-2xl px-4 py-3 shadow-xl shadow-blue-900/10">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-800">AI-Powered</p>
                            <p class="text-[10px] text-slate-500">Instant results</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1.5 animate-bounce opacity-50">
        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-widest">Explore</span>
        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>

{{-- ══ FEATURES ═══════════════════════════════════════════════════════════════ --}}
<section id="features" class="py-20 sm:py-28 bg-white relative">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(37,99,235,.05),transparent_55%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="text-center max-w-2xl mx-auto mb-14 section-fade">
            <span class="inline-block text-blue-600 text-xs font-black uppercase tracking-widest bg-blue-50 px-4 py-1.5 rounded-full border border-blue-100">Services Offered</span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 mt-4 mb-4 leading-tight">Everything You Need, <br class="hidden sm:inline">All in One Place</h2>
            <p class="text-slate-500 text-base sm:text-lg">Smart guidance powered by real grades, assessment data, and AI intelligence.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach([
                ['bg-blue-600','M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z','AI Career Matching','Upload your grades and take our smart assessment. The AI instantly maps you to careers that match your strengths, interests, and academic performance.'],
                ['bg-blue-500','M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253','Subject Combination Guide','Get a personalised Form 3 & 4 subject combination plan — Science or Humanities — based on your real grades, not guesswork.'],
                ['bg-blue-700','M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4','University Roadmap','See exactly which universities and programmes you qualify for, with entry requirements mapped to your current grades and predicted MSCE performance.'],
            ] as [$color,$icon,$title,$desc])
            <div class="group gradient-border p-7 sm:p-8 rounded-2xl card-hover section-fade shadow-sm hover:shadow-blue-100/80">
                <div class="w-13 h-13 {{ $color }} rounded-2xl flex items-center justify-center mb-5 shadow-lg w-12 h-12 feat-icon">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-900 text-lg mb-2.5">{{ $title }}</h3>
                <p class="text-slate-500 leading-relaxed text-sm sm:text-base">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ HOW IT WORKS ════════════════════════════════════════════════════════════ --}}
<section id="how-it-works" class="py-20 sm:py-28 bg-white relative">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom,rgba(37,99,235,.05),transparent_55%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="text-center max-w-2xl mx-auto mb-16 section-fade">
            <span class="inline-block text-blue-600 text-xs font-black uppercase tracking-widest bg-blue-50 px-4 py-1.5 rounded-full border border-blue-100">Simple Process</span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 mt-4 mb-4 leading-tight">From Sign-Up to Clarity<br class="hidden sm:block"> in 4 Steps</h2>
            <p class="text-slate-500 text-base sm:text-lg">No complicated setup. No confusion. Just answers.</p>
        </div>

        <div class="relative">
            {{-- Connector line --}}
            <div class="hidden lg:block absolute top-20 left-[12%] right-[12%] h-0.5 bg-gradient-to-r from-blue-200 via-blue-400 to-blue-200"></div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach([
                    ['01','Create Profile','Register in under 2 minutes. Enter your name, form level, and let the system connect with your school data.','bg-blue-600','text-blue-600'],
                    ['02','Take Assessment','Complete our 15-minute smart questionnaire about your interests, skills, and career dreams.','bg-blue-700','text-blue-700'],
                    ['03','Get Recommendations','Receive AI-generated career and subject combination recommendations instantly — personalised to you.','bg-blue-800','text-blue-800'],
                    ['04','Plan Your Future','Review your results with your counsellor, set goals, and track your progress to your dream career.','bg-blue-900','text-blue-900'],
                ] as [$step,$title,$desc,$bg,$textColor])
                <div class="text-center section-fade group">
                    <div class="relative inline-block mb-6">
                        <div class="w-16 sm:w-20 h-16 sm:h-20 {{ $bg }} rounded-2xl flex items-center justify-center mx-auto text-white text-xl sm:text-2xl font-black shadow-xl group-hover:scale-110 transition-transform duration-300">{{ $step }}</div>
                    </div>
                    <h3 class="font-bold text-lg sm:text-xl text-slate-900 mb-2">{{ $title }}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-12 section-fade">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold px-8 py-4 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-600/25 hover:-translate-y-0.5 text-sm sm:text-base">
                Start Your Assessment Now
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ══ BENEFITS / WHY THIS MATTERS ════════════════════════════════════════════ --}}
<section id="benefits" class="py-20 sm:py-28 bg-white relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="section-fade">
                <span class="inline-block text-blue-600 text-xs font-black uppercase tracking-widest bg-blue-50 px-4 py-1.5 rounded-full border border-blue-100">Why It Matters</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 mt-4 mb-8 leading-tight">Making Smart Choices<br>Accessible to All</h2>
                <div class="space-y-5">
                    @foreach([
                        ['Data-Driven Guidance','Students make informed subject choices based on actual grades and AI analysis — not family pressure or guesswork.'],
                        ['Saves Counsellor Time','Automated recommendations free up counsellors to focus on students who need extra support.'],
                        ['Higher Academic Success','Better subject alignment means students study what they excel at, leading to improved MSCE results.'],
                        ['School-Wide Visibility','Admins and teachers see real-time data on student performance, at-risk cases, and career trends.'],
                    ] as [$title,$desc])
                    <div class="flex gap-4 p-4 rounded-xl hover:bg-blue-50/60 transition-colors duration-300 group">
                        <div class="w-8 h-8 bg-blue-100 group-hover:bg-blue-600 rounded-xl flex items-center justify-center shrink-0 mt-0.5 transition-colors duration-300">
                            <svg class="w-4 h-4 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 mb-1 text-sm sm:text-base">{{ $title }}</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="section-fade space-y-4 sm:space-y-5">
                @foreach([['Career Match Score','92%'],['Student Satisfaction','96%'],['University Placement Rate','89%']] as [$label,$pct])
                <div class="glass-card rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-bold text-slate-800 text-sm sm:text-base">{{ $label }}</span>
                        <span class="text-blue-600 font-black text-xl">{{ $pct }}</span>
                    </div>
                    <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-1500" style="width:{{ $pct }}"></div>
                    </div>
                </div>
                @endforeach

                {{-- Testimonial --}}
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 text-white shadow-lg">
                    <div class="flex gap-1 mb-3">
                        @foreach(range(1,5) as $_)
                        <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endforeach
                    </div>
                    <p class="text-white/90 text-sm leading-relaxed italic mb-4">"This system showed me that I was actually better suited for Humanities than Science. I changed my combination and my grades improved immediately. I wish every school in Malawi had this!"</p>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold">CP</div>
                        <div>
                            <p class="text-xs font-bold">Chisomo Phiri</p>
                            <p class="text-blue-200/70 text-[11px]">Form 4 Student</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ CTA ═════════════════════════════════════════════════════════════════════ --}}
<section class="py-20 sm:py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-800 via-blue-700 to-blue-800"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,.1),transparent_65%)]"></div>
    <div class="absolute top-0 left-1/4 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-blue-300/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 relative section-fade">
        <div class="inline-flex items-center gap-2.5 bg-white/15 backdrop-blur-sm text-white px-4 py-2 rounded-full text-xs sm:text-sm font-bold border border-white/20 mb-6">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
            </span>
            Free for all Luwinga Secondary School students
        </div>
        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white mb-5 leading-tight">Your Future Starts<br>with One Click</h2>
        <p class="text-blue-100/80 text-base sm:text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
            Join thousands of students who have discovered their ideal career path, chosen the right MSCE subjects, and secured a place at their dream university.
        </p>
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2.5 bg-white text-blue-800 font-bold px-8 sm:px-10 py-4 rounded-xl hover:bg-blue-50 transition-all shadow-2xl shadow-black/15 hover:shadow-blue-200/40 hover:-translate-y-1 text-sm sm:text-base">
                Create Free Account
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2.5 glass-card-dark text-white font-semibold px-8 sm:px-10 py-4 rounded-xl hover:bg-white/15 transition-all text-sm sm:text-base">
                I Have an Account
            </a>
        </div>
        <p class="text-blue-200/50 text-xs mt-6">No payment required · Takes less than 2 minutes to register</p>
    </div>
</section>

{{-- ══ FOOTER ══════════════════════════════════════════════════════════════════ --}}
<footer id="contact" class="bg-slate-950 text-slate-400 py-14 sm:py-16 relative">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(37,99,235,.08),transparent_45%)]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-8 mb-12">
            <div class="sm:col-span-2 md:col-span-1">
                <img src="{{ asset('images/luwinga-logo.png') }}" alt="Luwinga" class="h-10 w-auto object-contain brightness-0 invert mb-4">
                <p class="text-sm leading-relaxed text-slate-500 max-w-xs">Smart AI-powered career and subject guidance for Malawian secondary school students.</p>
                <div class="flex gap-2.5 mt-5">
                    <a href="#" class="w-9 h-9 bg-white/5 border border-white/10 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:border-blue-600 transition-all text-[11px] font-bold text-slate-400 hover:text-white">FB</a>
                    <a href="#" class="w-9 h-9 bg-white/5 border border-white/10 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:border-blue-600 transition-all text-[11px] font-bold text-slate-400 hover:text-white">TW</a>
                    <a href="#" class="w-9 h-9 bg-white/5 border border-white/10 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:border-blue-600 transition-all text-[11px] font-bold text-slate-400 hover:text-white">IG</a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 text-xs uppercase tracking-wider">Quick Links</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#features"     class="hover:text-white transition-colors">Services Offered</a></li>
                    <li><a href="#how-it-works"  class="hover:text-white transition-colors">How It Works</a></li>
                    <li><a href="#benefits"      class="hover:text-white transition-colors">Why It Matters</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 text-xs uppercase tracking-wider">Account</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Register</a></li>
                    <li><a href="{{ route('login') }}"    class="hover:text-white transition-colors">Sign In</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 text-xs uppercase tracking-wider">Contact</h4>
                <ul class="space-y-2.5 text-sm">
                    <li class="text-slate-500">Luwinga Secondary School</li>
                    <li class="text-slate-500">Mzuzu City, Malawi</li>
                    <li><a href="mailto:guidance@luwinga.edu.mw" class="hover:text-white transition-colors">guidance@luwinga.edu.mw</a></li>
                </ul>
            </div>
        </div>
        <div class="pt-8 border-t border-white/[0.07] flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-slate-600">&copy; {{ date('Y') }} Smart Career Guidance Tool — Luwinga Secondary School</p>
            <p class="text-xs text-slate-700">Empowering Malawian students through data-driven guidance</p>
        </div>
    </div>
</footer>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Scroll-reveal
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.section-fade').forEach(el => observer.observe(el));

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Animated counter
    const counters = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            const raw = el.getAttribute('data-target') || el.textContent;
            const suffix = raw.replace(/[\d]/g, '');
            const num = parseFloat(raw.replace(/[^\d.]/g, ''));
            if (isNaN(num)) return;
            let current = 0;
            const step = num / 40;
            const interval = setInterval(() => {
                current = Math.min(current + step, num);
                el.textContent = (Number.isInteger(num) ? Math.round(current) : current.toFixed(1)).toLocaleString() + suffix;
                if (current >= num) clearInterval(interval);
            }, 35);
            counterObserver.unobserve(el);
        });
    }, { threshold: 0.5 });
    counters.forEach(el => counterObserver.observe(el));
});
</script>
@endpush
