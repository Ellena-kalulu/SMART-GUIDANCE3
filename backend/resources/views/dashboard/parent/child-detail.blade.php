@extends('layouts.dashboard')

@section('title', __('parent.view_details'))

@section('sidebar')
    @include('dashboard.parent.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('parent.children') }}" class="text-black/50 hover:text-blue-600">My Children</a>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">{{ $child->name }}</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-linear-to-br from-blue-700 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 60%)"></div>
        <div class="relative flex items-center gap-5">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shrink-0">
                {{ strtoupper(substr($child->name, 0, 2)) }}
            </div>
            <div>
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Student Profile</p>
                <h1 class="text-2xl font-bold">{{ $child->name }}</h1>
                <p class="text-blue-200 text-sm mt-0.5">
                    {{ $child->studentProfile->form_level ?? '' }}
                    @if($child->studentProfile?->stream) &nbsp;·&nbsp; {{ $child->studentProfile->stream }} Stream @endif
                    &nbsp;·&nbsp; Luwinga Secondary School
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @php
        $topCareer = $careers->sortByDesc('confidence_score')->first();
        $avgScore  = $child->academicResults->avg('score');
        $uniElig   = $universities->where('confidence_score','>=',80)->count();
        $assessed  = $child->assessmentAttempts->where('status','completed')->isNotEmpty();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-xl font-bold {{ $assessed ? 'border-blue-600' : 'border-blue-600' }}">
                {{ $assessed ? __('parent.completed') : __('parent.pending') }}
            </p>
            <p class="text-xs text-black/50 mt-1">Assessment</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-xl font-bold text-blue-600">{{ $avgScore ? round($avgScore) . '%' : '—' }}</p>
            <p class="text-xs text-black/50 mt-1">Avg Score</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-lg font-bold border-blue-600 leading-tight">{{ $topCareer?->recommended?->title ?? '—' }}</p>
            <p class="text-xs text-black/50 mt-1">Top Career Match</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-xl font-bold border-blue-600">{{ $uniElig }}</p>
            <p class="text-xs text-black/50 mt-1">Uni Eligible</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Career Recommendations -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5">
            <h2 class="font-bold text-black/80 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Career Recommendations
            </h2>
            @forelse($careers->sortByDesc('confidence_score')->take(5) as $rec)
            <div class="flex items-center justify-between py-2.5 border-b border-black/5 last:border-0">
                <div>
                    <p class="font-semibold text-black/80 text-sm">{{ $rec->recommended->title ?? 'Career' }}</p>
                    <p class="text-xs text-black/50">{{ $rec->recommended->category ?? '' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-14 h-1.5 bg-black/[0.03] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width:{{ $rec->confidence_score }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-blue-600">{{ round($rec->confidence_score) }}%</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-black/40 text-center py-6">{{ __('parent.assessment_pending') }}</p>
            @endforelse
        </div>

        <!-- Academic Performance -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5">
            <h2 class="font-bold text-black/80 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 border-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Academic Performance
            </h2>
            @forelse($child->academicResults->sortByDesc('term')->take(8) as $result)
            @php $s = $result->score; $c = $s >= 70 ? 'green' : ($s >= 50 ? 'orange' : 'red'); @endphp
            <div class="flex items-center gap-3 py-2 border-b border-black/5 last:border-0">
                <p class="flex-1 text-sm font-medium text-black/80">{{ $result->subject->name ?? 'Subject' }}</p>
                <div class="flex items-center gap-2">
                    <div class="w-16 h-1.5 bg-black/[0.03] rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $c }}-500 rounded-full" style="width:{{ $s }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-{{ $c }}-600 w-8 text-right">{{ $s }}%</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-black/40 text-center py-6">{{ __('parent.no_grades') }}</p>
            @endforelse
        </div>
    </div>

    <!-- University Eligibility -->
    @if($universities->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">University Programs</h2>
        </div>
        <div class="divide-y divide-black/10">
            @foreach($universities->sortByDesc('confidence_score') as $rec)
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-black/80 text-sm">{{ $rec->recommended->name ?? 'Programme' }}</p>
                    <p class="text-xs text-black/50">{{ $rec->recommended->university ?? '' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $rec->confidence_score >= 80 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ round($rec->confidence_score) }}% Match
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <a href="{{ route('parent.children') }}"
           class="inline-flex items-center gap-2 text-sm text-black/60 hover:text-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('parent.back_to_children') }}
        </a>
    </div>
</div>

@if(isset($academicReport))
    @include('dashboard.student.partials.academic-intelligence', ['report' => $academicReport])
@endif

@endsection
