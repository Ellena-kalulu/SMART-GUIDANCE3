@extends('layouts.dashboard')

@section('title', 'Scholarship Finder')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Scholarship Finder</span>
</nav>
@endsection

@section('content')
@php
    $typeColors = [
        'government'    => 'blue',
        'university'    => 'violet',
        'ngo'           => 'emerald',
        'private'       => 'amber',
        'international' => 'rose',
    ];
    $typeBadge = [
        'government'    => 'bg-blue-100 text-blue-700',
        'university'    => 'bg-violet-100 text-violet-700',
        'ngo'           => 'bg-emerald-100 text-emerald-700',
        'private'       => 'bg-amber-100 text-amber-700',
        'international' => 'bg-rose-100 text-rose-700',
    ];
@endphp

<div class="space-y-6 pb-8">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold">Scholarship Finder</h1>
                <p class="text-blue-100 mt-1 text-sm">Personalised funding opportunities matched to your academic profile</p>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        {{-- Profile snapshot --}}
        <div class="mt-4 grid grid-cols-3 gap-3">
            <div class="bg-white/15 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold">{{ number_format($avgScore, 1) }}%</div>
                <div class="text-blue-100 text-xs mt-0.5">Your Avg. Score</div>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold">{{ $matched->count() }}</div>
                <div class="text-blue-100 text-xs mt-0.5">Scholarships Matched</div>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center">
                <div class="text-2xl font-bold">{{ $recommendedPath }}</div>
                <div class="text-blue-100 text-xs mt-0.5">Your Academic Path</div>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'All Types', 'government' => 'Government', 'university' => 'University', 'ngo' => 'NGO', 'private' => 'Private', 'international' => 'International'] as $key => $label)
            <a href="{{ route('student.scholarships', ['type' => $key]) }}"
               class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors
                      {{ $filterType === $key ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300 hover:text-blue-600' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Matched scholarships --}}
    @php
        $displayMatched = $filterType === 'all' ? $matched : $matched->filter(fn($s) => $s->type === $filterType);
        $displayNearMiss = $filterType === 'all' ? $nearMiss : $nearMiss->filter(fn($s) => $s->type === $filterType);
    @endphp

    @if($displayMatched->isNotEmpty())
    <div>
        <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            You Qualify For ({{ $displayMatched->count() }})
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($displayMatched as $scholarship)
            <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm hover:shadow-md transition-shadow p-5">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">{{ $scholarship->name }}</h3>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $scholarship->provider }}</p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap flex-shrink-0 {{ $typeBadge[$scholarship->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $typeLabels[$scholarship->type] ?? $scholarship->type }}
                    </span>
                </div>

                <p class="text-gray-600 text-xs leading-relaxed line-clamp-3 mb-3">{{ $scholarship->description }}</p>

                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="bg-emerald-50 rounded-lg p-2">
                        <div class="text-emerald-700 font-semibold text-xs">Amount</div>
                        <div class="text-gray-800 text-xs mt-0.5">{{ $scholarship->amount ?? 'Contact provider' }}</div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-2">
                        <div class="text-blue-700 font-semibold text-xs">Deadline</div>
                        <div class="text-gray-800 text-xs mt-0.5">{{ $scholarship->deadline ?? 'See website' }}</div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Min. {{ $scholarship->min_grade }}% avg.
                    </div>
                    @if($scholarship->application_url)
                    <a href="{{ $scholarship->application_url }}" target="_blank" rel="noopener"
                       class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        Apply / Learn More
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @else
                    <span class="text-xs text-gray-400 italic">Ask your school office</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-gray-500 text-sm">No scholarships matched for this filter. Try selecting "All Types" above.</p>
    </div>
    @endif

    {{-- Near-miss scholarships --}}
    @if($displayNearMiss->isNotEmpty())
    <div>
        <h2 class="text-lg font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Almost There — Improve Your Grades ({{ $displayNearMiss->count() }})
        </h2>
        <p class="text-gray-500 text-xs mb-3">You are within 10% of qualifying for these scholarships. Work hard and you can unlock them.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($displayNearMiss as $scholarship)
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 opacity-90">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">{{ $scholarship->name }}</h3>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $scholarship->provider }}</p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap flex-shrink-0 {{ $typeBadge[$scholarship->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $typeLabels[$scholarship->type] ?? $scholarship->type }}
                    </span>
                </div>

                <div class="mb-3 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-amber-700 text-xs font-medium">
                        Requires {{ $scholarship->min_grade }}% avg. — you need
                        {{ number_format($scholarship->min_grade - $avgScore, 1) }}% more
                    </span>
                </div>

                <p class="text-gray-600 text-xs leading-relaxed line-clamp-2 mb-2">{{ $scholarship->description }}</p>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">{{ $scholarship->amount ?? 'See provider' }}</span>
                    @if($scholarship->application_url)
                    <a href="{{ $scholarship->application_url }}" target="_blank" rel="noopener"
                       class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        Learn More
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tips --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
        <h3 class="font-bold text-blue-900 text-sm mb-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Tips for Applying
        </h3>
        <ul class="space-y-1.5 text-blue-800 text-xs">
            <li class="flex gap-2"><span class="font-bold">1.</span> Apply early — many scholarships are first-come, first-served.</li>
            <li class="flex gap-2"><span class="font-bold">2.</span> Keep copies of your MSCE results slip and birth certificate ready.</li>
            <li class="flex gap-2"><span class="font-bold">3.</span> Ask your school counsellor to write a recommendation letter.</li>
            <li class="flex gap-2"><span class="font-bold">4.</span> For government bursaries, apply at your district education office after MSCE results.</li>
            <li class="flex gap-2"><span class="font-bold">5.</span> Check scholarship websites regularly — deadlines can change.</li>
        </ul>
    </div>

</div>
@endsection
