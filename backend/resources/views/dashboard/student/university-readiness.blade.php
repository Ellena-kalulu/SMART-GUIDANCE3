@extends('layouts.dashboard')

@section('title', 'University Readiness Checker')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">University Readiness</span>
</nav>
@endsection

@section('content')
@php
    $levelGrad = match($level['color']) {
        'green'  => 'from-emerald-600 to-teal-700',
        'blue'   => 'from-blue-600 to-blue-800',
        'yellow' => 'from-amber-500 to-orange-600',
        default  => 'from-rose-600 to-rose-800',
    };
    $circum    = round(2 * 3.14159 * 46, 1);
    $dashOff   = round($circum * (1 - $readiness / 100), 1);

    $priorityBg  = ['high' => 'bg-rose-50 border-rose-200 text-rose-800', 'medium' => 'bg-amber-50 border-amber-200 text-amber-800', 'low' => 'bg-emerald-50 border-emerald-200 text-emerald-800'];
    $priorityDot = ['high' => 'bg-rose-500', 'medium' => 'bg-amber-400', 'low' => 'bg-emerald-500'];
    $priorityLbl = ['high' => 'Action Required', 'medium' => 'Recommended', 'low' => 'Well Done'];
@endphp

<div class="space-y-5 pb-10">

{{-- ─── HERO ──────────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-br {{ $levelGrad }} rounded-2xl shadow-lg overflow-hidden">
    <div class="relative p-5 md:p-6">
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none" style="background-image: url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")"></div>

        <div class="relative flex items-center gap-5">
            {{-- Gauge --}}
            <div class="flex-shrink-0 relative w-28 h-28">
                <svg class="w-28 h-28 -rotate-90" viewBox="0 0 110 110">
                    <circle cx="55" cy="55" r="46" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="10"/>
                    <circle cx="55" cy="55" r="46" fill="none" stroke="white" stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circum }}"
                            stroke-dashoffset="{{ $dashOff }}"
                            style="transition: stroke-dashoffset 1.5s cubic-bezier(.4,0,.2,1)"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-black text-white leading-none">{{ round($readiness) }}<span class="text-lg">%</span></span>
                    <span class="text-white/60 text-[10px] uppercase tracking-widest mt-0.5">Ready</span>
                </div>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-white/20 text-white text-xs font-semibold px-2.5 py-1 rounded-full">{{ $level['label'] }}</span>
                </div>
                <h1 class="text-xl font-bold text-white leading-snug">University Readiness Report</h1>
                <p class="text-white/60 text-xs mt-0.5">{{ $user->name }} &bull; {{ $profile?->form_level ?? 'Secondary' }} &bull; Luwinga Secondary School</p>

                @if($careerGoal)
                <div class="mt-3 inline-flex items-center gap-2 bg-white/15 rounded-lg px-3 py-1.5">
                    <svg class="w-3.5 h-3.5 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span class="text-white/80 text-xs">Target career: <strong class="text-white">{{ $careerGoal->career?->title }}</strong></span>
                </div>
                @else
                <a href="{{ route('student.career.path') }}" class="mt-3 inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 rounded-lg px-3 py-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-white text-xs font-medium">Set a career goal to unlock full analysis</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="relative mt-4 grid grid-cols-4 gap-2">
            @php
                $stats = [
                    ['val' => number_format($avgScore, 1).'%', 'lbl' => 'Avg. Grade'],
                    ['val' => $gradesCount,                    'lbl' => 'Subjects'],
                    ['val' => $checklistDone.'/'.count($checklist), 'lbl' => 'Checklist'],
                    ['val' => $matchingProgrammes->count() ?: '—', 'lbl' => 'Programmes Found'],
                ];
            @endphp
            @foreach($stats as $s)
            <div class="bg-white/15 rounded-xl p-2.5 text-center">
                <div class="text-lg font-bold text-white">{{ $s['val'] }}</div>
                <div class="text-white/60 text-[10px] mt-0.5">{{ $s['lbl'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ─── ROW: Score Breakdown + MSCE Points ──────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-5 gap-4">

    {{-- Readiness components (3/5) --}}
    <div class="md:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide">Readiness Score Breakdown</h2>
        <div class="space-y-4">
            @foreach($components as $comp)
            @php
                $pct  = min(100, max(0, $comp['score']));
                $barC = $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-blue-500' : ($pct >= 25 ? 'bg-amber-400' : 'bg-rose-500'));
                $txtC = $pct >= 75 ? 'text-emerald-600' : ($pct >= 50 ? 'text-blue-600' : ($pct >= 25 ? 'text-amber-600' : 'text-rose-500'));
            @endphp
            <div>
                <div class="flex justify-between mb-1 items-end">
                    <div>
                        <span class="text-sm font-medium text-gray-800">{{ $comp['label'] }}</span>
                        <span class="text-gray-400 text-xs ml-1">&bull; {{ $comp['weight'] }}%</span>
                    </div>
                    <span class="text-sm font-bold {{ $txtC }}">{{ round($pct) }}%</span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="{{ $barC }} h-full rounded-full" style="width: {{ $pct }}%; transition: width 1.2s ease"></div>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">{{ $comp['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- MSCE Points Estimate (2/5) --}}
    <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col">
        <h2 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wide">MSCE Points Estimate</h2>
        <div class="flex-1 flex flex-col items-center justify-center text-center py-4">
            <div class="w-20 h-20 rounded-full border-4 border-{{ $mscePointLabel['color'] }}-200 bg-{{ $mscePointLabel['color'] }}-50 flex flex-col items-center justify-center mb-3">
                <span class="text-2xl font-extrabold text-{{ $mscePointLabel['color'] }}-600">{{ $best6Points > 0 ? $best6Points : '—' }}</span>
                <span class="text-{{ $mscePointLabel['color'] }}-400 text-[10px] leading-tight">pts</span>
            </div>
            <p class="text-sm font-bold text-{{ $mscePointLabel['color'] }}-700 mb-1">{{ $mscePointLabel['label'] }}</p>
            <p class="text-xs text-gray-500 leading-relaxed">{{ $mscePointLabel['desc'] }}</p>
        </div>
        <div class="mt-2 pt-3 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">Based on {{ $gradesCount }} subject{{ $gradesCount !== 1 ? 's' : '' }} recorded ({{ $pointsDisplay }})</p>
            <p class="text-xs text-gray-400 mt-0.5">Lower points = better in MSCE system</p>
        </div>
    </div>
</div>

{{-- ─── INTELLIGENT: Career–Programme Match ─────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                University Programmes That Match Your Career
            </h2>
            <p class="text-gray-400 text-xs mt-0.5">
                @if($careerGoal)
                    Programmes found for <strong class="text-gray-600">{{ $careerGoal->career?->title }}</strong>
                @else
                    Set a career goal to see matched programmes
                @endif
            </p>
        </div>
        @if($careerGoal && $matchingProgrammes->isEmpty())
        <a href="{{ route('student.universities') }}" class="text-xs text-blue-500 hover:underline whitespace-nowrap">Browse all programmes →</a>
        @endif
    </div>

    @if(!$careerGoal)
    <div class="p-8 text-center">
        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <p class="text-gray-500 text-sm mb-3">No career goal set yet. Set one to see which university programmes you should apply for.</p>
        <a href="{{ route('student.career.path') }}" class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Set Career Goal
        </a>
    </div>
    @elseif($matchingProgrammes->isEmpty())
    <div class="p-8 text-center">
        <p class="text-gray-500 text-sm">No exact programme matches found for <strong>{{ $careerGoal->career?->title }}</strong>. Browse all available programmes below.</p>
        <a href="{{ route('student.universities') }}" class="mt-3 inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Browse University Library
        </a>
    </div>
    @else
    <div class="divide-y divide-gray-50">
        @foreach($matchingProgrammes as $prog)
        <div class="px-5 py-4 flex items-start gap-4 hover:bg-gray-50/50 transition-colors">
            <div class="flex-shrink-0 mt-0.5">
                @if($prog['eligible'])
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                @else
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $prog['name'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $prog['university'] }}</p>
                        @if($prog['faculty'])
                        <p class="text-xs text-gray-400">{{ $prog['faculty'] }}</p>
                        @endif
                    </div>
                    @if($prog['eligible'])
                    <span class="flex-shrink-0 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">You Qualify</span>
                    @else
                    <span class="flex-shrink-0 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">Improve Grades</span>
                    @endif
                </div>
                @if($prog['entry_req'])
                <p class="text-xs text-gray-500 mt-2 leading-relaxed bg-gray-50 rounded-lg p-2">
                    <strong>Entry Requirements:</strong> {{ $prog['entry_req'] }}
                </p>
                @endif
                <div class="mt-2 flex items-center gap-3">
                    <span class="text-xs text-gray-400">Min. ~{{ $prog['min_avg'] }}% avg. grade</span>
                    <span class="text-xs text-gray-300">|</span>
                    @php $probPct = $prog['eligible'] ? min(95, 50 + (($avgScore - $prog['min_avg']) * 2.5)) : max(5, 30 + (($avgScore - $prog['min_avg']) * 2)); @endphp
                    <div class="flex items-center gap-1.5">
                        <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="{{ $prog['eligible'] ? 'bg-emerald-500' : 'bg-amber-400' }} h-full rounded-full" style="width: {{ round($probPct) }}%"></div>
                        </div>
                        <span class="text-xs {{ $prog['eligible'] ? 'text-emerald-600' : 'text-amber-600' }} font-medium">{{ round($probPct) }}% chance</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
        <a href="{{ route('student.universities') }}" class="text-xs text-blue-500 hover:underline">Browse full university library →</a>
    </div>
    @endif
</div>

{{-- ─── INTELLIGENT: Academic Path & Subject Alignment ──────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Path Analysis --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wide flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            MSCE Academic Path
        </h2>

        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Your Current Subjects Lean Towards</p>
                    <p class="text-base font-bold text-blue-700 mt-0.5">{{ $dominantPath }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    @if($dominantPath === 'Science')
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    @elseif($dominantPath === 'Commerce')
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    @endif
                </div>
            </div>

            @if($recommendedPath !== 'Any')
            <div class="flex items-center justify-between p-3 {{ $pathAligned ? 'bg-emerald-50 border-emerald-100' : 'bg-amber-50 border-amber-100' }} rounded-xl border">
                <div>
                    <p class="text-xs text-gray-500 font-medium">System Recommended Path</p>
                    <p class="text-base font-bold {{ $pathAligned ? 'text-emerald-700' : 'text-amber-700' }} mt-0.5">{{ $recommendedPath }}</p>
                </div>
                @if($pathAligned)
                <div class="flex items-center gap-1 text-emerald-600 text-xs font-semibold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Aligned
                </div>
                @else
                <div class="flex items-center gap-1 text-amber-600 text-xs font-semibold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Mismatch
                </div>
                @endif
            </div>
            @endif

            @if(!$pathAligned && $recommendedPath !== 'Any')
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                Your subjects lean <strong>{{ $dominantPath }}</strong> but the system recommends <strong>{{ $recommendedPath }}</strong> for your career goal.
                Review your subject choices on the <a href="{{ route('student.subject.combinations') }}" class="underline font-semibold">Subject Combination page</a>.
            </div>
            @elseif($recommendedPath !== 'Any')
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-xs text-emerald-800">
                Your subject path aligns with the system's recommendation for your career goal. Keep it up!
            </div>
            @endif
        </div>
    </div>

    {{-- Subject-Career Alignment --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-1 uppercase tracking-wide flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Subject Performance
        </h2>
        <p class="text-gray-400 text-xs mb-3">Sorted weakest → strongest
            @if($relevantCount > 0)
            &bull; <span class="text-violet-600 font-medium">Highlighted = relevant to your career</span>
            @endif
        </p>
        <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
            @foreach($subjectAlignment as $s)
            @php
                $sc = $s['score'];
                $bc = $sc >= 75 ? 'bg-emerald-500' : ($sc >= 55 ? 'bg-blue-500' : ($sc >= 40 ? 'bg-amber-400' : 'bg-rose-500'));
                $tc = $sc >= 75 ? 'text-emerald-600' : ($sc >= 55 ? 'text-blue-600' : ($sc >= 40 ? 'text-amber-600' : 'text-rose-600'));
            @endphp
            <div class="{{ $s['career_relevant'] ? 'bg-violet-50 border border-violet-100 rounded-lg px-2 pt-1.5 pb-1' : '' }}">
                <div class="flex items-center justify-between mb-0.5">
                    <span class="text-xs font-medium text-gray-700 flex items-center gap-1.5">
                        {{ $s['name'] }}
                        @if($s['career_relevant'])
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                        @endif
                    </span>
                    <span class="text-xs font-bold {{ $tc }}">{{ $sc }}%</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="{{ $bc }} h-full rounded-full" style="width: {{ $sc }}%; transition: width 1s ease"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ─── Application Readiness Checklist ──────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            University Application Checklist
        </h2>
        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">{{ $checklistDone }}/{{ count($checklist) }} done</span>
    </div>

    <div class="h-2 bg-gray-100 rounded-full mb-4 overflow-hidden">
        <div class="h-full bg-blue-500 rounded-full transition-all duration-1000"
             style="width: {{ $checklistDone > 0 ? round($checklistDone / count($checklist) * 100) : 0 }}%"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        @foreach($checklist as $item)
        <div class="flex items-center justify-between gap-3 p-3 rounded-xl border {{ $item['done'] ? 'bg-emerald-50 border-emerald-100' : 'bg-gray-50 border-gray-100' }}">
            <div class="flex items-center gap-2.5">
                @if($item['done'])
                <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 line-through">{{ $item['item'] }}</span>
                @else
                <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                <span class="text-xs text-gray-700 font-medium">{{ $item['item'] }}</span>
                @endif
            </div>
            @if(!$item['done'] && $item['link'])
            <a href="{{ $item['link'] }}" class="text-xs text-blue-500 hover:underline whitespace-nowrap flex-shrink-0">{{ $item['link_label'] }} →</a>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ─── Action Plan ────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <h2 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Your Personalised Action Plan
    </h2>
    <div class="space-y-2.5">
        @foreach($actions as $i => $action)
        <div class="flex items-start gap-3 p-3.5 rounded-xl border {{ $priorityBg[$action['priority']] }}">
            <div class="w-6 h-6 rounded-full {{ $priorityDot[$action['priority']] }} flex items-center justify-center flex-shrink-0 text-white text-xs font-bold mt-0.5">
                {{ $i + 1 }}
            </div>
            <div class="flex-1">
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-70">{{ $priorityLbl[$action['priority']] }}</span>
                <p class="text-sm leading-relaxed mt-0.5">{{ $action['text'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ─── Quick links ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @php
        $links = [
            ['route' => 'student.assessment',        'label' => 'Self-Assessment',        'bg' => 'bg-emerald-100', 'hover' => 'hover:bg-emerald-200', 'ic' => 'text-emerald-600', 'path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['route' => 'student.career.path',       'label' => 'Career Path',             'bg' => 'bg-blue-100',    'hover' => 'hover:bg-blue-200',    'ic' => 'text-blue-600',    'path' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['route' => 'student.subject.combinations','label' => 'Subject Combinations',   'bg' => 'bg-violet-100',  'hover' => 'hover:bg-violet-200',  'ic' => 'text-violet-600',  'path' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['route' => 'student.appointments',      'label' => 'Book Counselling',        'bg' => 'bg-amber-100',   'hover' => 'hover:bg-amber-200',   'ic' => 'text-amber-600',   'path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ];
    @endphp
    @foreach($links as $lnk)
    <a href="{{ route($lnk['route']) }}"
       class="group flex flex-col items-center gap-2 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all text-center">
        <div class="w-10 h-10 {{ $lnk['bg'] }} {{ $lnk['hover'] }} rounded-xl flex items-center justify-center transition-colors">
            <svg class="w-5 h-5 {{ $lnk['ic'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $lnk['path'] }}"/>
            </svg>
        </div>
        <div class="font-semibold text-gray-700 text-xs">{{ $lnk['label'] }}</div>
    </a>
    @endforeach
</div>

</div>
@endsection
