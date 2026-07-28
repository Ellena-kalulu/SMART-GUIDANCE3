@extends('layouts.dashboard')

@section('title', __('parent.reports'))

@section('sidebar')
    @include('dashboard.parent.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <span class="text-black/50">Guardian</span>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Reports</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-blue-700 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 60%)"></div>
        <div class="relative">
            <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Guardian Portal</p>
            <h1 class="text-2xl md:text-3xl font-bold mb-1">Reports</h1>
            <p class="text-blue-200 text-sm">{{ __('parent.reports_page_desc') }}</p>
        </div>
    </div>

    @if($children->isEmpty())
    <div class="bg-white rounded-2xl p-12 border border-black/10 text-center">
        <div class="w-14 h-14 bg-black/5 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-black/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="font-bold text-black/60 mb-1">No Children Linked</h3>
        <p class="text-sm text-black/40">{{ __('parent.contact_school') }}</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($children as $child)
        @php
            $assessed  = $child->assessmentAttempts->where('status','completed')->isNotEmpty();
            $avgScore  = $child->academicResults->avg('score');
            $topCareer = $child->recommendations->where('type','career')->sortByDesc('confidence_score')->first();
            $uniElig   = $child->recommendations->where('type','university_program')->where('confidence_score','>=',80)->count();
            $avgColor  = $avgScore >= 70 ? 'green' : ($avgScore >= 50 ? 'yellow' : 'red');
        @endphp
        <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
            {{-- Child summary row --}}
            <div class="flex items-center gap-4 p-5">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shrink-0">
                    {{ strtoupper(substr($child->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-black/80 text-base">{{ $child->name }}</h2>
                    <p class="text-xs text-black/50">
                        {{ $child->studentProfile?->form_level ?? '' }}
                        @if($child->studentProfile?->stream)
                            &nbsp;·&nbsp; {{ $child->studentProfile->stream }} Stream
                        @endif
                    </p>
                </div>
                {{-- Quick stats --}}
                <div class="hidden sm:flex items-center gap-6 text-center shrink-0">
                    <div>
                        <p class="text-sm font-bold {{ $avgScore ? 'text-' . $avgColor . '-600' : 'text-black/30' }}">
                            {{ $avgScore ? round($avgScore) . '%' : '—' }}
                        </p>
                        <p class="text-xs text-black/40">Avg Score</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold {{ $assessed ? 'text-green-600' : 'text-black/30' }}">
                            {{ $assessed ? __('parent.done') : __('parent.pending') }}
                        </p>
                        <p class="text-xs text-black/40">Assessment</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-600">{{ $uniElig }}</p>
                        <p class="text-xs text-black/40">Uni Eligible</p>
                    </div>
                </div>
            </div>

            {{-- Career & download --}}
            <div class="px-5 pb-5 border-t border-black/5 pt-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="text-sm">
                        <span class="text-black/40">{{ __('parent.top_career') }}: </span>
                        <span class="font-semibold text-black/70">
                            {{ $topCareer?->recommended?->title ?? __('parent.no_assessment_short') }}
                        </span>
                        @if($topCareer)
                        <span class="ml-2 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold">
                            {{ round($topCareer->confidence_score) }}% {{ __('parent.match') }}
                        </span>
                        @endif
                    </div>
                </div>
                {{-- Download buttons row --}}
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('parent.reports.download', $child) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('parent.download_pdf') }} — {{ __('parent.full_report') }}
                    </a>
                    <a href="{{ route('parent.reports.download', $child) }}?type=career"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-blue-200 hover:bg-blue-50 text-blue-700 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('parent.career_recommendations') }}
                    </a>
                    <a href="{{ route('parent.reports.download', $child) }}?type=subjects"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-blue-200 hover:bg-blue-50 text-blue-700 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        {{ __('parent.subject_recommendations') }}
                    </a>
                </div>
            </div>

            {{-- Academic results preview --}}
            @if($child->academicResults->isNotEmpty())
            <div class="px-5 pb-5">
                <div class="border border-black/5 rounded-xl overflow-hidden">
                    <div class="px-4 py-2 bg-black/[0.02] border-b border-black/5">
                        <p class="text-xs font-semibold text-black/50 uppercase tracking-wide">Recent Results</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 p-3">
                        @foreach($child->academicResults->sortByDesc('updated_at')->take(8) as $result)
                        @php $s = $result->score; $c = $s >= 70 ? 'green' : ($s >= 50 ? 'yellow' : 'red'); @endphp
                        <div class="bg-white border border-black/5 rounded-xl p-3 text-center">
                            <p class="text-xs text-black/50 truncate mb-1">{{ $result->subject?->name ?? '—' }}</p>
                            <p class="font-bold text-{{ $c }}-600 text-sm">{{ $s }}%</p>
                            <p class="text-xs text-black/30">{{ $result->grade }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
