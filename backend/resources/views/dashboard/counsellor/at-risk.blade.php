@extends('layouts.dashboard')

@section('title', 'At-Risk Student Alerts')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">At-Risk Alerts</span>
</nav>
@endsection

@section('content')
<div class="space-y-6 pb-8">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-red-600 to-rose-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">At-Risk Student Alert System</h1>
                <p class="text-red-100 mt-1 text-sm">AI-powered early warning — identify students who need urgent counselling intervention</p>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        {{-- Summary chips --}}
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white/15 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold">{{ $summary['total'] }}</div>
                <div class="text-red-100 text-xs mt-0.5">Total Students</div>
            </div>
            <div class="bg-red-900/40 rounded-xl p-3 text-center border border-red-300/30">
                <div class="text-2xl font-bold">{{ $summary['high'] }}</div>
                <div class="text-red-100 text-xs mt-0.5">High Risk</div>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold">{{ $summary['medium'] }}</div>
                <div class="text-red-100 text-xs mt-0.5">Medium Risk</div>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold">{{ $summary['low'] }}</div>
                <div class="text-red-100 text-xs mt-0.5">Low Risk</div>
            </div>
        </div>
    </div>

    {{-- Key stats row --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900">{{ $summary['no_assessment'] }}</div>
                <div class="text-xs text-gray-500">Have not done assessment</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900">{{ $summary['no_goal'] }}</div>
                <div class="text-xs text-gray-500">Have no career goal set</div>
            </div>
        </div>
    </div>

    {{-- HIGH RISK --}}
    @if($highRisk->isNotEmpty())
    <div>
        <div class="flex items-center gap-2 mb-3">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <h2 class="text-base font-bold text-red-700">High Risk — Immediate Action Required ({{ $highRisk->count() }})</h2>
        </div>
        <div class="space-y-3">
            @foreach($highRisk as $data)
            @include('dashboard.counsellor.partials.at-risk-card', ['data' => $data, 'level' => 'high'])
            @endforeach
        </div>
    </div>
    @endif

    {{-- MEDIUM RISK --}}
    @if($mediumRisk->isNotEmpty())
    <div>
        <div class="flex items-center gap-2 mb-3">
            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
            <h2 class="text-base font-bold text-amber-700">Medium Risk — Follow Up Soon ({{ $mediumRisk->count() }})</h2>
        </div>
        <div class="space-y-3">
            @foreach($mediumRisk as $data)
            @include('dashboard.counsellor.partials.at-risk-card', ['data' => $data, 'level' => 'medium'])
            @endforeach
        </div>
    </div>
    @endif

    {{-- LOW RISK --}}
    @if($lowRisk->isNotEmpty())
    <div>
        <div class="flex items-center gap-2 mb-3">
            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
            <h2 class="text-base font-bold text-emerald-700">On Track — No Immediate Concern ({{ $lowRisk->count() }})</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($lowRisk as $data)
            <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ strtoupper(substr($data['student']->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $data['student']->name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $data['student']->studentProfile?->form_level ?? 'N/A' }}
                        @if($data['avgScore']) · Avg: {{ $data['avgScore'] }}% @endif
                    </p>
                </div>
                <a href="{{ route('counsellor.student.detail', $data['student']) }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium flex-shrink-0">View</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($studentData->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <p class="text-gray-500 text-sm">No students registered yet.</p>
    </div>
    @endif

    {{-- Legend --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
        <h3 class="font-bold text-blue-900 text-xs mb-2">How Risk Scores Are Calculated</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-blue-800">
            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>No assessment completed (+30 pts)</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>Average grade below 40% (+25 pts)</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>No career goal set (+20 pts)</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>No grades uploaded (+15 pts)</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>Average grade 40-54% (+10 pts)</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>No counselling session ever (+10 pts)</div>
        </div>
        <p class="text-blue-700 text-xs mt-2">High Risk: 50+ pts &nbsp;|&nbsp; Medium Risk: 20-49 pts &nbsp;|&nbsp; Low Risk: below 20 pts</p>
    </div>

</div>
@endsection
